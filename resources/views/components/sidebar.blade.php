<aside class="sidebar">

    {{-- SIDEBAR TOGGLE --}}
    <button
        type="button"
        class="sidebar-toggle-button"
        id="sidebarToggleButton"
        aria-label="Sembunyikan sidebar"
        title="Sembunyikan sidebar"
    >
        ‹
    </button>

    {{-- BRAND --}}
    <div class="brand">
        <div class="brand-mark">
            MA
        </div>

        <div class="brand-text">
            <div class="brand-name">MC ALMARA</div>
            <div class="brand-subtitle">Money Changer Digital OS</div>
        </div>
    </div>


    {{-- TENANT --}}
    <div class="tenant-box">
        <div class="tenant-label">TENANT</div>

        <div class="tenant-name">
            {{ auth()->user()->tenant->name ?? 'MC Almara' }}
        </div>
    </div>


    {{-- NAVIGATION --}}
    <nav class="sidebar-nav">

        {{-- WORKSPACE --}}
        <div class="nav-section">

            <div class="nav-section-title">
                WORKSPACE
            </div>

            <a href="/" class="nav-item {{ request()->is('/') ? 'active' : '' }}">
                <span class="nav-icon">⌂</span>
                <span class="nav-text">Dashboard</span>
            </a>

            <a href="#" class="nav-item">
                <span class="nav-icon">⇄</span>
                <span class="nav-text">Transaksi</span>
            </a>

            <a
                    href="{{ route('customers.index') }}"
                    class="nav-item {{ request()->routeIs('customers.*') ? 'active' : '' }}"
            >
            <span class="nav-icon">♙</span>
            <span class="nav-text">Customer</span>
            </a>

            <a href="#" class="nav-item">
                <span class="nav-icon">✓</span>
                <span class="nav-text">KYC & Compliance</span>
            </a>

        </div>


        {{-- TREASURY --}}
        <div class="nav-section">

            <div class="nav-section-title">
                TREASURY
            </div>

            <a href="#" class="nav-item">
                <span class="nav-icon">◎</span>
                <span class="nav-text">Mata Uang</span>
            </a>

            <a href="#" class="nav-item">
                <span class="nav-icon">↗</span>
                <span class="nav-text">Kurs & Rate</span>
            </a>

            <a href="#" class="nav-item">
                <span class="nav-icon">▣</span>
                <span class="nav-text">Kas & Bank</span>
            </a>

            <a href="#" class="nav-item">
                <span class="nav-icon">▤</span>
                <span class="nav-text">Stok Valas</span>
            </a>

        </div>


        {{-- MANAGEMENT --}}
        <div class="nav-section">

            <div class="nav-section-title">
                MANAGEMENT
            </div>

            <a href="#" class="nav-item">
                <span class="nav-icon">▥</span>
                <span class="nav-text">Laporan</span>
            </a>

            <a href="#" class="nav-item">
                <span class="nav-icon">▤</span>
                <span class="nav-text">Accounting</span>
            </a>

            <a href="#" class="nav-item">
                <span class="nav-icon">⚙</span>
                <span class="nav-text">Pengaturan</span>
            </a>

        </div>

    </nav>


    {{-- USER FOOTER --}}
    <div class="sidebar-footer">

        <button
            type="button"
            class="sidebar-user-button"
            id="sidebarUserButton"
            aria-expanded="false"
        >

            <div class="footer-avatar">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
            </div>

            <div class="footer-user">

                <div class="footer-name">
                    {{ auth()->user()->name ?? 'User' }}
                </div>

                <div class="footer-role">
                    {{ auth()->user()->roles()->first()->name ?? 'User' }}
                </div>

            </div>

            <div class="footer-logout">
                ↪
            </div>

        </button>


        {{-- USER POPUP --}}
        <div
            class="sidebar-user-menu"
            id="sidebarUserMenu"
        >

            {{-- USER INFO --}}
            <div class="sidebar-user-menu-header">

                <div class="sidebar-user-menu-avatar">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                </div>

                <div class="sidebar-user-menu-info">

                    <div class="sidebar-user-menu-name">
                        {{ auth()->user()->name ?? 'User' }}
                    </div>

                    <div class="sidebar-user-menu-email">
                        {{ auth()->user()->email ?? '' }}
                    </div>

                </div>

            </div>


            <div class="sidebar-user-menu-divider"></div>


            {{-- PROFILE --}}
            <a
                href="#"
                class="sidebar-user-menu-item"
            >
                <span class="menu-item-icon">👤</span>
                <span>Profil Saya</span>
            </a>


            {{-- TENANT & BRANCH --}}
            <a
                href="#"
                class="sidebar-user-menu-item"
            >
                <span class="menu-item-icon">🏢</span>
                <span>Tenant & Cabang</span>
            </a>


            {{-- SETTINGS --}}
            <a
                href="#"
                class="sidebar-user-menu-item"
            >
                <span class="menu-item-icon">⚙</span>
                <span>Pengaturan</span>
            </a>


            {{-- SECURITY --}}
            <a
                href="#"
                class="sidebar-user-menu-item"
            >
                <span class="menu-item-icon">🔐</span>
                <span>Keamanan</span>
            </a>


            <div class="sidebar-user-menu-divider"></div>


            {{-- LOGOUT --}}
            <form
                method="POST"
                action="{{ route('logout') }}"
            >
                @csrf

                <button
                    type="submit"
                    class="sidebar-user-menu-item logout-item"
                >
                    <span class="menu-item-icon">🚪</span>
                    <span>Keluar</span>
                </button>

            </form>

        </div>

    </div>

</aside>


<style>

    /* =========================================================
       MC ALMARA SIDEBAR
       Emerald Green + Gold
       ========================================================= */

    .sidebar {
        position: fixed;

        top: 0;
        left: 0;

        width: 250px;
        height: 100vh;

        display: flex;
        flex-direction: column;

        color: #f5faf7;

        background:
            radial-gradient(
                circle at 10% 8%,
                rgba(6, 77, 46, 0.3),
                transparent 32%
            ),
            radial-gradient(
                circle at 95% 42%,
                rgba(4, 75, 48, 0.22),
                transparent 35%
            ),
            linear-gradient(
                180deg,
                #176B50 0%,
                #12654B 38%,
                #0E5A43 70%,
                #0B503B 100%
            );

        border-right: 1px solid rgba(255,255,255,.10);

        box-shadow:
            4px 0 22px rgba(9, 53, 39, .12);

        overflow: visible;

        z-index: 100;
    }


    /* =========================================================
       DECORATIVE GRADIENT
       ========================================================= */

    .sidebar::before {
        content: "";

        position: absolute;

        width: 260px;
        height: 260px;

        right: -150px;
        bottom: 70px;

        background:
            linear-gradient(
                135deg,
                transparent 35%,
                rgba(224,196,122,.16) 36%,
                rgba(224,196,122,.08) 55%,
                transparent 56%
            );

        transform: rotate(-18deg);

        pointer-events: none;
    }


    /* =========================================================
       BRAND
       ========================================================= */

    .brand {
        display: flex;
        align-items: center;

        gap: 11px;

        padding: 23px 21px 19px;

        position: relative;
        z-index: 2;
    }


    .brand-mark {
        width: 38px;
        height: 38px;

        display: flex;
        align-items: center;
        justify-content: center;

        flex-shrink: 0;

        border-radius: 9px;

        background:
            linear-gradient(
                135deg,
                #E0C47A,
                #B08A45
            );

        color: #174D3A;

        font-size: 12px;
        font-weight: 900;

        letter-spacing: -1px;

        box-shadow:
            0 4px 12px rgba(0,0,0,.12);
    }


    .brand-name {
        color: #FFFFFF;

        font-size: 16px;

        font-weight: 850;

        letter-spacing: .9px;

        line-height: 1.1;
    }


    .brand-subtitle {
        margin-top: 4px;

        color: rgba(255,255,255,.64);

        font-size: 8px;

        white-space: nowrap;
    }


    /* =========================================================
       TENANT
       ========================================================= */

    .tenant-box {
        position: relative;
        z-index: 2;

        margin: 4px 16px 18px;

        padding: 14px 15px;

        border-radius: 10px;

        background:
            linear-gradient(
                135deg,
                rgba(255,255,255,.12),
                rgba(255,255,255,.055)
            );

        border: 1px solid rgba(255,255,255,.12);

        box-shadow:
            inset 0 1px 0 rgba(255,255,255,.05);
    }


    .tenant-label {
        color: rgba(255,255,255,.55);

        font-size: 9px;

        font-weight: 650;

        letter-spacing: .8px;
    }


    .tenant-name {
        margin-top: 6px;

        color: #FFFFFF;

        font-size: 13px;

        font-weight: 750;
    }


    /* =========================================================
       NAVIGATION
       ========================================================= */

    .sidebar-nav {
        position: relative;
        z-index: 2;

        flex: 1;

        overflow-y: auto;

        padding: 0 14px 15px;
    }


    .sidebar-nav::-webkit-scrollbar {
        width: 4px;
    }


    .sidebar-nav::-webkit-scrollbar-track {
        background: transparent;
    }


    .sidebar-nav::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,.18);

        border-radius: 20px;
    }


    .nav-section {
        margin-bottom: 20px;
    }


    .nav-section-title {
        padding: 0 11px 8px;

        color: rgba(255,255,255,.48);

        font-size: 9px;

        font-weight: 750;

        letter-spacing: .8px;
    }


    /* =========================================================
       MENU
       ========================================================= */

    .nav-item {
        position: relative;

        display: flex;
        align-items: center;

        gap: 12px;

        min-height: 42px;

        padding: 0 12px;

        margin-bottom: 3px;

        border-radius: 8px;

        color: rgba(255,255,255,.86);

        text-decoration: none;

        font-size: 12px;

        font-weight: 550;

        transition:
            background .18s ease,
            color .18s ease,
            transform .18s ease;
    }


    .nav-item:hover {
        background: rgba(255,255,255,.09);

        color: #FFFFFF;

        transform: translateX(1px);
    }


    /* =========================================================
       ACTIVE MENU
       ========================================================= */

    .nav-item.active {
        color: #F1D68F;

        font-weight: 750;

        background:
            linear-gradient(
                90deg,
                rgba(224,196,122,.26),
                rgba(224,196,122,.13),
                rgba(255,255,255,.04)
            );

        box-shadow:
            inset 3px 0 0 #E0C47A,
            0 3px 12px rgba(0,0,0,.06);
    }


    .nav-item.active::after {
        content: "";

        position: absolute;

        right: 10px;

        width: 5px;
        height: 5px;

        border-radius: 50%;

        background: #E0C47A;

        box-shadow:
            0 0 9px rgba(224,196,122,.75);
    }


    /* =========================================================
       ICON
       ========================================================= */

    .nav-icon {
        width: 19px;

        display: inline-flex;

        align-items: center;
        justify-content: center;

        flex-shrink: 0;

        color: rgba(255,255,255,.72);

        font-size: 14px;
    }


    .nav-item.active .nav-icon {
        color: #E0C47A;
    }


    /* =========================================================
       FOOTER
       ========================================================= */

    .sidebar-footer {
        position: relative;
        z-index: 20;

        flex-shrink: 0;

        display: flex;

        align-items: center;

        gap: 10px;

        padding: 13px 16px;

        background:
            linear-gradient(
                90deg,
                rgba(5,40,29,.30),
                rgba(5,40,29,.08)
            );

        border-top: 1px solid rgba(255,255,255,.10);
    }


    /* =========================================================
       USER BUTTON
       ========================================================= */

    .sidebar-user-button {
        width: 100%;

        display: flex;

        align-items: center;

        gap: 10px;

        padding: 0;

        border: 0;

        background: transparent;

        color: inherit;

        text-align: left;

        cursor: pointer;

        font-family: inherit;
    }


    .sidebar-user-button:hover {
        opacity: .96;
    }


    .sidebar-user-button:focus {
        outline: none;
    }


    .sidebar-user-button:focus-visible {
        outline: 2px solid rgba(224,196,122,.65);

        outline-offset: 3px;

        border-radius: 8px;
    }


    /* =========================================================
       AVATAR
       ========================================================= */

    .footer-avatar {
        width: 38px;
        height: 38px;

        display: flex;

        align-items: center;
        justify-content: center;

        flex-shrink: 0;

        border-radius: 50%;

        background:
            linear-gradient(
                135deg,
                #2B8063,
                #095c3f
            );

        border: 1px solid rgba(255,255,255,.16);

        color: #FFFFFF;

        font-size: 12px;

        font-weight: 800;
    }


    .footer-user {
        flex: 1;

        min-width: 0;
    }


    .footer-name {
        color: #FFFFFF;

        font-size: 12px;

        font-weight: 750;

        overflow: hidden;

        text-overflow: ellipsis;

        white-space: nowrap;
    }


    .footer-role {
        margin-top: 3px;

        color: rgba(255,255,255,.60);

        font-size: 9px;

        overflow: hidden;

        text-overflow: ellipsis;

        white-space: nowrap;
    }


    .footer-logout {
        color: rgba(255,255,255,.70);

        font-size: 18px;

        transition:
            transform .18s ease,
            color .18s ease;
    }


    .sidebar-user-button:hover .footer-logout {
        transform: translateX(2px);

        color: #E0C47A;
    }


    /* =========================================================
       USER POPUP
       ========================================================= */

    .sidebar-user-menu {
        position: absolute;

        left: 14px;
        right: 14px;

        bottom: calc(100% + 10px);

        padding: 8px;

        background: #FFFFFF;

        border: 1px solid rgba(15,81,60,.12);

        border-radius: 12px;

        box-shadow:
            0 18px 40px rgba(0,0,0,.20),
            0 5px 15px rgba(0,0,0,.08);

        opacity: 0;

        visibility: hidden;

        pointer-events: none;

        transform: translateY(8px);

        transition:
            opacity .18s ease,
            transform .18s ease,
            visibility .18s ease;

        z-index: 9999;
    }


    .sidebar-user-menu.show {
        opacity: 1;

        visibility: visible;

        pointer-events: auto;

        transform: translateY(0);
    }


    /* =========================================================
       USER POPUP HEADER
       ========================================================= */

    .sidebar-user-menu-header {
        display: flex;

        align-items: center;

        gap: 10px;

        padding: 8px 8px 10px;
    }


    .sidebar-user-menu-avatar {
        width: 38px;
        height: 38px;

        display: flex;

        align-items: center;
        justify-content: center;

        flex-shrink: 0;

        border-radius: 50%;

        background:
            linear-gradient(
                135deg,
                #2B8063,
                #095c3f
            );

        color: #FFFFFF;

        font-size: 12px;

        font-weight: 800;
    }


    .sidebar-user-menu-info {
        min-width: 0;

        flex: 1;
    }


    .sidebar-user-menu-name {
        color: #173F31;

        font-size: 12px;

        font-weight: 800;

        overflow: hidden;

        text-overflow: ellipsis;

        white-space: nowrap;
    }


    .sidebar-user-menu-email {
        margin-top: 3px;

        color: #7A8B84;

        font-size: 9px;

        overflow: hidden;

        text-overflow: ellipsis;

        white-space: nowrap;
    }


    /* =========================================================
       DIVIDER
       ========================================================= */

    .sidebar-user-menu-divider {
        height: 1px;

        margin: 5px 4px;

        background: #EDF1EF;
    }


    /* =========================================================
       USER MENU ITEM
       ========================================================= */

    .sidebar-user-menu-item {
        width: 100%;

        display: flex;

        align-items: center;

        gap: 10px;

        padding: 10px;

        border: 0;

        border-radius: 7px;

        background: transparent;

        color: #365249;

        text-decoration: none;

        font-family: inherit;

        font-size: 11px;

        font-weight: 600;

        text-align: left;

        cursor: pointer;

        transition:
            background .15s ease,
            color .15s ease;
    }


    .sidebar-user-menu-item:hover {
        background: #F1F7F4;

        color: #0E5A43;
    }


    .menu-item-icon {
        width: 19px;

        display: inline-flex;

        align-items: center;

        justify-content: center;

        flex-shrink: 0;

        font-size: 13px;
    }


    .sidebar-user-menu form {
        margin: 0;
    }

/* =========================================================
   SIDEBAR HIDE / SHOW BUTTON
   ========================================================= */

.sidebar-toggle-button {
    position: absolute;

    top: 22px;
    right: -13px;

    width: 27px;
    height: 27px;

    display: flex;
    align-items: center;
    justify-content: center;

    border: 1px solid rgba(255,255,255,.22);
    border-radius: 50%;

    background: #176B50;
    color: #E0C47A;

    font-size: 21px;
    font-weight: 700;
    line-height: 1;

    cursor: pointer;

    box-shadow:
        0 3px 10px rgba(0,0,0,.18);

    z-index: 1000;

    transition:
        background .18s ease,
        transform .18s ease,
        color .18s ease;
}

.sidebar-toggle-button:hover {
    background: #0B503B;
    color: #FFFFFF;
    transform: scale(1.06);
}


/* =========================================================
   COLLAPSED SIDEBAR
   ========================================================= */

body.sidebar-collapsed .sidebar {
    width: 70px;
}

body.sidebar-collapsed .main {
    margin-left: 70px;
}

body.sidebar-collapsed .brand {
    justify-content: center;
    padding-left: 8px;
    padding-right: 8px;
}

body.sidebar-collapsed .brand-text,
body.sidebar-collapsed .tenant-box,
body.sidebar-collapsed .nav-section-title,
body.sidebar-collapsed .nav-text,
body.sidebar-collapsed .footer-user,
body.sidebar-collapsed .footer-logout {
    display: none;
}

body.sidebar-collapsed .nav-item {
    justify-content: center;
    padding-left: 0;
    padding-right: 0;
}

body.sidebar-collapsed .nav-item.active::after {
    display: none;
}

body.sidebar-collapsed .sidebar-footer {
    justify-content: center;
    padding-left: 8px;
    padding-right: 8px;
}

body.sidebar-collapsed .sidebar-user-button {
    justify-content: center;
}

body.sidebar-collapsed .sidebar-toggle-button {
    right: -13px;
}


/* =========================================================
   TOGGLE ARROW
   ========================================================= */

body.sidebar-collapsed .sidebar-toggle-button {
    font-size: 21px;
}
    /* =========================================================
       LOGOUT
       ========================================================= */

    .sidebar-user-menu .logout-item {
        color: #A43D3D;
    }


    .sidebar-user-menu .logout-item:hover {
        background: #FFF2F2;

        color: #8F2727;
    }


    /* =========================================================
       MOBILE
       ========================================================= */

    @media (max-width: 800px) {

        .sidebar {
            width: 70px;
        }


        .brand {
            justify-content: center;

            padding: 20px 8px;
        }


        .brand-text,
        .tenant-box,
        .nav-section-title,
        .nav-text,
        .footer-user,
        .footer-logout {
            display: none;
        }


        .nav-item {
            justify-content: center;

            padding: 0;
        }


        .nav-item.active::after {
            display: none;
        }


        .sidebar-footer {
            justify-content: center;

            padding: 12px 8px;
        }


        .sidebar-user-button {
            justify-content: center;
        }


        .sidebar-user-menu {
            left: 8px;
            right: auto;

            width: 240px;

            bottom: 8px;

            transform: translateX(-8px);
        }


        .sidebar-user-menu.show {
            transform: translateX(0);
        }

    }

</style>


<script>

document.addEventListener('DOMContentLoaded', function () {

    const button = document.getElementById('sidebarUserButton');
    const menu = document.getElementById('sidebarUserMenu');

    if (!button || !menu) {
        return;
    }
    /* =========================================================
       SIDEBAR HIDE / SHOW
       ========================================================= */

    const sidebarToggleButton = document.getElementById('sidebarToggleButton');

    if (sidebarToggleButton) {

        sidebarToggleButton.addEventListener('click', function () {

            const collapsed = document.body.classList.toggle('sidebar-collapsed');

            sidebarToggleButton.textContent = collapsed ? '›' : '‹';

            sidebarToggleButton.setAttribute(
                'aria-label',
                collapsed
                    ? 'Tampilkan sidebar'
                    : 'Sembunyikan sidebar'
            );

            sidebarToggleButton.setAttribute(
                'title',
                collapsed
                    ? 'Tampilkan sidebar'
                    : 'Sembunyikan sidebar'
            );

        });

    }

    /* =========================================================
       TOGGLE USER MENU
       ========================================================= */

    button.addEventListener('click', function (event) {

        event.stopPropagation();

        const isOpen = menu.classList.toggle('show');

        button.setAttribute(
            'aria-expanded',
            isOpen ? 'true' : 'false'
        );

    });


    /* =========================================================
       CLICK DI DALAM MENU
       ========================================================= */

    menu.addEventListener('click', function (event) {

        event.stopPropagation();

    });


    /* =========================================================
       CLICK DI LUAR
       ========================================================= */

    document.addEventListener('click', function () {

        menu.classList.remove('show');

        button.setAttribute(
            'aria-expanded',
            'false'
        );

    });


    /* =========================================================
       ESCAPE
       ========================================================= */

    document.addEventListener('keydown', function (event) {

        if (event.key === 'Escape') {

            menu.classList.remove('show');

            button.setAttribute(
                'aria-expanded',
                'false'
            );

        }

    });

});

</script>