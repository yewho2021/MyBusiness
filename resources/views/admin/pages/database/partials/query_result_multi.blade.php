{{-- Multi-query results: shows each statement with its own status --}}
@php
    $successCount = collect($multiResults)->where('status', 'success')->count();
    $errorCount = collect($multiResults)->where('status', 'error')->count();
    $totalStatements = count($multiResults);
@endphp

{{-- Summary bar --}}
<div style="padding:10px 16px;background:{{ $errorCount > 0 ? 'var(--c-danger-light)' : 'var(--c-success-light)' }};border-bottom:1px solid {{ $errorCount > 0 ? 'var(--c-danger-border)' : 'var(--c-success-border)' }};display:flex;align-items:center;gap:12px;flex-wrap:wrap">
    <span style="font-size:14px;font-weight:600;color:{{ $errorCount > 0 ? 'var(--c-danger)' : 'var(--c-success)' }}">
        <i class="fas fa-{{ $errorCount > 0 ? 'exclamation-triangle' : 'check-circle' }}"></i>
        {{ $totalStatements }} statement{{ $totalStatements > 1 ? 's' : '' }} executed
    </span>
    <span style="font-size:13px;color:var(--c-success);display:flex;align-items:center;gap:4px"><i class="fas fa-check-circle"></i> {{ $successCount }} OK</span>
    @if($errorCount > 0)
    <span style="font-size:13px;color:var(--c-danger);display:flex;align-items:center;gap:4px"><i class="fas fa-times-circle"></i> {{ $errorCount }} failed</span>
    @endif
    <span style="margin-left:auto;font-size:12px;color:var(--text-faint)">Total: {{ $totalTime }}ms</span>
</div>

{{-- Per-statement results --}}
@foreach($multiResults as $r)
<div class="multi-stmt" style="border-bottom:1px solid var(--border-light)">
    {{-- Statement header --}}
    <div style="padding:8px 16px;display:flex;align-items:center;gap:10px;cursor:pointer;background:{{ $r['status'] === 'error' ? 'var(--c-danger-light)' : 'var(--hover-bg)' }}" onclick="this.nextElementSibling.style.display=this.nextElementSibling.style.display==='none'?'block':'none';this.querySelector('.stmt-arrow').classList.toggle('fa-chevron-right');this.querySelector('.stmt-arrow').classList.toggle('fa-chevron-down')">

        <i class="fas fa-chevron-down stmt-arrow" style="font-size:10px;color:var(--text-faint);width:12px"></i>

        <span style="font-size:11px;font-weight:700;color:var(--text-faint);min-width:20px">#{{ $r['index'] }}</span>

        @if($r['status'] === 'success')
            <span style="display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:600;color:var(--c-success);background:var(--c-success-light);border:1px solid var(--c-success-border);padding:2px 8px;border-radius:4px"><i class="fas fa-check-circle"></i> OK</span>
        @else
            <span style="display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:600;color:var(--c-danger);background:var(--c-danger-light);border:1px solid var(--c-danger-border);padding:2px 8px;border-radius:4px"><i class="fas fa-times-circle"></i> Error</span>
        @endif

        <code style="flex:1;font-size:12px;color:var(--text-secondary);font-family:'JetBrains Mono',monospace;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $r['sql_preview'] }}</code>

        @if($r['status'] === 'success')
            @if($r['results'] !== null)
                <span style="font-size:11px;color:var(--c-secondary);background:var(--c-secondary-light);padding:2px 8px;border-radius:4px">{{ $r['row_count'] ?? count($r['results']) }} row(s)</span>
            @elseif($r['affected_rows'] !== null)
                <span style="font-size:11px;color:var(--c-success);background:var(--c-success-light);padding:2px 8px;border-radius:4px">{{ $r['affected_rows'] }} affected</span>
            @endif
        @endif

        <span style="font-size:11px;color:var(--hover-border)">{{ $r['time_ms'] }}ms</span>
    </div>

    {{-- Statement body (collapsible) --}}
    <div style="display:block">
        @if($r['status'] === 'error')
            <div style="padding:8px 16px 8px 58px;font-size:13px;color:var(--c-danger);background:var(--c-danger-light)">
                <i class="fas fa-exclamation-triangle" style="margin-right:4px"></i> {{ $r['error'] }}
            </div>
        @endif

        @if($r['results'] !== null && !empty($r['results']))
            <div style="overflow:auto;max-height:300px;margin:0 16px 8px 42px;border:1px solid var(--border-color);border-radius:6px">
                <table class="result-table" style="margin:0">
                    <thead>
                        <tr>
                            <th class="row-num">#</th>
                            @foreach($r['columns'] as $col)
                                <th>{{ $col }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($r['results'] as $i => $row)
                            <tr>
                                <td class="row-num">{{ $i + 1 }}</td>
                                @foreach((array)$row as $val)
                                    <td @if(is_null($val)) class="null-val" @elseif(is_numeric($val)) class="num-val" @endif>{!! is_null($val) ? '<i>NULL</i>' : e(\Illuminate\Support\Str::limit((string)$val, 120)) !!}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @elseif($r['results'] !== null && empty($r['results']))
            <div style="padding:6px 16px 6px 58px;font-size:12px;color:var(--text-faint)">Empty result set.</div>
        @endif
    </div>
</div>
@endforeach
