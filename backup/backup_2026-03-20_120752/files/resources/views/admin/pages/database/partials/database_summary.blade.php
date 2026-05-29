<div class="summary-tab-content">
    @php $totalRows = array_sum(array_column($tableList, 'rows')); @endphp
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-table"></i></div>
            <div><div class="stat-val">{{ count($tableList) }}</div><div class="stat-label">Tables</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-hdd"></i></div>
            <div><div class="stat-val">{{ $totalSize >= 1048576 ? number_format($totalSize/1048576,1).' MB' : number_format($totalSize/1024,1).' KB' }}</div><div class="stat-label">Database Size</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon amber"><i class="fas fa-layer-group"></i></div>
            <div><div class="stat-val">{{ number_format($totalRows) }}</div><div class="stat-label">Total Rows</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fas fa-server"></i></div>
            <div><div class="stat-val" style="font-size:14px">{{ config('database.connections.mysql.host') }}</div><div class="stat-label">Server</div></div>
        </div>
    </div>

    <div class="card" style="margin-bottom:0">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-table" style="color:#94a3b8;margin-right:6px"></i> All Tables</span>
            <div style="display:flex;gap:8px;align-items:center">
                <input type="text" class="search-box" style="width:200px" placeholder="Filter tables..." onkeyup="DatabaseManager.filterSummaryTables(this.value)">
                <button class="btn-xs btn-blue" onclick="DatabaseManager.openQueryTab()"><i class="fas fa-plus"></i> New SQL</button>
                <button class="btn-xs btn-blue" onclick="DatabaseManager.openExportTab()"><i class="fas fa-download"></i> Export</button>
            </div>
        </div>
        <div style="overflow:auto;max-height:calc(100vh - 360px)">
            <table class="summary-table">
                <thead>
                    <tr>
                        <th style="width:30px">#</th>
                        <th>Table Name</th>
                        <th style="text-align:right">Rows</th>
                        <th style="text-align:right">Size</th>
                        <th>Engine</th>
                        <th>Collation</th>
                        <th style="width:80px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tableList as $i => $t)
                    <tr class="summary-row">
                        <td style="color:#cbd5e1;font-size:11px">{{ $i+1 }}</td>
                        <td>
                            <a href="javascript:void(0)" onclick="DatabaseManager.openTable('{{ $t['name'] }}')" class="tname">{{ $t['name'] }}</a>
                        </td>
                        <td style="text-align:right;font-family:'JetBrains Mono',monospace;font-size:11px">{{ number_format($t['rows']) }}</td>
                        <td style="text-align:right;font-family:'JetBrains Mono',monospace;font-size:11px">{{ $t['size'] >= 1048576 ? number_format($t['size']/1048576,2).' MB' : number_format($t['size']/1024,1).' KB' }}</td>
                        <td><span style="font-size:10px;background:#f1f5f9;padding:2px 8px;border-radius:4px;color:#64748b">{{ $t['engine'] }}</span></td>
                        <td style="font-size:11px;color:#94a3b8">{{ $t['collation'] }}</td>
                        <td>
                            <button class="btn-xs btn-blue" onclick="DatabaseManager.openTable('{{ $t['name'] }}')"><i class="fas fa-eye"></i></button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
