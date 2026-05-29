@extends('admin.layouts.app')
@section('title', 'Menu Management')

@push('styles')
<style>
:root { --primary:var(--c-secondary); --primary-dark:var(--c-secondary); --success:var(--c-success); --danger:var(--c-danger); --warn:var(--c-warning); --g50:var(--hover-bg); --g100:var(--hover-bg); --g200:var(--border-color); --g300:var(--input-border); --g400:var(--text-faint); --g500:var(--text-muted); --g600:var(--text-secondary); --g700:var(--text-body); --g800:var(--text-heading); --g900:var(--text-heading); --r:10px; --rs:7px; }

.page-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:28px; }
.page-header h2 { font-size:22px; font-weight:700; color:var(--g900); margin:0 0 4px; }
.page-desc { font-size:13px; color:var(--g500); margin:0; }
.header-actions { display:flex; gap:10px; }

.btn { padding:9px 16px; border-radius:var(--rs); font-size:13px; font-weight:500; cursor:pointer; border:none; display:inline-flex; align-items:center; gap:7px; transition:all .15s; }
.btn-primary { background:var(--primary); color:#fff; }
.btn-primary:hover { background:var(--primary-dark); }
.btn-outline { background:#fff; color:var(--g700); border:1px solid var(--g200); }
.btn-outline:hover { background:var(--g50); border-color:var(--g300); }
.btn-cancel { background:var(--g100); color:var(--g600); }
.btn-cancel:hover { background:var(--g200); }

.alert { display:flex; align-items:center; gap:10px; padding:12px 16px; border-radius:var(--rs); margin-bottom:20px; font-size:13px; }
.alert-success { background:var(--c-success-light); color:var(--c-success); border:1px solid var(--c-success-border); }
.alert-danger { background:var(--c-secondary-light); color:var(--c-danger); border:1px solid var(--c-secondary-border); }

/* ── Group Cards ─────────────────────────────── */
.menu-container { display:flex; flex-direction:column; gap:20px; }

.menu-section { background:#fff; border-radius:var(--r); border:1px solid var(--g200); overflow:visible; transition:box-shadow .2s; }
.menu-section.drag-over { box-shadow:0 0 0 2px var(--primary),0 4px 12px rgba(59,130,246,.15); }

.section-header { display:flex; justify-content:space-between; align-items:center; padding:14px 20px; background:var(--g50); border-bottom:1px solid var(--g200); }
.section-title { display:flex; align-items:center; gap:10px; }
.group-drag-handle { color:var(--g300); cursor:grab; font-size:14px; padding:4px 2px; flex-shrink:0; }
.group-drag-handle:active { cursor:grabbing; }
.group-drag-handle:hover { color:var(--g500); }
.section-name { font-size:13px; font-weight:700; color:var(--g700); text-transform:uppercase; letter-spacing:.5px; }
.section-badge { font-size:11px; color:var(--g500); background:#fff; padding:3px 9px; border-radius:20px; border:1px solid var(--g200); }
.section-actions { display:flex; gap:6px; }

.btn-action { width:30px; height:30px; border-radius:var(--rs); border:1px solid var(--g200); background:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center; color:var(--g400); font-size:12px; transition:all .15s; }
.btn-action:hover { background:var(--g50); color:var(--g700); border-color:var(--g300); }
.btn-action-danger:hover { background:var(--c-danger-light); color:var(--danger); border-color:var(--c-secondary-border); }

/* ── Sortable List ───────────────────────────── */
.menu-list { min-height:48px; padding:6px 0; }
.menu-list:empty::after { content:'Drop menu items here'; display:block; text-align:center; padding:30px; color:var(--g400); font-size:13px; font-style:italic; }

.menu-item { display:flex; align-items:center; padding:10px 20px; gap:10px; border-bottom:1px solid var(--g100); background:#fff; cursor:default; transition:background .1s; }
.menu-item:last-child { border-bottom:none; }
.menu-item:hover { background:var(--hover-bg); }

.drag-handle { color:var(--g300); cursor:grab; font-size:14px; padding:4px; flex-shrink:0; }
.drag-handle:active { cursor:grabbing; }
.drag-handle:hover { color:var(--g500); }

.menu-icon-box { width:34px; height:34px; background:var(--primary); border-radius:var(--rs); display:flex; align-items:center; justify-content:center; color:#fff; font-size:13px; flex-shrink:0; }
.menu-icon-box.child { width:28px; height:28px; font-size:11px; background:var(--c-danger); }

.item-info { flex:1; min-width:0; }
.item-title { font-size:13px; font-weight:600; color:var(--g800); }
.item-meta { font-size:11px; color:var(--g400); margin-top:2px; display:flex; gap:12px; flex-wrap:wrap; }
.item-meta code { background:var(--g100); padding:1px 6px; border-radius:3px; font-size:10px; color:var(--g600); }

.status-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
.status-dot.active { background:var(--success); }
.status-dot.inactive { background:var(--g300); }

.item-actions { display:flex; gap:4px; flex-shrink:0; opacity:0; transition:opacity .15s; }
.menu-item:hover .item-actions { opacity:1; }

/* ── Children (sub-menus) ────────────────────── */
.children-list { margin-left:54px; border-left:2px solid var(--g200); min-height:8px; padding:2px 0; }
.children-list .menu-item { padding:8px 16px; border-bottom:1px solid var(--g100); }
.children-list .menu-item:last-child { border-bottom:none; }

/* ── Sortable feedback ───────────────────────── */
.sortable-ghost { opacity:.3; background:var(--c-secondary-light) !important; }
.sortable-drag { background:#fff !important; box-shadow:0 8px 24px rgba(0,0,0,.12); border-radius:var(--rs); z-index:9999; }
.sortable-chosen { background:var(--hover-bg); }
.drag-placeholder { height:4px; background:var(--primary); border-radius:2px; margin:2px 0; }

/* Group-level sortable */
.menu-container.sortable-group-drag > .sortable-ghost { opacity:.3; border:2px dashed var(--primary); }
.menu-container > .sortable-drag { box-shadow:0 12px 32px rgba(0,0,0,.18); border-radius:var(--r); }

/* ── Modal ───────────────────────────────────── */
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(17,24,39,.6); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(3px); }
.modal-overlay.show { display:flex; }
.modal { background:#fff; border-radius:var(--r); width:100%; max-width:480px; max-height:90vh; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,.2); animation:modalIn .2s ease; }
.modal.modal-lg { max-width:600px; }
@keyframes modalIn { from{opacity:0;transform:scale(.96) translateY(-8px)} to{opacity:1;transform:scale(1) translateY(0)} }

.modal-header { display:flex; justify-content:space-between; align-items:center; padding:18px 22px; border-bottom:1px solid var(--g200); }
.modal-header h3 { font-size:16px; font-weight:700; color:var(--g900); margin:0; }
.modal-close { width:32px; height:32px; border-radius:var(--rs); border:none; background:var(--g100); cursor:pointer; display:flex; align-items:center; justify-content:center; color:var(--g500); transition:all .15s; }
.modal-close:hover { background:var(--g200); color:var(--g700); }
.modal-body { padding:22px; max-height:calc(90vh - 140px); overflow-y:auto; }
.modal-footer { display:flex; justify-content:flex-end; gap:10px; padding:14px 22px; background:var(--g50); border-top:1px solid var(--g200); }

.form-section { margin-bottom:20px; padding-bottom:20px; border-bottom:1px solid var(--g100); }
.form-section:last-child { margin-bottom:0; padding-bottom:0; border-bottom:none; }
.form-section h4 { font-size:11px; font-weight:700; color:var(--g400); text-transform:uppercase; letter-spacing:.6px; margin:0 0 14px; }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.form-group { margin-bottom:14px; }
.form-group:last-child { margin-bottom:0; }
.form-group label { display:block; font-size:12px; font-weight:500; color:var(--g600); margin-bottom:5px; }
.form-control { width:100%; padding:9px 12px; border:1px solid var(--g200); border-radius:var(--rs); font-size:13px; color:var(--g800); transition:all .15s; background:#fff; }
.form-control:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(59,130,246,.1); }
.form-control::placeholder { color:var(--g400); }

.icon-input { display:flex; gap:8px; }
.icon-input .form-control { flex:1; cursor:pointer; background:var(--g50); }
.icon-preview { width:40px; height:40px; background:var(--g100); border-radius:var(--rs); display:flex; align-items:center; justify-content:center; color:var(--g600); font-size:16px; flex-shrink:0; }

/* ── Icon Picker ── */
.ip-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(90px,1fr)); gap:8px; }
.ip-cat { grid-column:1/-1; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:var(--g400); padding:12px 0 6px; border-bottom:1px solid var(--g100); margin-bottom:4px; }
.ip-cat:first-child { padding-top:0; }
.ip-item { display:flex; flex-direction:column; align-items:center; gap:6px; padding:12px 6px; border-radius:var(--rs); border:1.5px solid transparent; cursor:pointer; transition:all .15s; text-align:center; }
.ip-item:hover { background:var(--g50); border-color:var(--g200); }
.ip-item.selected { background:var(--g100); border-color:var(--primary); box-shadow:0 0 0 2px rgba(37,99,235,.15); }
.ip-item i { font-size:20px; color:var(--g700); width:28px; height:28px; display:flex; align-items:center; justify-content:center; }
.ip-item:hover i { color:var(--primary); }
.ip-item span { font-size:10px; color:var(--g500); line-height:1.2; word-break:break-all; max-width:100%; overflow:hidden; text-overflow:ellipsis; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; }

.toggle-label { display:flex; align-items:center; justify-content:space-between; cursor:pointer; }
.toggle-label>span { font-size:13px; color:var(--g700); }
.toggle-switch { position:relative; width:44px; height:24px; }
.toggle-switch input { opacity:0; width:0; height:0; }
.toggle-slider { position:absolute; cursor:pointer; inset:0; background:var(--g300); border-radius:24px; transition:.25s; }
.toggle-slider:before { position:absolute; content:""; height:18px; width:18px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:.25s; box-shadow:0 1px 2px rgba(0,0,0,.15); }
.toggle-switch input:checked+.toggle-slider { background:var(--primary); }
.toggle-switch input:checked+.toggle-slider:before { transform:translateX(20px); }

/* Toast */
.toast { position:fixed; bottom:24px; right:24px; padding:12px 18px; background:var(--g800); color:#fff; border-radius:var(--rs); font-size:13px; font-weight:500; display:flex; align-items:center; gap:8px; z-index:99999; box-shadow:0 8px 24px rgba(0,0,0,.15); animation:toastIn .3s ease; }
.toast-success { background:var(--success); }
.toast-error { background:var(--danger); }
.toast-info { background:var(--primary); }
@keyframes toastIn { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h2><i class="fas fa-sitemap" style="color:var(--primary);margin-right:8px"></i>Menu Management</h2>
        <p class="page-desc">Drag groups to reorder sections &middot; Drag items across groups &middot; Drag sub-menus between parents</p>
    </div>
    <div class="header-actions">
        <button class="btn btn-outline" onclick="openGroupModal()"><i class="fas fa-layer-group"></i> New Group</button>
        <button class="btn btn-primary" onclick="openMenuModal()"><i class="fas fa-plus"></i> New Menu</button>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
@endif

<div class="menu-container" id="menuContainer">
@foreach($menuGroups as $group)
<div class="menu-section" data-group-id="{{ $group->id }}">
    <div class="section-header">
        <div class="section-title">
            <div class="group-drag-handle" title="Drag to reorder group"><i class="fas fa-grip-vertical"></i></div>
            <span class="section-name">{{ $group->title }}</span>
            <span class="section-badge count-badge">{{ $menus->where('group_id', $group->id)->count() }} items</span>
        </div>
        <div class="section-actions">
            <button class="btn-action" onclick="editGroup({{ $group->id }}, '{{ addslashes($group->title) }}', '{{ $group->slug }}', {{ $group->sort_order }}, {{ $group->is_active }})" title="Edit"><i class="fas fa-pen"></i></button>
            <form action="{{ route('admin.menus.groups.destroy', $group->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete this group?')">@csrf @method('DELETE')
                <button type="submit" class="btn-action btn-action-danger" title="Delete"><i class="fas fa-trash"></i></button>
            </form>
        </div>
    </div>

    <div class="menu-list sortable-parent" id="group-{{ $group->id }}" data-group-id="{{ $group->id }}">
        @php $groupMenus = $menus->where('group_id', $group->id)->whereNull('parent_id')->sortBy('sort_order'); @endphp
        @forelse($groupMenus as $menu)
        <div class="menu-item" data-id="{{ $menu->id }}" data-group="{{ $group->id }}">
            <div class="drag-handle"><i class="fas fa-grip-vertical"></i></div>
            <div class="menu-icon-box"><i class="{{ $menu->icon ?? 'fas fa-circle' }}"></i></div>
            <div class="item-info">
                <div class="item-title">{{ $menu->title }}</div>
                <div class="item-meta">
                    @if($menu->route_name)<span><i class="fas fa-route"></i> <code>{{ $menu->route_name }}</code></span>@endif
                    @if($menu->permission_key)<span><i class="fas fa-key"></i> <code>{{ $menu->permission_key }}</code></span>@endif
                </div>
            </div>
            <div class="status-dot {{ $menu->is_active ? 'active' : 'inactive' }}" title="{{ $menu->is_active ? 'Active' : 'Inactive' }}"></div>
            <div class="item-actions">
                <button class="btn-action" onclick='editMenu(@json($menu))' title="Edit"><i class="fas fa-pen"></i></button>
                <form action="{{ route('admin.menus.destroy.menu', $menu->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')
                    <button type="submit" class="btn-action btn-action-danger" title="Delete"><i class="fas fa-trash"></i></button>
                </form>
            </div>
        </div>

        {{-- Children --}}
        <div class="children-list sortable-children" data-parent-id="{{ $menu->id }}" data-group-id="{{ $group->id }}">
            @php $childMenus = $menus->where('parent_id', $menu->id)->sortBy('sort_order'); @endphp
            @foreach($childMenus as $child)
            <div class="menu-item" data-id="{{ $child->id }}" data-group="{{ $group->id }}">
                <div class="drag-handle"><i class="fas fa-grip-vertical"></i></div>
                <div class="menu-icon-box child"><i class="{{ $child->icon ?? 'fas fa-circle' }}"></i></div>
                <div class="item-info">
                    <div class="item-title">{{ $child->title }}</div>
                    <div class="item-meta">
                        @if($child->route_name)<span><i class="fas fa-route"></i> <code>{{ $child->route_name }}</code></span>@endif
                        @if($child->permission_key)<span><i class="fas fa-key"></i> <code>{{ $child->permission_key }}</code></span>@endif
                    </div>
                </div>
                <div class="status-dot {{ $child->is_active ? 'active' : 'inactive' }}" title="{{ $child->is_active ? 'Active' : 'Inactive' }}"></div>
                <div class="item-actions">
                    <button class="btn-action" onclick='editMenu(@json($child))' title="Edit"><i class="fas fa-pen"></i></button>
                    <form action="{{ route('admin.menus.destroy.menu', $child->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')
                        <button type="submit" class="btn-action btn-action-danger" title="Delete"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @empty
        @endforelse
    </div>
</div>
@endforeach
</div>

{{-- Group Modal --}}
<div class="modal-overlay" id="groupModal">
<div class="modal">
    <div class="modal-header">
        <h3 id="groupModalTitle">New Menu Group</h3>
        <button class="modal-close" onclick="closeGroupModal()"><i class="fas fa-times"></i></button>
    </div>
    <form id="groupForm" method="POST">@csrf
        <div id="groupMethodField"></div>
        <div class="modal-body">
            <div class="form-group"><label>Title</label><input type="text" name="title" id="groupTitle" class="form-control" required placeholder="MAIN MENU"></div>
            <div class="form-row">
                <div class="form-group"><label>Slug</label><input type="text" name="slug" id="groupSlug" class="form-control" required placeholder="main-menu"></div>
                <div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" id="groupSortOrder" class="form-control" required min="0" value="0"></div>
            </div>
            <div class="form-group"><label class="toggle-label"><span>Active</span><div class="toggle-switch"><input type="checkbox" name="is_active" id="groupIsActive" checked><span class="toggle-slider"></span></div></label></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-cancel" onclick="closeGroupModal()">Cancel</button>
            <button type="submit" class="btn btn-primary" id="groupSubmitBtn">Create Group</button>
        </div>
    </form>
</div>
</div>

{{-- Menu Modal --}}
<div class="modal-overlay" id="menuModal">
<div class="modal modal-lg">
    <div class="modal-header">
        <h3 id="menuModalTitle">New Menu Item</h3>
        <button class="modal-close" onclick="closeMenuModal()"><i class="fas fa-times"></i></button>
    </div>
    <form id="menuForm" method="POST">@csrf
        <div id="menuMethodField"></div>
        <div class="modal-body">
            <div class="form-section"><h4>Basic</h4>
                <div class="form-row">
                    <div class="form-group"><label>Title</label><input type="text" name="title" id="menuTitle" class="form-control" required placeholder="Dashboard"></div>
                    <div class="form-group"><label>Icon Class</label><div class="icon-input"><input type="text" name="icon" id="menuIcon" class="form-control" placeholder="fas fa-home" oninput="previewIcon(this.value)" readonly onclick="openIconPicker()"><div class="icon-preview" id="iconPreview" onclick="openIconPicker()" title="Click to browse icons" style="cursor:pointer"><i class="fas fa-home"></i></div></div><small style="color:var(--g400);font-size:11px;margin-top:4px;display:block;">Click the field or icon to browse</small></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Group</label><select name="group_id" id="menuGroupId" class="form-control" required>@foreach($menuGroups as $g)<option value="{{ $g->id }}">{{ $g->title }}</option>@endforeach</select></div>
                    <div class="form-group"><label>Parent Menu</label><select name="parent_id" id="menuParentId" class="form-control"><option value="">None (Top Level)</option>@foreach($menus->whereNull('parent_id') as $m)<option value="{{ $m->id }}" data-group="{{ $m->group_id }}">{{ $m->group->title ?? '' }} → {{ $m->title }}</option>@endforeach</select></div>
                </div>
            </div>
            <div class="form-section"><h4>Navigation</h4>
                <div class="form-row">
                    <div class="form-group"><label>Route Name</label>
                        <div class="icon-input">
                            <select name="route_name" id="menuRouteName" class="form-control" style="flex:1;">
                                <option value="">— No route (parent menu) —</option>
                                @php
                                    $adminRoutes = collect(\Route::getRoutes()->getRoutesByName())
                                        ->filter(fn($r, $name) => str_starts_with($name, 'admin.') && !str_contains($name, 'login') && !str_contains($name, 'logout'))
                                        ->keys()
                                        ->sort()
                                        ->groupBy(fn($name) => explode('.', $name)[1] ?? 'other');
                                @endphp
                                @foreach($adminRoutes as $module => $routes)
                                    <optgroup label="{{ ucfirst(str_replace('-', ' ', $module)) }}">
                                        @foreach($routes as $route)
                                            <option value="{{ $route }}"
                                                {{ str_ends_with($route, '.index') ? 'style=font-weight:600' : '' }}>
                                                {{ $route }}{{ str_ends_with($route, '.index') ? ' ★' : '' }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            <div class="icon-preview" id="routeCheck" title="Route status" style="font-size:13px;"><i class="fas fa-route" style="color:var(--g400)"></i></div>
                        </div>
                        <small style="color:var(--g400);font-size:11px;margin-top:4px;display:block;">★ = main page. Leave empty for parent-only items.</small>
                    </div>
                    <div class="form-group"><label>Custom URL</label><input type="text" name="url" id="menuUrl" class="form-control" placeholder="/admin/page"><small style="color:var(--g400);font-size:11px;margin-top:4px;display:block;">Optional — overrides route if set</small></div>
                </div>
            </div>
            <div class="form-section"><h4>Settings</h4>
                <div class="form-row">
                    <div class="form-group"><label>Permission Key</label><input type="text" name="permission_key" id="menuPermissionKey" class="form-control" placeholder="dashboard"></div>
                    <div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" id="menuSortOrder" class="form-control" required min="0" value="0"></div>
                </div>
                <div class="form-group"><label class="toggle-label"><span>Active</span><div class="toggle-switch"><input type="checkbox" name="is_active" id="menuIsActive" checked><span class="toggle-slider"></span></div></label></div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-cancel" onclick="closeMenuModal()">Cancel</button>
            <button type="submit" class="btn btn-primary" id="menuSubmitBtn">Create Menu</button>
        </div>
    </form>
</div>
</div>
{{-- ──────── Icon Picker Modal ──────── --}}
<div class="modal-overlay" id="iconPickerModal">
<div class="modal modal-lg" style="max-width:720px;">
    <div class="modal-header">
        <h3><i class="fas fa-icons" style="color:var(--primary)"></i> Choose Icon</h3>
        <button class="modal-close" onclick="closeIconPicker()"><i class="fas fa-times"></i></button>
    </div>
    <div style="padding:14px 22px;border-bottom:1px solid var(--g200);">
        <input type="text" id="iconSearch" class="form-control" placeholder="Search icons... (e.g. user, chart, file)" oninput="filterIcons(this.value)" style="width:100%;">
    </div>
    <div class="modal-body" style="max-height:55vh;overflow-y:auto;padding:16px 22px;">
        <div id="iconGrid" class="ip-grid"></div>
        <div id="iconEmpty" style="display:none;text-align:center;padding:40px;color:var(--g400);">
            <i class="fas fa-search" style="font-size:32px;margin-bottom:12px;display:block;"></i>
            <p>No icons match your search</p>
        </div>
    </div>
    <div class="modal-footer" style="justify-content:space-between;">
        <div style="font-size:12px;color:var(--g400);" id="iconCount">0 icons</div>
        <div style="display:flex;gap:8px;">
            <button type="button" class="btn btn-outline" onclick="closeIconPicker()">Cancel</button>
            <button type="button" class="btn btn-outline" onclick="document.getElementById('menuIcon').removeAttribute('readonly');closeIconPicker();" style="font-size:12px;">Type manually</button>
        </div>
    </div>
</div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
const CSRF = '{{ csrf_token() }}';
const SAVE_URL = '{{ route("admin.menus.update-order") }}';
const SAVE_GROUP_URL = '{{ route("admin.menus.groups.update-order") }}';

// ─── Initialize all sortable lists ────────────────
function initSortables() {
    // Group-level sorting (drag entire groups up/down)
    new Sortable(document.getElementById('menuContainer'), {
        group: 'groups',
        animation: 250,
        handle: '.group-drag-handle',
        ghostClass: 'sortable-ghost',
        dragClass: 'sortable-drag',
        draggable: '.menu-section',
        swapThreshold: 0.65,
        onEnd: handleGroupDragEnd
    });

    // Parent-level lists (items within each group, cross-group enabled)
    document.querySelectorAll('.sortable-parent').forEach(el => {
        new Sortable(el, {
            group: 'parents',
            animation: 180,
            handle: '.drag-handle',
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            fallbackOnBody: true,
            swapThreshold: 0.65,
            // Only drag .menu-item (not .children-list)
            draggable: '> .menu-item',
            onEnd: handleDragEnd
        });
    });

    // Children lists (sub-menus, cross-parent enabled)
    document.querySelectorAll('.sortable-children').forEach(el => {
        new Sortable(el, {
            group: 'children',
            animation: 180,
            handle: '.drag-handle',
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            fallbackOnBody: true,
            swapThreshold: 0.65,
            onEnd: handleDragEnd
        });
    });
}

function handleDragEnd(evt) {
    saveAllOrder();
    updateBadges();
}

function handleGroupDragEnd(evt) {
    saveGroupOrder();
}

// ─── Save group order ─────────────────────────────
function saveGroupOrder() {
    const groups = [];
    document.querySelectorAll('#menuContainer > .menu-section').forEach(el => {
        groups.push(el.dataset.groupId);
    });

    showToast('Saving group order...', 'info');

    fetch(SAVE_GROUP_URL, {
        method: 'POST',
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ groups })
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) showToast('Group order saved!', 'success');
        else showToast('Save failed', 'error');
    })
    .catch(() => showToast('Save failed', 'error'));
}

// ─── Save complete order across all groups ────────
function saveAllOrder() {
    const items = [];

    document.querySelectorAll('.sortable-parent').forEach(parentList => {
        const groupId = parentList.dataset.groupId;

        // Walk through direct children of the parent list
        parentList.querySelectorAll(':scope > .menu-item').forEach((el, idx) => {
            const id = el.dataset.id;
            items.push({ id, parent_id: null, group_id: groupId });

            // Find children list that follows this item
            const childrenList = el.nextElementSibling;
            if (childrenList && childrenList.classList.contains('children-list')) {
                const parentId = id;
                childrenList.querySelectorAll(':scope > .menu-item').forEach((child, cIdx) => {
                    items.push({ id: child.dataset.id, parent_id: parentId, group_id: groupId });
                });
            }
        });
    });

    showToast('Saving...', 'info');

    fetch(SAVE_URL, {
        method: 'POST',
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ items })
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) showToast('Order saved!', 'success');
        else showToast('Save failed', 'error');
    })
    .catch(() => showToast('Save failed', 'error'));
}

function updateBadges() {
    document.querySelectorAll('.menu-section').forEach(section => {
        const groupId = section.dataset.groupId;
        const list = section.querySelector('.sortable-parent');
        const count = list ? list.querySelectorAll('.menu-item').length : 0;
        const badge = section.querySelector('.count-badge');
        if (badge) badge.textContent = count + ' items';
    });
}

// ─── Toast ────────────────────────────────────────
function showToast(msg, type) {
    document.querySelectorAll('.toast').forEach(t => t.remove());
    const icons = { success:'check-circle', error:'exclamation-circle', info:'spinner fa-spin' };
    const t = document.createElement('div');
    t.className = 'toast toast-' + type;
    t.innerHTML = '<i class="fas fa-' + icons[type] + '"></i> ' + msg;
    document.body.appendChild(t);
    if (type !== 'info') setTimeout(() => { t.style.opacity='0'; t.style.transform='translateY(8px)'; t.style.transition='all .3s'; setTimeout(() => t.remove(), 300); }, 2500);
}

// ─── Icon preview ─────────────────────────────────
function previewIcon(v) { document.getElementById('iconPreview').innerHTML = '<i class="' + (v || 'fas fa-home') + '"></i>'; }

// ─── Route select indicator ──────────────────────
document.addEventListener('DOMContentLoaded', function() {
    var routeSel = document.getElementById('menuRouteName');
    if (routeSel) {
        routeSel.addEventListener('change', function() {
            var check = document.getElementById('routeCheck');
            if (this.value) {
                check.innerHTML = '<i class="fas fa-check-circle" style="color:var(--success)"></i>';
                check.title = this.value;
            } else {
                check.innerHTML = '<i class="fas fa-route" style="color:var(--g400)"></i>';
                check.title = 'No route selected';
            }
        });
    }
});

// ─── Icon Picker ──────────────────────────────────
var IP_ICONS = {
    'Navigation': ['home','bars','arrow-left','arrow-right','arrow-up','arrow-down','chevron-left','chevron-right','chevron-up','chevron-down','angles-left','angles-right','caret-down','caret-up','external-link-alt','link','unlink','sitemap','route','compass','location-dot','map-marker-alt','directions','sign-out-alt','sign-in-alt'],
    'User & Access': ['user','users','user-plus','user-minus','user-edit','user-shield','user-cog','user-tag','user-lock','user-check','user-circle','id-card','id-badge','address-book','people-group','people-arrows','handshake','key','lock','lock-open','unlock','shield-alt','shield-halved','fingerprint'],
    'Data & Tables': ['database','table','th','th-large','th-list','list','list-ul','list-ol','stream','sort','sort-up','sort-down','filter','search','magnifying-glass','eye','eye-slash','hashtag','barcode','qrcode'],
    'Files & Documents': ['file','file-alt','file-pdf','file-word','file-excel','file-powerpoint','file-image','file-video','file-audio','file-code','file-archive','file-csv','file-lines','file-export','file-import','folder','folder-open','folder-plus','folder-minus','copy','paste','clipboard','clipboard-list','clipboard-check'],
    'Media & Images': ['image','images','photo-video','camera','video','film','play','pause','stop','volume-up','volume-down','volume-mute','music','headphones','microphone','podcast','palette','paint-brush','pen','pencil-alt','eraser','crop','adjust','wand-magic-sparkles'],
    'Charts & Analytics': ['chart-bar','chart-line','chart-pie','chart-area','chart-column','poll','signal','tachometer-alt','gauge-high','percentage','calculator','square-root-alt','infinity','bullseye','crosshairs','analytics'],
    'Communication': ['envelope','envelope-open','paper-plane','inbox','reply','reply-all','share','share-alt','comment','comments','comment-dots','message','phone','phone-alt','mobile-alt','fax','bell','bell-slash','bullhorn','rss','at','hashtag'],
    'E-commerce': ['shopping-cart','cart-shopping','store','shop','bag-shopping','credit-card','money-bill','money-bill-wave','coins','wallet','receipt','tags','tag','percent','gift','box','boxes-stacked','truck','shipping-fast','dolly'],
    'System & Settings': ['cog','cogs','gear','gears','sliders-h','wrench','tools','screwdriver-wrench','hammer','plug','power-off','sync','rotate','refresh','redo','undo','history','clock','stopwatch','timer','terminal','code','bug','broom','magic','wand-magic-sparkles'],
    'Layout & UI': ['columns','grip-vertical','grip-horizontal','border-all','expand','compress','maximize','minimize','window-maximize','window-minimize','window-restore','layer-group','object-group','object-ungroup','shapes','draw-polygon','vector-square','puzzle-piece'],
    'Status & Alerts': ['check','check-circle','check-double','times','times-circle','exclamation','exclamation-triangle','exclamation-circle','info-circle','question-circle','ban','minus-circle','plus-circle','star','star-half-alt','heart','thumbs-up','thumbs-down','flag','bookmark'],
    'Arrows & Actions': ['download','upload','cloud-download-alt','cloud-upload-alt','cloud','save','trash','trash-alt','edit','pen-to-square','plus','minus','plus-circle','minus-circle','arrow-circle-up','arrow-circle-down','exchange-alt','random','compress-arrows-alt','expand-arrows-alt'],
    'Content': ['heading','paragraph','align-left','align-center','align-right','align-justify','indent','outdent','quote-left','quote-right','bold','italic','underline','strikethrough','text-height','font','spell-check','language','globe','earth-americas','book','book-open','newspaper','blog'],
    'Security & Legal': ['shield-alt','user-shield','user-lock','lock','key','fingerprint','mask','user-secret','gavel','balance-scale','scroll','file-contract','stamp','certificate','award','medal','trophy','crown'],
    'Server & Cloud': ['server','database','hdd','microchip','memory','network-wired','wifi','satellite-dish','cloud','cloud-download-alt','cloud-upload-alt','docker','cube','cubes','box','archive','warehouse']
};

var ipAllItems = [];
var ipCurrentValue = '';

function buildIconGrid() {
    var grid = document.getElementById('iconGrid');
    var html = '';
    var count = 0;
    var seen = {};
    for (var cat in IP_ICONS) {
        html += '<div class="ip-cat">' + cat + '</div>';
        IP_ICONS[cat].forEach(function(name) {
            if (seen[name]) return;
            seen[name] = true;
            var cls = 'fas fa-' + name;
            html += '<div class="ip-item" data-icon="' + cls + '" data-name="' + name + '" onclick="selectIcon(\'' + cls + '\')" title="' + cls + '">';
            html += '<i class="' + cls + '"></i>';
            html += '<span>' + name.replace(/-/g, ' ') + '</span>';
            html += '</div>';
            count++;
        });
    }
    grid.innerHTML = html;
    document.getElementById('iconCount').textContent = count + ' icons';
    ipAllItems = grid.querySelectorAll('.ip-item');
}

function openIconPicker() {
    if (!ipAllItems.length) buildIconGrid();
    // Highlight current selection
    var current = document.getElementById('menuIcon').value;
    ipAllItems.forEach(function(el) {
        el.classList.toggle('selected', el.dataset.icon === current);
    });
    document.getElementById('iconSearch').value = '';
    filterIcons('');
    document.getElementById('iconPickerModal').classList.add('show');
    document.body.style.overflow = 'hidden';
    setTimeout(function() { document.getElementById('iconSearch').focus(); }, 100);
}

function closeIconPicker() {
    document.getElementById('iconPickerModal').classList.remove('show');
    document.body.style.overflow = '';
}

function selectIcon(cls) {
    document.getElementById('menuIcon').value = cls;
    previewIcon(cls);
    // Highlight selected
    ipAllItems.forEach(function(el) {
        el.classList.toggle('selected', el.dataset.icon === cls);
    });
    closeIconPicker();
}

function filterIcons(query) {
    var q = query.toLowerCase().trim();
    var visible = 0;
    var visibleCats = {};
    ipAllItems.forEach(function(el) {
        var match = !q || el.dataset.name.indexOf(q) !== -1 || el.dataset.icon.indexOf(q) !== -1;
        el.style.display = match ? '' : 'none';
        if (match) {
            visible++;
            // Find parent category
            var prev = el.previousElementSibling;
            while (prev && !prev.classList.contains('ip-cat')) prev = prev.previousElementSibling;
            if (prev) visibleCats[prev.textContent] = true;
        }
    });
    // Show/hide category headers
    document.querySelectorAll('.ip-cat').forEach(function(cat) {
        cat.style.display = visibleCats[cat.textContent] ? '' : 'none';
    });
    document.getElementById('iconEmpty').style.display = visible === 0 ? 'block' : 'none';
    document.getElementById('iconCount').textContent = visible + ' icons';
}

// ─── Group modal ──────────────────────────────────
function openGroupModal() {
    document.getElementById('groupModalTitle').textContent = 'New Menu Group';
    document.getElementById('groupForm').action = '{{ route("admin.menus.groups.store") }}';
    document.getElementById('groupMethodField').innerHTML = '';
    document.getElementById('groupTitle').value = '';
    document.getElementById('groupSlug').value = '';
    document.getElementById('groupSortOrder').value = '0';
    document.getElementById('groupIsActive').checked = true;
    document.getElementById('groupSubmitBtn').textContent = 'Create Group';
    document.getElementById('groupModal').classList.add('show');
}

function editGroup(id, title, slug, sortOrder, isActive) {
    document.getElementById('groupModalTitle').textContent = 'Edit Menu Group';
    document.getElementById('groupForm').action = '/menus/groups/' + id;
    document.getElementById('groupMethodField').innerHTML = '@method("PUT")';
    document.getElementById('groupTitle').value = title;
    document.getElementById('groupSlug').value = slug;
    document.getElementById('groupSortOrder').value = sortOrder;
    document.getElementById('groupIsActive').checked = isActive == 1;
    document.getElementById('groupSubmitBtn').textContent = 'Update Group';
    document.getElementById('groupModal').classList.add('show');
}

function closeGroupModal() { document.getElementById('groupModal').classList.remove('show'); }

// ─── Menu modal ───────────────────────────────────
function openMenuModal() {
    document.getElementById('menuModalTitle').textContent = 'New Menu Item';
    document.getElementById('menuForm').action = '{{ route("admin.menus.store") }}';
    document.getElementById('menuMethodField').innerHTML = '';
    ['menuGroupId','menuParentId','menuTitle','menuIcon','menuRouteName','menuUrl','menuPermissionKey'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('menuRouteName').dispatchEvent(new Event('change'));
    document.getElementById('menuSortOrder').value = '0';
    document.getElementById('menuIsActive').checked = true;
    document.getElementById('menuSubmitBtn').textContent = 'Create Menu';
    previewIcon('');
    document.getElementById('menuModal').classList.add('show');
}

function editMenu(menu) {
    document.getElementById('menuModalTitle').textContent = 'Edit Menu Item';
    document.getElementById('menuForm').action = '/menus/' + menu.id;
    document.getElementById('menuMethodField').innerHTML = '@method("PUT")';
    document.getElementById('menuGroupId').value = menu.group_id;
    document.getElementById('menuParentId').value = menu.parent_id || '';
    document.getElementById('menuTitle').value = menu.title;
    document.getElementById('menuIcon').value = menu.icon || '';
    document.getElementById('menuRouteName').value = menu.route_name || '';
    document.getElementById('menuRouteName').dispatchEvent(new Event('change'));
    document.getElementById('menuUrl').value = menu.url || '';
    document.getElementById('menuPermissionKey').value = menu.permission_key || '';
    document.getElementById('menuSortOrder').value = menu.sort_order;
    document.getElementById('menuIsActive').checked = menu.is_active == 1;
    document.getElementById('menuSubmitBtn').textContent = 'Update Menu';
    previewIcon(menu.icon || '');
    document.getElementById('menuModal').classList.add('show');
}

function closeMenuModal() { document.getElementById('menuModal').classList.remove('show'); }

// ─── Close modals on backdrop/escape ──────────────
['groupModal','menuModal'].forEach(id => {
    
});

// ─── Auto-generate slug from title ────────────────
document.getElementById('groupTitle').addEventListener('input', function() {
    const slug = document.getElementById('groupSlug');
    if (!slug.dataset.manual) slug.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
});
document.getElementById('groupSlug').addEventListener('input', function() { this.dataset.manual = '1'; });

// ─── Init ─────────────────────────────────────────
document.addEventListener('DOMContentLoaded', initSortables);
</script>
@endpush
