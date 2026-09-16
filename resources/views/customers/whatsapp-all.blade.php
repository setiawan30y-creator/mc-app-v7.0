@extends('layouts.app')

@section('title', 'WhatsApp Semua Nasabah')
@section('page-title', 'WhatsApp Semua Nasabah')

@section('content')
<style>
    .wa-page { width:100%; margin:0 20px; }
    .wa-card { background:#fff; border:1px solid #e5e7eb; border-radius:10px; overflow:hidden; }
    .wa-head { padding:16px; background:#f4f9f6; border-bottom:1px solid #dfe9e3; }
    .wa-head h2 { margin:0; font-size:18px; color:#123b2a; }
    .wa-head p { margin:5px 0 0; font-size:12px; color:#6b7280; }
    .wa-list { padding:12px; }
    .wa-row { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:9px 0; border-bottom:1px solid #edf0ee; }
    .wa-name { font-size:12px; font-weight:700; color:#17251e; }
    .wa-phone { font-size:11px; color:#6b7280; }
    .wa-btn { display:inline-flex; padding:6px 10px; border-radius:5px; background:#eaf7ef; border:1px solid #b9dfc8; color:#176b4d; text-decoration:none; font-size:11px; font-weight:700; }
    .wa-note { margin:12px; padding:10px; background:#fff7ed; border:1px solid #fed7aa; border-radius:7px; color:#9a3412; font-size:11px; }
</style>

<div class="wa-page">
    <div class="wa-card">
        <div class="wa-head">
            <h2>WhatsApp Semua Nasabah</h2>
            <p>{{ $customers->count() }} nasabah dengan nomor WhatsApp tersedia.</p>
        </div>

        <div class="wa-note">
            WhatsApp resmi tidak menyediakan URL yang dapat mengirim pesan ke banyak kontak
            sekaligus tanpa tindakan pengguna. Halaman ini menyiapkan kontak satu per satu.
            Untuk pengiriman massal sekali klik, nantinya kita sambungkan ke WhatsApp Gateway.
        </div>

        <div class="wa-list">
            @forelse($customers as $customer)
                @php
                    $number = preg_replace('/\D+/', '', (string) $customer->phone);
                    if (str_starts_with($number, '0')) {
                        $number = '62' . substr($number, 1);
                    }
                @endphp

                <div class="wa-row">
                    <div>
                        <div class="wa-name">{{ $customer->full_name }}</div>
                        <div class="wa-phone">{{ $customer->phone }}</div>
                    </div>

                    <a
                        href="https://wa.me/{{ $number }}"
                        target="_blank"
                        rel="noopener"
                        class="wa-btn"
                    >
                        Buka WA
                    </a>
                </div>
            @empty
                <div style="padding:20px;text-align:center;color:#9ca3af;">
                    Tidak ada nasabah dengan nomor HP.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
