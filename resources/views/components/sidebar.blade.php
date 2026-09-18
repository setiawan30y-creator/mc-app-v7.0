<aside class="sidebar">

    {{-- SIDEBAR TOGGLE --}}
    <button type="button" class="sidebar-toggle-button" id="sidebarToggleButton" aria-label="Sembunyikan sidebar" title="Sembunyikan sidebar">‹</button>

    {{-- BRAND --}}
    <div class="brand">
        <div class="brand-mark">MA</div>
        <div class="brand-text">
            <div class="brand-name">MC ALMARA</div>
            <div class="brand-subtitle">Money Changer Digital OS</div>
        </div>
    </div>

    {{-- TENANT --}}
    <div class="tenant-box">
        <div class="tenant-label">TENANT</div>
        <div class="tenant-name">{{ auth()->user()->tenant->name ?? 'MC Almara' }}</div>
    </div>

    {{-- NAVIGATION --}}
    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-section-title">WORKSPACE</div>

            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <span class="nav-icon">⌂</span><span class="nav-text">Dashboard</span>
            </a>

            <a href="{{ route('transactions.create') }}" class="nav-item {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
                <span class="nav-icon">⇄</span><span class="nav-text">Transaksi</span>
            </a>

            <a href="{{ route('customers.index') }}" class="nav-item {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                <span class="nav-icon">♙</span><span class="nav-text">Nasabah</span>
            </a>

            <a href="{{ route('settings.compliance-threshold.index') }}" class="nav-item {{ request()->routeIs('settings.compliance-threshold.*') ? 'active' : '' }}">
                <span class="nav-icon">✓</span><span class="nav-text">KYC & Compliance</span>
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">TREASURY</div>

            <a href="{{ route('settings.currency-variants.index') }}" class="nav-item {{ request()->routeIs('settings.currency-variants.*') ? 'active' : '' }}">
                <span class="nav-icon">◎</span><span class="nav-text">Mata Uang</span>
            </a>

            <a href="{{ route('settings.denominations.index') }}" class="nav-item {{ request()->routeIs('settings.denominations.*') ? 'active' : '' }}">
                <span class="nav-icon">▤</span><span class="nav-text">Pecahan</span>
            </a>

            <a href="{{ route('settings.rates.index') }}" class="nav-item {{ request()->routeIs('settings.rates.*') ? 'active' : '' }}">
                <span class="nav-icon">↗</span><span class="nav-text">Kurs & Rate</span>
            </a>

            <a href="{{ route('settings.bank-accounts.index') }}" class="nav-item {{ request()->routeIs('settings.bank-accounts.*') ? 'active' : '' }}">
                <span class="nav-icon">▣</span><span class="nav-text">Kas & Bank</span>
            </a>

            <a href="{{ route('gantungan.index') }}" class="nav-item {{ request()->routeIs('gantungan.*') ? 'active' : '' }}">
                <span class="nav-icon">⊙</span><span class="nav-text">Gantungan</span>
            </a>

            <a href="{{ route('closing.index') }}" class="nav-item {{ request()->routeIs('closing.*') ? 'active' : '' }}">
                <span class="nav-icon">◫</span><span class="nav-text">Closing Operasional</span>
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">MANAGEMENT</div>

            <a href="{{ route('settings.company.edit') }}" class="nav-item {{ request()->routeIs('settings.company.*') ? 'active' : '' }}">
                <span class="nav-icon">▥</span><span class="nav-text">Perusahaan</span>
            </a>

            <a href="{{ route('settings.customer-risk.index') }}" class="nav-item {{ request()->routeIs('settings.customer-risk.*') ? 'active' : '' }}">
                <span class="nav-icon">▤</span><span class="nav-text">Master Customer Risk</span>
            </a>

            <a href="{{ route('settings.iso-currencies.index') }}" class="nav-item {{ request()->routeIs('settings.iso-currencies.*') ? 'active' : '' }}">
                <span class="nav-icon">◎</span><span class="nav-text">ISO Currencies</span>
            </a>
        </div>
    </nav>

    {{-- USER FOOTER --}}
    <div class="sidebar-footer">
        <button type="button" class="sidebar-user-button" id="sidebarUserButton" aria-expanded="false">
            <div class="footer-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
            <div class="footer-user">
                <div class="footer-name">{{ auth()->user()->name ?? 'User' }}</div>
                <div class="footer-role">{{ auth()->user()->roles()->first()->name ?? 'User' }}</div>
            </div>
            <div class="footer-logout">↪</div>
        </button>

        <div class="sidebar-user-menu" id="sidebarUserMenu">
            <div class="sidebar-user-menu-header">
                <div class="sidebar-user-menu-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
                <div class="sidebar-user-menu-info">
                    <div class="sidebar-user-menu-name">{{ auth()->user()->name ?? 'User' }}</div>
                    <div class="sidebar-user-menu-email">{{ auth()->user()->email ?? '' }}</div>
                </div>
            </div>
            <div class="sidebar-user-menu-divider"></div>
            <a href="#" class="sidebar-user-menu-item"><span class="menu-item-icon">👤</span><span>Profil Saya</span></a>
            <a href="#" class="sidebar-user-menu-item"><span class="menu-item-icon">🏢</span><span>Tenant & Cabang</span></a>
            <a href="{{ route('settings.company.edit') }}" class="sidebar-user-menu-item"><span class="menu-item-icon">⚙</span><span>Pengaturan</span></a>
            <a href="#" class="sidebar-user-menu-item"><span class="menu-item-icon">🔐</span><span>Keamanan</span></a>
            <div class="sidebar-user-menu-divider"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sidebar-user-menu-item logout-item"><span class="menu-item-icon">🚪</span><span>Keluar</span></button>
            </form>
        </div>
    </div>
</aside>

<style>
.sidebar{position:fixed;top:0;left:0;width:250px;height:100vh;display:flex;flex-direction:column;color:#f5faf7;background:radial-gradient(circle at 10% 8%,rgba(6,77,46,.3),transparent 32%),radial-gradient(circle at 95% 42%,rgba(4,75,48,.22),transparent 35%),linear-gradient(180deg,#176B50 0%,#12654B 38%,#0E5A43 70%,#0B503B 100%);border-right:1px solid rgba(255,255,255,.10);box-shadow:4px 0 22px rgba(9,53,39,.12);overflow:visible;z-index:100}.sidebar::before{content:"";position:absolute;width:260px;height:260px;right:-150px;bottom:70px;background:linear-gradient(135deg,transparent 35%,rgba(224,196,122,.16) 36%,rgba(224,196,122,.08) 55%,transparent 56%);transform:rotate(-18deg);pointer-events:none}.brand{display:flex;align-items:center;gap:11px;padding:23px 21px 19px;position:relative;z-index:2}.brand-mark{width:38px;height:38px;display:flex;align-items:center;justify-content:center;flex-shrink:0;border-radius:9px;background:linear-gradient(135deg,#E0C47A,#B08A45);color:#174D3A;font-size:12px;font-weight:900;letter-spacing:-1px;box-shadow:0 4px 12px rgba(0,0,0,.12)}.brand-name{color:#fff;font-size:16px;font-weight:850;letter-spacing:.9px;line-height:1.1}.brand-subtitle{margin-top:4px;color:rgba(255,255,255,.64);font-size:8px;white-space:nowrap}.tenant-box{position:relative;z-index:2;margin:4px 16px 18px;padding:14px 15px;border-radius:10px;background:linear-gradient(135deg,rgba(255,255,255,.12),rgba(255,255,255,.055));border:1px solid rgba(255,255,255,.12);box-shadow:inset 0 1px 0 rgba(255,255,255,.05)}.tenant-label{color:rgba(255,255,255,.55);font-size:9px;font-weight:650;letter-spacing:.8px}.tenant-name{margin-top:6px;color:#fff;font-size:13px;font-weight:750}.sidebar-nav{position:relative;z-index:2;flex:1;overflow-y:auto;padding:0 14px 15px}.sidebar-nav::-webkit-scrollbar{width:4px}.sidebar-nav::-webkit-scrollbar-track{background:transparent}.sidebar-nav::-webkit-scrollbar-thumb{background:rgba(255,255,255,.18);border-radius:20px}.nav-section{margin-bottom:20px}.nav-section-title{padding:0 11px 8px;color:rgba(255,255,255,.48);font-size:9px;font-weight:750;letter-spacing:.8px}.nav-item{position:relative;display:flex;align-items:center;gap:12px;min-height:42px;padding:0 12px;margin-bottom:3px;border-radius:8px;color:rgba(255,255,255,.86);text-decoration:none;font-size:12px;font-weight:550;transition:background .18s ease,color .18s ease,transform .18s ease}.nav-item:hover{background:rgba(255,255,255,.09);color:#fff;transform:translateX(1px)}.nav-item.active{color:#F1D68F;font-weight:750;background:linear-gradient(90deg,rgba(224,196,122,.26),rgba(224,196,122,.13),rgba(255,255,255,.04));box-shadow:inset 3px 0 0 #E0C47A,0 3px 12px rgba(0,0,0,.06)}.nav-item.active::after{content:"";position:absolute;right:10px;width:5px;height:5px;border-radius:50%;background:#E0C47A;box-shadow:0 0 9px rgba(224,196,122,.75)}.nav-icon{width:19px;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;color:rgba(255,255,255,.72);font-size:14px}.nav-item.active .nav-icon{color:#E0C47A}.sidebar-footer{position:relative;z-index:20;flex-shrink:0;display:flex;align-items:center;gap:10px;padding:13px 16px;background:linear-gradient(90deg,rgba(5,40,29,.30),rgba(5,40,29,.08));border-top:1px solid rgba(255,255,255,.10)}.sidebar-user-button{width:100%;display:flex;align-items:center;gap:10px;padding:0;border:0;background:transparent;color:inherit;text-align:left;cursor:pointer;font-family:inherit}.sidebar-user-button:hover{opacity:.96}.sidebar-user-button:focus{outline:none}.footer-avatar{width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:rgba(255,255,255,.13);color:#E0C47A;font-weight:800}.footer-user{min-width:0;flex:1}.footer-name{color:#fff;font-size:11px;font-weight:750;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.footer-role{margin-top:3px;color:rgba(255,255,255,.52);font-size:8px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.footer-logout{color:rgba(255,255,255,.58);font-size:16px}.sidebar-user-menu{position:absolute;left:16px;bottom:65px;width:218px;padding:8px;border-radius:12px;background:#fff;color:#174D3A;box-shadow:0 12px 35px rgba(0,0,0,.22);border:1px solid rgba(15,77,57,.12);display:none}.sidebar-user-menu.show{display:block}.sidebar-user-menu-header{display:flex;gap:10px;padding:10px}.sidebar-user-menu-avatar{width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#E0C47A;color:#174D3A;font-weight:800}.sidebar-user-menu-name{font-size:11px;font-weight:800}.sidebar-user-menu-email{margin-top:3px;font-size:8px;color:#6b7d75;word-break:break-all}.sidebar-user-menu-divider{height:1px;background:#e7eee9;margin:5px 0}.sidebar-user-menu-item{display:flex;align-items:center;gap:9px;width:100%;box-sizing:border-box;padding:9px 10px;border:0;border-radius:7px;background:transparent;color:#174D3A;text-decoration:none;font:inherit;font-size:10px;text-align:left;cursor:pointer}.sidebar-user-menu-item:hover{background:#f2f7f4}.menu-item-icon{width:17px;text-align:center}.logout-item{color:#a33a32}
</style>