@extends('admin.layouts.app')
@section('title', 'Menu Permissions')

@section('content')
<div class="page-header">
    <div>
        <h2>Menu Permissions</h2>
        <p class="page-desc">Assign menu access permissions to each role</p>
    </div>
    <div class="header-actions">
        <button type="submit" form="permissionForm" class="btn btn-primary">
            <i class="fas fa-save"></i> Save Changes
        </button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        {{ session('error') }}
    </div>
@endif

<form action="{{ route('admin.permissions.update') }}" method="POST" id="permissionForm">
    @csrf
    
    <div class="permission-container">
        <!-- Role Headers -->
        <div class="role-headers">
            <div class="role-header-spacer">
                <span class="legend-title">Menu</span>
            </div>
            @foreach($roles as $role)
            <div class="role-header">
                <span class="role-badge role-{{ $role->slug }}">{{ $role->name }}</span>
                <div class="role-quick-actions">
                    <button type="button" class="btn-mini" onclick="selectAllRole({{ $role->id }})" title="Select All">
                        <i class="fas fa-check-double"></i>
                    </button>
                    <button type="button" class="btn-mini" onclick="deselectAllRole({{ $role->id }})" title="Deselect All">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            @endforeach
        </div>
        
        <!-- Permission Sections -->
        @foreach($menuGroups as $group)
        <div class="permission-section">
            <div class="section-header">
                <span class="section-name">{{ $group->title }}</span>
                <span class="section-badge">{{ $menus->where('group_id', $group->id)->count() }} items</span>
            </div>
            
            <div class="permission-table">
                @php
                    $groupMenus = $menus->where('group_id', $group->id)->whereNull('parent_id');
                @endphp
                @foreach($groupMenus as $menu)
                <!-- Parent Menu -->
                <div class="permission-row parent-row">
                    <div class="menu-cell">
                        <div class="menu-icon-box">
                            <i class="{{ $menu->icon ?? 'fas fa-circle' }}"></i>
                        </div>
                        <span class="menu-name">{{ $menu->title }}</span>
                    </div>
                    @foreach($roles as $role)
                    @php
                        $key = $role->id . '-' . $menu->id;
                        $perm = $permissions[$key] ?? null;
                        $isAdmin = $role->slug === 'administrator';
                    @endphp
                    <div class="permission-cell" data-role="{{ $role->id }}">
                        <label class="perm-checkbox {{ $isAdmin ? 'disabled' : '' }}" title="View">
                            <input type="checkbox" name="permissions[{{ $key }}][can_view]" value="1" 
                                {{ ($perm && $perm->can_view) || $isAdmin ? 'checked' : '' }} 
                                {{ $isAdmin ? 'disabled' : '' }}>
                            <span class="checkmark"></span>
                            <span class="perm-label"><i class="fas fa-eye"></i> View</span>
                        </label>
                        <label class="perm-checkbox {{ $isAdmin ? 'disabled' : '' }}" title="Create">
                            <input type="checkbox" name="permissions[{{ $key }}][can_create]" value="1" 
                                {{ ($perm && $perm->can_create) || $isAdmin ? 'checked' : '' }} 
                                {{ $isAdmin ? 'disabled' : '' }}>
                            <span class="checkmark"></span>
                            <span class="perm-label"><i class="fas fa-plus"></i> Create</span>
                        </label>
                        <label class="perm-checkbox {{ $isAdmin ? 'disabled' : '' }}" title="Edit">
                            <input type="checkbox" name="permissions[{{ $key }}][can_edit]" value="1" 
                                {{ ($perm && $perm->can_edit) || $isAdmin ? 'checked' : '' }} 
                                {{ $isAdmin ? 'disabled' : '' }}>
                            <span class="checkmark"></span>
                            <span class="perm-label"><i class="fas fa-pen"></i> Edit</span>
                        </label>
                        <label class="perm-checkbox {{ $isAdmin ? 'disabled' : '' }}" title="Delete">
                            <input type="checkbox" name="permissions[{{ $key }}][can_delete]" value="1" 
                                {{ ($perm && $perm->can_delete) || $isAdmin ? 'checked' : '' }} 
                                {{ $isAdmin ? 'disabled' : '' }}>
                            <span class="checkmark"></span>
                            <span class="perm-label"><i class="fas fa-trash"></i> Delete</span>
                        </label>
                    </div>
                    @endforeach
                </div>
                
                <!-- Child Menus -->
                @php $childMenus = $menus->where('parent_id', $menu->id); @endphp
                @foreach($childMenus as $child)
                <div class="permission-row child-row">
                    <div class="menu-cell">
                        <div class="child-indicator"></div>
                        <div class="menu-icon-box small">
                            <i class="{{ $child->icon ?? 'fas fa-circle' }}"></i>
                        </div>
                        <span class="menu-name">{{ $child->title }}</span>
                    </div>
                    @foreach($roles as $role)
                    @php
                        $key = $role->id . '-' . $child->id;
                        $perm = $permissions[$key] ?? null;
                        $isAdmin = $role->slug === 'administrator';
                    @endphp
                    <div class="permission-cell" data-role="{{ $role->id }}">
                        <label class="perm-checkbox {{ $isAdmin ? 'disabled' : '' }}" title="View">
                            <input type="checkbox" name="permissions[{{ $key }}][can_view]" value="1" 
                                {{ ($perm && $perm->can_view) || $isAdmin ? 'checked' : '' }} 
                                {{ $isAdmin ? 'disabled' : '' }}>
                            <span class="checkmark"></span>
                            <span class="perm-label"><i class="fas fa-eye"></i> View</span>
                        </label>
                        <label class="perm-checkbox {{ $isAdmin ? 'disabled' : '' }}" title="Create">
                            <input type="checkbox" name="permissions[{{ $key }}][can_create]" value="1" 
                                {{ ($perm && $perm->can_create) || $isAdmin ? 'checked' : '' }} 
                                {{ $isAdmin ? 'disabled' : '' }}>
                            <span class="checkmark"></span>
                            <span class="perm-label"><i class="fas fa-plus"></i> Create</span>
                        </label>
                        <label class="perm-checkbox {{ $isAdmin ? 'disabled' : '' }}" title="Edit">
                            <input type="checkbox" name="permissions[{{ $key }}][can_edit]" value="1" 
                                {{ ($perm && $perm->can_edit) || $isAdmin ? 'checked' : '' }} 
                                {{ $isAdmin ? 'disabled' : '' }}>
                            <span class="checkmark"></span>
                            <span class="perm-label"><i class="fas fa-pen"></i> Edit</span>
                        </label>
                        <label class="perm-checkbox {{ $isAdmin ? 'disabled' : '' }}" title="Delete">
                            <input type="checkbox" name="permissions[{{ $key }}][can_delete]" value="1" 
                                {{ ($perm && $perm->can_delete) || $isAdmin ? 'checked' : '' }} 
                                {{ $isAdmin ? 'disabled' : '' }}>
                            <span class="checkmark"></span>
                            <span class="perm-label"><i class="fas fa-trash"></i> Delete</span>
                        </label>
                    </div>
                    @endforeach
                </div>
                @endforeach
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
    
    <!-- Footer -->
    <div class="permission-footer">
        <div class="legend">
            <div class="legend-item"><i class="fas fa-eye"></i> View - Can access the page</div>
            <div class="legend-item"><i class="fas fa-plus"></i> Create - Can add new items</div>
            <div class="legend-item"><i class="fas fa-pen"></i> Edit - Can modify items</div>
            <div class="legend-item"><i class="fas fa-trash"></i> Delete - Can remove items</div>
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Save Permissions
        </button>
    </div>
</form>
@endsection

@push('styles')
<style>
:root {
    --primary: #2563eb;
    --primary-dark: #1d4ed8;
    --success: #10b981;
    --danger: #ef4444;
    --warning: #f59e0b;
    --gray-50: #f9fafb;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
    --gray-300: #d1d5db;
    --gray-400: #9ca3af;
    --gray-500: #6b7280;
    --gray-600: #4b5563;
    --gray-700: #374151;
    --gray-800: #1f2937;
    --gray-900: #111827;
    --radius: 12px;
    --radius-sm: 8px;
    --shadow: 0 1px 3px rgba(0,0,0,0.1), 0 1px 2px rgba(0,0,0,0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
}

.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 32px; }
.page-header h2 { font-size: 24px; font-weight: 700; color: var(--gray-900); margin: 0 0 4px 0; }
.page-desc { font-size: 14px; color: var(--gray-500); margin: 0; }
.header-actions { display: flex; gap: 12px; }

.btn { padding: 10px 18px; border-radius: var(--radius-sm); font-size: 14px; font-weight: 500; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s; }
.btn-primary { background: var(--primary); color: #fff; }
.btn-primary:hover { background: var(--primary-dark); }

.alert { display: flex; align-items: center; gap: 12px; padding: 14px 18px; border-radius: var(--radius-sm); margin-bottom: 24px; font-size: 14px; }
.alert-success { background: #ecfdf5; color: #065f46; }
.alert-danger { background: #eff6ff; color: #991b1b; }

.permission-container { display: flex; flex-direction: column; gap: 0; }

/* Role Headers */
.role-headers { 
    display: grid; 
    grid-template-columns: 280px repeat({{ count($roles) }}, 1fr); 
    background: #fff; 
    border-radius: var(--radius) var(--radius) 0 0; 
    box-shadow: var(--shadow);
    position: sticky;
    top: 0;
    z-index: 10;
    border-bottom: 2px solid var(--gray-200);
}
.role-header-spacer { 
    padding: 20px 24px; 
    display: flex; 
    align-items: center; 
    border-right: 1px solid var(--gray-200);
}
.legend-title { font-size: 13px; font-weight: 600; color: var(--gray-500); text-transform: uppercase; letter-spacing: 0.5px; }
.role-header { 
    padding: 16px; 
    display: flex; 
    flex-direction: column; 
    align-items: center; 
    gap: 10px; 
    border-right: 1px solid var(--gray-100);
}
.role-header:last-child { border-right: none; }

.role-badge { 
    padding: 6px 16px; 
    border-radius: 20px; 
    font-size: 13px; 
    font-weight: 600; 
}
.role-administrator { background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%); color: #7c3aed; }
.role-supervisor { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); color: #d97706; }
.role-staff { background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); color: #059669; }

.role-quick-actions { display: flex; gap: 6px; }
.btn-mini { 
    width: 28px; 
    height: 28px; 
    border-radius: 6px; 
    border: 1px solid var(--gray-200); 
    background: #fff; 
    cursor: pointer; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    font-size: 11px; 
    color: var(--gray-500); 
    transition: all 0.2s;
}
.btn-mini:hover { background: var(--gray-50); color: var(--gray-700); border-color: var(--gray-300); }

/* Permission Sections */
.permission-section { 
    background: #fff; 
    box-shadow: var(--shadow);
    margin-bottom: 0;
}
.permission-section:last-child { border-radius: 0 0 var(--radius) var(--radius); }

.section-header { 
    display: flex; 
    align-items: center; 
    gap: 12px;
    padding: 14px 24px; 
    background: var(--gray-50); 
    border-bottom: 1px solid var(--gray-200);
    border-top: 1px solid var(--gray-200);
}
.section-name { 
    font-size: 12px; 
    font-weight: 700; 
    color: var(--primary); 
    text-transform: uppercase; 
    letter-spacing: 0.5px; 
}
.section-badge { 
    font-size: 11px; 
    color: var(--gray-500); 
    background: #fff; 
    padding: 3px 10px; 
    border-radius: 20px; 
    border: 1px solid var(--gray-200); 
}

/* Permission Table */
.permission-row { 
    display: grid; 
    grid-template-columns: 280px repeat({{ count($roles) }}, 1fr); 
    border-bottom: 1px solid var(--gray-100); 
    transition: background 0.2s;
}
.permission-row:hover { background: var(--gray-50); }
.permission-row:last-child { border-bottom: none; }

.parent-row { background: #fff; }
.child-row { background: var(--gray-50); }
.child-row:hover { background: var(--gray-100); }

.menu-cell { 
    display: flex; 
    align-items: center; 
    gap: 12px; 
    padding: 14px 24px; 
    border-right: 1px solid var(--gray-200);
}
.child-row .menu-cell { padding-left: 40px; }

.child-indicator { 
    width: 16px; 
    height: 16px; 
    border-left: 2px solid var(--gray-300); 
    border-bottom: 2px solid var(--gray-300); 
    border-radius: 0 0 0 4px; 
    margin-right: 4px; 
}

.menu-icon-box { 
    width: 36px; 
    height: 36px; 
    background: var(--primary); 
    border-radius: var(--radius-sm); 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    color: #fff; 
    font-size: 14px; 
    flex-shrink: 0; 
}
.menu-icon-box.small { width: 30px; height: 30px; font-size: 12px; }
.menu-name { font-size: 14px; font-weight: 500; color: var(--gray-800); }

.permission-cell { 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    gap: 6px; 
    padding: 14px 8px; 
    border-right: 1px solid var(--gray-100);
    flex-wrap: wrap;
}
.permission-cell:last-child { border-right: none; }

/* Custom Checkboxes */
.perm-checkbox { 
    display: flex; 
    align-items: center; 
    gap: 4px; 
    cursor: pointer; 
    padding: 4px 8px;
    border-radius: 6px;
    transition: all 0.2s;
}
.perm-checkbox:hover { background: var(--gray-100); }
.perm-checkbox.disabled { opacity: 0.6; cursor: not-allowed; }
.perm-checkbox.disabled:hover { background: transparent; }

.perm-checkbox input { display: none; }

.checkmark { 
    width: 18px; 
    height: 18px; 
    border: 2px solid var(--gray-300); 
    border-radius: 4px; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    transition: all 0.2s;
    background: #fff;
    flex-shrink: 0;
}
.checkmark::after { 
    content: ''; 
    display: none; 
    width: 5px; 
    height: 8px; 
    border: solid #fff; 
    border-width: 0 2px 2px 0; 
    transform: rotate(45deg); 
    margin-bottom: 2px;
}

.perm-checkbox input:checked + .checkmark { 
    background: var(--primary); 
    border-color: var(--primary); 
}
.perm-checkbox input:checked + .checkmark::after { display: block; }

.perm-checkbox.disabled input:checked + .checkmark { 
    background: var(--gray-400); 
    border-color: var(--gray-400); 
}

.perm-label { 
    font-size: 11px; 
    font-weight: 500; 
    color: var(--gray-600);
    display: flex;
    align-items: center;
    gap: 3px;
    white-space: nowrap;
}
.perm-label i { font-size: 10px; color: var(--gray-400); }

/* Footer */
.permission-footer { 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    margin-top: 24px;
    padding: 20px 24px;
    background: #fff;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
}

.legend { display: flex; gap: 20px; flex-wrap: wrap; }
.legend-item { 
    display: flex; 
    align-items: center; 
    gap: 6px; 
    font-size: 12px; 
    color: var(--gray-600); 
}
.legend-item i { color: var(--gray-400); font-size: 11px; }

/* Toast */
.toast {
    position: fixed;
    bottom: 24px;
    right: 24px;
    padding: 14px 20px;
    background: var(--gray-800);
    color: #fff;
    border-radius: var(--radius-sm);
    font-size: 14px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 10px;
    z-index: 99999;
    box-shadow: var(--shadow-lg);
    animation: toastIn 0.3s ease;
}
.toast-success { background: var(--success); }
.toast-error { background: var(--danger); }

@keyframes toastIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Responsive */
@media (max-width: 1400px) {
    .role-headers,
    .permission-row {
        grid-template-columns: 220px repeat({{ count($roles) }}, 1fr);
    }
    .perm-label span { display: none; }
}
</style>
@endpush

@push('scripts')
<script>
function selectAllRole(roleId) {
    document.querySelectorAll('.permission-cell[data-role="' + roleId + '"] input[type="checkbox"]:not(:disabled)').forEach(function(cb) {
        cb.checked = true;
    });
    showToast('All permissions selected', 'success');
}

function deselectAllRole(roleId) {
    document.querySelectorAll('.permission-cell[data-role="' + roleId + '"] input[type="checkbox"]:not(:disabled)').forEach(function(cb) {
        cb.checked = false;
    });
    showToast('All permissions cleared', 'success');
}

function showToast(message, type) {
    const existingToast = document.querySelector('.toast');
    if (existingToast) existingToast.remove();
    
    const toast = document.createElement('div');
    toast.className = 'toast toast-' + type;
    toast.innerHTML = '<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + '"></i> ' + message;
    document.body.appendChild(toast);
    
    setTimeout(function() {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
        toast.style.transition = 'all 0.3s';
        setTimeout(function() { toast.remove(); }, 300);
    }, 2000);
}

// Form submit feedback
document.getElementById('permissionForm').addEventListener('submit', function() {
    const btns = this.querySelectorAll('button[type="submit"]');
    btns.forEach(function(btn) {
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        btn.disabled = true;
    });
});
</script>
@endpush