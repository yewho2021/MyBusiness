@extends('admin.layouts.app')
@section('title', 'Telegram Bot')

@push('styles')
<style>

.tg-btn{padding:8px 18px;border:none;border-radius:var(--btn-radius);cursor:pointer;font-weight:600;font-size:var(--fs-sm);font-family:inherit;transition:all .15s;display:inline-flex;align-items:center;gap:6px;}
.tg-btn:hover{filter:brightness(.9);}.tg-btn:disabled{opacity:.45;cursor:not-allowed;filter:none;}
.tg-btn.prim{background:var(--c-secondary);color:#fff;}
.tg-btn.succ{background:var(--c-success);color:#fff;}
.tg-btn.sec{background:var(--hover-bg);color:var(--text-secondary);border:1px solid var(--border-color);}
.tg-btn.del{background:transparent;color:var(--c-danger);border:1px solid var(--c-danger-border);padding:4px 9px;font-size:calc(var(--fs-base)*.79);}
.tg-btn.del:hover{background:var(--c-danger);color:#fff;}
.tg-btn.sm{padding:5px 12px;font-size:calc(var(--fs-base)*.86);}
.tg-wrap{max-width:760px;}
.tg-tabs{display:flex;border-bottom:2px solid var(--border-color);margin-bottom:20px;gap:2px;}
.tg-tab{padding:9px 18px;cursor:pointer;font-weight:600;font-size:var(--fs-base);color:var(--text-muted);border-bottom:3px solid transparent;margin-bottom:-2px;transition:all .15s;border-radius:var(--btn-radius) var(--btn-radius) 0 0;display:flex;align-items:center;gap:7px;}
.tg-tab:hover{color:var(--text-body);background:var(--hover-bg);}
.tg-tab.on{color:var(--c-secondary);border-bottom-color:var(--c-secondary);}
.tg-panel{display:none;}.tg-panel.on{display:block;}
.tg-card{background:var(--card-bg);border:1px solid var(--border-color);border-radius:var(--card-radius);margin-bottom:16px;overflow:hidden;}
.tg-card-hd{padding:12px 18px;border-bottom:1px solid var(--border-color);background:var(--hover-bg);display:flex;align-items:center;justify-content:space-between;}
.tg-card-hd h3{margin:0;font-size:var(--fs-base);font-weight:700;display:flex;align-items:center;gap:8px;}
.tg-card-bd{padding:18px 20px;}
.tg-fg{margin-bottom:14px;}
.tg-fg label{display:block;font-size:calc(var(--fs-base)*.71);font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;}
.tg-fg input,.tg-fg select,.tg-fg textarea{width:100%;padding:8px 12px;border:1px solid var(--border-color);border-radius:var(--btn-radius);font-size:var(--fs-sm);font-family:inherit;background:var(--hover-bg);color:var(--text-body);transition:border-color .15s;box-sizing:border-box;}
.tg-fg input:focus,.tg-fg select:focus{outline:none;border-color:var(--c-secondary);background:var(--card-bg);}
.tg-mono{font-family:var(--font-mono);}
.tg-hint{font-size:calc(var(--fs-base)*.71);color:var(--text-muted);margin-top:3px;line-height:1.5;}
.tg-code{font-family:var(--font-mono);background:var(--hover-bg);border:1px solid var(--border-color);border-radius:4px;padding:1px 6px;font-size:calc(var(--fs-base)*.86);}
.tg-row2{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.tg-row3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;}
@media(max-width:600px){.tg-row2,.tg-row3{grid-template-columns:1fr;}}
.tg-res{margin-top:10px;padding:10px 14px;border-radius:var(--btn-radius);font-size:calc(var(--fs-base)*.86);display:none;line-height:1.6;}
.tg-res.ok{background:var(--c-success-light);border:1px solid var(--c-success-border);color:var(--c-success);display:block;}
.tg-res.err{background:var(--c-danger-light);border:1px solid var(--c-danger-border);color:var(--c-danger);display:block;}
.tg-res.info{background:var(--c-info-light);border:1px solid var(--c-info-border,var(--c-info));color:var(--c-info);display:block;}
/* Target list */
.tgt-list{display:flex;flex-direction:column;gap:8px;}
.tgt-row{display:flex;align-items:center;gap:10px;padding:12px 14px;background:var(--hover-bg);border:1px solid var(--border-color);border-radius:var(--card-radius);transition:border-color .12s;}
.tgt-row.default{border-color:var(--c-secondary);background:var(--c-secondary-light,#eff6ff);}
.tgt-icon{width:34px;height:34px;border-radius:var(--btn-radius);display:flex;align-items:center;justify-content:center;font-size:1.1em;flex-shrink:0;}
.tgt-name{font-weight:700;font-size:var(--fs-base);}
.tgt-id{font-family:var(--font-mono);font-size:calc(var(--fs-base)*.79);color:var(--text-muted);}
.tgt-badge{padding:2px 7px;border-radius:var(--btn-radius);font-size:calc(var(--fs-base)*.71);font-weight:700;}
.tgt-badge.def{background:var(--c-secondary);color:#fff;}
.tgt-badge.typ{background:var(--hover-bg);color:var(--text-secondary);border:1px solid var(--border-color);}
/* Send selector */
/* target select handled by native <select> */
/* Setup steps */
.tg-steps{display:flex;flex-direction:column;gap:12px;}
.tg-step{display:flex;gap:12px;align-items:flex-start;}
.tg-sn{width:24px;height:24px;border-radius:50%;background:var(--c-secondary);color:#fff;font-weight:800;font-size:11px;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px;}
.tg-sb strong{display:block;font-weight:700;margin-bottom:2px;}
.tg-sb p{margin:0;font-size:calc(var(--fs-base)*.86);color:var(--text-secondary);line-height:1.65;}
.tg-status{padding:5px 12px;border-radius:var(--btn-radius);font-size:calc(var(--fs-base)*.79);font-weight:700;display:none;}
.tg-status.ok{background:var(--c-success-light);color:var(--c-success);display:inline-flex;align-items:center;gap:5px;}
.sp{display:inline-block;width:13px;height:13px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:_sp .7s linear infinite;vertical-align:middle;}
@keyframes _sp{to{transform:rotate(360deg)}}


/* ═══ Reports ═══ */
.rpt-list{width:100%;border-collapse:collapse;font-size:calc(var(--fs-base)*.86);}
.rpt-list th{padding:8px 10px;font-weight:700;font-size:calc(var(--fs-base)*.71);text-transform:uppercase;background:var(--hover-bg);border-bottom:2px solid var(--border-color);text-align:left;white-space:nowrap;}
.rpt-list td{padding:8px 10px;border-bottom:1px solid var(--border-light);vertical-align:middle;}
.rpt-list tr:hover td{background:var(--hover-bg);}
.rpt-list tr.disabled td{opacity:.45;}
.rpt-row-name{font-weight:700;cursor:pointer;color:var(--c-secondary);}
.rpt-row-name:hover{text-decoration:underline;}
.rpt-badge{display:inline-block;padding:1px 7px;border-radius:10px;font-size:calc(var(--fs-base)*.64);font-weight:700;text-transform:uppercase;letter-spacing:.5px;}
.rpt-badge.code{background:#e3f2fd;color:#1565c0;} .rpt-badge.template{background:#e8f5e9;color:#2e7d32;} .rpt-badge.alert{background:#fff3e0;color:#e65100;}
.rpt-st{font-weight:600;} .rpt-st.ok{color:var(--c-success);} .rpt-st.fail{color:var(--c-danger);} .rpt-st.none{color:var(--text-muted);}

/* ═══ Editor ═══ */
.rpt-editor{display:none;}
.rpt-editor.active{display:block;}
.rpt-split{display:grid;grid-template-columns:320px 1fr;gap:16px;margin-bottom:16px;}
@media(max-width:900px){.rpt-split{grid-template-columns:1fr;}}
.rpt-left{background:var(--card-bg);border:1px solid var(--border-color);border-radius:var(--card-radius);padding:16px;}
.rpt-right{background:var(--card-bg);border:1px solid var(--border-color);border-radius:var(--card-radius);padding:16px;min-height:400px;}
.rpt-fg{display:flex;flex-direction:column;gap:3px;margin-bottom:10px;}
.rpt-fg label{font-size:calc(var(--fs-base)*.71);color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;}
.rpt-fg input,.rpt-fg select,.rpt-fg textarea{padding:6px 10px;border:1px solid var(--border-color);border-radius:var(--btn-radius);font-size:var(--fs-sm);background:var(--hover-bg);color:var(--text-body);font-family:inherit;}
.rpt-fg input:focus,.rpt-fg select:focus{outline:none;border-color:var(--c-secondary);}
.rpt-row2{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
.rpt-row3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;}
.rpt-status-bar{display:flex;gap:16px;padding:8px 0;font-size:calc(var(--fs-base)*.71);color:var(--text-muted);flex-wrap:wrap;}
.rpt-monaco{width:100%;height:250px;border:1px solid var(--border-color);border-radius:var(--btn-radius);}
.rpt-preview-bar{background:var(--card-bg);border:1px solid var(--border-color);border-radius:var(--card-radius);padding:16px;}
.rpt-preview-out{background:var(--hover-bg);border:1px solid var(--border-color);border-radius:var(--btn-radius);padding:14px;font-family:monospace;font-size:calc(var(--fs-base)*.86);white-space:pre-wrap;max-height:350px;overflow-y:auto;line-height:1.5;margin-top:10px;}
.rpt-preview-info{font-size:calc(var(--fs-base)*.71);color:var(--text-muted);margin-top:4px;}

/* ═══ Log ═══ */
.log-filters{display:flex;gap:10px;margin-bottom:12px;flex-wrap:wrap;align-items:end;}
.log-filters .rpt-fg{margin-bottom:0;min-width:110px;}
.log-tbl{width:100%;border-collapse:collapse;font-size:calc(var(--fs-base)*.79);}
.log-tbl th{background:var(--hover-bg);padding:6px 8px;font-weight:700;font-size:calc(var(--fs-base)*.64);text-transform:uppercase;border-bottom:2px solid var(--border-color);text-align:left;}
.log-tbl td{padding:6px 8px;border-bottom:1px solid var(--border-light);vertical-align:top;}

/* ═══ Editor Toolbar ═══ */
.ed-tb{padding:3px 7px;border:1px solid var(--border-color);border-radius:3px;cursor:pointer;font-size:calc(var(--fs-base)*.71);background:var(--card-bg);color:var(--text-muted);font-family:inherit;transition:all .1s;line-height:1;}
.ed-tb:hover{background:var(--c-secondary);color:white;border-color:var(--c-secondary);}
.rpt-monaco{border:1px solid var(--border-color);}
.rpt-monaco.fullscreen{position:fixed!important;top:0;left:0;right:0;bottom:0;z-index:9999;height:100vh!important;width:100vw!important;border-radius:0!important;}

/* ═══ Common ═══ */
.tg-empty{text-align:center;padding:30px;color:var(--text-muted);font-size:calc(var(--fs-base)*.93);}

</style>
@endpush

@section('content')
<h2><span class="dot"></span> Telegram <span id="statusBadge" class="tg-status"></span></h2>

<div class="tg-tabs">
    <div class="tg-tab on" onclick="switchTab('setup',this)"><i class="fa-solid fa-gear"></i> Setup</div>
    <div class="tg-tab" onclick="switchTab('targets',this)"><i class="fa-solid fa-bullseye"></i> Targets</div>
    <div class="tg-tab" onclick="switchTab('reports',this)"><i class="fa-solid fa-chart-bar"></i> Reports</div>
    <div class="tg-tab" onclick="switchTab('log',this)"><i class="fa-solid fa-list"></i> Send Log</div>
</div>

<div class="tg-panel on" id="panel-setup">
    <div class="tg-card">
        <div class="tg-card-hd">
            <h3><i class="fa-solid fa-list-check" style="color:var(--c-secondary);"></i> Quick Setup Guide</h3>
            <button class="tg-btn sm sec" id="guideToggle" onclick="toggleGuide()">Hide</button>
        </div>
        <div class="tg-card-bd" id="guideBody">
            <div class="tg-steps">
                <div class="tg-step"><div class="tg-sn">1</div><div class="tg-sb"><strong>Create bot via @BotFather</strong><p>Open Telegram → search <span class="tg-code">@BotFather</span> → send <span class="tg-code">/newbot</span> → copy the <strong>Bot Token</strong>.</p></div></div>
                <div class="tg-step"><div class="tg-sn">2</div><div class="tg-sb"><strong>Paste token below → click Connect Bot</strong><p>Auto-saves, tests, and discovers your chats in one click.</p></div></div>
                <div class="tg-step"><div class="tg-sn">3</div><div class="tg-sb"><strong>Go to Targets tab → Add your chats</strong><p>Get Chat ID: send bot a message → open <span class="tg-code">api.telegram.org/bot<em>TOKEN</em>/getUpdates</span> → find <span class="tg-code">"chat":{"id": …}</span>. Group IDs are negative e.g. <span class="tg-code">-987654321</span>.</p></div></div>
                <div class="tg-step"><div class="tg-sn">4</div><div class="tg-sb"><strong>Go to Send tab → pick target → send</strong><p>Select any saved target and send a test message or report.</p></div></div>
            </div>
        </div>
    </div>
    <div class="tg-card">
        <div class="tg-card-hd"><h3><i class="fa-solid fa-key" style="color:var(--c-warning);"></i> Bot Token</h3></div>
        <div class="tg-card-bd">
            <div class="tg-fg">
                <label>Token</label>
                <div style="display:flex;gap:6px;">
                    <input type="password" id="cfgToken" class="tg-mono"
                        placeholder="7123456789:AAFxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                        value="{{ $config['bot_token'] ?? '' }}" style="flex:1;">
                    <button class="tg-btn sm sec" onclick="toggleToken(this)"><i class="fa-solid fa-eye"></i></button>
                </div>
                <div class="tg-hint">From @BotFather — keep this private</div>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <button class="tg-btn prim" id="connectBtn" onclick="connectBot(this)"><i class="fa-solid fa-rocket"></i> Connect Bot</button>
                <button class="tg-btn sec" id="testConnBtn" onclick="testConn()" style="font-size:calc(var(--fs-base)*.79);padding:6px 12px;"><i class="fa-solid fa-plug-circle-check"></i> Test Only</button>
                @if(!empty($config['bot_token']))
                <button class="tg-btn" id="clearBotBtn" onclick="clearBot()" style="font-size:calc(var(--fs-base)*.79);padding:6px 12px;background:var(--c-danger);color:#fff;"><i class="fa-solid fa-trash-can"></i> Disconnect & Clear</button>
                @endif
            </div>
            <div class="tg-res" id="connRes"></div>
        </div>
    </div>
</div>

<!-- ── TARGETS ───────────────────────────────────────────────────────────── -->

<div class="tg-panel" id="panel-targets">
    <div class="tg-card">
        <div class="tg-card-hd">
            <h3><i class="fa-solid fa-list" style="color:var(--c-secondary);"></i> Saved Targets</h3>
            <span style="font-size:calc(var(--fs-base)*.79);color:var(--text-muted);">★ = set as default · 🗑 = delete</span>
        </div>
        <div class="tg-card-bd" id="tgtListWrap">
            @if(count($targets) === 0)
            <div id="tgtEmpty" style="text-align:center;color:var(--text-muted);padding:20px;">No targets yet — add one below.</div>
            @else
            <div class="tgt-list" id="tgtList">
            @foreach($targets as $t)
            <div class="tgt-row {{ $t->is_default ? 'default' : '' }}" id="tgt-{{ $t->id }}">
                <div class="tgt-icon">{{ $t->type==='personal'?'📱':($t->type==='channel'?'📢':'👥') }}</div>
                <div style="flex:1;min-width:0;">
                    <div class="tgt-name">{{ $t->name }}
                        @if($t->is_default)<span class="tgt-badge def">★ default</span>@endif
                        <span class="tgt-badge typ">{{ $t->type }}</span>
                    </div>
                    <div class="tgt-id">{{ $t->chat_id }}{{ $t->notes ? ' · '.$t->notes : '' }}</div>
                </div>
                <button class="tg-btn sm succ" title="Send test message" onclick="sendTestTo({{ $t->id }},'{{ addslashes($t->name) }}',this)"><i class="fa-solid fa-paper-plane"></i></button>
                <button class="tg-btn sm sec" title="Set as default" onclick="setDefault({{ $t->id }},this)">★</button>
                <button class="tg-btn sm sec" title="Edit" onclick="editTarget({{ $t->id }},'{{ addslashes($t->name) }}','{{ $t->chat_id }}','{{ $t->type }}','{{ addslashes($t->notes ?? '') }}')"><i class="fa-solid fa-pen"></i></button>
                <button class="tg-btn del" onclick="deleteTarget({{ $t->id }},'{{ addslashes($t->name) }}')"><i class="fa-solid fa-trash"></i></button>
            </div>
            @endforeach
            </div>
            @endif
        </div>
    </div>

    <div class="tg-card" id="targetFormCard">
        <div class="tg-card-hd"><h3 id="targetFormTitle"><i class="fa-solid fa-plus" style="color:var(--c-success);"></i> Add New Target</h3></div>
        <div class="tg-card-bd">
            <div class="tg-row3">
                <div class="tg-fg">
                    <label>Name</label>
                    <input type="text" id="addName" placeholder="e.g. Management Group">
                </div>
                <div class="tg-fg">
                    <label>Chat ID</label>
                    <input type="text" id="addChatId" class="tg-mono" placeholder="-987654321">
                </div>
                <div class="tg-fg">
                    <label>Type</label>
                    <select id="addType">
                        <option value="group">👥 Group</option>
                        <option value="personal">📱 Personal</option>
                        <option value="channel">📢 Channel</option>
                    </select>
                </div>
            </div>
            <div class="tg-fg">
                <label>Notes (optional)</label>
                <input type="text" id="addNotes" placeholder="e.g. Daily report recipients">
            </div>
            <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                <input type="hidden" id="editingId" value="">
                <button class="tg-btn succ" id="targetSubmitBtn" onclick="submitTarget(this)"><i class="fa-solid fa-plus"></i> Add Target</button>
                <button class="tg-btn sec" id="cancelEditBtn" onclick="cancelEdit()" style="display:none;"><i class="fa-solid fa-xmark"></i> Cancel</button>
                <label style="display:flex;align-items:center;gap:6px;font-size:calc(var(--fs-base)*.86);cursor:pointer;">
                    <input type="checkbox" id="addDefault" style="accent-color:var(--c-secondary);width:auto;"> Set as default
                </label>
                <label style="display:flex;align-items:center;gap:6px;font-size:calc(var(--fs-base)*.86);cursor:pointer;color:var(--text-muted);" title="Save without testing the Chat ID via Telegram API">
                    <input type="checkbox" id="skipValidate" style="width:auto;"> Skip validation
                </label>
            </div>
            <div class="tg-res" id="addRes"></div>
            <div class="tg-hint" style="margin-top:12px;">
                💡 <strong>How to get Chat ID:</strong><br>
                <strong>Personal:</strong> User must send <span class="tg-code">/start</span> to your bot first<br>
                <strong>Group:</strong> Add bot to group → send any message in the group<br>
                ⚡ Click <strong>Discover Chats</strong> below to auto-detect available chats. Chat ID is validated before saving.
            </div>
            <div style="margin-top:12px;">
                <button class="tg-btn prim" onclick="discoverChats(this)" style="font-size:calc(var(--fs-base)*.86);"><i class="fa-solid fa-satellite-dish"></i> Discover Chats</button>
                <div id="discoverResult" style="margin-top:10px;"></div>
            </div>
        </div>
    </div>

    {{-- Quick Test Message --}}
    <div class="tg-card" style="margin-top:16px;">
        <div class="tg-card-hd"><h3><i class="fa-solid fa-paper-plane" style="color:var(--c-secondary);"></i> Quick Test Message</h3></div>
        <div class="tg-card-bd">
            <div style="display:flex;gap:10px;align-items:stretch;">
                <textarea id="testMsgBody" rows="2" placeholder="Type a test message..." style="flex:1;padding:10px 14px;border:1px solid var(--border-color);border-radius:var(--card-radius);font-family:inherit;font-size:var(--fs-sm);resize:vertical;box-sizing:border-box;background:var(--hover-bg);color:var(--text-body);"></textarea>
                <button class="tg-btn succ" onclick="sendQuickTest(this)" style="white-space:nowrap;align-self:end;"><i class="fa-solid fa-paper-plane"></i> Send</button>
            </div>
            <div style="font-size:var(--fs-xs);color:var(--text-muted);margin-top:6px;">Sends to the chat ID currently in the form above. Edit a target first, or enter a new chat ID.</div>
            <div id="sendRes" class="tg-res"></div>
        </div>
    </div>
</div>

<!-- ── SEND ──────────────────────────────────────────────────────────────── -->


<!-- ═══ Reports Tab ═══ -->
<div class="tg-panel" id="panel-reports">

<!-- Listing View -->
<div id="rptListView">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
        <div style="font-size:calc(var(--fs-base)*.79);color:var(--text-muted);" id="rptStats">Loading...</div>
        <div style="display:flex;gap:6px;">
            <button class="tg-btn sec" onclick="bulkSend()" title="Send selected"><i class="fa-solid fa-paper-plane"></i> Send Selected</button>
            <button class="tg-btn" onclick="openEditor(0,'template')"><i class="fa-solid fa-plus"></i> New Custom Report</button>
            <button class="tg-btn" onclick="openEditor(0,'code')"><i class="fa-solid fa-plus"></i> Register Built-in</button>
        </div>
    </div>
    <div style="overflow-x:auto;">
        <table class="rpt-list">
            <thead><tr>
                <th><input type="checkbox" id="rptCheckAll" onchange="toggleCheckAll(this)"></th>
                <th></th><th>Name</th><th>Type</th><th>Schedule</th><th>Target</th><th>Last Sent</th><th>Status</th><th>Actions</th>
            </tr></thead>
            <tbody id="rptBody"><tr><td colspan="9" class="tg-empty"><i class="fa-solid fa-spinner fa-spin"></i></td></tr></tbody>
        </table>
    </div>
</div>

<!-- Editor View -->
<div id="rptEditorView" class="rpt-editor">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
        <button class="tg-btn sec" onclick="closeEditor()"><i class="fa-solid fa-arrow-left"></i> Back</button>
        <h3 style="margin:0;font-size:calc(var(--fs-base)*1.07);font-weight:700;" id="editorTitle">New Report</h3>
        <span style="margin-left:auto;display:flex;gap:6px;">
            <button class="tg-btn sec" onclick="diagnoseReport()" title="Check report health"><i class="fa-solid fa-stethoscope"></i> Diagnose</button>
            <button class="tg-btn" onclick="previewReport()"><i class="fa-solid fa-eye"></i> Preview</button>
            <button class="tg-btn sec" onclick="testSendReport()"><i class="fa-solid fa-paper-plane"></i> Test Send</button>
            <button class="tg-btn on" onclick="saveReport()"><i class="fa-solid fa-check"></i> Save</button>
        </span>
    </div>

    <div class="rpt-split">
        <!-- Left: Settings -->
        <div class="rpt-left">
            <div class="rpt-fg"><label>Name</label><input type="text" id="edName" placeholder="Daily Summary"></div>
            <div class="rpt-row2">
                <div class="rpt-fg"><label>Icon</label><input type="text" id="edIcon" value="📊" style="width:50px;text-align:center;font-size:1.3em;"></div>
                <div class="rpt-fg"><label>Category</label>
                    <select id="edCategory"><option value="marketing">Marketing</option><option value="operations">Operations</option><option value="traffic">Traffic</option><option value="alerts">Alerts</option><option value="custom">Custom</option></select>
                </div>
            </div>
            <div class="rpt-fg"><label>Description</label><input type="text" id="edDesc" placeholder="What this report shows..."></div>

            <hr style="border:none;border-top:1px solid var(--border-color);margin:12px 0;">
            <div style="font-weight:700;font-size:calc(var(--fs-base)*.79);color:var(--text-muted);margin-bottom:8px;">DELIVERY</div>

            <div class="rpt-fg"><label>Target</label><select id="edTarget"><option value="">— No target (manual only) —</option></select></div>

            <div class="rpt-fg"><label>Schedule</label>
                <select id="edSchedule" onchange="onSchedChange()" style="width:100%;">
                    <option value="manual">Manual (send by button only)</option>
                    <option value="every5m">Every 5 Minutes</option>
                    <option value="every15m">Every 15 Minutes</option>
                    <option value="every30m">Every 30 Minutes</option>
                    <option value="hourly">Hourly</option>
                    <option value="daily">Daily</option>
                    <option value="weekday">Weekday (Mon–Fri)</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly (1st of month)</option>
                </select>
            </div>
            <div id="edSchedExtra" style="display:none;gap:10px;">
                <div class="rpt-fg" id="edDayWrap" style="display:none;"><label>Day</label>
                    <select id="edDay" onchange="onSchedChange()"><option value="1">Monday</option><option value="2">Tuesday</option><option value="3">Wednesday</option><option value="4">Thursday</option><option value="5">Friday</option><option value="6">Saturday</option><option value="7">Sunday</option></select>
                </div>
                <div class="rpt-fg" id="edTimeWrap" style="display:none;"><label>Send Time</label><input type="time" id="edTime" value="09:00" step="300" onchange="onSchedChange()"></div>
                <div class="rpt-fg" id="edMinWrap" style="display:none;"><label>At Minute</label>
                    <select id="edMinute" onchange="onSchedChange()"><option value="0">:00</option><option value="5">:05</option><option value="10">:10</option><option value="15">:15</option><option value="20">:20</option><option value="25">:25</option><option value="30">:30</option><option value="35">:35</option><option value="40">:40</option><option value="45">:45</option><option value="50">:50</option><option value="55">:55</option></select>
                </div>
                <div id="edSchedDesc" style="font-size:calc(var(--fs-base)*0.72);color:var(--text-muted);padding:4px 0;"></div>
            </div>

            <hr style="border:none;border-top:1px solid var(--border-color);margin:12px 0;">
            <input type="hidden" id="edDateRange" value="yesterday">

            <div class="rpt-fg"><label>Enabled</label>
                <select id="edEnabled"><option value="1">✅ Enabled</option><option value="0">⏸ Paused</option></select>
            </div>

            <div class="rpt-status-bar" id="edStatusBar" style="display:none;">
                <span id="edStSent">Sent: 0</span>
                <span id="edStFail">Fail: 0</span>
                <span id="edStLast">Last: —</span>
            </div>
        </div>

        <!-- Right: Code -->
        <div class="rpt-right">
            <input type="hidden" id="edType" value="code">
            <input type="hidden" id="edSlug" value="">

            <!-- PHP Code Editor -->
            <div id="edCodePanel">
                <div class="rpt-fg"><label>Source Code <span style="font-weight:400;text-transform:none;">(PHP — saves to database)</span></label></div>
                <!-- Editor Toolbar -->
                <div style="display:flex;gap:3px;padding:4px 6px;background:var(--hover-bg);border:1px solid var(--border-color);border-bottom:none;border-radius:var(--btn-radius) var(--btn-radius) 0 0;flex-wrap:wrap;align-items:center;">
                    <button class="ed-tb" onclick="edAction('fold')" title="Fold All (Ctrl+K Ctrl+0)"><i class="fa-solid fa-compress"></i></button>
                    <button class="ed-tb" onclick="edAction('unfold')" title="Unfold All"><i class="fa-solid fa-expand"></i></button>
                    <span style="width:1px;height:16px;background:var(--border-color);margin:0 3px;"></span>
                    <button class="ed-tb" onclick="edAction('format')" title="Format Code"><i class="fa-solid fa-align-left"></i></button>
                    <button class="ed-tb" onclick="edAction('wrap')" title="Toggle Word Wrap"><i class="fa-solid fa-text-width"></i></button>
                    <button class="ed-tb" onclick="edAction('minimap')" title="Toggle Minimap"><i class="fa-solid fa-map"></i></button>
                    <span style="width:1px;height:16px;background:var(--border-color);margin:0 3px;"></span>
                    <button class="ed-tb" onclick="edAction('zoomin')" title="Zoom In">A+</button>
                    <button class="ed-tb" onclick="edAction('zoomout')" title="Zoom Out">A-</button>
                    <span style="width:1px;height:16px;background:var(--border-color);margin:0 3px;"></span>
                    <button class="ed-tb" onclick="edAction('find')" title="Find (Ctrl+F)"><i class="fa-solid fa-search"></i></button>
                    <button class="ed-tb" onclick="edAction('replace')" title="Replace (Ctrl+H)"><i class="fa-solid fa-arrows-rotate"></i></button>
                    <span style="width:1px;height:16px;background:var(--border-color);margin:0 3px;"></span>
                    <button class="ed-tb" onclick="edAction('copy')" title="Copy All"><i class="fa-solid fa-copy"></i></button>
                    <button class="ed-tb" onclick="edAction('fullscreen')" title="Toggle Fullscreen"><i class="fa-solid fa-maximize"></i></button>
                    <span style="width:1px;height:16px;background:var(--border-color);margin:0 3px;"></span>
                    <button class="ed-tb" onclick="edAction('dark')" title="Toggle Theme"><i class="fa-solid fa-circle-half-stroke"></i></button>
                    <span style="width:1px;height:16px;background:var(--border-color);margin:0 3px;"></span>
                    <button class="ed-tb" onclick="saveSourceCode()" style="background:var(--c-success);color:white;font-weight:700;" title="Save code to PHP file"><i class="fa-solid fa-floppy-disk"></i> Save Code</button>
                    <span style="flex:1;"></span>
                    <span id="edCursorPos" style="font-size:calc(var(--fs-base)*0.64);color:var(--text-muted);font-family:monospace;">Ln 1, Col 1</span>
                </div>
                <div id="monacoPhp" class="rpt-monaco" style="height:420px;border-radius:0 0 var(--btn-radius) var(--btn-radius);"></div>
            </div>

            <!-- Custom: SQL + Template editors -->
            <div id="edTemplatePanel" style="display:none;">
                <div class="rpt-fg"><label>SQL Query <span style="font-weight:400;text-transform:none;">(SELECT only — use @{{date_from}}, @{{date_to}})</span></label></div>
                <!-- SQL Toolbar -->
                <div style="display:flex;gap:3px;padding:4px 6px;background:var(--hover-bg);border:1px solid var(--border-color);border-bottom:none;border-radius:var(--btn-radius) var(--btn-radius) 0 0;flex-wrap:wrap;align-items:center;">
                    <button class="ed-tb" onclick="edAction('fold','sql')" title="Fold All"><i class="fa-solid fa-compress"></i></button>
                    <button class="ed-tb" onclick="edAction('unfold','sql')" title="Unfold All"><i class="fa-solid fa-expand"></i></button>
                    <button class="ed-tb" onclick="edAction('wrap','sql')" title="Word Wrap"><i class="fa-solid fa-text-width"></i></button>
                    <button class="ed-tb" onclick="edAction('copy','sql')" title="Copy"><i class="fa-solid fa-copy"></i></button>
                    <button class="ed-tb" onclick="edAction('fullscreen','sql')" title="Fullscreen"><i class="fa-solid fa-maximize"></i></button>
                    <span style="flex:1;"></span>
                    <span style="font-size:calc(var(--fs-base)*0.64);color:var(--text-muted);">SQL</span>
                </div>
                <div id="monacoSql" class="rpt-monaco" style="height:200px;border-radius:0 0 var(--btn-radius) var(--btn-radius);"></div>
                <div style="margin:6px 0;"><button class="tg-btn sec" onclick="testQuery()" style="font-size:calc(var(--fs-base)*.71);padding:3px 10px;"><i class="fa-solid fa-play"></i> Test Query</button> <span id="queryResult" style="font-size:calc(var(--fs-base)*.71);color:var(--text-muted);"></span></div>
                <div id="queryPreview" style="display:none;max-height:150px;overflow:auto;border:1px solid var(--border-color);border-radius:var(--btn-radius);margin-bottom:10px;font-size:calc(var(--fs-base)*.64);"></div>

                <div class="rpt-fg"><label>Message Template <span style="font-weight:400;text-transform:none;">(Telegram Markdown)</span></label></div>
                <div id="monacoTpl" class="rpt-monaco" style="height:200px;"></div>

                <div class="rpt-fg" style="margin-top:10px;"><label>Computed Fields <span style="font-weight:400;text-transform:none;">(JSON)</span></label></div>
                <div id="monacoComp" class="rpt-monaco" style="height:60px;"></div>
            </div>
        </div>
    </div>

    <!-- Preview Bar -->
    <div class="rpt-preview-bar">
        <div style="display:flex;gap:8px;align-items:center;">
            <button class="tg-btn" onclick="previewReport()"><i class="fa-solid fa-eye"></i> Generate Preview</button>
            <select id="prevTarget" style="padding:5px 8px;border:1px solid var(--border-color);border-radius:var(--btn-radius);font-size:calc(var(--fs-base)*.79);"></select>
            <button class="tg-btn sec" onclick="testSendReport()"><i class="fa-solid fa-paper-plane"></i> Test Send</button>
            <span id="prevMsg" style="font-size:calc(var(--fs-base)*.79);"></span>
        </div>
        <div class="rpt-preview-out" id="prevOutput">Click "Generate Preview" to see the report output.</div>
        <div class="rpt-preview-info" id="prevInfo"></div>
    </div>
</div>
</div>

<!-- ═══ Log Tab ═══ -->
<div class="tg-panel" id="panel-log">
    <div class="log-filters">
        <div class="rpt-fg"><label>Status</label><select id="logStatus" onchange="loadLog()"><option value="">All</option><option value="sent">Sent</option><option value="failed">Failed</option></select></div>
        <div class="rpt-fg"><label>Type</label><select id="logType" onchange="loadLog()"><option value="">All</option><option value="scheduled">Scheduled</option><option value="manual">Manual</option><option value="bulk">Bulk</option></select></div>
        <div class="rpt-fg"><label>Target</label><input type="text" id="logTarget" placeholder="Search..." onchange="loadLog()" style="width:110px;"></div>
        <button class="tg-btn sec" onclick="loadLog()" style="height:32px;"><i class="fa-solid fa-sync"></i></button>
    </div>
    <div style="overflow-x:auto;">
        <table class="log-tbl">
            <thead><tr><th>Time</th><th>Type</th><th>Report</th><th>Target</th><th>Status</th><th>ms</th><th>Preview</th></tr></thead>
            <tbody id="logBody"><tr><td colspan="7" class="tg-empty">Switch to this tab to load</td></tr></tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>

const baseUrl = '';
const csrf = document.querySelector('meta[name="csrf-token"]').content;
const portalName = @json(\App\Models\Configuration::get('portal_name', config('app.name', 'Admin Portal')));
const portalTimezone = @json(\App\Models\Configuration::get('default_timezone', config('app.timezone', 'UTC')));

let activeSendTarget = '{{ optional($targets->firstWhere("is_default",1))->id ?? optional($targets->first())->id ?? "custom" }}';

function xss(s){if(!s&&s!==0)return'';const d=document.createElement('div');d.textContent=String(s);return d.innerHTML;}
function showRes(id,html,t){const el=document.getElementById(id);if(!el)return;el.className='tg-res '+t;el.innerHTML=html;}
function confirmAction(title,msg,type,cb){if(confirm(title+(msg?'\n\n'+msg:'')))cb();}



function toggleToken(btn){
    const inp=document.getElementById('cfgToken');const ic=btn.querySelector('i');
    inp.type=inp.type==='password'?'text':'password';
    ic.className=inp.type==='password'?'fa-solid fa-eye':'fa-solid fa-eye-slash';
}

function toggleGuide(){
    const body=document.getElementById('guideBody');
    const btn=document.getElementById('guideToggle');
    const open=body.style.display!=='none';
    body.style.display=open?'none':'';
    btn.textContent=open?'Show':'Hide';
}

function saveToken(){
    return fetch(baseUrl+'/telegram/save',{method:'POST',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json','Content-Type':'application/json'},
        body:JSON.stringify({bot_token:document.getElementById('cfgToken').value})
    }).then(r=>r.json());
}

async function clearBot(){
    if(!confirm('Disconnect bot and delete ALL saved targets?\nThis cannot be undone.'))return;
    const btn=document.getElementById('clearBotBtn');
    btn.disabled=true;btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Clearing...';
    try{
        const r=await fetch(baseUrl+'/telegram/save',{method:'POST',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json','Content-Type':'application/json'},
            body:JSON.stringify({bot_token:'',clear_all:true})});
        const d=await r.json();
        if(d.success){location.reload();}
        else{showRes('connRes','✗ '+(d.message||'Clear failed'),'err');btn.disabled=false;btn.innerHTML='<i class="fa-solid fa-trash-can"></i> Disconnect & Clear';}
    }catch(e){showRes('connRes','✗ '+e.message,'err');btn.disabled=false;btn.innerHTML='<i class="fa-solid fa-trash-can"></i> Disconnect & Clear';}
}

async function connectBot(btn){
    const token=document.getElementById('cfgToken').value.trim();
    if(!token){showRes('connRes','Enter a bot token first.','err');return;}

    btn.disabled=true;
    const steps=['Saving token...','Testing connection...','Discovering chats...','Done!'];
    function showStep(i){btn.innerHTML='<span class="sp"></span> '+steps[i];}

    try{
        // Step 1: Save
        showStep(0);
        showRes('connRes','<i class="fa-solid fa-spinner fa-spin"></i> Saving token...','info');
        const save=await saveToken();
        if(!save.success){showRes('connRes','✗ '+xss(save.message||'Save failed'),'err');btn.disabled=false;btn.innerHTML='<i class="fa-solid fa-rocket"></i> Connect Bot';return;}

        // Step 2: Test connection
        showStep(1);
        showRes('connRes','<i class="fa-solid fa-spinner fa-spin"></i> Connecting to Telegram API...','info');
        const test=await fetch(baseUrl+'/telegram/test-connection',{method:'POST',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json','Content-Type':'application/json'},
            body:JSON.stringify({bot_token:token})}).then(r=>r.json());

        const badge=document.getElementById('statusBadge');
        if(!test.success){
            showRes('connRes','<strong>✗ Connection failed:</strong> '+xss(test.error),'err');
            if(badge)badge.className='tg-status';
            btn.disabled=false;btn.innerHTML='<i class="fa-solid fa-rocket"></i> Connect Bot';return;
        }

        showRes('connRes','<strong>✓ Connected!</strong> <strong>'+xss(test.bot_name)+'</strong> (@'+xss(test.username)+') · ID: '+xss(test.bot_id),'ok');
        if(badge){badge.className='tg-status ok';badge.innerHTML='<i class="fa-solid fa-circle" style="font-size:8px;"></i> @'+xss(test.username)+' ready';}

        // Step 3: Auto-discover chats
        showStep(2);
        const disc=await fetch(baseUrl+'/telegram/discover',{headers:{'Accept':'application/json'}}).then(r=>r.json());

        if(disc.success && disc.chats && disc.chats.length>0){
            // Switch to Targets tab and trigger discover display
            switchTab('targets',document.querySelectorAll('.tg-tab')[1]);
            // Render discover results
            const res=document.getElementById('discoverResult');
            let h='<div style="font-weight:700;margin-bottom:8px;color:var(--c-success);"><i class="fa-solid fa-check-circle"></i> Bot connected! Found '+disc.chats.length+' chat(s) — click Add to connect:</div>';
            h+='<div style="display:flex;flex-direction:column;gap:8px;">';
            disc.chats.forEach(c=>{
                const icon=c.type==='private'?'👤':c.type==='channel'?'📢':'👥';
                const typeLabel=c.type==='private'?'Personal':c.type==='channel'?'Channel':'Group';
                const typeBg=c.type==='private'?'var(--c-info-light)':c.type==='channel'?'var(--c-warning-light)':'var(--c-success-light)';
                const typeColor=c.type==='private'?'var(--c-info)':c.type==='channel'?'var(--c-warning)':'var(--c-success)';
                h+='<div style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:var(--card-bg);border:1px solid var(--border-color);border-radius:var(--card-radius);">';
                h+='<span style="font-size:20px;">'+icon+'</span>';
                h+='<div style="flex:1;min-width:0;">';
                h+='<div style="font-weight:700;">'+xss(c.title)+(c.username?' <span style="font-weight:400;color:var(--text-muted);">@'+xss(c.username)+'</span>':'')+'</div>';
                h+='<div style="display:flex;gap:8px;align-items:center;margin-top:2px;">';
                h+='<span style="font-family:var(--font-mono);font-size:var(--fs-xs);color:var(--text-muted);">ID: '+c.chat_id+'</span>';
                h+='<span style="padding:1px 8px;border-radius:10px;font-size:var(--fs-xs);font-weight:700;background:'+typeBg+';color:'+typeColor+';">'+typeLabel+'</span>';
                h+='</div></div>';
                if(c.already_added){
                    h+='<span style="color:var(--c-success);font-weight:700;font-size:var(--fs-sm);"><i class="fa-solid fa-check-circle"></i> Added</span>';
                } else {
                    h+="<button class=\"tg-btn sm succ\" id=\"discBtn-"+c.chat_id+"\" onclick=\"quickAddTarget('"+c.chat_id+"','"+xss(c.title).replace(/'/g,"\\'")+"','"+c.type+"')\" style=\"white-space:nowrap;\"><i class=\"fa-solid fa-plus\"></i> Add</button>";
                }
                h+='</div>';
            });
            h+='</div>';
            if(res)res.innerHTML=h;
        } else {
            // No chats found — switch to targets tab with instructions
            switchTab('targets',document.querySelectorAll('.tg-tab')[1]);
            const res=document.getElementById('discoverResult');
            if(res)res.innerHTML='<div style="padding:16px;text-align:center;background:var(--c-success-light);border:1px solid var(--c-success-border);border-radius:var(--card-radius);margin-top:8px;">'
                +'<i class="fa-solid fa-check-circle" style="font-size:20px;color:var(--c-success);margin-bottom:6px;display:block;"></i>'
                +'<strong style="color:var(--c-success);">Bot connected!</strong><br>'
                +'<span style="font-size:var(--fs-sm);color:var(--text-muted);">No chats yet. Send <code>/start</code> to your bot, or add it to a group and send a message, then click Discover Chats.</span></div>';
        }

        showStep(3);
    }catch(e){
        showRes('connRes','✗ '+xss(e.message),'err');
    }
    btn.disabled=false;btn.innerHTML='<i class="fa-solid fa-rocket"></i> Connect Bot';
}

function testConn(){
    const btn=document.getElementById('testConnBtn');
    btn.disabled=true;btn.innerHTML='<span class="sp"></span> Testing…';
    showRes('connRes','Calling Telegram API…','info');
    fetch(baseUrl+'/telegram/test-connection',{method:'POST',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json','Content-Type':'application/json'},
        body:JSON.stringify({bot_token:document.getElementById('cfgToken').value})
    }).then(r=>r.json()).then(d=>{
        btn.disabled=false;btn.innerHTML='<i class="fa-solid fa-plug-circle-check"></i> Test Connection';
        const badge=document.getElementById('statusBadge');
        if(d.success){
            showRes('connRes','<strong>✓ Connected!</strong> &nbsp; <strong>'+xss(d.bot_name)+'</strong> ('+xss(d.username)+') &nbsp;·&nbsp; ID: '+xss(d.bot_id),'ok');
            if(badge){badge.className='tg-status ok';badge.innerHTML='<i class="fa-solid fa-circle" style="font-size:8px;"></i> '+xss(d.username)+' connected';}
        } else {
            showRes('connRes','<strong>✗ Failed:</strong> '+xss(d.error),'err');
            if(badge) badge.className='tg-status';
        }
    }).catch(e=>{btn.disabled=false;btn.innerHTML='<i class="fa-solid fa-plug-circle-check"></i> Test Connection';showRes('connRes','✗ '+xss(e.message),'err');});
}

// ── Targets ────────────────────────────────────────────────────────────────
function discoverChats(btn){
    btn.disabled=true;
    const orig=btn.innerHTML;
    btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Scanning...';
    const res=document.getElementById('discoverResult');
    res.innerHTML='<div style="color:var(--text-muted);padding:12px;">Fetching updates from Telegram...</div>';
    fetch(baseUrl+'/telegram/discover',{headers:{'Accept':'application/json'}})
    .then(r=>r.json()).then(d=>{
        btn.disabled=false;btn.innerHTML=orig;
        if(!d.success){res.innerHTML='<div style="color:var(--c-danger);padding:8px;">'+xss(d.error)+'</div>';return;}
        if(!d.chats||!d.chats.length){
            res.innerHTML='<div style="padding:16px;text-align:center;color:var(--text-muted);background:var(--hover-bg);border-radius:var(--card-radius);border:1px solid var(--border-color);">'
                +'<i class="fa-solid fa-inbox" style="font-size:24px;display:block;margin-bottom:8px;opacity:.5;"></i>'
                +'<strong>No chats found</strong><br>'
                +'<span style="font-size:calc(var(--fs-base)*.79);">Send <code>/start</code> to your bot for personal, or add bot to a group and send a message.</span></div>';
            return;
        }
        let h='<div style="font-weight:700;margin-bottom:8px;">Found '+d.chats.length+' chat(s):</div>';
        h+='<div style="display:flex;flex-direction:column;gap:8px;">';
        d.chats.forEach(c=>{
            const icon=c.type==='private'?'👤':c.type==='channel'?'📢':'👥';
            const typeLabel=c.type==='private'?'Personal':c.type==='channel'?'Channel':c.type==='supergroup'?'Group':'Group';
            const typeBg=c.type==='private'?'var(--c-info-light)':c.type==='channel'?'var(--c-warning-light)':'var(--c-success-light)';
            const typeColor=c.type==='private'?'var(--c-info)':c.type==='channel'?'var(--c-warning)':'var(--c-success)';
            h+='<div style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:var(--card-bg);border:1px solid var(--border-color);border-radius:var(--card-radius);">';
            h+='<span style="font-size:20px;">'+icon+'</span>';
            h+='<div style="flex:1;min-width:0;">';
            h+='<div style="font-weight:700;font-size:var(--fs-base);">'+xss(c.title)+(c.username?' <span style="font-weight:400;color:var(--text-muted);">@'+xss(c.username)+'</span>':'')+'</div>';
            h+='<div style="display:flex;gap:8px;align-items:center;margin-top:2px;">';
            h+='<span style="font-family:var(--font-mono);font-size:var(--fs-xs);color:var(--text-muted);">ID: '+c.chat_id+'</span>';
            h+='<span style="padding:1px 8px;border-radius:10px;font-size:var(--fs-xs);font-weight:700;background:'+typeBg+';color:'+typeColor+';">'+typeLabel+'</span>';
            h+='</div></div>';
            if(c.already_added){
                h+='<span style="color:var(--c-success);font-weight:700;font-size:var(--fs-sm);display:flex;align-items:center;gap:4px;"><i class="fa-solid fa-check-circle"></i> Added</span>';
            } else {
                h+='<button class="tg-btn sm succ" id="discBtn-'+c.chat_id+'" onclick="quickAddTarget(\''+c.chat_id+'\',\''+xss(c.title).replace(/'/g,"\\'")+'\',\''+c.type+'\')" style="white-space:nowrap;"><i class="fa-solid fa-plus"></i> Add</button>';
            }
            h+='</div>';
        });
        h+='</div>';
        res.innerHTML=h;
    }).catch(e=>{btn.disabled=false;btn.innerHTML=orig;res.innerHTML='<div style="color:var(--c-danger);padding:8px;">'+xss(e.message)+'</div>';});
}

function quickAddTarget(chatId,title,type){
    const btn=document.getElementById('discBtn-'+chatId);
    if(btn){btn.disabled=true;btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i>';}
    const typeMap={private:'personal',supergroup:'group',group:'group',channel:'channel'};
    const body={name:title,chat_id:String(chatId),type:typeMap[type]||'group',is_default:false,skip_validate:false};
    fetch(baseUrl+'/telegram/targets',{method:'POST',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json','Content-Type':'application/json'},body:JSON.stringify(body)})
    .then(r=>r.json()).then(d=>{
        if(d.success){
            if(btn)btn.outerHTML='<span style="color:var(--c-success);font-weight:700;font-size:var(--fs-sm);display:flex;align-items:center;gap:4px;"><i class="fa-solid fa-check-circle"></i> Added</span>';
            if(d.target) renderNewTarget(d.target);
            showRes('addRes','✅ '+xss(d.message),'ok');
        } else {
            if(btn){btn.disabled=false;btn.innerHTML='<i class="fa-solid fa-plus"></i> Add';}
            showRes('addRes','✗ '+xss(d.error),'err');
        }
    }).catch(e=>{
        if(btn){btn.disabled=false;btn.innerHTML='<i class="fa-solid fa-plus"></i> Add';}
        showRes('addRes','✗ '+xss(e.message),'err');
    });
}

function useChatId(id,title,type){
    document.getElementById('addChatId').value=id;
    document.getElementById('addName').value=title;
    const typeMap={private:'personal',supergroup:'group',group:'group',channel:'channel'};
    const sel=document.getElementById('addType');if(sel)sel.value=typeMap[type]||'group';
    showRes('addRes','✓ Chat ID filled — click Add Target to save','ok');
}

function editTarget(id,name,chatId,type,notes){
    document.getElementById('editingId').value=id;
    document.getElementById('addName').value=name;
    document.getElementById('addChatId').value=chatId;
    document.getElementById('addType').value=type;
    document.getElementById('addNotes').value=notes||'';
    document.getElementById('targetFormTitle').innerHTML='<i class="fa-solid fa-pen" style="color:var(--c-secondary);"></i> Edit Target #'+id;
    document.getElementById('targetSubmitBtn').innerHTML='<i class="fa-solid fa-check"></i> Update Target';
    const tsb=document.getElementById('targetSubmitBtn');if(tsb)tsb.className='tg-btn primary';
    document.getElementById('cancelEditBtn').style.display='';
    document.getElementById('targetFormCard').scrollIntoView({behavior:'smooth'});
    showRes('addRes','Editing target #'+id+' — change fields and click Update','info');
}

function cancelEdit(){
    document.getElementById('editingId').value='';
    ['addName','addChatId','addNotes'].forEach(id=>document.getElementById(id).value='');
    document.getElementById('addDefault').checked=false;
    document.getElementById('skipValidate').checked=false;
    document.getElementById('targetFormTitle').innerHTML='<i class="fa-solid fa-plus" style="color:var(--c-success);"></i> Add New Target';
    document.getElementById('targetSubmitBtn').innerHTML='<i class="fa-solid fa-plus"></i> Add Target';
    const tsb2=document.getElementById('targetSubmitBtn');if(tsb2)tsb2.className='tg-btn succ';
    document.getElementById('cancelEditBtn').style.display='none';
    document.getElementById('addRes').className='tg-res';
    document.getElementById('addRes').textContent='';
}

function submitTarget(btn){
    const editId=document.getElementById('editingId').value;
    if(editId) updateTarget(btn,editId);
    else addTarget(btn);
}

function updateTarget(btn,id){
    const name=document.getElementById('addName').value.trim();
    const chatId=document.getElementById('addChatId').value.trim();
    const type=document.getElementById('addType').value;
    const notes=document.getElementById('addNotes').value.trim();
    if(!name||!chatId){showRes('addRes','Name and Chat ID are required.','err');return;}
    btn.disabled=true;
    const origText=btn.innerHTML;
    btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Updating...';
    showRes('addRes','🔍 Validating...','info');
    fetch(baseUrl+'/telegram/targets/'+id,{method:'PUT',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json','Content-Type':'application/json'},
        body:JSON.stringify({name,chat_id:chatId,type,notes,skip_validate:document.getElementById('skipValidate').checked?1:0})
    }).then(r=>{
        if(!r.ok) return r.text().then(t=>{throw new Error('HTTP '+r.status+': '+t.substring(0,300));});
        return r.json();
    }).then(d=>{
        btn.disabled=false;btn.innerHTML=origText;
        if(d.success){
            showRes('addRes','✓ '+xss(d.message),'ok');
            // Update the card in the list
            const t=d.target;
            const row=document.getElementById('tgt-'+id);
            if(row){
                const icon=t.type==='personal'?'📱':t.type==='channel'?'📢':'👥';
                const defBadge=t.is_default?'<span class="tgt-badge def">★ default</span>':'';
                row.innerHTML=`<div class="tgt-icon">${icon}</div>
                    <div style="flex:1;min-width:0;">
                        <div class="tgt-name">${xss(t.name)} ${defBadge}<span class="tgt-badge typ">${xss(t.type)}</span></div>
                        <div class="tgt-id">${xss(t.chat_id)}${t.notes?' · '+xss(t.notes):''}</div>
                    </div>
                    <button class="tg-btn sm sec" title="Set as default" onclick="setDefault(${t.id},this)">★</button>
                    <button class="tg-btn sm sec" title="Edit" onclick="editTarget(${t.id},'${xss(t.name).replace(/'/g,"\\'")}','${t.chat_id}','${t.type}','${xss(t.notes||'').replace(/'/g,"\\'")}')"><i class="fa-solid fa-pen"></i></button>
                    <button class="tg-btn del" onclick="deleteTarget(${t.id},'${xss(t.name).replace(/'/g,"\\'")}')"><i class="fa-solid fa-trash"></i></button>`;
            }
            // Update dropdown
            const opt=document.querySelector('#sendTargetSel option[value="'+id+'"]');if(!opt)return;
            if(opt) opt.textContent=(t.type==='personal'?'📱 ':t.type==='channel'?'📢 ':'👥 ')+t.name+' — '+t.chat_id+(t.notes?' ('+t.notes+')':'');
            cancelEdit();
        } else showRes('addRes','✗ '+xss(d.error||'Failed'),'err');
    }).catch(e=>{btn.disabled=false;btn.innerHTML=origText;showRes('addRes','✗ '+xss(e.message),'err');});
}

function addTarget(btn){
    const name=document.getElementById('addName').value.trim();
    const chatId=document.getElementById('addChatId').value.trim();
    const type=document.getElementById('addType').value;
    const notes=document.getElementById('addNotes').value.trim();
    const isDef=document.getElementById('addDefault').checked;
    if(!name||!chatId){showRes('addRes','Name and Chat ID are required.','err');return;}
    btn.disabled=true;
    const origText=btn.innerHTML;
    btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Validating...';
    showRes('addRes','🔍 Verifying Chat ID with Telegram...','info');
    fetch(baseUrl+'/telegram/targets',{method:'POST',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json','Content-Type':'application/json'},
        body:JSON.stringify({name,chat_id:chatId,type,notes,is_default:isDef?1:0,skip_validate:document.getElementById('skipValidate').checked?1:0})
    }).then(r=>{
        if(!r.ok) return r.text().then(t=>{throw new Error('HTTP '+r.status+': '+t.substring(0,300));});
        return r.json();
    }).then(d=>{
        btn.disabled=false;btn.innerHTML=origText;
        if(d.success){
            showRes('addRes','✓ '+xss(d.message),'ok');
            ['addName','addChatId','addNotes'].forEach(id=>document.getElementById(id).value='');
            document.getElementById('addDefault').checked=false;
    document.getElementById('skipValidate').checked=false;
            renderNewTarget(d.target);
            (document.getElementById('tgtCount')||{}).textContent=(parseInt((document.getElementById('tgtCount')||{}).textContent)||0)+1;
            const empty=document.getElementById('tgtEmpty');if(empty)empty.remove();
        } else showRes('addRes','✗ '+xss(d.error||'Failed'),'err');
    }).catch(e=>{btn.disabled=false;btn.innerHTML=origText;showRes('addRes','✗ '+xss(e.message),'err');});
}

function renderNewTarget(t){
    const icon=t.type==='personal'?'📱':t.type==='channel'?'📢':'👥';
    const defBadge=t.is_default?'<span class="tgt-badge def">★ default</span>':'';
    let list=document.getElementById('tgtList');
    if(!list){
        document.getElementById('tgtListWrap').innerHTML='<div class="tgt-list" id="tgtList"></div>';
        list=document.getElementById('tgtList');
    }
    const empty=document.getElementById('tgtEmpty');if(empty)empty.remove();
    if(t.is_default) document.querySelectorAll('.tgt-row').forEach(r=>{r.classList.remove('default');r.querySelectorAll('.tgt-badge.def').forEach(b=>b.remove());});
    const eName=xss(t.name).replace(/'/g,"&#39;");
    const eNotes=xss(t.notes||'').replace(/'/g,"&#39;");
    const div=document.createElement('div');
    div.className='tgt-row'+(t.is_default?' default':'');div.id='tgt-'+t.id;
    div.innerHTML=`<div class="tgt-icon">${icon}</div>
        <div style="flex:1;min-width:0;">
            <div class="tgt-name">${xss(t.name)} ${defBadge}<span class="tgt-badge typ">${xss(t.type)}</span></div>
            <div class="tgt-id">${xss(String(t.chat_id))}${t.notes?' · '+xss(t.notes):''}</div>
        </div>
        <button class="tg-btn sm succ" title="Send test message" onclick="sendTestTo(${t.id},'${eName}',this)"><i class="fa-solid fa-paper-plane"></i></button>
        <button class="tg-btn sm sec" title="Set as default" onclick="setDefault(${t.id},this)">★</button>
        <button class="tg-btn sm sec" title="Edit" onclick="editTarget(${t.id},'${eName}','${xss(String(t.chat_id))}','${t.type}','${eNotes}')"><i class="fa-solid fa-pen"></i></button>
        <button class="tg-btn del" onclick="deleteTarget(${t.id},'${eName}')"><i class="fa-solid fa-trash"></i></button>`;
    list.appendChild(div);
}

function setDefault(id,btn){
    fetch(baseUrl+'/telegram/targets/'+id+'/default',{method:'POST',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json','Content-Type':'application/json'}})
    .then(r=>r.json()).then(d=>{
        if(!d.success)return;
        document.querySelectorAll('.tgt-row').forEach(r=>{r.classList.remove('default');r.querySelectorAll('.tgt-badge.def').forEach(b=>b.remove());});
        const row=document.getElementById('tgt-'+id);
        if(row){
            row.classList.add('default');
            const nm=row.querySelector('.tgt-name');
            const b=document.createElement('span');b.className='tgt-badge def';b.textContent='★ default';nm.insertBefore(b,nm.firstChild);
        }
    });
}



function sendQuickTest(btn){
    const msg=(document.getElementById('testMsgBody').value||'').trim();
    if(!msg){showRes('sendRes','Type a message first.','err');return;}
    const editId=document.getElementById('editingId').value;
    const chatId=document.getElementById('addChatId').value;
    const name=document.getElementById('addName').value||'Test';
    if(!chatId){showRes('sendRes','No chat ID — select a target or enter one.','err');return;}
    const orig=btn.innerHTML;
    btn.disabled=true;btn.innerHTML='<span class="sp"></span> Sending...';
    fetch(baseUrl+'/telegram/test-send',{method:'POST',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json','Content-Type':'application/json'},
        body:JSON.stringify({target_id:editId||'custom',custom_chat_id:chatId,message:msg,bot_token:document.getElementById('cfgToken').value})
    }).then(r=>r.json()).then(d=>{
        btn.disabled=false;btn.innerHTML=orig;
        if(d.success) showRes('sendRes','✓ Message sent to '+xss(name)+'! Check Telegram.','ok');
        else showRes('sendRes','✗ '+xss(d.error||'Failed'),'err');
    }).catch(e=>{btn.disabled=false;btn.innerHTML=orig;showRes('sendRes','✗ '+xss(e.message),'err');});
}
function sendTestTo(targetId,name,btn){
    const orig=btn.innerHTML;
    btn.disabled=true;btn.innerHTML='<span class="sp"></span>';
    const msg='🔔 Test message from '+portalName+'\n\n📌 Target: '+name+'\n🕐 '+new Date().toLocaleString(undefined,{timeZone:portalTimezone})+'\n\n✅ If you see this, notifications are working!';
    fetch(baseUrl+'/telegram/test-send',{method:'POST',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json','Content-Type':'application/json'},
        body:JSON.stringify({target_id:String(targetId),message:msg,bot_token:document.getElementById('cfgToken').value})
    }).then(r=>r.json()).then(d=>{
        btn.disabled=false;
        if(d.success){btn.innerHTML='<i class="fa-solid fa-check"></i>';btn.title='Sent!';setTimeout(()=>{btn.innerHTML=orig;btn.title='Send test message';},2000);}
        else{btn.innerHTML=orig;showRes('addRes','✗ '+xss(d.error),'err');}
    }).catch(e=>{btn.disabled=false;btn.innerHTML=orig;showRes('addRes','✗ '+xss(e.message),'err');});
}
function deleteTarget(id,name){
    confirmAction('Delete "'+name+'"?', '', 'danger', () => {
        fetch(baseUrl+'/telegram/targets/'+id,{method:'DELETE',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json','Content-Type':'application/json'}})
        .then(r=>r.json()).then(d=>{
            if(d.success){
                const row=document.getElementById('tgt-'+id);if(row)row.remove();
                const opt=document.querySelector('#sendTargetSel option[value="'+id+'"]');if(opt)opt.remove();
                const cnt=document.getElementById('tgtCount');cnt.textContent=Math.max(0,(parseInt(cnt.textContent)||1)-1);
                if(activeSendTarget===String(id)){activeSendTarget='custom';const s=document.getElementById('sendTargetSel');if(s)s.value='custom';}
            }
        });
    });
}

// ── Send ───────────────────────────────────────────────────────────────────
function selTarget(val){
    activeSendTarget=val;
    (document.getElementById('customIdRow')||{style:{}}).style.display=val==='custom'?'':'none';
}

// ── Auto-check connection on page load ──────────────────────────────────
document.addEventListener('DOMContentLoaded', function(){
    const token = document.getElementById('cfgToken').value;
    if(!token) return; // no token saved, skip
    fetch(baseUrl+'/telegram/test-connection',{method:'POST',
        headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json','Content-Type':'application/json'},
        body:JSON.stringify({bot_token:token})
    }).then(r=>r.json()).then(d=>{
        const badge=document.getElementById('statusBadge');
        if(badge && d.success){
            badge.className='tg-status ok';
            badge.innerHTML='<i class="fa-solid fa-circle" style="font-size:8px;"></i> '+xss(d.username)+' ready';
        }
    }).catch(()=>{}); // silent fail
});

function sendTest(btn){
    btn.disabled=true;btn.innerHTML='<span class="sp"></span> Sending…';
    showRes('sendRes','Sending message…','info');
    const body={target_id:activeSendTarget,message:document.getElementById('testMsgBody').value,bot_token:document.getElementById('cfgToken').value};
    if(activeSendTarget==='custom')body.custom_chat_id=document.getElementById('customChatId').value;
    fetch(baseUrl+'/telegram/test-send',{method:'POST',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json','Content-Type':'application/json'},body:JSON.stringify(body)})
    .then(r=>r.json()).then(d=>{
        btn.disabled=false;btn.innerHTML='<i class="fa-solid fa-paper-plane"></i> Send Message';
        if(d.success) showRes('sendRes',xss(d.message||'✓ Sent! Check your Telegram.'),'ok');
        else showRes('sendRes','✗ '+xss(d.error||'Failed'),'err');
    }).catch(e=>{btn.disabled=false;btn.innerHTML='<i class="fa-solid fa-paper-plane"></i> Send Message';showRes('sendRes','✗ '+xss(e.message),'err');});
}


// ═══ TAB SWITCHING (unified) ═══
function switchTab(name, btn) {
    document.querySelectorAll('.tg-tab').forEach(t => t.classList.remove('on'));
    document.querySelectorAll('.tg-panel').forEach(p => p.classList.remove('on'));
    if (btn) btn.classList.add('on');
    const panel = document.getElementById('panel-' + name);
    if (panel) panel.classList.add('on');
    if (name === 'reports') loadReports();
    if (name === 'log') loadLog();
}

// ═══ REPORTS MODULE ═══
let allReports = [], allTargets = [], editingId = 0, editingSlug = '';
let monacoReady = false, sqlEditor = null, tplEditor = null, compEditor = null, phpEditor = null;

function targetIcon(t) { return t === 'personal' ? '📱' : t === 'channel' ? '📢' : '👥'; }

function loadReports() {
    fetch(baseUrl+'/telegram/reports/list', { headers: { Accept: 'application/json' } })
    .then(r => r.json()).then(d => {
        allReports = d.reports || [];
        renderReportList();
        document.getElementById('rptStats').textContent =
            allReports.length + ' reports | ' +
            allReports.filter(r => r.enabled).length + ' active | ' +
            allReports.filter(r => r.schedule_type !== 'manual' && r.target_id).length + ' scheduled';
    }).catch(e => {
        document.getElementById('rptBody').innerHTML = '<tr><td colspan="9" class="tg-empty" style="color:var(--c-danger);">Error: ' + xss(e.message) + '</td></tr>';
    });
}

function renderReportList() {
    const el = document.getElementById('rptBody');
    if (!allReports.length) { el.innerHTML = '<tr><td colspan="9" class="tg-empty">No reports yet.</td></tr>'; return; }
    el.innerHTML = allReports.map(r => {
        const schedMap = {every5m:'5min', every15m:'15min', every30m:'30min'};
        const sched = schedMap[r.schedule_type] ? schedMap[r.schedule_type] :
                     r.schedule_type === 'hourly' ? 'Hourly :' + (r.schedule_time || '00:00').split(':')[1] :
                     r.schedule_type === 'daily' ? (r.schedule_time || '').substring(0,5) :
                     r.schedule_type === 'weekday' ? 'M-F ' + (r.schedule_time || '').substring(0,5) :
                     r.schedule_type === 'weekly' ? ['','Mon','Tue','Wed','Thu','Fri','Sat','Sun'][r.schedule_day||0] + ' ' + (r.schedule_time||'').substring(0,5) :
                     r.schedule_type === 'monthly' ? '1st ' + (r.schedule_time || '').substring(0,5) :
                     r.schedule_type;
        const last = r.last_sent_at ? new Date(r.last_sent_at).toLocaleString() : '—';
        const stCls = r.last_status === 'sent' ? 'ok' : r.last_status === 'failed' ? 'fail' : 'none';
        const stTxt = r.last_status === 'sent' ? '✓ ' + r.send_count : r.last_status === 'failed' ? '✗ ' + r.fail_count : '—';
        const failWarn = r.consecutive_fails >= 3 ? ' <span style="color:var(--c-danger);">⚠' + r.consecutive_fails + '/5</span>' : '';
        const typeBadge = r.category === 'alerts' ? '<span class="rpt-badge alert">ALERT</span>' :
                         r.php_code ? '<span class="rpt-badge code">DB</span>' :
                         '<span class="rpt-badge code">BUILT-IN</span>';
        return '<tr class="' + (r.enabled ? '' : 'disabled') + '">' +
            '<td><input type="checkbox" class="rpt-check" value="' + r.id + '"></td>' +
            '<td style="font-size:1.3em;">' + (r.icon || '📊') + '</td>' +
            '<td><span class="rpt-row-name" onclick="openEditor(' + r.id + ')">' + xss(r.name) + '</span>' + failWarn + '</td>' +
            '<td>' + typeBadge + '</td>' +
            '<td style="font-size:calc(var(--fs-base)*.79);">' + sched + '</td>' +
            '<td style="font-size:calc(var(--fs-base)*.79);">' + (r.target_name ? targetIcon(r.target_type) + ' ' + xss(r.target_name) : '—') + '</td>' +
            '<td style="font-size:calc(var(--fs-base)*.71);color:var(--text-muted);">' + last + '</td>' +
            '<td class="rpt-st ' + stCls + '">' + stTxt + '</td>' +
            '<td style="white-space:nowrap;">' +
                '<button class="tg-btn sec" style="padding:2px 6px;font-size:calc(var(--fs-base)*.71);" onclick="openEditor(' + r.id + ')" title="Edit"><i class="fa-solid fa-pen"></i></button> ' +
                '<button class="tg-btn sec" style="padding:2px 6px;font-size:calc(var(--fs-base)*.71);" onclick="sendNow(' + r.id + ')" title="Send now"><i class="fa-solid fa-paper-plane"></i></button> ' +
                '<button class="tg-btn sec" style="padding:2px 6px;font-size:calc(var(--fs-base)*.71);" onclick="cloneReport(' + r.id + ')" title="Clone"><i class="fa-solid fa-copy"></i></button> ' +
                '<button class="tg-btn sec" style="padding:2px 6px;font-size:calc(var(--fs-base)*.71);" onclick="toggleReport(' + r.id + ')" title="Toggle">' + (r.enabled ? '<i class="fa-solid fa-pause"></i>' : '<i class="fa-solid fa-play"></i>') + '</button> ' +
                '<button class="tg-btn sec" style="padding:2px 6px;font-size:calc(var(--fs-base)*.71);color:var(--c-danger);" onclick="deleteReport(' + r.id + ',\'' + xss(r.name).replace(/'/g, "\\'") + '\')" title="Delete"><i class="fa-solid fa-trash"></i></button>' +
            '</td></tr>';
    }).join('');
}

function toggleCheckAll(cb) { document.querySelectorAll('.rpt-check').forEach(c => c.checked = cb.checked); }

// ═══ EDITOR ═══
function openEditor(id, forceType) {
    editingId = id;
    document.getElementById('rptListView').style.display = 'none';
    document.getElementById('rptEditorView').classList.add('active');

    // Load targets for dropdowns
    let pendingTargetId = null;
    fetch(baseUrl+'/telegram/targets', { headers: { Accept: 'application/json' } })
    .then(r => r.json()).then(d => {
        allTargets = d.targets || d.data || [];
        const opts = '<option value="">— No target —</option>' + allTargets.map(t =>
            '<option value="' + t.id + '">' + targetIcon(t.type) + ' ' + xss(t.name) + '</option>'
        ).join('');
        document.getElementById('edTarget').innerHTML = opts;
        document.getElementById('prevTarget').innerHTML = opts;
        // Apply pending target if report loaded first
        if (pendingTargetId) {
            document.getElementById('edTarget').value = pendingTargetId;
            document.getElementById('prevTarget').value = pendingTargetId;
        }
    });

    // Load slug dropdown for built-in
    fetch(baseUrl+'/telegram/reports/list', { headers: { Accept: 'application/json' } })
    .then(r => r.json()).then(d => {
        const unreg = d.unregistered || [];
        const existing = (d.reports || []).filter(r => r.report_type === 'code').map(r => r.slug);
        // Slug is now auto-managed (hidden input)
    });

    if (id) {
        // Load existing report
        fetch(baseUrl+'/telegram/reports/' + id, { headers: { Accept: 'application/json' } })
        .then(r => r.json()).then(d => {
            if (!d.success) return;
            const r = d.report;
            document.getElementById('editorTitle').textContent = '✏ ' + r.name;
            editingSlug = r.slug || '';
            document.getElementById('edName').value = r.name || '';
            document.getElementById('edIcon').value = r.icon || '📊';
            document.getElementById('edCategory').value = r.category || 'custom';
            document.getElementById('edDesc').value = r.description || '';
            document.getElementById('edType').value = r.report_type || 'code';
            document.getElementById('edTarget').value = r.target_id || '';
            pendingTargetId = r.target_id || '';
            document.getElementById('edSchedule').value = r.schedule_type || 'manual';
            document.getElementById('edTime').value = r.schedule_time || '09:00';
            document.getElementById('edDay').value = r.schedule_day || 1;
            if (r.schedule_type === 'hourly' && r.schedule_time) {
                document.getElementById('edMinute').value = parseInt(r.schedule_time.split(':')[1] || 0);
            }
            document.getElementById('edEnabled').value = r.enabled ? '1' : '0';
            const p = typeof r.params === 'string' ? JSON.parse(r.params || '{}') : r.params || {};
            document.getElementById('edDateRange').value = p.date_range || 'yesterday';

            // Set slug
            document.getElementById('edSlug').value = r.slug || '';

            onSchedChange();

            // Set Monaco editors after UI is visible
            initMonaco(() => {
                if (r.report_type === 'template') {
                    if (sqlEditor) sqlEditor.setValue(r.query || '');
                    if (tplEditor) tplEditor.setValue(r.template || '');
                    if (compEditor) compEditor.setValue(r.computed_fields ? JSON.stringify(r.computed_fields, null, 2) : '{}');
                } else {
                    // Load PHP source: from DB first, then fall back to file
                    if (r.php_code) {
                        if (phpEditor) phpEditor.setValue(r.php_code);
                    } else {
                        fetch(baseUrl+'/telegram/reports/' + (r.slug || 'unknown') + '/source', { headers: { Accept: 'application/json' } })
                        .then(x => x.json()).then(x => {
                            if (phpEditor) phpEditor.setValue(x.source || '// Source not found for: ' + r.slug);
                        }).catch(() => { if (phpEditor) phpEditor.setValue('// Error loading source'); });
                    }
                }
            });

            // Status bar
            document.getElementById('edStatusBar').style.display = '';
            document.getElementById('edStSent').textContent = 'Sent: ' + (r.send_count || 0);
            document.getElementById('edStFail').textContent = 'Fail: ' + (r.fail_count || 0);
            document.getElementById('edStLast').textContent = 'Last: ' + (r.last_sent_at ? new Date(r.last_sent_at).toLocaleString() : '—');

            // Set preview target
            if (r.target_id) document.getElementById('prevTarget').value = r.target_id;
        });
    } else {
        // New report
        document.getElementById('editorTitle').textContent = '+ New Report';
        ['edName', 'edDesc'].forEach(id => document.getElementById(id).value = '');
        document.getElementById('edIcon').value = '📊';
        document.getElementById('edCategory').value = 'custom';
        document.getElementById('edType').value = 'code';
        document.getElementById('edSchedule').value = 'manual';
        document.getElementById('edEnabled').value = '1';
        document.getElementById('edStatusBar').style.display = 'none';
        document.getElementById('prevOutput').textContent = 'Click "Generate Preview" to see the report output.';
        onSchedChange();
        initMonaco(() => {
            if (phpEditor) phpEditor.setValue('    private function buildNewReport(array $params): string\n    {\n        [$dateFrom, $dateTo] = $this->resolveDates($params);\n        $label = $this->dateLabel($dateFrom, $dateTo);\n\n        // Your code here\n        // Use: DB::table(), $this->n(), $dl = \'$\'\n        // Return text, use __SPLIT__ for multi-message\n\n        return \'📊 *Report* \' . $label;\n    }');
        });
    }
}

function closeEditor() {
    document.getElementById('rptEditorView').classList.remove('active');
    document.getElementById('rptListView').style.display = '';
    loadReports();
}

function onSchedChange() {
    const v = document.getElementById('edSchedule').value;
    const extra = document.getElementById('edSchedExtra');
    const timeWrap = document.getElementById('edTimeWrap');
    const dayWrap = document.getElementById('edDayWrap');
    const minWrap = document.getElementById('edMinWrap');
    const desc = document.getElementById('edSchedDesc');

    if (v === 'manual') {
        extra.style.display = 'none';
        return;
    }

    extra.style.display = 'block';
    const hasTime = ['daily','weekday','weekly','monthly'].includes(v);
    const hasDay = v === 'weekly';
    const hasMin = v === 'hourly';

    timeWrap.style.display = hasTime ? '' : 'none';
    dayWrap.style.display = hasDay ? '' : 'none';
    minWrap.style.display = hasMin ? '' : 'none';

    const time = document.getElementById('edTime').value || '09:00';
    const dayName = ['','Mon','Tue','Wed','Thu','Fri','Sat','Sun'][document.getElementById('edDay').value || 1];
    const minute = document.getElementById('edMinute').value || '0';

    const descs = {
        every5m: '⏱ Sends every 5 minutes, 24/7',
        every15m: '⏱ Sends every 15 minutes, 24/7',
        every30m: '⏱ Sends every 30 minutes, 24/7',
        hourly: '⏱ Sends every hour at :' + String(minute).padStart(2,'0'),
        daily: '📅 Sends every day at ' + time,
        weekday: '📅 Sends Mon–Fri at ' + time,
        weekly: '📅 Sends every ' + dayName + ' at ' + time,
        monthly: '📅 Sends on the 1st of each month at ' + time,
    };
    desc.textContent = descs[v] || '';
}
function onTypeChange() {} // kept for backward compat

// ═══ SAVE ═══
function saveReport() {
    const body = {
        name: document.getElementById('edName').value.trim(),
        report_type: 'code',
        icon: document.getElementById('edIcon').value || '📊',
        category: document.getElementById('edCategory').value,
        description: document.getElementById('edDesc').value,
        target_id: document.getElementById('edTarget').value || null,
        schedule_type: document.getElementById('edSchedule').value,
        schedule_time: document.getElementById('edSchedule').value === 'hourly'
            ? '00:' + String(document.getElementById('edMinute').value || 0).padStart(2,'0')
            : document.getElementById('edTime').value,
        schedule_day: document.getElementById('edDay').value,
        enabled: document.getElementById('edEnabled').value === '1',
        params: JSON.stringify({ date_range: document.getElementById('edDateRange').value }),
    };
    body.slug = editingSlug || body.name.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
    if (phpEditor) body.php_code = phpEditor.getValue();
    if (!body.name) { toast('Name is required', 'err'); return; }

    const url = editingId ? '/telegram/reports/' + editingId : '/telegram/reports';
    const method = editingId ? 'PUT' : 'POST';

    fetch(url, { method, headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json', 'Content-Type': 'application/json' }, body: JSON.stringify(body) })
    .then(r => { if (!r.ok) return r.text().then(t => { throw new Error(t.substring(0, 200)); }); return r.json(); })
    .then(d => {
        if (d.success) { toast(d.message || 'Saved!', 'ok'); if (!editingId && d.report) editingId = d.report.id; }
        else toast(d.error || 'Failed', 'err');
    }).catch(e => toast(e.message, 'err'));
}

// ═══ SAVE SOURCE CODE TO PHP FILE ═══
function saveSourceCode() {
    if (!phpEditor) { toast('Editor not loaded', 'err'); return; }
    const slug = editingSlug || document.getElementById('edSlug').value;
    if (!slug) { toast('No slug — save the report settings first', 'err'); return; }
    const code = phpEditor.getValue();
    if (!code.trim()) { toast('Code is empty', 'err'); return; }

    confirmAction('Save code to server?', 'This overwrites the PHP method for "' + slug + '" in TelegramReportBuilder.php. A backup will be created automatically.', 'warning', () => {
        const btn = document.querySelector('[onclick="saveSourceCode()"]');
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...'; }

        fetch(baseUrl+'/telegram/reports/' + slug + '/save-source', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ code: code })
        })
        .then(r => {
            if (!r.ok) return r.text().then(t => { throw new Error('HTTP ' + r.status + ': ' + t.substring(0, 300)); });
            return r.json();
        })
        .then(d => {
            if (d.success) {
                toast(d.message || 'Code saved!', 'ok');
                document.getElementById('prevOutput').textContent = '✅ ' + (d.message || 'Code saved!') + '\n\nClick Preview to test the updated code.';
            } else {
                toast('Failed: ' + (d.error || 'Unknown'), 'err');
                document.getElementById('prevOutput').textContent = '❌ SAVE FAILED\n\n' + (d.error || 'Unknown error');
            }
        })
        .catch(e => {
            toast('Error: ' + e.message, 'err');
            document.getElementById('prevOutput').textContent = '❌ SAVE ERROR\n\n' + e.message;
        })
        .finally(() => {
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Save Code'; }
        });
    });
}

// ═══ DIAGNOSE ═══
function diagnoseReport() {
    if (!editingId) { toast('Save the report first', 'info'); return; }
    const out = document.getElementById('prevOutput');
    out.textContent = '🔍 Running diagnostics...';
    document.getElementById('prevInfo').textContent = '';

    fetch(baseUrl+'/telegram/reports/' + editingId + '/diagnose', { headers: { Accept: 'application/json' } })
    .then(r => {
        if (!r.ok) return r.text().then(t => { throw new Error('HTTP ' + r.status + ': ' + t.substring(0, 500)); });
        return r.json();
    })
    .then(d => {
        let txt = '🔍 DIAGNOSTIC REPORT\n';
        txt += '━━━━━━━━━━━━━━━━━━━━\n';
        txt += 'Report: ' + (d.name || '?') + '\n';
        txt += 'Slug: ' + (d.slug || '?') + '\n';
        txt += 'Type: ' + (d.type || '?') + '\n';
        txt += 'Status: ' + (d.status === 'healthy' ? '✅ HEALTHY' : '⚠️ ISSUES FOUND') + '\n\n';

        if (d.checks && d.checks.length) {
            d.checks.forEach(c => {
                txt += (c.pass ? '✅' : '❌') + ' ' + c.test + '\n';
                txt += '   → ' + c.detail + '\n';
            });
        } else {
            txt += '⚠️ No checks returned.\n';
        }

        out.textContent = txt;
        document.getElementById('prevInfo').textContent = d.status === 'healthy' ? 'All checks passed' : 'Fix the ❌ items above';
        toast(d.status === 'healthy' ? 'All checks passed!' : 'Issues found — see details', d.status === 'healthy' ? 'ok' : 'err');
    }).catch(e => {
        out.textContent = '❌ Diagnostic failed\n\n' + e.message + '\n\nThis usually means a server error. Check the report exists and has a valid slug.';
        toast('Diagnostic error', 'err');
    });
}

// ═══ PREVIEW ═══
function previewReport() {
    if (!editingId) { toast('Save the report first', 'info'); return; }
    document.getElementById('prevOutput').textContent = '⏳ Generating...';
    const dr = document.getElementById('edDateRange').value;
    fetch(baseUrl+'/telegram/reports/' + editingId + '/preview', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json', 'Content-Type': 'application/json' }, body: JSON.stringify({ date_range: dr }) })
    .then(r => r.json()).then(d => {
        document.getElementById('prevOutput').textContent = d.text || 'No output';
        document.getElementById('prevInfo').textContent = (d.chars || 0) + ' chars | ' + (d.duration_ms || 0) + 'ms' + (d.chars > 4096 ? ' ⚠️ Will split into parts' : '');
    }).catch(e => { document.getElementById('prevOutput').textContent = 'Error: ' + e.message; });
}

function testSendReport() {
    if (!editingId) { toast('Save the report first', 'info'); return; }
    const targetId = document.getElementById('prevTarget').value;
    if (!targetId) { toast('Select a target to send to', 'err'); document.getElementById('prevMsg').innerHTML = '<span style="color:var(--c-danger);">✗ No target selected. Pick one from the dropdown.</span>'; return; }
    const msgEl = document.getElementById('prevMsg');
    msgEl.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending to target #' + targetId + '...';
    fetch(baseUrl+'/telegram/reports/' + editingId + '/send', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json', 'Content-Type': 'application/json' }, body: JSON.stringify({ target_id: targetId }) })
    .then(r => {
        if (!r.ok) return r.text().then(t => { throw new Error('HTTP ' + r.status + ': ' + t.substring(0, 300)); });
        return r.json();
    })
    .then(d => {
        if (d.success) {
            msgEl.innerHTML = '<span style="color:var(--c-success);">✓ ' + xss(d.message || 'Sent!') + '</span>';
            toast(d.message || 'Sent!', 'ok');
        } else {
            const errDetail = d.error || 'Unknown error';
            msgEl.innerHTML = '<span style="color:var(--c-danger);">✗ ' + xss(errDetail) + '</span>';
            toast('Send failed: ' + errDetail, 'err');
            console.error('Test Send failed:', d);
        }
    }).catch(e => {
        msgEl.innerHTML = '<span style="color:var(--c-danger);">✗ Network/Server error: ' + xss(e.message) + '</span>';
        toast('Error: ' + e.message, 'err');
        console.error('Test Send error:', e);
    });
}

function testQuery() {
    const query = sqlEditor ? sqlEditor.getValue() : '';
    if (!query) { toast('Enter a SQL query first', 'err'); return; }
    document.getElementById('queryResult').innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
    fetch(baseUrl+'/telegram/reports/test-query', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json', 'Content-Type': 'application/json' }, body: JSON.stringify({ query, date_range: document.getElementById('edDateRange').value }) })
    .then(r => r.json()).then(d => {
        if (d.success) {
            document.getElementById('queryResult').innerHTML = '<span style="color:var(--c-success);">✓ ' + d.count + ' row(s) in ' + d.duration_ms + 'ms</span>';
            if (d.rows && d.rows.length) {
                let h = '<table style="width:100%;border-collapse:collapse;"><tr>' + d.columns.map(c => '<th style="padding:2px 5px;background:var(--hover-bg);border:1px solid var(--border-color);font-size:calc(var(--fs-base)*.6);">' + xss(c) + '</th>').join('') + '</tr>';
                d.rows.slice(0, 5).forEach(r => { h += '<tr>' + d.columns.map(c => '<td style="padding:2px 5px;border:1px solid var(--border-light);font-family:monospace;font-size:calc(var(--fs-base)*.6);">' + xss(String(r[c]??'')) + '</td>').join('') + '</tr>'; });
                h += '</table><div style="font-size:calc(var(--fs-base)*.6);color:var(--c-info);padding:3px;">Placeholders: ' + d.columns.map(c => '{{' + c + '}}').join(', ') + '</div>';
                document.getElementById('queryPreview').innerHTML = h;
                document.getElementById('queryPreview').style.display = '';
            }
        } else {
            document.getElementById('queryResult').innerHTML = '<span style="color:var(--c-danger);">✗ ' + xss(d.error||'') + '</span>';
            document.getElementById('queryPreview').style.display = 'none';
        }
    }).catch(e => { document.getElementById('queryResult').innerHTML = '<span style="color:var(--c-danger);">✗ ' + xss(e.message) + '</span>'; });
}

// ═══ ACTIONS ═══
function sendNow(id) {
    const r = allReports.find(x => x.id === id);
    if (r && !r.target_id) { toast('No target set. Edit the report first.', 'err'); return; }
    toast('Sending...', 'info');
    fetch(baseUrl+'/telegram/reports/' + id + '/send', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' } })
    .then(r => { if (!r.ok) return r.text().then(t => { throw new Error('HTTP ' + r.status); }); return r.json(); })
    .then(d => { toast(d.success ? (d.message||'Sent!') : ('Failed: ' + (d.error||'Unknown')), d.success ? 'ok' : 'err'); loadReports(); })
    .catch(e => toast('Error: ' + e.message, 'err'));
}
function cloneReport(id) {
    fetch(baseUrl+'/telegram/reports/' + id + '/clone', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' } })
    .then(r => r.json()).then(d => { toast(d.success ? (d.message||'Cloned!') : (d.error||'Failed'), d.success ? 'ok' : 'err'); loadReports(); })
    .catch(e => toast(e.message, 'err'));
}
function toggleReport(id) {
    fetch(baseUrl+'/telegram/reports/' + id + '/toggle', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' } })
    .then(r => r.json()).then(d => { loadReports(); }).catch(e => toast(e.message, 'err'));
}
function deleteReport(id, name) {
    confirmAction('Delete "' + name + '"?', 'This report and its schedule will be permanently removed.', 'danger', () => {
        fetch(baseUrl+'/telegram/reports/' + id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' } })
        .then(r => r.json()).then(d => { toast(d.success ? 'Deleted' : (d.error||'Failed'), d.success ? 'ok' : 'err'); loadReports(); })
        .catch(e => toast(e.message, 'err'));
    });
}
function bulkSend() {
    const ids = [...document.querySelectorAll('.rpt-check:checked')].map(c => parseInt(c.value));
    if (!ids.length) { toast('Select reports first', 'info'); return; }
    confirmAction('Send ' + ids.length + ' report(s) now?', '', 'warning', () => {
        fetch(baseUrl+'/telegram/reports/send-bulk', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json', 'Content-Type': 'application/json' }, body: JSON.stringify({ ids }) })
        .then(r => r.json()).then(d => { toast(d.message || 'Done', d.success ? 'ok' : 'err'); loadReports(); })
        .catch(e => toast(e.message, 'err'));
    });
}

// ═══ LOG ═══
function loadLog() {
    const p = new URLSearchParams();
    const st = document.getElementById('logStatus').value;
    const tp = document.getElementById('logType').value;
    const tg = document.getElementById('logTarget').value;
    if (st) p.set('status', st); if (tp) p.set('type', tp); if (tg) p.set('target', tg);
    document.getElementById('logBody').innerHTML = '<tr><td colspan="7" class="tg-empty"><i class="fa-solid fa-spinner fa-spin"></i></td></tr>';
    fetch(baseUrl+'/telegram/log?' + p, { headers: { Accept: 'application/json' } })
    .then(r => r.json()).then(d => {
        const logs = d.logs || [];
        if (!logs.length) { document.getElementById('logBody').innerHTML = '<tr><td colspan="7" class="tg-empty">No entries.</td></tr>'; return; }
        document.getElementById('logBody').innerHTML = logs.map(l =>
            '<tr><td style="white-space:nowrap;font-size:calc(var(--fs-base)*.71);">' + (l.sent_at||'') + '</td>' +
            '<td>' + xss(l.type||'') + '</td>' +
            '<td><code>' + xss(l.report_slug||'') + '</code></td>' +
            '<td>' + xss(l.target||'') + '</td>' +
            '<td class="rpt-st ' + (l.status==='sent'?'ok':'fail') + '">' + (l.status==='sent'?'✓':'✗ ' + xss((l.error||'').substring(0,40))) + '</td>' +
            '<td>' + (l.duration_ms||'') + '</td>' +
            '<td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:calc(var(--fs-base)*.71);" title="' + xss(l.message||'') + '">' + xss((l.message||'').substring(0,60)) + '</td></tr>'
        ).join('');
    }).catch(e => { document.getElementById('logBody').innerHTML = '<tr><td colspan="7" class="tg-empty" style="color:var(--c-danger);">' + xss(e.message) + '</td></tr>'; });
}

// ═══ MONACO LAZY LOAD ═══
function initMonaco(cb) {
    if (monacoReady) {
        setupEditors();
        setTimeout(() => { if (cb) cb(); }, 200);
        return;
    }
    const script = document.createElement('script');
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.52.2/min/vs/loader.min.js';
    script.onload = () => {
        require.config({ paths: { vs: 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.52.2/min/vs' } });
        require(['vs/editor/editor.main'], () => {
            monacoReady = true;
            setupEditors();
            setTimeout(() => { if (cb) cb(); }, 200);
        });
    };
    script.onerror = () => { console.error('Monaco CDN failed'); if (cb) cb(); };
    document.head.appendChild(script);
}

function setupEditors() {
    const opts = {
        minimap: { enabled: false },
        automaticLayout: true,
        fontSize: 13,
        wordWrap: 'on',
        scrollBeyondLastLine: false,
        tabSize: 4,
        folding: true,
        foldingStrategy: 'indentation',
        foldingHighlight: true,
        showFoldingControls: 'always',
        bracketPairColorization: { enabled: true },
        matchBrackets: 'always',
        renderLineHighlight: 'all',
        smoothScrolling: true,
        cursorBlinking: 'smooth',
        cursorSmoothCaretAnimation: 'on',
        formatOnPaste: true,
        suggestOnTriggerCharacters: true,
        quickSuggestions: true,
        snippetSuggestions: 'inline',
        renderWhitespace: 'selection',
        guides: { indentation: true, bracketPairs: true },
        padding: { top: 8, bottom: 8 },
    };
    const bg = getComputedStyle(document.documentElement).getPropertyValue('--color-bg').trim();
    const theme = (bg && (bg.startsWith('#1') || bg.startsWith('#2') || bg.startsWith('#0') || bg.startsWith('rgb(1') || bg.startsWith('rgb(2'))) ? 'vs-dark' : 'vs';
    window._monacoTheme = theme;

    setTimeout(() => {
        try {
            const phpDiv = document.getElementById('monacoPhp');
            if (!phpEditor && phpDiv && phpDiv.offsetHeight > 0) {
                phpEditor = monaco.editor.create(phpDiv, {...opts, language:'php', theme, lineNumbers:'on', tabSize:4});
                phpEditor.onDidChangeCursorPosition(e => {
                    const pos = document.getElementById('edCursorPos');
                    if (pos) pos.textContent = 'Ln ' + e.position.lineNumber + ', Col ' + e.position.column;
                });
            }
            const sqlDiv = document.getElementById('monacoSql');
            if (!sqlEditor && sqlDiv && sqlDiv.offsetHeight > 0)
                sqlEditor = monaco.editor.create(sqlDiv, {...opts, language:'sql', theme, lineNumbers:'on', tabSize:2});
            const tplDiv = document.getElementById('monacoTpl');
            if (!tplEditor && tplDiv && tplDiv.offsetHeight > 0)
                tplEditor = monaco.editor.create(tplDiv, {...opts, language:'markdown', theme, tabSize:2});
            const compDiv = document.getElementById('monacoComp');
            if (!compEditor && compDiv && compDiv.offsetHeight > 0)
                compEditor = monaco.editor.create(compDiv, {...opts, language:'json', theme, lineNumbers:'off', tabSize:2});
        } catch(e) { console.error('Monaco setup error:', e); }
    }, 100);
}

// ═══ EDITOR TOOLBAR ACTIONS ═══
function edAction(action, target) {
    const ed = target === 'sql' ? sqlEditor : target === 'tpl' ? tplEditor : phpEditor;
    if (!ed) return;
    switch(action) {
        case 'fold':
            ed.getAction('editor.foldAll')?.run();
            break;
        case 'unfold':
            ed.getAction('editor.unfoldAll')?.run();
            break;
        case 'format':
            ed.getAction('editor.action.formatDocument')?.run();
            break;
        case 'wrap':
            const current = ed.getOption(monaco.editor.EditorOption.wordWrap);
            ed.updateOptions({ wordWrap: current === 'on' ? 'off' : 'on' });
            break;
        case 'minimap':
            const mm = ed.getOption(monaco.editor.EditorOption.minimap);
            ed.updateOptions({ minimap: { enabled: !mm.enabled } });
            break;
        case 'zoomin':
            const fs1 = ed.getOption(monaco.editor.EditorOption.fontSize);
            ed.updateOptions({ fontSize: Math.min(fs1 + 1, 24) });
            break;
        case 'zoomout':
            const fs2 = ed.getOption(monaco.editor.EditorOption.fontSize);
            ed.updateOptions({ fontSize: Math.max(fs2 - 1, 10) });
            break;
        case 'find':
            ed.getAction('actions.find')?.run();
            break;
        case 'replace':
            ed.getAction('editor.action.startFindReplaceAction')?.run();
            break;
        case 'copy':
            navigator.clipboard.writeText(ed.getValue()).then(() => toast('Copied!', 'ok'));
            break;
        case 'fullscreen':
            const container = ed.getDomNode().parentElement;
            container.classList.toggle('fullscreen');
            ed.layout();
            break;
        case 'dark':
            window._monacoTheme = window._monacoTheme === 'vs-dark' ? 'vs' : 'vs-dark';
            monaco.editor.setTheme(window._monacoTheme);
            break;
    }
}

// Slug change → load PHP source
// ESC closes editor
document.addEventListener('keydown', e => { if (e.key === 'Escape' && document.getElementById('rptEditorView').classList.contains('active')) closeEditor(); });
// Ctrl+S saves
document.addEventListener('keydown', e => { if ((e.ctrlKey || e.metaKey) && e.key === 's' && document.getElementById('rptEditorView').classList.contains('active')) { e.preventDefault(); saveReport(); } });


</script>
@endpush
