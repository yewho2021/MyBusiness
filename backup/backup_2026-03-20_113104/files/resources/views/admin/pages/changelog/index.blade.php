@extends('admin.layouts.app')

@section('title', 'System Change Log')

@push('styles')
    <style>
        .changelog-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .changelog-table th {
            text-align: left;
            padding: 14px 20px;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        .changelog-table td {
            padding: 14px 20px;
            font-size: 14px;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
            cursor: pointer;
            transition: background 0.2s;
        }

        .changelog-table tr:hover td {
            background: #fafbfc;
        }

        .app-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-office {
            background: #e0f2fe;
            color: #0369a1;
        }

        .badge-apps {
            background: #fef3c7;
            color: #92400e;
        }

        .version-tag {
            font-family: monospace;
            font-weight: 600;
            color: #4f46e5;
            background: #f5f3ff;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .details-row {
            display: none;
            background: #f8fafc;
        }

        .details-content {
            padding: 20px 40px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
            line-height: 1.6;
            color: #475569;
        }

        .details-content h4 {
            font-size: 13px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .tech-info {
            margin-top: 15px;
            padding: 12px;
            background: #0f172a;
            border-radius: 6px;
            color: #e2e8f0;
            font-family: 'Courier New', monospace;
            font-size: 12px;
        }

        .expand-icon {
            transition: transform 0.2s;
            color: #94a3b8;
        }

        .expanded .expand-icon {
            transform: rotate(180deg);
        }

        .expanded .main-row td {
            border-bottom: none;
            background: #f8fafc;
        }
    </style>
@endpush

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h1 style="font-size: 24px; font-weight: 700; color: #1e293b; margin-bottom: 4px;">System Change Log</h1>
            <p style="font-size: 14px; color: #64748b;">Tracking updates and patches across Office and Apps portals</p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('admin.changelog.index') }}" class="btn-outline {{ !request('app_type') ? 'active' : '' }}"
                style="padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 13px; border: 1px solid #d1d5db; color: #374151;">All
                Updates</a>
            <a href="{{ route('admin.changelog.index', ['app_type' => 'office']) }}"
                style="padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 13px; border: 1px solid #d1d5db; color: #374151; background: {{ request('app_type') == 'office' ? '#f3f4f6' : 'transparent' }}">Office
                Portal</a>
            <a href="{{ route('admin.changelog.index', ['app_type' => 'apps']) }}"
                style="padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 13px; border: 1px solid #d1d5db; color: #374151; background: {{ request('app_type') == 'apps' ? '#f3f4f6' : 'transparent' }}">Apps
                Portal</a>
        </div>
    </div>

    <div class="changelog-table-wrap">
        @if($logs->isEmpty())
            <div
                style="background: #fff; border-radius: 10px; border: 1px solid #e2e8f0; padding: 60px; text-align: center; color: #94a3b8;">
                <i class="fas fa-clipboard-list" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
                <p>No change logs found.</p>
            </div>
        @else
            <table class="changelog-table">
                <thead>
                    <tr>
                        <th style="width: 40px;"></th>
                        <th style="width: 150px;">Version</th>
                        <th style="width: 120px;">Portal</th>
                        <th>Title / Summary</th>
                        <th style="width: 180px;">Date (GMT+8)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr class="main-row" onclick="toggleRow({{ $log->id }}, this)">
                            <td><i class="fas fa-chevron-down expand-icon"></i></td>
                            <td><span class="version-tag">{{ $log->version }}</span></td>
                            <td>
                                <span class="app-badge badge-{{ $log->app_type }}">
                                    {{ $log->app_type == 'office' ? 'Office' : 'Portal' }}
                                </span>
                            </td>
                            <td style="font-weight: 600; color: #1e293b;">{{ $log->title }}</td>
                            <td style="color: #64748b;">{{ $log->created_at->format('d M Y, H:i') }}
                            </td>
                        </tr>
                        <tr id="details-{{ $log->id }}" class="details-row">
                            <td colspan="5">
                                <div class="details-content">
                                    <h4>Description & Changes</h4>
                                    <div style="white-space: pre-wrap;">{{ $log->details }}</div>

                                    @if($log->technical_info)
                                        <h4 style="margin-top: 20px;">Technical Details</h4>
                                        <div class="tech-info">
                                            <pre style="margin:0;">{{ json_encode($log->technical_info, JSON_PRETTY_PRINT) }}</pre>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top: 24px; display: flex; justify-content: center;">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        function toggleRow(id, rowEl) {
            const detailsRow = document.getElementById('details-' + id);
            const isVisible = detailsRow.style.display === 'table-row';

            // Close all others if needed (optional)
            // document.querySelectorAll('.details-row').forEach(r => r.style.display = 'none');
            // document.querySelectorAll('.main-row').forEach(r => r.classList.remove('expanded'));

            if (isVisible) {
                detailsRow.style.display = 'none';
                rowEl.classList.remove('expanded');
            } else {
                detailsRow.style.display = 'table-row';
                rowEl.classList.add('expanded');
            }
        }
    </script>
@endpush