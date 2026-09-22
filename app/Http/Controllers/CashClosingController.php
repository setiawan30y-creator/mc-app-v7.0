<?php

namespace App\Http\Controllers;

use App\Models\CashClosing;
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
        $user = auth()->user(); $query = CashClosing::query()->with(['preparedBy','closedBy'])->where('tenant_id',$user->tenant_id);
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
        $existing=CashClosing::query()->where('tenant_id',$user->tenant_id)->when($user->branch_id,fn($q)=>$q->where('branch_id',$user->branch_id))->whereDate('business_date',$businessDate)->where('shift',$shift)->whereIn('status',['draft','submitted','approved','closed'])->latest('closing_date')->first();
        $preview=$this->reconciliation->calculate($user->tenant_id,$user->branch_id,$businessDate,$shift);
        return view('closing.create',compact('denominations','businessDate','shift','existing','preview'));
    }

    public function store(Request $request)
    {
        $user=auth()->user();
        $validated=$request->validate(['business_date'=>['required','date'],'shift'=>['required','in:morning,afternoon'],'closing_type'=>['required','in:shift_handover,end_of_day'],'notes'=>['nullable','string','max:5000'],'physical_bank_amount'=>['nullable','numeric','min:0'],'physical_quantity'=>['nullable','array'],'physical_quantity.*'=>['nullable','numeric','min:0']]);
        $alreadyClosed=CashClosing::query()->where('tenant_id',$user->tenant_id)->when($user->branch_id,fn($q)=>$q->where('branch_id',$user->branch_id))->whereDate('business_date',$validated['business_date'])->where('shift',$validated['shift'])->whereIn('status',['submitted','approved','closed'])->exists();
        if($alreadyClosed) return back()->withInput()->withErrors(['shift'=>'Closing untuk tanggal dan shift tersebut sudah dibuat dan tidak dapat dibuat ulang.']);
        $closing=DB::transaction(function() use($request,$validated,$user){
            $prefix='CLS-'.str_replace('-','',$validated['business_date']); $sequence=CashClosing::query()->where('tenant_id',$user->tenant_id)->where('branch_id',$user->branch_id)->whereDate('business_date',$validated['business_date'])->count()+1;
            $closing=CashClosing::create(['id'=>(string)Str::ulid(),'tenant_id'=>$user->tenant_id,'branch_id'=>$user->branch_id,'business_date'=>$validated['business_date'],'shift'=>$validated['shift'],'closing_type'=>$validated['closing_type'],'closing_no'=>$prefix.'-'.str_pad((string)$sequence,2,'0',STR_PAD_LEFT),'closing_date'=>now(),'status'=>'draft','notes'=>$validated['notes']??null,'prepared_by'=>$user->id,'bank_physical_amount'=>(float)($validated['physical_bank_amount']??0)]);
            $quantities=$request->input('physical_quantity',[]); $denominations=CurrencyDenomination::query()->with(['variant.currency'])->whereIn('id',array_keys($quantities))->whereHas('variant.currency',fn($query)=>$query->where('code','IDR'))->get()->keyBy('id');
            foreach($quantities as $denominationId=>$quantity){$quantity=(float)$quantity;if($quantity<=0||!isset($denominations[$denominationId]))continue;$denomination=$denominations[$denominationId];$amount=round($quantity*(float)$denomination->value,2);$closing->details()->create(['id'=>(string)Str::ulid(),'currency_id'=>$denomination->variant->currency_id,'currency_variant_id'=>$denomination->currency_variant_id,'currency_denomination_id'=>$denomination->id,'system_quantity'=>0,'physical_quantity'=>$quantity,'difference_quantity'=>0,'system_amount'=>0,'physical_amount'=>$amount,'difference_amount'=>0,'adjustment_status'=>'none','created_by'=>$user->id]);}
            $closing->update(['physical_cash_amount'=>$closing->details()->sum('physical_amount'),'physical_amount'=>$closing->details()->sum('physical_amount')]);
            return $this->reconciliation->apply($closing);
        });
        return redirect()->route('closing.show',$closing)->with('success','Draft closing berhasil dibuat dan saldo kas, bank, serta stok valas sudah dihitung dari ledger.');
    }

    public function refresh(CashClosing $closing){$this->authorizeScope($closing);abort_if($closing->isClosed(),422,'Closing yang sudah ditutup tidak dapat dihitung ulang.');$this->reconciliation->apply($closing);return back()->with('success','Rekonsiliasi closing berhasil dihitung ulang dari ledger terbaru.');}

    public function close(CashClosing $closing){$this->authorizeScope($closing);abort_if($closing->isClosed(),422,'Closing sudah ditutup.');abort_if($closing->status==='rejected',422,'Closing yang ditolak tidak dapat ditutup.');$closing=$this->reconciliation->apply($closing);if(!$this->reconciliation->canClose($closing))return back()->withErrors(['closing'=>'Closing belum balance. Kas, bank, dan stok valas harus balance sebelum closing dapat dikunci.']);$closing->update(['status'=>'closed','closed_by'=>auth()->id(),'closed_at'=>now()]);return redirect()->route('closing.show',$closing)->with('success','Closing berhasil ditutup dan dikunci.');}

    public function show(CashClosing $closing){$this->authorizeScope($closing);if(!$closing->isClosed())$this->reconciliation->apply($closing);$closing->load(['details.currency','details.currencyVariant','details.currencyDenomination','preparedBy','closedBy']);return view('closing.show',compact('closing'));}
    private function authorizeScope(CashClosing $closing):void{$user=auth()->user();abort_unless($closing->tenant_id===$user->tenant_id&&(!$user->branch_id||$closing->branch_id===$user->branch_id),403);}
}
