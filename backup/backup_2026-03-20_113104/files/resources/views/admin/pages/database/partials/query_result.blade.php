@if($error)
    <div class="result-msg error"><i class="fas fa-times-circle"></i> {{ $error }}</div>
@endif

@if($affectedRows !== null)
    <div class="result-msg success"><i class="fas fa-check-circle"></i> Query OK. {{ $affectedRows }} row(s) affected. <span class="result-time">{{ $executionTime }}ms</span></div>
@endif

@if($results !== null)
    <div class="result-header">
        <div class="result-stats">
            <span class="result-badge">{{ count($results) }} row(s)</span>
            <span class="result-time">{{ $executionTime }}ms</span>
        </div>
        @if(!empty($results))
        <div style="display:flex;gap:6px">
            <button class="tb-btn" onclick="DatabaseManager.copyResultsJSON(this)" title="Copy as JSON"><i class="fas fa-copy"></i> JSON</button>
            <button class="tb-btn" onclick="DatabaseManager.exportResultsCSV(this)" title="Export as CSV"><i class="fas fa-file-csv"></i> CSV</button>
        </div>
        @endif
    </div>
    <div class="result-wrap">
        @if(empty($results))
            <div style="text-align:center;padding:30px;color:#94a3b8">No results returned.</div>
        @else
            <table class="result-table">
                <thead><tr><th class="row-num">#</th>@foreach($columns as $col)<th>{{ $col }}</th>@endforeach</tr></thead>
                <tbody>
                    @foreach($results as $i => $row)
                        <tr>
                            <td class="row-num">{{ $i + 1 }}</td>
                            @foreach((array)$row as $val)
                                <td @if(is_null($val)) class="null-val" @elseif(is_numeric($val)) class="num-val" @endif>{!! is_null($val) ? '<i>NULL</i>' : e(\Illuminate\Support\Str::limit((string)$val, 120)) !!}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endif
