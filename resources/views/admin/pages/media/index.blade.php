@extends('admin.layouts.app')
@section('title', 'Media Library')

@push('styles')
<style>
/* ── Page Header ── */
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; }
.page-header-left h1 { font-size: 24px; font-weight: 700; color: var(--code-bg); margin-bottom: 5px; }
.page-header-left p { font-size: 14px; color: var(--text-muted); }
.page-header-right { display: flex; gap: 10px; flex-wrap: wrap; }

/* ── Buttons ── */
.btn { padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 7px; text-decoration: none; transition: all .2s; box-shadow: 0 1px 2px rgba(0,0,0,.05); }
.btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,.1); }
.btn-primary { background: linear-gradient(135deg, var(--c-danger) 0%, var(--c-primary-hover) 100%); color: #fff; }
.btn-primary:hover { background: linear-gradient(135deg, var(--c-danger) 0%, var(--c-danger) 100%); }
.btn-outline { background: transparent; color: var(--text-secondary); border: 1.5px solid var(--input-border); }
.btn-outline:hover { background: var(--table-header-bg); border-color: var(--text-faint); transform: none; box-shadow: none; }
.btn-danger { background: #fff; color: var(--c-danger); border: 1.5px solid var(--c-danger-border); }
.btn-danger:hover { background: var(--c-danger-light); border-color: var(--c-danger-border); }
.btn-sm { padding: 8px 14px; font-size: 13px; }
.btn-icon { width: 38px; height: 38px; padding: 0; display: flex; align-items: center; justify-content: center; border-radius: 8px; border: 1.5px solid var(--border-color); background: #fff; cursor: pointer; color: var(--text-muted); transition: all .15s; }
.btn-icon:hover { background: var(--table-header-bg); border-color: var(--hover-border); }
.btn-icon.active { background: var(--c-danger); color: #fff; border-color: var(--c-danger); }

/* ── Alert ── */
.alert { padding: 14px 18px; border-radius: 10px; margin-bottom: 18px; font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 10px; }
.alert-success { background: var(--c-success-light); color: var(--c-success); border: 1px solid var(--c-success-border); }
.alert-danger { background: var(--c-danger-light); color: var(--c-primary-hover); border: 1px solid var(--c-danger-border); }

/* ── Stats Row ── */
.stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 22px; }
@media(max-width:1200px) { .stats-row { grid-template-columns: repeat(2, 1fr); } }
@media(max-width:640px) { .stats-row { grid-template-columns: 1fr; } }
.stat-card { background: #fff; border-radius: 12px; padding: 20px 22px; border: 1px solid var(--border-color); transition: all .25s; position: relative; overflow: hidden; }
.stat-card::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; border-radius: 12px 0 0 12px; }
.stat-card.c-blue::before { background: var(--c-secondary); }
.stat-card.c-green::before { background: var(--c-success); }
.stat-card.c-amber::before { background: var(--c-warning); }
.stat-card.c-purple::before { background: var(--c-purple); }
.stat-value { font-size: 28px; font-weight: 800; color: var(--code-bg); line-height: 1.1; margin-bottom: 4px; }
.stat-label { font-size: 13px; color: var(--text-muted); font-weight: 500; }
.stat-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; margin-bottom: 14px; }
.stat-icon.blue { background: linear-gradient(135deg, var(--c-secondary-light), var(--c-secondary-light)); color: var(--c-secondary); }
.stat-icon.green { background: linear-gradient(135deg, var(--c-success-light), var(--c-success-light)); color: var(--c-success); }
.stat-icon.amber { background: linear-gradient(135deg, var(--c-warning-light), var(--c-warning-light)); color: var(--c-warning); }
.stat-icon.purple { background: linear-gradient(135deg, var(--c-purple-light), var(--c-purple-light)); color: var(--c-purple); }

/* ── Upload Zone ── */
.upload-zone { background: #fff; border: 2px dashed var(--input-border); border-radius: 14px; padding: 40px; text-align: center; cursor: pointer; transition: all .25s; margin-bottom: 22px; }
.upload-zone:hover, .upload-zone.dragover { border-color: var(--c-danger); background: var(--c-danger-light); }
.upload-zone i { font-size: 42px; color: var(--hover-border); margin-bottom: 14px; display: block; }
.upload-zone.dragover i { color: var(--c-danger); }
.upload-zone p { font-size: 15px; color: var(--text-muted); margin-bottom: 6px; }
.upload-zone small { font-size: 12px; color: var(--text-faint); }
.upload-zone input[type="file"] { display: none; }
.upload-progress { display: none; margin-top: 16px; }
.upload-progress.show { display: block; }
.progress-bar-wrap { height: 8px; background: var(--border-light); border-radius: 4px; overflow: hidden; margin-top: 8px; }
.progress-bar { height: 100%; background: linear-gradient(90deg, var(--c-danger), var(--c-danger)); border-radius: 4px; transition: width .3s; width: 0; }
.upload-status { font-size: 13px; color: var(--text-muted); margin-top: 8px; }

/* ── Toolbar ── */
.toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 12px; }
.toolbar-left { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
.toolbar-right { display: flex; gap: 8px; align-items: center; }
.search-input { padding: 9px 14px 9px 36px; border: 1.5px solid var(--border-color); border-radius: 8px; font-size: 14px; color: var(--text-heading); width: 240px; transition: all .2s; background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85zm-5.242.656a5 5 0 1 1 0-10 5 5 0 0 1 0 10z'/%3E%3C/svg%3E") no-repeat 12px center; background-size: 14px; }
.search-input:focus { outline: none; border-color: var(--c-secondary); box-shadow: 0 0 0 3px rgba(59,130,246,.12); }
.collection-tabs { display: flex; gap: 4px; flex-wrap: wrap; }
.collection-tab { padding: 7px 16px; border-radius: 20px; font-size: 13px; font-weight: 500; cursor: pointer; border: 1.5px solid var(--border-color); background: #fff; color: var(--text-muted); text-decoration: none; transition: all .15s; }
.collection-tab:hover { background: var(--table-header-bg); border-color: var(--hover-border); }
.collection-tab.active { background: var(--c-danger); color: #fff; border-color: var(--c-danger); }

/* ── Card ── */
.card { background: #fff; border-radius: 14px; border: 1px solid var(--border-color); overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.card-head { padding: 18px 22px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-light); }
.card-title { font-size: 16px; font-weight: 600; color: var(--code-bg); display: flex; align-items: center; gap: 10px; }
.card-title i { color: var(--text-faint); }
.card-count { font-size: 14px; color: var(--text-muted); }

/* ── Grid View ── */
.media-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 16px; padding: 22px; }
@media(max-width:640px) { .media-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; } }
.media-item { border-radius: 12px; border: 2px solid transparent; overflow: hidden; cursor: pointer; transition: all .2s; background: var(--table-header-bg); position: relative; }
.media-item:hover { border-color: var(--hover-border); box-shadow: 0 4px 12px rgba(0,0,0,.08); transform: translateY(-2px); }
.media-item.selected { border-color: var(--c-danger); box-shadow: 0 0 0 3px rgba(220,38,38,.15); }
.media-thumb { width: 100%; height: 140px; object-fit: cover; display: block; background: var(--border-light); }
.media-thumb-placeholder { width: 100%; height: 140px; display: flex; align-items: center; justify-content: center; background: var(--border-light); color: var(--text-faint); font-size: 36px; }
.media-info { padding: 10px 12px; }
.media-name { font-size: 13px; font-weight: 600; color: var(--text-heading); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 3px; }
.media-meta { font-size: 11px; color: var(--text-faint); display: flex; justify-content: space-between; }
.media-check { position: absolute; top: 8px; left: 8px; width: 22px; height: 22px; border-radius: 6px; background: rgba(255,255,255,.9); border: 2px solid var(--input-border); display: flex; align-items: center; justify-content: center; font-size: 12px; color: transparent; transition: all .15s; z-index: 2; }
.media-item.selected .media-check { background: var(--c-danger); border-color: var(--c-danger); color: #fff; }
.media-actions { position: absolute; top: 8px; right: 8px; display: flex; gap: 4px; opacity: 0; transition: opacity .15s; z-index: 2; }
.media-item:hover .media-actions { opacity: 1; }
.media-action-btn { width: 28px; height: 28px; border-radius: 6px; background: rgba(255,255,255,.9); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; font-size: 12px; color: var(--text-muted); cursor: pointer; transition: all .15s; }
.media-action-btn:hover { background: #fff; color: var(--c-danger); border-color: var(--c-danger-border); }
.media-action-btn.download:hover { color: var(--c-success); border-color: var(--c-success-border); }

/* ── List View ── */
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { text-align: left; padding: 13px 18px; font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: .5px; background: var(--table-header-bg); border-bottom: 2px solid var(--border-light); white-space: nowrap; }
.data-table td { padding: 12px 18px; font-size: 14px; color: var(--text-body); border-bottom: 1px solid var(--border-light); vertical-align: middle; }
.data-table tbody tr { cursor: pointer; transition: background .15s; }
.data-table tbody tr:hover td { background: var(--table-header-bg); }
.list-thumb { width: 40px; height: 40px; border-radius: 8px; object-fit: cover; background: var(--border-light); }
.list-thumb-placeholder { width: 40px; height: 40px; border-radius: 8px; background: var(--border-light); display: flex; align-items: center; justify-content: center; color: var(--text-faint); font-size: 16px; }
.badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; }
.badge-collection { background: var(--border-light); color: var(--text-secondary); border: 1px solid var(--border-color); }

/* ── Modal ── */
.modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15,23,42,.6); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; }
.modal-overlay.show { display: flex; }
.modal { background: #fff; border-radius: 16px; width: 100%; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px rgba(0,0,0,.2); }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid var(--border-light); }
.modal-header h3 { font-size: 18px; font-weight: 700; color: var(--code-bg); display: flex; align-items: center; gap: 10px; }
.modal-close { width: 32px; height: 32px; border-radius: 8px; background: var(--table-header-bg); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; font-size: 18px; cursor: pointer; color: var(--text-muted); }
.modal-close:hover { background: var(--c-danger-light); color: var(--c-danger); border-color: var(--c-danger-border); }
.modal-body { padding: 24px; }
.modal-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 18px 24px; border-top: 1px solid var(--border-light); }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-size: 14px; font-weight: 600; color: var(--text-body); margin-bottom: 6px; }
.form-control { width: 100%; padding: 10px 14px; border: 1.5px solid var(--border-color); border-radius: 8px; font-size: 14px; transition: all .2s; box-sizing: border-box; }
.form-control:focus { outline: none; border-color: var(--c-secondary); box-shadow: 0 0 0 3px rgba(59,130,246,.1); }
.preview-img { max-width: 100%; max-height: 250px; border-radius: 10px; margin-bottom: 16px; display: block; border: 1px solid var(--border-light); }

/* ── Bulk Bar ── */
.bulk-bar { display: none; padding: 14px 22px; background: var(--code-bg); color: #fff; align-items: center; justify-content: space-between; }
.bulk-bar.show { display: flex; }
.bulk-bar .bulk-info { font-size: 14px; font-weight: 500; }
.bulk-bar .bulk-actions { display: flex; gap: 8px; }
.bulk-btn { padding: 7px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; transition: all .15s; }
.bulk-btn.danger { background: var(--c-danger); color: #fff; }
.bulk-btn.danger:hover { background: var(--c-danger); }
.bulk-btn.cancel { background: rgba(255,255,255,.15); color: #fff; }
.bulk-btn.cancel:hover { background: rgba(255,255,255,.25); }

/* ── Pagination ── */
.pagination-wrap { padding: 18px 22px; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-light); }
.pagination-info { font-size: 14px; color: var(--text-muted); }
.pagination-links { display: flex; gap: 4px; }
.pagination-links a, .pagination-links span { padding: 7px 14px; border-radius: 8px; font-size: 14px; text-decoration: none; border: 1px solid var(--border-color); color: var(--text-body); transition: all .15s; }
.pagination-links a:hover { background: var(--border-light); }
.pagination-links .active span { background: var(--c-danger); color: #fff; border-color: var(--c-danger); }
.pagination-links .disabled span { color: var(--input-border); }

/* ── Empty ── */
.empty-state { padding: 70px 20px; text-align: center; }
.empty-state i { font-size: 52px; margin-bottom: 18px; display: block; color: var(--hover-border); }
.empty-state p { font-size: 15px; color: var(--text-muted); }
</style>
@endpush

@section('content')

@if(session('success'))
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
@endif

{{-- Page Header --}}
<div class="page-header">
    <div class="page-header-left">
        <h1>Media Library</h1>
        <p>Upload, organize and manage your files and images</p>
    </div>
    <div class="page-header-right">
        <button class="btn btn-primary" onclick="document.getElementById('fileInput').click()">
            <i class="fas fa-cloud-upload-alt"></i> Upload Files
        </button>
    </div>
</div>

{{-- Stats --}}
<div class="stats-row">
    <div class="stat-card c-blue">
        <div class="stat-icon blue"><i class="fas fa-layer-group"></i></div>
        <div class="stat-value">{{ number_format($stats['total']) }}</div>
        <div class="stat-label">Total Files</div>
    </div>
    <div class="stat-card c-green">
        <div class="stat-icon green"><i class="fas fa-image"></i></div>
        <div class="stat-value">{{ number_format($stats['images']) }}</div>
        <div class="stat-label">Images</div>
    </div>
    <div class="stat-card c-amber">
        <div class="stat-icon amber"><i class="fas fa-file-alt"></i></div>
        <div class="stat-value">{{ number_format($stats['documents']) }}</div>
        <div class="stat-label">Documents</div>
    </div>
    <div class="stat-card c-purple">
        <div class="stat-icon purple"><i class="fas fa-hdd"></i></div>
        <div class="stat-value">{{ \App\Http\Controllers\Admin\MediaController::formatBytes($stats['total_size']) }}</div>
        <div class="stat-label">Disk Usage</div>
    </div>
</div>

{{-- Upload Zone --}}
<div class="upload-zone" id="uploadZone" onclick="document.getElementById('fileInput').click()">
    <i class="fas fa-cloud-upload-alt"></i>
    <p>Drag & drop files here, or click to browse</p>
    <small>Max 20 MB per file — Images, PDFs, Documents accepted</small>
    <input type="file" id="fileInput" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.zip">
    <div class="upload-progress" id="uploadProgress">
        <div class="progress-bar-wrap"><div class="progress-bar" id="progressBar"></div></div>
        <div class="upload-status" id="uploadStatus">Uploading...</div>
    </div>
</div>

{{-- Toolbar --}}
<div class="toolbar">
    <div class="toolbar-left">
        <div class="collection-tabs">
            <a href="{{ route('admin.media.index', array_merge(request()->except('collection','page'), ['view' => $viewMode])) }}" class="collection-tab {{ !request('collection') || request('collection') == 'all' ? 'active' : '' }}">All</a>
            @foreach($collections as $col)
                <a href="{{ route('admin.media.index', array_merge(request()->except('page'), ['collection' => $col, 'view' => $viewMode])) }}" class="collection-tab {{ request('collection') == $col ? 'active' : '' }}">{{ ucfirst($col) }}</a>
            @endforeach
        </div>
    </div>
    <div class="toolbar-right">
        <form method="GET" action="{{ route('admin.media.index') }}" style="display:flex;gap:8px;">
            <input type="hidden" name="view" value="{{ $viewMode }}">
            @if(request('collection'))<input type="hidden" name="collection" value="{{ request('collection') }}">@endif
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search files..." class="search-input">
        </form>
        <a href="{{ route('admin.media.index', array_merge(request()->except('view'), ['view' => 'grid'])) }}" class="btn-icon {{ $viewMode == 'grid' ? 'active' : '' }}" title="Grid"><i class="fas fa-th"></i></a>
        <a href="{{ route('admin.media.index', array_merge(request()->except('view'), ['view' => 'list'])) }}" class="btn-icon {{ $viewMode == 'list' ? 'active' : '' }}" title="List"><i class="fas fa-list"></i></a>
    </div>
</div>

{{-- Data Card --}}
<div class="card">
    {{-- Bulk Bar --}}
    <div class="bulk-bar" id="bulkBar">
        <span class="bulk-info"><span id="selectedCount">0</span> file(s) selected</span>
        <div class="bulk-actions">
            <button class="bulk-btn cancel" onclick="clearSelection()">Cancel</button>
            <button class="bulk-btn danger" onclick="bulkDelete()"><i class="fas fa-trash"></i> Delete Selected</button>
        </div>
    </div>

    <div class="card-head">
        <div class="card-title"><i class="fas fa-photo-video"></i> Files</div>
        <div class="card-count">{{ $media->total() }} files</div>
    </div>

    @if($media->count() > 0)

        @if($viewMode == 'grid')
        {{-- Grid View --}}
        <div class="media-grid">
            @foreach($media as $item)
            <div class="media-item" data-id="{{ $item->id }}" onclick="handleItemClick(event, {{ $item->id }})">
                <div class="media-check"><i class="fas fa-check"></i></div>
                <div class="media-actions">
                    <a href="{{ route('admin.media.download', $item->id) }}" class="media-action-btn download" onclick="event.stopPropagation();" title="Download"><i class="fas fa-download"></i></a>
                    <button class="media-action-btn" onclick="event.stopPropagation(); deleteMedia({{ $item->id }})" title="Delete"><i class="fas fa-trash"></i></button>
                </div>
                @if(str_starts_with($item->mime_type, 'image/'))
                    <img src="{{ route('admin.media.serve-conversion', ['id' => $item->id, 'conversion' => 'thumb']) }}" alt="{{ $item->name }}" class="media-thumb" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                    <div class="media-thumb-placeholder" style="display:none;"><i class="fas fa-image"></i></div>
                @else
                    <div class="media-thumb-placeholder">
                        @php
                            $icon = match(true) {
                                str_contains($item->mime_type, 'pdf') => 'fa-file-pdf',
                                str_contains($item->mime_type, 'word') || str_contains($item->mime_type, 'document') => 'fa-file-word',
                                str_contains($item->mime_type, 'sheet') || str_contains($item->mime_type, 'excel') => 'fa-file-excel',
                                str_contains($item->mime_type, 'zip') || str_contains($item->mime_type, 'archive') => 'fa-file-archive',
                                str_contains($item->mime_type, 'text') || str_contains($item->mime_type, 'csv') => 'fa-file-alt',
                                default => 'fa-file',
                            };
                        @endphp
                        <i class="fas {{ $icon }}"></i>
                    </div>
                @endif
                <div class="media-info">
                    <div class="media-name" title="{{ $item->file_name }}">{{ $item->name }}</div>
                    <div class="media-meta">
                        <span>{{ \App\Http\Controllers\Admin\MediaController::formatBytes($item->size) }}</span>
                        <span>{{ $item->created_at?->format('M d') }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        {{-- List View --}}
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:50px;"></th>
                        <th>File</th>
                        <th>Collection</th>
                        <th>Type</th>
                        <th>Size</th>
                        <th>Date</th>
                        <th style="width:100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($media as $item)
                    <tr onclick="openEditModal({{ $item->id }})">
                        <td>
                            @if(str_starts_with($item->mime_type, 'image/'))
                                <img src="{{ route('admin.media.serve-conversion', ['id' => $item->id, 'conversion' => 'thumb']) }}" class="list-thumb" loading="lazy" onerror="this.outerHTML='<div class=list-thumb-placeholder><i class=fas\ fa-image></i></div>';">
                            @else
                                <div class="list-thumb-placeholder"><i class="fas fa-file"></i></div>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight:600;color:var(--code-bg);">{{ $item->name }}</div>
                            <div style="font-size:12px;color:var(--text-faint);">{{ $item->file_name }}</div>
                        </td>
                        <td><span class="badge badge-collection">{{ ucfirst($item->collection_name) }}</span></td>
                        <td style="font-size:13px;color:var(--text-muted);">{{ strtoupper(pathinfo($item->file_name, PATHINFO_EXTENSION)) }}</td>
                        <td style="font-size:13px;color:var(--text-muted);">{{ \App\Http\Controllers\Admin\MediaController::formatBytes($item->size) }}</td>
                        <td style="font-size:13px;color:var(--text-muted);">{{ $item->created_at?->format('d M Y') }}</td>
                        <td onclick="event.stopPropagation();">
                            <div style="display:flex;gap:6px;">
                                <a href="{{ route('admin.media.download', $item->id) }}" class="media-action-btn download" title="Download"><i class="fas fa-download"></i></a>
                                <button class="media-action-btn" onclick="deleteMedia({{ $item->id }})" title="Delete"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if($media->hasPages())
        <div class="pagination-wrap">
            <div class="pagination-info">Showing {{ $media->firstItem() }} – {{ $media->lastItem() }} of {{ $media->total() }}</div>
            <div class="pagination-links">{!! $media->links('pagination::simple-bootstrap-4') !!}</div>
        </div>
        @endif

    @else
    <div class="empty-state">
        <i class="fas fa-photo-video"></i>
        <p>No files uploaded yet. Drag & drop files above or click the Upload button.</p>
    </div>
    @endif
</div>

{{-- Edit Modal --}}
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-edit" style="color: var(--c-secondary);"></i> Edit Media</h3>
            <button class="modal-close" onclick="closeEditModal()">×</button>
        </div>
        <div class="modal-body" id="editBody">
            <div style="text-align:center;padding:30px;color:var(--text-faint);"><i class="fas fa-spinner fa-spin"></i> Loading...</div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline btn-sm" onclick="closeEditModal()">Cancel</button>
            <button class="btn btn-primary btn-sm" onclick="saveMedia()"><i class="fas fa-save"></i> Save</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
let selectedIds = [];
let editingId = null;

// ── Upload ──
const uploadZone = document.getElementById('uploadZone');
const fileInput = document.getElementById('fileInput');

['dragenter','dragover'].forEach(e => uploadZone.addEventListener(e, ev => { ev.preventDefault(); uploadZone.classList.add('dragover'); }));
['dragleave','drop'].forEach(e => uploadZone.addEventListener(e, ev => { ev.preventDefault(); uploadZone.classList.remove('dragover'); }));

uploadZone.addEventListener('drop', ev => {
    const files = ev.dataTransfer.files;
    if (files.length > 0) uploadFiles(files);
});

fileInput.addEventListener('change', () => {
    if (fileInput.files.length > 0) uploadFiles(fileInput.files);
});

function uploadFiles(files) {
    const form = new FormData();
    for (let i = 0; i < files.length; i++) {
        form.append('files[]', files[i]);
    }
    form.append('collection', 'general');

    const progress = document.getElementById('uploadProgress');
    const bar = document.getElementById('progressBar');
    const status = document.getElementById('uploadStatus');
    progress.classList.add('show');
    bar.style.width = '0%';
    status.textContent = `Uploading ${files.length} file(s)...`;

    const xhr = new XMLHttpRequest();
    xhr.open('POST', '{{ route("admin.media.upload") }}');
    xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

    xhr.upload.onprogress = e => {
        if (e.lengthComputable) {
            const pct = Math.round((e.loaded / e.total) * 100);
            bar.style.width = pct + '%';
            status.textContent = `Uploading... ${pct}%`;
        }
    };

    xhr.onload = () => {
        if (xhr.status >= 200 && xhr.status < 300) {
            const data = JSON.parse(xhr.responseText);
            status.textContent = data.message || 'Upload complete!';
            bar.style.width = '100%';
            setTimeout(() => location.reload(), 1000);
        } else {
            let msg = 'Upload failed.';
            try { msg = JSON.parse(xhr.responseText).message || msg; } catch(e){}
            status.textContent = msg;
            bar.style.background = 'var(--c-danger)';
        }
    };

    xhr.onerror = () => { status.textContent = 'Upload failed — network error.'; };
    xhr.send(form);
    fileInput.value = '';
}

// ── Selection ──
function handleItemClick(event, id) {
    if (event.target.closest('.media-actions') || event.target.closest('.media-action-btn')) return;

    if (event.ctrlKey || event.metaKey) {
        toggleSelect(id);
    } else {
        openEditModal(id);
    }
}

function toggleSelect(id) {
    const idx = selectedIds.indexOf(id);
    if (idx > -1) selectedIds.splice(idx, 1);
    else selectedIds.push(id);
    updateSelectionUI();
}

function clearSelection() {
    selectedIds = [];
    updateSelectionUI();
}

function updateSelectionUI() {
    document.querySelectorAll('.media-item').forEach(el => {
        el.classList.toggle('selected', selectedIds.includes(parseInt(el.dataset.id)));
    });
    const bar = document.getElementById('bulkBar');
    document.getElementById('selectedCount').textContent = selectedIds.length;
    if (selectedIds.length > 0) bar.classList.add('show');
    else bar.classList.remove('show');
}

// ── Edit Modal ──
function openEditModal(id) {
    editingId = id;
    const modal = document.getElementById('editModal');
    const body = document.getElementById('editBody');
    modal.classList.add('show');
    body.innerHTML = '<div style="text-align:center;padding:30px;color:var(--text-faint);"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';

    fetch(`{{ url("media") }}/${id}/show`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.json())
    .then(data => {
        if (!data.success) { body.innerHTML = '<p style="color:var(--c-danger);">Failed to load.</p>'; return; }
        let html = '';
        if (data.mime_type && data.mime_type.startsWith('image/')) {
            html += `<img src="${data.url}" class="preview-img" alt="${data.name}">`;
        }
        html += `<div class="form-group"><label>Name</label><input type="text" class="form-control" id="editName" value="${escAttr(data.name)}"></div>`;
        html += `<div class="form-group"><label>Collection</label><select class="form-control" id="editCollection">`;
        ['general','avatars','documents','logos'].forEach(c => {
            html += `<option value="${c}" ${data.collection === c ? 'selected' : ''}>${c.charAt(0).toUpperCase()+c.slice(1)}</option>`;
        });
        html += `</select></div>`;
        html += `<div class="form-group"><label>Alt Text</label><input type="text" class="form-control" id="editAlt" value="${escAttr(data.alt_text)}"></div>`;
        html += `<div class="form-group"><label>Description</label><input type="text" class="form-control" id="editDesc" value="${escAttr(data.description)}"></div>`;
        html += `<div class="form-group"><label>Tags</label><input type="text" class="form-control" id="editTags" value="${escAttr(data.tags)}" placeholder="Comma-separated"></div>`;
        html += `<div style="font-size:12px;color:var(--text-faint);margin-top:8px;">File: ${data.file_name} · ${data.mime_type} · Uploaded: ${data.created_at}</div>`;
        body.innerHTML = html;
    })
    .catch(() => { body.innerHTML = '<p style="color:var(--c-danger);">Failed to load media info.</p>'; });
}

function closeEditModal() { document.getElementById('editModal').classList.remove('show'); editingId = null; }

function saveMedia() {
    if (!editingId) return;
    const body = {
        name: document.getElementById('editName').value,
        collection: document.getElementById('editCollection').value,
        alt_text: document.getElementById('editAlt').value,
        description: document.getElementById('editDesc').value,
        tags: document.getElementById('editTags').value,
    };

    fetch(`{{ url("media") }}/${editingId}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify(body),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) { closeEditModal(); location.reload(); }
        else alert(data.message || 'Update failed.');
    })
    .catch(() => alert('Update failed.'));
}

// ── Delete ──
function deleteMedia(id) {
    if (!confirm('Delete this file? This cannot be undone.')) return;
    fetch(`{{ url("media") }}/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    })
    .then(r => r.json())
    .then(data => { if (data.success) location.reload(); else alert(data.message); })
    .catch(() => alert('Delete failed.'));
}

function bulkDelete() {
    if (selectedIds.length === 0) return;
    if (!confirm(`Delete ${selectedIds.length} file(s)? This cannot be undone.`)) return;
    fetch('{{ route("admin.media.bulk-delete") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ ids: selectedIds }),
    })
    .then(r => r.json())
    .then(data => { if (data.success) location.reload(); else alert(data.message); })
    .catch(() => alert('Bulk delete failed.'));
}

function escAttr(str) { return (str || '').replace(/"/g, '&quot;').replace(/</g, '&lt;'); }

</script>
@endpush
