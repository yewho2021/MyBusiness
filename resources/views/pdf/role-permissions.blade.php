@extends('pdf.layout')
@section('title', 'Role Permissions Matrix')

@section('styles')
<style>
    .perm-yes { text-align: center; color: #166534; font-weight: 700; font-size: 12px; }
    .perm-no { text-align: center; color: #d1d5db; font-size: 10px; }
    .role-header { background: #dc2626 !important; color: #fff !important; text-align: center !important; font-size: 9px !important; }
    .menu-title { font-weight: 600; padding-left: 20px; }
    .group-row td { background: #f1f5f9 !important; font-weight: 700; font-size: 10px; color: #475569; text-transform: uppercase; letter-spacing: .5px; }
</style>
@endsection

@section('content')
<div class="meta-bar">
    <strong>Roles:</strong> {{ $roles->count() }} |
    <strong>Menu Items:</strong> {{ $menuGroups->sum(fn($g) => $g->menus->count()) }} |
    <strong>Note:</strong> Administrator role has full access (bypasses all permission checks)
</div>

<table>
    <thead>
        <tr>
            <th style="width:200px;">Menu Item</th>
            @foreach($roles as $role)
            <th class="role-header" style="width:{{ 60 / max($roles->count(), 1) }}%;">{{ $role->name }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($menuGroups as $group)
        <tr class="group-row">
            <td colspan="{{ $roles->count() + 1 }}">{{ $group->title }}</td>
        </tr>
        @foreach($group->menus as $menu)
        <tr>
            <td class="menu-title">
                <i class="{{ $menu->icon }}" style="color:#94a3b8;margin-right:6px;font-size:10px;"></i>
                {{ $menu->title }}
            </td>
            @foreach($roles as $role)
            <td>
                @if($role->slug === 'administrator')
                    <div class="perm-yes">&#10003;</div>
                @else
                    @php
                        $roleAccess = $access->get($role->id);
                        $hasAccess = $roleAccess ? $roleAccess->where('menu_id', $menu->id)->first() : null;
                    @endphp
                    @if($hasAccess && $hasAccess->can_view)
                        <div class="perm-yes">&#10003;</div>
                    @else
                        <div class="perm-no">—</div>
                    @endif
                @endif
            </td>
            @endforeach
        </tr>
        @endforeach
        @endforeach
    </tbody>
</table>
@endsection
