@extends('admin.layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="dashboard">
    <div class="welcome-card">
        <h2>Welcome back, {{ $admin->name ?? 'Admin' }}!</h2>
        <p>You are logged in as <strong>{{ $admin->role->name ?? 'User' }}</strong></p>
    </div>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-info">
                <p>Total Users</p>
                <h3>1,234</h3>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-chart-line"></i></div>
            <div class="stat-info">
                <p>Revenue</p>
                <h3>$45,678</h3>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-shopping-cart"></i></div>
            <div class="stat-info">
                <p>Orders</p>
                <h3>892</h3>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon red"><i class="fas fa-tasks"></i></div>
            <div class="stat-info">
                <p>Pending Tasks</p>
                <h3>23</h3>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .welcome-card { background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 12px; padding: 30px; margin-bottom: 24px; color: #fff; }
    .welcome-card h2 { font-size: 24px; margin-bottom: 8px; }
    .welcome-card p { opacity: 0.9; }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; }
    .stat-card { background: #fff; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    .stat-icon { width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; background: #ede9fe; color: #7c3aed; }
    .stat-icon.green { background: #dcfce7; color: #16a34a; }
    .stat-icon.orange { background: #fef3c7; color: #d97706; }
    .stat-icon.red { background: #fee2e2; color: #dc2626; }
    .stat-info p { color: #64748b; font-size: 13px; margin-bottom: 4px; }
    .stat-info h3 { color: #1e293b; font-size: 24px; font-weight: 700; }
</style>
@endpush
