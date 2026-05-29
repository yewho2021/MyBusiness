## Encrypted User IDs + Per-User Permission Overrides (v5.5.0)

Two security and access-control improvements.

### Encrypted User IDs in URLs

**Problem:** User edit URLs exposed raw database IDs (`/users/3/edit`) — an attacker could enumerate all users by incrementing the ID.

**Fix:** All user routes now use encrypted tokens instead of raw IDs.

- **Before:** `/users/3/edit?tab=profile`
- **After:** `/users/eyJpdiI6IjVkM2...`/edit?tab=profile`

Every route parameter `{id}` changed to `{token}`. The `Admin` model has new helper methods:
- `$admin->getRouteToken()` — generates an encrypted URL token
- `Admin::findByToken($token)` — decrypts and finds the admin (returns null on tamper)
- `Admin::findByTokenOrFail($token)` — same but throws 404 on failure

All controllers (AdminController, TwoFactorController), views (index, edit), and JS fetch calls updated. Tokens change on every page load (Laravel's `encrypt()` includes randomised IV), so they can't be bookmarked or cached.

### Per-User Permission Overrides

**Problem:** Permissions were role-level only. If one staff member needed extra access to a specific module, the entire role had to be changed — affecting all users with that role.

**Fix:** New "Permissions" tab on the user edit page allows per-user overrides on top of role defaults.

**How it works:**
1. Open any non-administrator user → click "Permissions" tab
2. Each menu shows the role's default permission (blue = inherited)
3. Click any checkbox to override for this specific user
   - **Green border** = user override granting access
   - **Red border** = user override denying access
4. Click "Save Overrides" — only the differences from role defaults are stored
5. Click "Reset to Role Defaults" — removes all overrides, user inherits role again

**Priority chain:** User override → Role permission → Deny

**Where it's enforced:**
- `CheckAdminMenuAccess` middleware (every request)
- `Admin::hasMenuPermission()` (sidebar rendering, controller checks)
- Sidebar menu cache cleared per-user on save

### Schema changes
- New table: `tbl_admin_user_menu_access` (mirrors `tbl_admin_role_menu_access` but per admin_id)
- Foreign keys to `tbl_admin` (CASCADE delete) and `tbl_admin_menus` (CASCADE delete)

### Files changed
- `routes/admin.php` — all user routes use `{token}`, 2 new permission routes
- `app/Http/Controllers/Admin/AdminController.php` — encrypted tokens + permissions tab data + save/reset endpoints
- `app/Http/Controllers/Admin/TwoFactorController.php` — encrypted tokens
- `app/Http/Middleware/CheckAdminMenuAccess.php` — checks user overrides before role
- `app/Models/Admin.php` — getRouteToken(), findByToken(), hasMenuPermission() with user overrides
- `app/Models/AdminUserMenuAccess.php` — new model
- `resources/views/admin/pages/users/index.blade.php` — encrypted tokens in links
- `resources/views/admin/pages/users/edit.blade.php` — encrypted tokens + new Permissions tab

### Rollback notes
- The `tbl_admin_user_menu_access` table would need to be dropped manually
- All route changes are reversible via the version system
