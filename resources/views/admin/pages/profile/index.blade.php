@extends('admin.layouts.app')
@section('title', 'My Profile')

@section('content')
<div class="sc-page-header">
    <div>
        <h2 class="sc-page-title"><i class="fas fa-user-circle" style="color:var(--text-muted);margin-right:4px;"></i> My Profile</h2>
        <p class="sc-page-subtitle">Manage your personal information and security settings</p>
    </div>
</div>

@if(session('warning'))
    <x-alert type="warning"><i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}</x-alert>
@endif
@if(session('success'))
    <x-alert type="success"><i class="fas fa-check-circle"></i> {{ session('success') }}</x-alert>
@endif
@if(session('error'))
    <x-alert type="danger"><i class="fas fa-times-circle"></i> {{ session('error') }}</x-alert>
@endif
@if($errors->any())
    <x-alert type="danger"><i class="fas fa-times-circle"></i> {{ $errors->first() }}</x-alert>
@endif

<div class="profile-grid">
    {{-- Left: Profile Info --}}
    <div>
        <x-card title="Personal Information">
            <x-slot:actions><i class="fas fa-id-card" style="color:var(--text-muted);"></i></x-slot:actions>

            <div class="profile-avatar">{{ strtoupper(substr($admin->name, 0, 1)) }}</div>
            <div class="profile-meta"><i class="fas fa-user-tag"></i> Role: {{ $admin->role->name ?? '—' }}</div>
            <div class="profile-meta"><i class="fas fa-at"></i> Username: {{ $admin->username }}</div>
            <div class="profile-meta"><i class="fas fa-clock"></i> Last login: {{ $admin->datetime_lastlogin?->format('M d, Y H:i') ?? 'Never' }}</div>
            <div class="profile-meta"><i class="fas fa-shield-alt"></i> 2FA: {{ $admin->twofa_enabled ? 'Enabled' : 'Disabled' }}</div>

            <hr style="border:none;border-top:1px solid var(--border-light,var(--border-light));margin:20px 0;">

            <form method="POST" action="{{ route('admin.profile.update') }}">
                @csrf
                @method('PUT')

                <x-form-group label="Name" name="name">
                    <x-input name="name" :value="$admin->name" required />
                </x-form-group>

                <x-form-group label="Email" name="email">
                    <x-input type="email" name="email" :value="$admin->email" required />
                </x-form-group>

                <x-form-group label="Username">
                    <x-input name="username_display" :value="$admin->username" readonly />
                    <p class="sc-form-help">Username cannot be changed from the profile page.</p>
                </x-form-group>

                <x-form-group label="Timezone" name="timezone" help="Common timezones are listed first. Leave as portal default unless you need a specific timezone.">
                    @php $currentTz = $admin->getRawOriginal('timezone') ?? ''; @endphp
                    <select name="timezone" class="sc-select">
                        <option value="">Use portal default ({{ \App\Models\Configuration::get('default_timezone', config('app.timezone', 'UTC')) }})</option>
                        @foreach(['Asia/Kuala_Lumpur','Asia/Singapore','Asia/Jakarta','Asia/Bangkok','Asia/Tokyo','Asia/Shanghai','Asia/Kolkata','Asia/Dubai','Asia/Dhaka','Europe/London','Europe/Paris','Europe/Berlin','America/New_York','America/Chicago','America/Denver','America/Los_Angeles','Australia/Sydney','Pacific/Auckland','UTC'] as $tz)
                            <option value="{{ $tz }}" {{ $currentTz === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                        @endforeach
                        <option disabled>──────────────</option>
                        @foreach(timezone_identifiers_list() as $tz)
                            @if(!in_array($tz, ['Asia/Kuala_Lumpur','Asia/Singapore','Asia/Jakarta','Asia/Bangkok','Asia/Tokyo','Asia/Shanghai','Asia/Kolkata','Asia/Dubai','Asia/Dhaka','Europe/London','Europe/Paris','Europe/Berlin','America/New_York','America/Chicago','America/Denver','America/Los_Angeles','Australia/Sydney','Pacific/Auckland','UTC']))
                                <option value="{{ $tz }}" {{ $currentTz === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                            @endif
                        @endforeach
                    </select>
                </x-form-group>

                <x-button type="submit" icon="fas fa-save">Update Profile</x-button>
            </form>
        </x-card>
    </div>

    {{-- Right: Password + Recent Logins --}}
    <div>
        <x-card title="Change Password" class="sc-mb-20">
            <x-slot:actions><i class="fas fa-key" style="color:var(--text-muted);"></i></x-slot:actions>

            <form method="POST" action="{{ route('admin.profile.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="name" value="{{ $admin->name }}">
                <input type="hidden" name="email" value="{{ $admin->email }}">

                <x-form-group label="Current Password" name="current_password">
                    <x-input type="password" name="current_password" placeholder="Enter current password" />
                </x-form-group>

                <x-form-group label="New Password" name="password">
                    <x-input type="password" name="password" placeholder="Min 8 chars, 1 uppercase, 1 number" />
                </x-form-group>

                <x-form-group label="Confirm New Password" name="password_confirmation">
                    <x-input type="password" name="password_confirmation" placeholder="Re-enter new password" />
                </x-form-group>

                <x-button type="submit" icon="fas fa-lock">Change Password</x-button>
            </form>
        </x-card>

        <x-card title="Recent Logins" :padding="false">
            <x-slot:actions><i class="fas fa-history" style="color:var(--text-muted);"></i></x-slot:actions>

            <div class="sc-overflow-x">
                <table class="sc-table">
                    <thead>
                        <tr><th>When</th><th>IP</th><th>Device</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse($recentLogins as $log)
                        <tr>
                            <td>{{ $log->login_at->format('M d, H:i') }}</td>
                            <td style="font-family:var(--font-mono);font-size:12px;">{{ $log->ip_address }}</td>
                            <td>{{ $log->browser ?? '—' }}</td>
                            <td>
                                @if($log->status === 'active')
                                    <x-badge variant="info">Active now</x-badge>
                                @elseif(in_array($log->status, ['success', 'expired']))
                                    <x-badge variant="success">{{ ucfirst($log->status) }}</x-badge>
                                @else
                                    <x-badge variant="danger">{{ ucfirst(str_replace('_', ' ', $log->status)) }}</x-badge>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="sc-text-center sc-text-muted" style="padding:24px;">No login history yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Profile-specific layout — not reusable across modules */
.profile-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
@media(max-width:900px) { .profile-grid { grid-template-columns:1fr; } }
.profile-avatar { width:72px; height:72px; border-radius:16px; background:var(--c-primary,var(--c-danger)); display:flex; align-items:center; justify-content:center; color:#fff; font-size:28px; font-weight:700; margin-bottom:16px; }
.profile-meta { font-size:13px; color:var(--text-muted); margin-bottom:4px; }
.profile-meta i { width:18px; text-align:center; margin-right:4px; }
.sc-mb-20 { margin-bottom:20px; }
</style>
@endpush
