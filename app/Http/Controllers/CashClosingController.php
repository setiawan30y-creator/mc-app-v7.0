<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\CashClosing;
use App\Models\CashClosingBank;
use App\Models\CurrencyDenomination;
use App\Services\CashClosingReconciliationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CashClosingController extends Controller
{
    public function __construct(private CashClosingReconciliationService $reconciliation) {}

    public function index(Request $request)
    {
        $user = auth()->user(); $query = CashClosing::query()->with(['preparedBy','closedBy','approvedBy'])->where('tenant_id',$user->tenant_id);
        if ($user->branch_id) $query->where('branch_id',$user->branch_id);
        if ($request->filled('date')) $query->whereDate('business_date',$request->date);
        if ($request->filled('shift')) $query->where('shift',$request->shift);
        $closings=$query->orderByDesc('business_date')->orderByDesc('closing_date')->paginate(20)->withQueryString();
        return view('closing.index',compact('closings'));
    }

    public function create(Request $request)
    {
        $user=auth()->user(); $businessDate=$request->input('business_date',now()->toDateString()); $shift=$request->input('shift','morning');
        $denominations=CurrencyDenomination::query()->with(['variant.currency'])->active()->whereHas('variant.currency',fn($query)=>$query->where('code','IDR'))->ordered()->get();
        $bankAccounts=BankAccount::query()->active()->where('tenant_id',$user->tenant_id)->when($user->branch_id,fn($q)=>$q->where('branch_id',$user->branch_id))->with('currency')->orderBy('bank_name')->orderBy('account_name')->get();
        $existing=CashClosing::query()->where('tenant_id',$user->tenant_id)->when($user->branch_id,fn($q)=>$q->where('branch_id',$user->branch_id))->whereDate('business_date',$businessDate)->where('shift',$shift)->whereIn('status',['draft','submitted','approved','closed'])->latest('closing_date')->first();
        $preview=$this->reconciliation->calculate($user->tenant_id,$user->branch_id,$businessDate,$shift);
        return view('closing.create',compact('denominations','bankAccounts','businessDate','shift','existing','preview'));
    }

    public function store(Request $request)
    {
        $user=auth()->user();
        $validated=$request->validate(['business_date'=>['required','date'],'shift'=>['required','in:morning,afternoon'],'closing_type'=>['required','in:shift_handover,end_of_day'],'notes'=>['nullable','string','max:5000'],'physical_bank_amount'=>['nullable','numeric','min:0'],'bank_physical'=>['nullable','array'],'bank_physical.*'=>['nullable','numeric','min:0'],'bank_notes'=>['nullable','array'],'physical_quantity'=>['nullable','array'],'physical_quantity.*'=>['nullable','numeric','min:0']]);
        $alreadyClosed=CashClosing::query()->where('tenant_id',$user->tenant_id)->when($user->branch_id,fn($q)=>$q->where('branch_id',$user->branch_id))->whereDate('business_date',$validated['business_date'])->where('shift',$validated['shift'])->whereIn('status',['submitted','approved','closed'])->exists();
        if($alreadyClosed) return back()->withInput()->withErrors(['shift'=>'Closing untuk tanggal dan shift tersebut sudah dibuat dan tidak dapat dibuat ulang.']);
        $closing=DB::transaction(function() use($request,$validated,$user){
            $prefix='CLS-'.str_replace('-','',$validated['business_date']); $sequence=CashClosing::query()->where('tenant_id',$user->tenant_id)->where('branch_id',$user->branch_id)->whereDate('business_date',$validated['business_date'])->count()+1;
            $closing=CashClosing::create(['id'=>(string)Str::ulid(),'tenant_id'=>$user->tenant_id,'branch_id'=>$user->branch_id,'business_date'=>$validated['business_date'],'shift'=>$validated['shift'],'closing_type'=>$validated['closing_type'],'closing_no'=>$prefix.'-'.str_pad((string)$sequence,2,'0',STR_PAD_LEFT),'closing_date'=>now(),'status'=>'draft','notes'=>$validated['notes']??null,'prepared_by'=>$user->id,'bank_physical_amount'=>(float)($validated['physical_bank_amount']??0)]);

            $bankPhysical=$request->input('bank_physical',[]); $bankNotes=$request->input('bank_notes',[]);
            $accounts=BankAccount::query()->active()->where('tenant_id',$user->tenant_id)->when($user->branch_id,fn($q)=>$q->where('branch_id',$user->branch_id))->whereIn('id',array_keys($bankPhysical))->get();
            foreach($accounts as $account){
                $physical=(float)($bankPhysical[$account->id]??0); $system=(float)$account->calculated_balance;
                CashClosingBank::create(['id'=>(string)Str::ulid(),'cash_closing_id'=>$closing->id,'bank_account_id'=>$account->id,'system_amount'=>$system,'physical_amount'=>$physical,'difference_amount'=>round($physical-$system,2),'notes'=>$bankNotes[$account->id]??null]);
            }
            $bankTotal=$closing->bankDetails()->sum('physical_amount');
            if($closing->bankDetails()->count()>0) $closing->update(['bank_physical_amount'=>$bankTotal]);

            $quantities=$request->input('physical_quantity',[]); $denominations=CurrencyDenomination::query()->with(['variant.currency'])->whereIn('id',array_keys($quantities))->whereHas('variant.currency',fn($query)=>$query->where('code','IDR'))->get()->keyBy('id');
            foreach($quantities as $denominationId=>$quantity){$quantity=(float)$quantity;if($quantity<=0||!isset($denominations[$denominationId]))continue;$denomination=$denominations[$denominationId];$amount=round($quantity*(float)$denomination->value,2);$closing->details()->create(['id'=>(string)Str::ulid(),'currency_id'=>$denomination->variant->currency_id,'currency_variant_id'=>$denomination->currency_variant_id,'currency_denomination_id'=>$denomination->id,'system_quantity'=>0,'physical_quantity'=>$quantity,'difference_quantity'=>0,'system_amount'=>0,'physical_amount'=>$amount,'difference_amount'=>0,'adjustment_status'=>'none','created_by'=>$user->id]);}
            $closing->update(['physical_cash_amount'=>$closing->details()->sum('physical_amount'),'physical_amount'=>$closing->details()->sum('physical_amount')]);
            return $this->reconciliation->apply($closing);
        });
        return redirect()->route('closing.show',$closing)->with('success','Draft closing berhasil dibuat dan rekonsiliasi Kas, Bank per rekening, serta stok valas sudah dihitung.');
    }

    public function refresh(CashClosing $closing){$this->authorizeScope($closing);abort_if(in_array($closing->status,['approved','closed']),422,'Closing yang sudah disetujui tidak dapat dihitung ulang.');$this->reconciliation->apply($closing);return back()->with('success','Rekonsiliasi closing berhasil dihitung ulang dari ledger terbaru.');}

    public function submit(CashClosing $closing){$this->authorizeScope($closing);abort_if($closing->isClosed(),422,'Closing sudah ditutup.');abort_if($closing->status!=='draft',422,'Hanya draft yang dapat diajukan.');$closing=$this->reconciliation->apply($closing);$closing->update(['status'=>'submitted']);return back()->with('success','Closing berhasil diajukan untuk approval.');}

    public function approve(CashClosing $closing){$this->authorizeScope($closing);abort_if($closing->isClosed(),422,'Closing sudah ditutup.');abort_if($closing->status!=='submitted',422,'Closing harus berstatus submitted sebelum approval.');$closing=$this->reconciliation->apply($closing);if(!$this->reconciliation->canClose($closing))return back()->withErrors(['closing'=>'Closing belum balance. Kas, setiap rekening bank, dan stok valas harus balance sebelum approval.']);$closing->update(['status'=>'approved','approved_by'=>auth()->id(),'approved_at'=>now(),'rejected_at'=>null,'rejection_reason'=>null]);return back()->with('success','Closing berhasil disetujui. Selanjutnya closing dapat dikunci.');}

    public function reject(Request $request, CashClosing $closing){$this->authorizeScope($closing);abort_if($closing->isClosed(),422,'Closing yang sudah ditutup tidak dapat ditolak.');abort_if(!in_array($closing->status,['submitted','approved']),422,'Closing belum dapat ditolak.');$validated=$request->validate(['rejection_reason'=>['required','string','max:2000']]);$closing->update(['status'=>'rejected','rejected_at'=>now(),'rejection_reason'=>$validated['rejection_reason'],'approved_by'=>null,'approved_at'=>null]);return back()->with('success','Closing ditolak dan dapat diperbaiki lalu diajukan kembali.');}

    public function close(CashClosing $closing){$this->authorizeScope($closing);abort_if($closing->isClosed(),422,'Closing sudah ditutup.');abort_if($closing->status!=='approved',422,'Closing harus berstatus approved sebelum dikunci.');$closing=$this->reconciliation->apply($closing);if(!$this->reconciliation->canClose($closing))return back()->withErrors(['closing'=>'Closing tidak lagi balance. Approval dibatalkan secara manual melalui workflow koreksi sebelum dapat dikunci.']);$closing->update(['status'=>'closed','closed_by'=>auth()->id(),'closed_at'=>now()]);return redirect()->route('closing.show',$closing)->with('success','Closing berhasil ditutup dan dikunci.');}

    public function show(CashClosing $closing){$this->authorizeScope($closing);if(!$closing->isClosed()&&!$closing->isApproved())$this->reconciliation->apply($closing);$closing->load(['details.currency','details.currencyVariant','details.currencyDenomination','bankDetails.bankAccount','preparedBy','approvedBy','closedBy']);return view('closing.show',compact('closing'));}
    private function authorizeScope(CashClosing $closing):void{$user=auth()->user();abort_unless($closing->tenant_id===$user->tenant_id&&(!$user->branch_id||$closing->branch_id===$user->branch_id),403);}
}
