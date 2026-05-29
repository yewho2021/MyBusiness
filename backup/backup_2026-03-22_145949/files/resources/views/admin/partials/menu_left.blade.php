@php
    use App\Models\Admin;
    use App\Models\AdminMenuGroup;
    use App\Models\AdminMenu;
    use App\Models\AdminRoleMenuAccess;
    
    $menuGroups = AdminMenuGroup::where('is_active', 1)->orderBy('sort_order')->get();
    $allMenus = AdminMenu::where('is_active', 1)->orderBy('sort_order')->get();
    
    // Get current route name
    $currentRoute = Route::currentRouteName();
    
    // Get current admin and role
    $adminId = request()->cookie('admin_id');
    $admin = Admin::find($adminId);
    $roleId = $admin ? $admin->role_id : null;
    $isAdministrator = $admin && $admin->role && $admin->role->slug === 'administrator';
    
    // Get permissions for this role
    $permissions = [];
    if ($roleId) {
        $permissions = AdminRoleMenuAccess::where('role_id', $roleId)
            ->where('can_view', 1)
            ->pluck('menu_id')
            ->toArray();
    }
    
    // Function to check if menu is accessible
    function canViewMenu($menuId, $permissions, $isAdministrator) {
        if ($isAdministrator) return true;
        return in_array($menuId, $permissions);
    }
@endphp

<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <div class="logo-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <span class="logo-text">Admin Portal</span>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        @foreach($menuGroups as $group)
            @php
                $groupMenus = $allMenus->where('group_id', $group->id)->whereNull('parent_id');
                
                // Check if any menu in this group is accessible
                $hasAccessibleMenu = false;
                foreach ($groupMenus as $menu) {
                    if (canViewMenu($menu->id, $permissions, $isAdministrator)) {
                        $hasAccessibleMenu = true;
                        break;
                    }
                    // Also check children
                    $children = $allMenus->where('parent_id', $menu->id);
                    foreach ($children as $child) {
                        if (canViewMenu($child->id, $permissions, $isAdministrator)) {
                            $hasAccessibleMenu = true;
                            break 2;
                        }
                    }
                }
            @endphp
            
            @if($groupMenus->count() > 0 && $hasAccessibleMenu)
                <div class="nav-group">
                    <span class="nav-group-title">{{ $group->title }}</span>
                    
                    <ul class="nav-menu">
                        @foreach($groupMenus as $menu)
                            @php
                                $children = $allMenus->where('parent_id', $menu->id);
                                $hasChildren = $children->count() > 0;
                                
                                // Check if current menu or any child is active
                                // Exact match OR same route prefix (e.g. admin.database.* matches admin.database.connections.index)
                                $routePrefix = $menu->route_name ? implode('.', array_slice(explode('.', $menu->route_name), 0, 2)) : '';
                                $currentPrefix = $currentRoute ? implode('.', array_slice(explode('.', $currentRoute), 0, 2)) : '';
                                $isActive = $currentRoute === $menu->route_name || ($routePrefix && $routePrefix === $currentPrefix);
                                $isChildActive = false;
                                
                                // Filter accessible children
                                $accessibleChildren = $children->filter(function($child) use ($permissions, $isAdministrator) {
                                    return canViewMenu($child->id, $permissions, $isAdministrator);
                                });
                                
                                if ($hasChildren) {
                                    foreach ($accessibleChildren as $child) {
                                        $childPrefix = $child->route_name ? implode('.', array_slice(explode('.', $child->route_name), 0, 2)) : '';
                                        if ($currentRoute === $child->route_name || ($childPrefix && $childPrefix === $currentPrefix)) {
                                            $isChildActive = true;
                                            break;
                                        }
                                    }
                                }
                                
                                // Check if this menu should be shown
                                $canViewThisMenu = canViewMenu($menu->id, $permissions, $isAdministrator);
                                $hasAccessibleChildren = $accessibleChildren->count() > 0;
                                
                                // Show menu if: can view it OR has accessible children
                                $showMenu = $canViewThisMenu || $hasAccessibleChildren;
                            @endphp
                            
                            @if($showMenu)
                                <li class="nav-item {{ $hasAccessibleChildren ? 'has-children open' : '' }}">
                                    @if($hasAccessibleChildren)
                                        <a href="javascript:void(0)" class="nav-link {{ $isChildActive ? 'parent-active' : '' }}" onclick="toggleSubmenu(this)">
                                            <span class="nav-icon"><i class="{{ $menu->icon ?? 'fas fa-circle' }}"></i></span>
                                            <span class="nav-text">{{ $menu->title }}</span>
                                            <span class="nav-arrow"><i class="fas fa-chevron-down"></i></span>
                                        </a>
                                        <ul class="nav-submenu">
                                            @foreach($accessibleChildren as $child)
                                                @php
                                                    $childRoutePrefix = $child->route_name ? implode('.', array_slice(explode('.', $child->route_name), 0, 2)) : '';
                                                    $isChildItemActive = $currentRoute === $child->route_name || ($childRoutePrefix && $childRoutePrefix === $currentPrefix);
                                                @endphp
                                                <li class="nav-subitem">
                                                    <a href="{{ $child->route_name ? route($child->route_name) : ($child->url ?? '#') }}" 
                                                       class="nav-sublink {{ $isChildItemActive ? 'active' : '' }}">
                                                        <span class="nav-icon"><i class="{{ $child->icon ?? 'fas fa-circle' }}"></i></span>
                                                        <span class="nav-text">{{ $child->title }}</span>
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <a href="{{ $menu->route_name ? route($menu->route_name) : ($menu->url ?? '#') }}" 
                                           class="nav-link {{ $isActive ? 'active' : '' }}">
                                            <span class="nav-icon"><i class="{{ $menu->icon ?? 'fas fa-circle' }}"></i></span>
                                            <span class="nav-text">{{ $menu->title }}</span>
                                        </a>
                                    @endif
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endif
        @endforeach
    </nav>
</aside>

<style>
.sidebar {
    width: 260px;
    height: 100vh;
    background: #111111;
    position: fixed;
    left: 0;
    top: 0;
    z-index: 100;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.sidebar-header {
    padding: 20px;
    border-bottom: 1px solid rgba(255,255,255,0.08);
}

.logo {
    display: flex;
    align-items: center;
    gap: 12px;
}

.logo-icon {
    width: 40px;
    height: 40px;
    background: #dc2626;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 18px;
}

.logo-text {
    font-size: 18px;
    font-weight: 700;
    color: #fff;
}

.sidebar-nav {
    flex: 1;
    overflow-y: auto;
    padding: 16px 0;
}

.nav-group {
    margin-bottom: 8px;
}

.nav-group-title {
    display: block;
    padding: 12px 20px 8px;
    font-size: 11px;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.nav-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.nav-item {
    margin: 2px 12px;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    color: #d1d5db;
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.2s;
    cursor: pointer;
}

.nav-link:hover {
    background: rgba(220,38,38,0.1);
    color: #fff;
}

.nav-link.active {
    background: #dc2626;
    color: #fff;
    
}

.nav-link.parent-active {
    background: rgba(220,38,38,0.12);
    color: #fca5a5;
}

.nav-icon {
    width: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}

.nav-text {
    flex: 1;
    font-size: 14px;
    font-weight: 500;
}

.nav-arrow {
    font-size: 12px;
    transition: transform 0.3s;
}

.nav-item.open .nav-arrow {
    transform: rotate(180deg);
}

.nav-submenu {
    list-style: none;
    padding: 4px 0 4px 0;
    margin: 0;
    display: block;
}

.nav-subitem {
    margin: 2px 0;
}

.nav-sublink {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 16px 10px 24px;
    color: #9ca3af;
    text-decoration: none;
    border-radius: 6px;
    transition: all 0.2s;
    margin-left: 20px;
    border-left: 2px solid #333;
}

.nav-sublink:hover {
    color: #fff;
    background: rgba(220,38,38,0.08);
    border-left-color: #ef4444;
}

.nav-sublink.active {
    color: #fff;
    background: #dc2626;
    border-left-color: #dc2626;
    font-weight: 500;
}

.nav-sublink .nav-icon {
    font-size: 12px;
}

.nav-sublink .nav-text {
    font-size: 13px;
}

.sidebar-nav::-webkit-scrollbar {
    width: 6px;
}

.sidebar-nav::-webkit-scrollbar-track {
    background: transparent;
}

.sidebar-nav::-webkit-scrollbar-thumb {
    background: #333;
    border-radius: 3px;
}

.sidebar-nav::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>

<script>
function toggleSubmenu(element) {
    const parent = element.parentElement;
    const submenu = parent.querySelector('.nav-submenu');
    
    if (parent.classList.contains('open')) {
        parent.classList.remove('open');
        submenu.style.display = 'none';
    } else {
        parent.classList.add('open');
        submenu.style.display = 'block';
    }
}
</script>