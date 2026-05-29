@php
    use App\Models\Admin;
    use App\Models\AdminMenuGroup;
    use App\Models\AdminMenu;
    use App\Models\AdminRoleMenuAccess;
    use Illuminate\Support\Facades\Cache;

    // Use admin singleton from middleware (Item #8 — avoids duplicate query)
    $admin = request()->attributes->get('admin');
    if (!$admin) {
        $rawCookie = request()->cookie('admin_id');
        $adminId = null;
        if ($rawCookie) {
            try { $adminId = (int) decrypt($rawCookie); } catch (\Exception $e) {
                $adminId = is_numeric($rawCookie) ? (int) $rawCookie : null;
            }
        }
        $admin = $adminId ? Admin::with('role')->find($adminId) : null;
    }

    $roleId = $admin ? $admin->role_id : null;
    $isAdministrator = $admin && $admin->role && $admin->role->slug === 'administrator';
    $currentRoute = Route::currentRouteName();

    // Item #9: Cache menu structure per role (invalidated when menus/permissions change)
    $cacheKey = 'sidebar_menu_' . ($roleId ?? 'guest');
    $menuData = Cache::remember($cacheKey, 300, function () use ($roleId) {
        $groups = AdminMenuGroup::where('is_active', 1)->orderBy('sort_order')->get();
        $menus = AdminMenu::where('is_active', 1)->orderBy('sort_order')->get();
        $permissions = [];
        if ($roleId) {
            $permissions = AdminRoleMenuAccess::where('role_id', $roleId)
                ->where('can_view', 1)
                ->pluck('menu_id')
                ->toArray();
        }
        return compact('groups', 'menus', 'permissions');
    });

    $menuGroups = $menuData['groups'];
    $allMenus = $menuData['menus'];
    $permissions = $menuData['permissions'];

    // Item #12: Use closure instead of global function
    $canViewMenu = function($menuId) use ($permissions, $isAdministrator) {
        if ($isAdministrator) return true;
        return in_array($menuId, $permissions);
    };
@endphp

<aside class="sidebar">
    <button class="sidebar-close" onclick="closeSidebar()" aria-label="Close menu"><i class="fas fa-times"></i></button>
    <div class="sidebar-header" style="{{ \App\Models\Configuration::get('sidebar_header_bg') ? 'background:' . \App\Models\Configuration::get('sidebar_header_bg') . ';' : '' }}padding:{{ \App\Models\Configuration::get('sidebar_header_padding', '20') }}px;">
        @php
            $logoType = \App\Models\Configuration::get('logo_type', 'icon');
            $logoIcon = \App\Models\Configuration::get('logo_icon', 'fas fa-shield-alt');
            $logoImage = \App\Models\Configuration::get('logo_image');
            $portalName = \App\Models\Configuration::get('portal_name', config('app.name', 'Admin Portal'));
            $logoHeight = \App\Models\Configuration::get('logo_height', '36');
            $logoAlign = \App\Models\Configuration::get('logo_align', 'left');
            $logoPadding = \App\Models\Configuration::get('logo_padding', '12');
            $justifyMap = ['center' => 'center', 'right' => 'flex-end'];
        @endphp
        <div class="logo" style="justify-content:{{ $justifyMap[$logoAlign] ?? 'flex-start' }};padding:{{ $logoPadding }}px;">
                @if(($logoType === 'image' || $logoType === 'both') && $logoImage)
                    <img src="{{ asset($logoImage) }}" alt="{{ $portalName }}" style="height:{{ $logoHeight }}px;max-width:calc(var(--sidebar-width) - 40px);object-fit:contain;">
                    @if($logoType === 'both')
                        <span class="logo-text">{{ $portalName }}</span>
                    @endif
                @else
                    <div class="logo-icon">
                        <i class="{{ $logoIcon }}"></i>
                    </div>
                    <span class="logo-text">{{ $portalName }}</span>
                @endif
        </div>
    </div>

    <nav class="sidebar-nav">
        @foreach($menuGroups as $group)
            @php
                $groupMenus = $allMenus->where('group_id', $group->id)->whereNull('parent_id');

                $hasAccessibleMenu = false;
                foreach ($groupMenus as $menu) {
                    if ($canViewMenu($menu->id)) {
                        $hasAccessibleMenu = true;
                        break;
                    }
                    $children = $allMenus->where('parent_id', $menu->id);
                    foreach ($children as $child) {
                        if ($canViewMenu($child->id)) {
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

                                $routePrefix = $menu->route_name ? implode('.', array_slice(explode('.', $menu->route_name), 0, 2)) : '';
                                $currentPrefix = $currentRoute ? implode('.', array_slice(explode('.', $currentRoute), 0, 2)) : '';
                                $isActive = $currentRoute === $menu->route_name || ($routePrefix && $routePrefix === $currentPrefix);
                                $isChildActive = false;

                                $accessibleChildren = $children->filter(fn($child) => $canViewMenu($child->id));

                                if ($hasChildren) {
                                    foreach ($accessibleChildren as $child) {
                                        $childPrefix = $child->route_name ? implode('.', array_slice(explode('.', $child->route_name), 0, 2)) : '';
                                        if ($currentRoute === $child->route_name || ($childPrefix && $childPrefix === $currentPrefix)) {
                                            $isChildActive = true;
                                            break;
                                        }
                                    }
                                }

                                $canViewThisMenu = $canViewMenu($menu->id);
                                $hasAccessibleChildren = $accessibleChildren->count() > 0;
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
    width: var(--sidebar-width, 260px);
    height: 100vh;
    background: var(--sidebar-bg, var(--text-heading));
    position: fixed;
    left: 0;
    top: 0;
    z-index: 100;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.sidebar-header { padding: 20px; border-bottom: 1px solid var(--sidebar-border, rgba(255,255,255,0.08)); }
.logo { display: flex; align-items: center; gap: 12px; }
.logo-icon { width: 40px; height: 40px; background: var(--sidebar-logo-bg, var(--c-danger)); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 18px; }
.logo-text { font-size: 18px; font-weight: 700; color: #fff; }
.sidebar-nav { flex: 1; overflow-y: auto; padding: 16px 0; }
.nav-group { margin-bottom: 8px; }
.nav-group-title { display: block; padding: 12px 20px 8px; font-size: 13px; font-weight: 600; color: var(--sidebar-text-muted, var(--text-muted)); text-transform: uppercase; letter-spacing: 0.5px; }
.nav-menu { list-style: none; padding: 0; margin: 0; }
.nav-item { margin: 2px 12px; }
.nav-link { display: flex; align-items: center; gap: 12px; padding: 12px 16px; color: var(--sidebar-text, var(--input-border)); text-decoration: none; border-radius: 8px; transition: all 0.2s; cursor: pointer; }
.nav-link:hover { background: var(--sidebar-hover-bg, rgba(220,38,38,0.1)); color: #fff; }
.nav-link.active { background: var(--sidebar-active-bg, var(--c-danger)); color: var(--sidebar-active-text, #fff); }
.nav-link.parent-active { background: rgba(220,38,38,0.12); color: var(--c-danger-border); }
.nav-icon { width: 22px; display: flex; align-items: center; justify-content: center; font-size: 16px; }
.nav-text { flex: 1; font-size: 16px; font-weight: 500; }
.nav-arrow { font-size: 12px; transition: transform 0.3s; }
.nav-item.open .nav-arrow { transform: rotate(180deg); }
.nav-submenu { list-style: none; padding: 4px 0 4px 0; margin: 0; display: block; }
.nav-subitem { margin: 2px 0; }
.nav-sublink { display: flex; align-items: center; gap: 12px; padding: 10px 16px 10px 24px; color: var(--text-faint); text-decoration: none; border-radius: 6px; transition: all 0.2s; margin-left: 20px; border-left: 2px solid #333; }
.nav-sublink:hover { color: #fff; background: rgba(220,38,38,0.08); border-left-color: var(--c-danger); }
.nav-sublink.active { color: #fff; background: var(--sidebar-active-bg, var(--c-danger)); border-left-color: var(--sidebar-active-bg, var(--c-danger)); font-weight: 500; }
.nav-sublink .nav-icon { font-size: 14px; }
.nav-sublink .nav-text { font-size: 15px; }
.sidebar-nav::-webkit-scrollbar { width: 6px; }
.sidebar-nav::-webkit-scrollbar-track { background: transparent; }
.sidebar-nav::-webkit-scrollbar-thumb { background: #333; border-radius: 3px; }
.sidebar-nav::-webkit-scrollbar-thumb:hover { background: #555; }
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
