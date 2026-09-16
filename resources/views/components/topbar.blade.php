<header class="topbar">

    {{-- KIRI --}}
    <div class="topbar-left">

        <div class="page-heading">

@php
    $routeName = request()->route()?->getName();

    $pageTitles = [
        'dashboard'       => 'Dashboard',

        'customers.index' => 'Master Data Nasabah',
        'customers.create' => 'Tambah Nasabah',
        'customers.edit'  => 'Edit Nasabah',

        'transactions.index' => 'Transaksi',
        'kyc.index'          => 'KYC & Compliance',

        'currencies.index'   => 'Mata Uang',
        'rates.index'        => 'Kurs & Rate',
        'cash-bank.index'    => 'Kas & Bank',
        'stocks.index'       => 'Stok Valas',
        'reports.index'      => 'Laporan',
        'accounting.index'   => 'Accounting',
        'settings.index'     => 'Pengaturan',
    ];

    $pageTitle = trim($__env->yieldContent('page-title')) ?: ($pageTitles[$routeName] ?? '');
@endphp

<div class="page-title">
    {{ $pageTitle }}
</div>

            <div class="page-subtitle">
                Money Changer Digital OS
            </div>

        </div>

    </div>


    {{-- KANAN --}}
    <div class="topbar-right">

        {{-- WEATHER --}}
        <div class="weather-widget">

            <div class="weather-icon">
                ☁️
            </div>

            <div class="weather-data">

                <div class="weather-temperature">
                    28°
                </div>

                <div class="weather-city">
                    Jakarta
                </div>

            </div>

        </div>


        <div class="topbar-divider"></div>


        {{-- TANGGAL --}}
        <div class="topbar-info">

            <span class="topbar-date">
                {{ now()->locale('id')->translatedFormat('l, d F Y') }}
            </span>

            <span class="topbar-branch">
                {{ auth()->user()->branch?->name ?? 'Cabang belum dipilih' }}
            </span>

        </div>


        {{-- NOTIFIKASI --}}
        <button
            type="button"
            class="topbar-icon"
            title="Notifikasi"
        >
            🔔
        </button>


        {{-- PENGATURAN --}}
        <button
            type="button"
            class="topbar-icon"
            title="Pengaturan"
        >
            ⚙
        </button>


        {{-- USER MENU --}}
        <div class="topbar-user-wrapper">

            <button
                type="button"
                class="topbar-user"
                id="userMenuButton"
                aria-expanded="false"
            >

                <div class="user-avatar">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                </div>

                <div class="user-info">

                    <div class="user-name">
                        {{ auth()->user()->name ?? 'User' }}
                    </div>

                    <div class="user-role">
                        Administrator
                    </div>

                </div>

                <span class="user-chevron">
                    ▾
                </span>

            </button>


            {{-- DROPDOWN --}}
            <div
                class="user-dropdown"
                id="userDropdown"
            >

                <div class="dropdown-user-header">

                    <div class="dropdown-avatar">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>

                    <div>

                        <div class="dropdown-user-name">
                            {{ auth()->user()->name ?? 'User' }}
                        </div>

                        <div class="dropdown-user-email">
                            {{ auth()->user()->email ?? '-' }}
                        </div>

                    </div>

                </div>


                <div class="dropdown-divider"></div>


                <div class="dropdown-context">

                    <div class="context-row">

                        <span>
                            Tenant
                        </span>

                        <strong>
                            {{ auth()->user()->tenant?->name ?? '-' }}
                        </strong>

                    </div>

                    <div class="context-row">

                        <span>
                            Cabang
                        </span>

                        <strong>
                            {{ auth()->user()->branch?->name ?? '-' }}
                        </strong>

                    </div>

                </div>


                <div class="dropdown-divider"></div>


                {{-- LOGOUT --}}
                <form
                    method="POST"
                    action="{{ route('logout') }}"
                >

                    @csrf

                    <button
                        type="submit"
                        class="logout-button"
                    >
                        <span>↪</span>
                        <span>Keluar</span>
                    </button>

                </form>

            </div>

        </div>

    </div>

</header>


<style>

/* =====================================================
   TOPBAR
===================================================== */

.topbar {

    min-height: 72px;

    background: #ffffff;

    border-bottom: 1px solid #e5e7eb;

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 0 30px;

    position: sticky;

    top: 0;

    z-index: 50;

}


/* =====================================================
   LEFT
===================================================== */

.topbar-left {
    min-width: 0;
}

.page-heading {

    display: flex;

    flex-direction: column;

    gap: 3px;

}

.page-title {

    font-size: 20px;

    font-weight: 800;

    color: #0f5139;

    line-height: 1.2;

}

.page-subtitle {

    font-size: 12px;

    color: #7a8982;

    letter-spacing: .2px;

}


/* =====================================================
   RIGHT
===================================================== */

.topbar-right {

    display: flex;

    align-items: center;

    gap: 10px;

}


/* =====================================================
   WEATHER
===================================================== */

.weather-widget {

    height: 42px;

    display: flex;

    align-items: center;

    gap: 8px;

    padding: 0 11px;

    border-radius: 9px;

    background: #e7f5ef;

    border: 1px solid #d2eee3;

}

.weather-icon {

    width: 30px;

    height: 30px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 8px;

    background: #ffffff;

    font-size: 16px;

}

.weather-data {

    display: flex;

    flex-direction: column;

    justify-content: center;

    line-height: 1;

}

.weather-temperature {

    font-size: 12px;

    font-weight: 800;

    color: #111827;

}

.weather-city {

    margin-top: 4px;

    font-size: 9px;

    font-weight: 750;

    color: #147957;

}


/* =====================================================
   DIVIDER
===================================================== */

.topbar-divider {

    width: 1px;

    height: 32px;

    background: #e5e7eb;

    margin: 0 2px;

}


/* =====================================================
   DATE
===================================================== */

.topbar-info {

    display: flex;

    flex-direction: column;

    align-items: flex-end;

    margin-right: 6px;

}

.topbar-date {

    font-size: 11px;

    color: #6b7280;

    white-space: nowrap;

}

.topbar-branch {

    font-size: 10px;

    color: #b08a45;

    font-weight: 700;

    margin-top: 3px;

}


/* =====================================================
   ICON BUTTON
===================================================== */

.topbar-icon {

    width: 36px;

    height: 36px;

    display: flex;

    align-items: center;

    justify-content: center;

    border: 1px solid #e5e7eb;

    border-radius: 8px;

    background: #ffffff;

    cursor: pointer;

    font-size: 15px;

}

.topbar-icon:hover {

    background: #f9fafb;

    border-color: #d1d5db;

}


/* =====================================================
   USER WRAPPER
===================================================== */

.topbar-user-wrapper {

    position: relative;

    margin-left: 6px;

}


/* =====================================================
   USER BUTTON
===================================================== */

.topbar-user {

    display: flex;

    align-items: center;

    gap: 9px;

    padding: 6px 8px 6px 10px;

    border: 1px solid transparent;

    border-radius: 10px;

    background: transparent;

    cursor: pointer;

    text-align: left;

}

.topbar-user:hover {

    background: #f9fafb;

    border-color: #e5e7eb;

}


.user-avatar {

    width: 34px;

    height: 34px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 50%;

    background: #111827;

    color: #ffffff;

    font-size: 12px;

    font-weight: 800;

}


.user-info {

    min-width: 80px;

}


.user-name {

    font-size: 11px;

    font-weight: 750;

    color: #111827;

}


.user-role {

    font-size: 9px;

    color: #9ca3af;

    margin-top: 2px;

}


.user-chevron {

    color: #9ca3af;

    font-size: 12px;

    margin-left: 2px;

}


/* =====================================================
   USER DROPDOWN
===================================================== */

.user-dropdown {

    position: absolute;

    top: calc(100% + 8px);

    right: 0;

    width: 290px;

    background: #ffffff;

    border: 1px solid #e5e7eb;

    border-radius: 14px;

    box-shadow:
        0 20px 45px rgba(15, 23, 42, .14),
        0 4px 12px rgba(15, 23, 42, .06);

    padding: 8px;

    display: none;

    z-index: 100;

}

.user-dropdown.show {

    display: block;

}


/* =====================================================
   DROPDOWN HEADER
===================================================== */

.dropdown-user-header {

    display: flex;

    align-items: center;

    gap: 11px;

    padding: 10px;

}

.dropdown-avatar {

    width: 42px;

    height: 42px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 12px;

    background: #ecfdf5;

    color: #047857;

    font-weight: 800;

    font-size: 15px;

}

.dropdown-user-name {

    font-size: 13px;

    font-weight: 800;

    color: #111827;

}

.dropdown-user-email {

    margin-top: 3px;

    font-size: 10px;

    color: #9ca3af;

}


/* =====================================================
   DIVIDER
===================================================== */

.dropdown-divider {

    height: 1px;

    background: #edf0ee;

    margin: 6px 0;

}


/* =====================================================
   CONTEXT
===================================================== */

.dropdown-context {

    padding: 5px 10px;

}

.context-row {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 12px;

    padding: 7px 0;

}

.context-row span {

    color: #9ca3af;

    font-size: 10px;

}

.context-row strong {

    color: #374151;

    font-size: 10px;

    text-align: right;

}


/* =====================================================
   LOGOUT
===================================================== */

.logout-button {

    width: 100%;

    height: 40px;

    display: flex;

    align-items: center;

    gap: 9px;

    padding: 0 11px;

    border: 0;

    border-radius: 9px;

    background: transparent;

    color: #b91c1c;

    font-size: 12px;

    font-weight: 750;

    cursor: pointer;

    text-align: left;

}

.logout-button:hover {

    background: #fef2f2;

}


/* =====================================================
   RESPONSIVE
===================================================== */

@media (max-width: 950px) {

    .weather-data {
        display: none;
    }

    .weather-widget {
        padding: 0 6px;
    }

}

@media (max-width: 800px) {

    .topbar {
        padding: 0 16px;
    }

    .weather-widget,
    .topbar-info {
        display: none;
    }

    .user-info {
        display: none;
    }

    .topbar-user {
        border-left: 0;
        padding-left: 0;
    }

}

</style>


<script>

document.addEventListener('DOMContentLoaded', function () {

    const button = document.getElementById('userMenuButton');

    const dropdown = document.getElementById('userDropdown');

    if (!button || !dropdown) {
        return;
    }

    button.addEventListener('click', function (event) {

        event.stopPropagation();

        const isOpen = dropdown.classList.toggle('show');

        button.setAttribute(
            'aria-expanded',
            isOpen ? 'true' : 'false'
        );

    });


    document.addEventListener('click', function (event) {

        if (
            !dropdown.contains(event.target) &&
            !button.contains(event.target)
        ) {

            dropdown.classList.remove('show');

            button.setAttribute(
                'aria-expanded',
                'false'
            );

        }

    });

});

</script>



