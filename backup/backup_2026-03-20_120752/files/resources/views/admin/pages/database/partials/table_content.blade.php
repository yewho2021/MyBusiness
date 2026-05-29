{{-- DATA TAB --}}
<div id="tab-data">
    <div class="data-table-wrap">
        @if(empty($rows))
            <div style="text-align:center;padding:40px;color:#94a3b8"><i class="fas fa-inbox"
                    style="font-size:30px;display:block;margin-bottom:8px"></i>No data</div>
        @else
            <table class="data-table" data-table="{{ $table }}" data-pk="{{ $pkColumn ?? 'id' }}"
                   data-update-url="{{ route('admin.database.update-cell', $table) }}"
                   data-delete-url="{{ route('admin.database.delete-row', $table) }}">
                <thead>
                    <tr>
                        <th style="width:40px">#</th>
                        @foreach(array_keys((array) $rows[0]) as $col)<th>{{ $col }}</th>@endforeach
                        <th style="width:60px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $i => $row)
                        @php $rowArr = (array) $row;
                            $pk = array_key_first($rowArr);
                        $pkVal = $rowArr[$pk]; @endphp
                        <tr data-pk-col="{{ $pkColumn ?? $pk }}" data-pk-val="{{ $pkVal }}">
                            <td style="color:#94a3b8">{{ $offset + $i + 1 }}</td>
                            @foreach($rowArr as $colName => $val)
                                <td class="editable" data-column="{{ $colName }}"
                                    data-original="{{ is_null($val) ? '' : $val }}" @if(is_null($val)) data-is-null="1" @endif>
                                    @if(is_null($val))<span
                                    class="null-val">NULL</span>@else{{ \Illuminate\Support\Str::limit((string) $val, 80) }}@endif
                                </td>
                            @endforeach
                            <td>
                                <button class="btn-xs btn-red btn-delete-row" data-where="`{{ $pk }}`='{{ addslashes($pkVal) }}'"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Pagination --}}
    @if($totalPages > 1)
        <div class="pager" data-table="{{ $table }}">
            <div style="display:flex;align-items:center;gap:8px">
                <span>Showing {{ $offset + 1 }}-{{ min($offset + $perPage, $totalRows) }} of
                    {{ number_format($totalRows) }}</span>
                <span style="color:#d1d5db">|</span>
                <label style="font-size:12px">Per page:</label>
                <select class="per-page-select" onchange="DatabaseManager.changePerPage('{{ $table }}', this.value)">
                    @foreach([50, 100, 500, 1000] as $pp)
                        <option value="{{ $pp }}" @if($perPage == $pp) selected @endif>{{ $pp }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;gap:4px;align-items:center" class="pagination-links">
                {{-- First & Prev --}}
                @if($page > 1)
                    <a href="javascript:void(0)" onclick="DatabaseManager.loadTable('{{ $table }}', 1, {{ $perPage }})" title="First"><i class="fas fa-angle-double-left"></i></a>
                    <a href="javascript:void(0)" onclick="DatabaseManager.loadTable('{{ $table }}', {{ $page - 1 }}, {{ $perPage }})">&laquo;</a>
                @else
                    <span class="pager-btn disabled"><i class="fas fa-angle-double-left"></i></span>
                    <span class="pager-btn disabled">&laquo;</span>
                @endif

                {{-- Page numbers --}}
                @for($p = max(1, $page - 3); $p <= min($totalPages, $page + 3); $p++)
                    @if($p == $page)<span class="current">{{ $p }}</span>@else<a
                    href="javascript:void(0)" onclick="DatabaseManager.loadTable('{{ $table }}', {{ $p }}, {{ $perPage }})">{{ $p }}</a>@endif
                @endfor

                {{-- Next & Last --}}
                @if($page < $totalPages)
                    <a href="javascript:void(0)" onclick="DatabaseManager.loadTable('{{ $table }}', {{ $page + 1 }}, {{ $perPage }})">&raquo;</a>
                    <a href="javascript:void(0)" onclick="DatabaseManager.loadTable('{{ $table }}', {{ $totalPages }}, {{ $perPage }})" title="Last"><i
                            class="fas fa-angle-double-right"></i></a>
                @else
                    <span class="pager-btn disabled">&raquo;</span>
                    <span class="pager-btn disabled"><i class="fas fa-angle-double-right"></i></span>
                @endif
            </div>
        </div>
    @endif
</div>
