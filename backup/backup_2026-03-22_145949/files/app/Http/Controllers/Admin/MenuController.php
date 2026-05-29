<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminMenu;
use App\Models\AdminMenuGroup;
use App\Models\AdminRole;
use App\Models\AdminRoleMenuAccess;
use Illuminate\Http\Request;

class MenuController extends Controller
{
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

    public function storeGroup(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:tbl_admin_menu_groups,slug',
            'sort_order' => 'required|integer|min:0',
        ]);

        AdminMenuGroup::create([
            'title' => $request->title,
            'slug' => $request->slug,
            'sort_order' => $request->sort_order,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect()->route('admin.menus.index')->with('success', 'Menu group created successfully.');
    }

    public function updateGroup(Request $request, $id)
    {
        $group = AdminMenuGroup::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:tbl_admin_menu_groups,slug,' . $id,
            'sort_order' => 'required|integer|min:0',
        ]);

        $group->title = $request->title;
        $group->slug = $request->slug;
        $group->sort_order = $request->sort_order;
        $group->is_active = $request->has('is_active') ? 1 : 0;
        $group->save();

        return redirect()->route('admin.menus.index')->with('success', 'Menu group updated successfully.');
    }

    public function destroyGroup($id)
    {
        $group = AdminMenuGroup::withCount('menus')->findOrFail($id);

        if ($group->menus_count > 0) {
            return redirect()->route('admin.menus.index')->with('error', 'Cannot delete group. There are ' . $group->menus_count . ' menu(s) in this group.');
        }

        $group->delete();

        return redirect()->route('admin.menus.index')->with('success', 'Menu group deleted successfully.');
    }

    public function storeMenu(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:tbl_admin_menu_groups,id',
            'title' => 'required|string|max:100',
            'icon' => 'nullable|string|max:50',
            'route_name' => 'nullable|string|max:100',
            'url' => 'nullable|string|max:255',
            'permission_key' => 'nullable|string|max:100',
            'parent_id' => 'nullable|exists:tbl_admin_menus,id',
            'sort_order' => 'required|integer|min:0',
        ]);

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
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect()->route('admin.menus.index')->with('success', 'Menu created successfully.');
    }

    public function updateMenu(Request $request, $id)
    {
        $menu = AdminMenu::findOrFail($id);

        $request->validate([
            'group_id' => 'required|exists:tbl_admin_menu_groups,id',
            'title' => 'required|string|max:100',
            'icon' => 'nullable|string|max:50',
            'route_name' => 'nullable|string|max:100',
            'url' => 'nullable|string|max:255',
            'permission_key' => 'nullable|string|max:100',
            'parent_id' => 'nullable|exists:tbl_admin_menus,id',
            'sort_order' => 'required|integer|min:0',
        ]);

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
        $menu->is_active = $request->has('is_active') ? 1 : 0;
        $menu->save();

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

        return redirect()->route('admin.menus.index')->with('success', 'Menu deleted successfully.');
    }

    public function permissions()
    {
        $menuGroups = AdminMenuGroup::where('is_active', 1)->orderBy('sort_order')->get();
        $menus = AdminMenu::where('is_active', 1)->orderBy('group_id')->orderBy('sort_order')->get();
        $roles = AdminRole::where('is_active', 1)->orderBy('level')->get();
        $permissions = AdminRoleMenuAccess::all()->keyBy(function ($item) {
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

        return redirect()->route('admin.permissions.index')->with('success', 'Permissions updated successfully.');
    }
    
    public function updateOrder(Request $request)
    {
        $items = $request->input('items', []);

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

        return response()->json(['success' => true]);
    }

}