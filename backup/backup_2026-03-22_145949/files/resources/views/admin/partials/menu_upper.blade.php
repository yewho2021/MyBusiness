@php
    $adminId = request()->cookie('admin_id');
    $currentAdmin = \App\Models\Admin::with('role')->find($adminId);
@endphp

<header class="header">
    <div class="header-left">
        <h1 class="page-title">@yield('title', 'Dashboard')</h1>
    </div>
    <div class="header-right">
        <div class="user-dropdown">
            <button class="user-btn" id="userDropdownBtn">
                <div class="user-avatar">{{ $currentAdmin ? strtoupper(substr($currentAdmin->name, 0, 1)) : 'A' }}</div>
                <div class="user-info">
                    <div class="user-name">{{ $currentAdmin->name ?? 'Admin' }}</div>
                    <div class="user-role">{{ $currentAdmin->role->name ?? 'User' }}</div>
                </div>
            </button>
            <div class="dropdown-menu" id="userDropdownMenu">
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="dropdown-item">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

<style>
    .header-left { display: flex; align-items: center; }
    .page-title { font-size: 18px; font-weight: 600; color: #1e293b; }
    .header-right { display: flex; align-items: center; }
    .user-dropdown { position: relative; }
    .user-btn { display: flex; align-items: center; gap: 10px; padding: 6px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; cursor: pointer; }
    .user-avatar { width: 36px; height: 36px; background: #dc2626; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 600; }
    .user-info { text-align: left; }
    .user-name { font-size: 13px; font-weight: 600; color: #1e293b; }
    .user-role { font-size: 11px; color: #64748b; }
    .dropdown-menu { position: absolute; top: 100%; right: 0; margin-top: 8px; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); display: none; min-width: 150px; }
    .dropdown-menu.show { display: block; }
    .dropdown-item { display: flex; align-items: center; gap: 8px; padding: 12px 16px; color: #475569; border: none; background: none; width: 100%; cursor: pointer; font-size: 14px; }
    .dropdown-item:hover { background: #f8fafc; }
</style>

<script>
    document.getElementById('userDropdownBtn').addEventListener('click', function(e) {
        e.stopPropagation();
        document.getElementById('userDropdownMenu').classList.toggle('show');
    });
    document.addEventListener('click', function() {
        document.getElementById('userDropdownMenu').classList.remove('show');
    });
</script>
