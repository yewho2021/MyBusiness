@extends('pdf.layout')
@section('title', 'Admin Users Report')

@section('content')
<div class="meta-bar">
    <strong>Total Users:</strong> {{ $users->count() }} |
    <strong>Active:</strong> {{ $users->where('is_active', true)->count() }} |
    <strong>Inactive:</strong> {{ $users->where('is_active', false)->count() }}
</div>

<table>
    <thead>
        <tr>
            <th style="width:30px;">#</th>
            <th>Name</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>2FA</th>
            <th>Status</th>
            <th>Last Login</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $user)
        <tr>
            <td>{{ $user->id }}</td>
            <td style="font-weight:600;">{{ $user->name }}</td>
            <td>{{ $user->username }}</td>
            <td>{{ $user->email }}</td>
            <td><span class="badge badge-blue">{{ $user->role->name ?? '—' }}</span></td>
            <td>{!! $user->twofa_enabled ? '<span class="badge badge-green">ON</span>' : '<span class="badge badge-gray">OFF</span>' !!}</td>
            <td>{!! $user->is_active ? '<span class="badge badge-green">Active</span>' : '<span class="badge badge-red">Inactive</span>' !!}</td>
            <td>{{ $user->datetime_lastlogin?->format('Y-m-d H:i') ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
