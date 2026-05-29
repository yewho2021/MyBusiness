<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMenuGroupRequest;
use App\Http\Requests\StoreMenuRequest;
use App\Http\Requests\UpdateMenuGroupRequest;
use App\Models\AdminMenu;
use App\Models\AdminMenuGroup;
use App\Models\AdminRole;
use App\Models\AdminRoleMenuAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    /**
     * Clear sidebar menu cache for all roles.
     * Called after any menu/permission change.
     */
    protected function clearMenuCache(): void
    {
        $roleIds = AdminRole::pluck('id');
        foreach ($roleIds as $id) {
            Cache::forget('sidebar_menu_' . $id);
        }
        Cache::forget('sidebar_menu_guest');
    }

    public function index()
    {
        $menuGroups = AdminMenuGroup::orderBy('sort_order')->get();
        $menus = AdminMenu::with(['group', 'parent'])->orderBy('group_id')->orderBy('sort_order')->get();
        $roles = AdminRole::where('is_active', 1)->orderBy('level')->get();

        return view('admin.pages.menus.index', [
            'menuGroups' => $menuGroups,
            'menus' => $menus,
            'roles' => $roles,
        ]);
    }

    public function storeGroup(StoreMenuGroupRequest $request)
    {
        AdminMenuGroup::create([
            'title' => $request->title,
            'slug' => $request->slug,
            'sort_order' => $request->sort_order,
            'is_active' => $request->boolean('is_active') ? 1 : 0,
        ]);

        $this->clearMenuCache();
        return redirect()->route('admin.menus.index')->with('success', 'Menu group created successfully.');
    }

    public function updateGroup(UpdateMenuGroupRequest $request, $id)
    {
        $group = AdminMenuGroup::findOrFail($id);

        $group->title = $request->title;
        $group->slug = $request->slug;
        $group->sort_order = $request->sort_order;
        $group->is_active = $request->boolean('is_active') ? 1 : 0;
        $group->save();

        $this->clearMenuCache();
        return redirect()->route('admin.menus.index')->with('success', 'Menu group updated successfully.');
    }

    public function destroyGroup($id)
    {
        $group = AdminMenuGroup::withCount('menus')->findOrFail($id);

        if ($group->menus_count > 0) {
            return redirect()->route('admin.menus.index')->with('error', 'Cannot delete group. There are ' . $group->menus_count . ' menu(s) in this group.');
        }

        $group->delete();

        $this->clearMenuCache();
        return redirect()->route('admin.menus.index')->with('success', 'Menu group deleted successfully.');
    }

    public function storeMenu(StoreMenuRequest $request)
    {
        $level = 1;
        if ($request->parent_id) {
            $parent = AdminMenu::find($request->parent_id);
            $level = $parent->level + 1;
        }

        AdminMenu::create([
            'group_id' => $request->group_id,
            'parent_id' => $request->parent_id,
            'level' => $level,
            'title' => $request->title,
            'icon' => $request->icon,
            'route_name' => $request->route_name,
            'url' => $request->url,
            'permission_key' => $request->permission_key,
            'sort_order' => $request->sort_order,
            'is_active' => $request->boolean('is_active') ? 1 : 0,
        ]);

        $this->clearMenuCache();
        return redirect()->route('admin.menus.index')->with('success', 'Menu created successfully.');
    }

    public function updateMenu(StoreMenuRequest $request, $id)
    {
        $menu = AdminMenu::findOrFail($id);

        $level = 1;
        if ($request->parent_id) {
            $parent = AdminMenu::find($request->parent_id);
            $level = $parent->level + 1;
        }

        $menu->group_id = $request->group_id;
        $menu->parent_id = $request->parent_id;
        $menu->level = $level;
        $menu->title = $request->title;
        $menu->icon = $request->icon;
        $menu->route_name = $request->route_name;
        $menu->url = $request->url;
        $menu->permission_key = $request->permission_key;
        $menu->sort_order = $request->sort_order;
        $menu->is_active = $request->boolean('is_active') ? 1 : 0;
        $menu->save();

        $this->clearMenuCache();
        return redirect()->route('admin.menus.index')->with('success', 'Menu updated successfully.');
    }

    public function destroyMenu($id)
    {
        $menu = AdminMenu::withCount('children')->findOrFail($id);

        if ($menu->children_count > 0) {
            return redirect()->route('admin.menus.index')->with('error', 'Cannot delete menu. There are ' . $menu->children_count . ' sub-menu(s) under this menu.');
        }

        AdminRoleMenuAccess::where('menu_id', $id)->delete();
        $menu->delete();

        $this->clearMenuCache();
        return redirect()->route('admin.menus.index')->with('success', 'Menu deleted successfully.');
    }

    public function permissions()
    {
        $menuGroups = AdminMenuGroup::where('is_active', 1)->orderBy('sort_order')->get();
        $menus = AdminMenu::where('is_active', 1)->orderBy('group_id')->orderBy('sort_order')->get();
        $roles = AdminRole::where('is_active', 1)->orderBy('level')->get();
        $permissions = AdminRoleMenuAccess::whereIn('role_id', $roles->pluck('id'))
            ->whereIn('menu_id', $menus->pluck('id'))
            ->get()
            ->keyBy(function ($item) {
                return $item->role_id . '-' . $item->menu_id;
            });

        return view('admin.pages.permissions.index', [
            'menuGroups' => $menuGroups,
            'menus' => $menus,
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function updatePermissions(Request $request)
    {
        $permissions = $request->input('permissions', []);

        $roles = AdminRole::where('is_active', 1)->get();
        $menus = AdminMenu::where('is_active', 1)->get();

        DB::transaction(function () use ($roles, $menus, $permissions) {
            foreach ($roles as $role) {
                foreach ($menus as $menu) {
                    $key = $role->id . '-' . $menu->id;

                    $access = AdminRoleMenuAccess::firstOrNew([
                        'role_id' => $role->id,
                        'menu_id' => $menu->id,
                    ]);

                    $access->can_view = isset($permissions[$key]['can_view']) ? 1 : 0;
                    $access->can_create = isset($permissions[$key]['can_create']) ? 1 : 0;
                    $access->can_edit = isset($permissions[$key]['can_edit']) ? 1 : 0;
                    $access->can_delete = isset($permissions[$key]['can_delete']) ? 1 : 0;
                    $access->save();
                }
            }
        });

        $this->clearMenuCache();
        return redirect()->route('admin.permissions.index')->with('success', 'Permissions updated successfully.');
    }
    
    public function updateOrder(Request $request)
    {
        $items = $request->input('items', []);

        DB::transaction(function () use ($items) {
            foreach ($items as $index => $item) {
                $data = [
                    'sort_order' => $index,
                    'parent_id' => $item['parent_id'] ?? null,
                    'level' => empty($item['parent_id']) ? 1 : 2,
                ];
                if (!empty($item['group_id'])) {
                    $data['group_id'] = $item['group_id'];
                }
                AdminMenu::where('id', $item['id'])->update($data);
            }
        });

        $this->clearMenuCache();
        return response()->json(['success' => true]);
    }

}