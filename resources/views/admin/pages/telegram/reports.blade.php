@extends('admin.layouts.app')
@section('title', 'Telegram Reports')

@push('styles')
<style>

/* ═══ Layout ═══ */
.tr-tabs{display:flex;gap:2px;margin-bottom:20px;border-bottom:2px solid var(--border-color);}
.tr-tab{padding:10px 20px;cursor:pointer;font-weight:600;font-size:calc(var(--fs-base)*.93);color:var(--text-muted);border-bottom:3px solid transparent;transition:all .15s;}
.tr-tab:hover{color:var(--text-body);}
.tr-tab.active{color:var(--c-secondary);border-bottom-color:var(--c-secondary);}
.tr-panel{display:none;} .tr-panel.active{display:block;}

/* ═══ Cards ═══ */
.tr-box{background:var(--card-bg);border:1px solid var(--border-color);border-radius:var(--card-radius);padding:16px;margin-bottom:16px;}
.tr-box h4{margin:0 0 12px;font-size:var(--fs-base);font-weight:700;display:flex;align-items:center;gap:8px;}
.tr-box h4 .tag{font-size:calc(var(--fs-base)*0.71);color:var(--text-muted);font-weight:400;}

/* ═══ Subscription cards ═══ */
.sub-list{display:flex;flex-direction:column;gap:10px;}
.sub-card{display:flex;align-items:center;gap:12px;padding:12px 16px;border:1px solid var(--border-color);border-radius:var(--btn-radius);background:var(--hover-bg);transition:all .15s;}
.sub-card:hover{border-color:var(--c-secondary);box-shadow:0 2px 8px rgba(0,0,0,.05);}
.sub-card.disabled{opacity:.5;}
.sub-icon{font-size:1.4em;flex-shrink:0;}
.sub-info{flex:1;min-width:0;}
.sub-name{font-weight:700;font-size:calc(var(--fs-base)*.93);}
.sub-meta{font-size:calc(var(--fs-base)*.71);color:var(--text-muted);margin-top:2px;display:flex;gap:12px;flex-wrap:wrap;}
.sub-badge{display:inline-block;padding:1px 8px;border-radius:10px;font-size:calc(var(--fs-base)*.64);font-weight:700;text-transform:uppercase;letter-spacing:.5px;}
.sub-badge.daily{background:var(--c-success-light);color:var(--c-success);} .sub-badge.weekly{background:var(--c-secondary-light);color:var(--c-secondary);}
.sub-badge.monthly{background:#fce4ec;color:#c62828;} .sub-badge.manual{background:var(--hover-bg);color:var(--text-muted);border:1px solid var(--border-color);}
.sub-badge.hourly{background:var(--c-warning-light);color:var(--c-warning);} .sub-badge.realtime{background:var(--c-purple-light);color:var(--c-purple);}
.sub-actions{display:flex;gap:4px;flex-shrink:0;}
.sub-status{font-size:calc(var(--fs-base)*.71);}
.sub-status.ok{color:var(--c-success);} .sub-status.fail{color:var(--c-danger);} .sub-status.never{color:var(--text-muted);}

/* ═══ Report catalog ═══ */
.rpt-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:10px;margin-top:10px;}
.rpt-card{padding:14px;border:1px solid var(--border-color);border-radius:var(--btn-radius);background:var(--hover-bg);cursor:pointer;transition:all .15s;text-align:center;}
.rpt-card:hover{border-color:var(--c-secondary);transform:translateY(-1px);box-shadow:0 4px 12px rgba(0,0,0,.06);}
.rpt-card.disabled{opacity:.4;} .rpt-icon{font-size:1.8em;margin-bottom:6px;}
.rpt-name{font-weight:700;font-size:calc(var(--fs-base)*.86);}
.rpt-desc{font-size:calc(var(--fs-base)*.64);color:var(--text-muted);margin-top:4px;line-height:1.3;}
.rpt-subs{font-size:calc(var(--fs-base)*.64);color:var(--c-secondary);margin-top:6px;font-weight:600;}
.cat-label{font-size:calc(var(--fs-base)*.71);font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted);margin:16px 0 6px;padding-bottom:4px;border-bottom:1px solid var(--border-light);}

/* ═══ Buttons ═══ */
.tr-btn{padding:5px 12px;border:1px solid var(--border-color);border-radius:var(--btn-radius);cursor:pointer;font-size:calc(var(--fs-base)*.79);font-weight:600;font-family:inherit;transition:all .12s;background:var(--hover-bg);color:var(--text-body);}
.tr-btn:hover{border-color:var(--c-secondary);color:var(--c-secondary);}
.tr-btn.primary{background:var(--c-secondary);color:white;border-color:var(--c-secondary);}
.tr-btn.primary:hover{opacity:.9;}
.tr-btn.success{background:var(--c-success);color:white;border-color:var(--c-success);}
.tr-btn.danger{background:var(--c-danger);color:white;border-color:var(--c-danger);}
.tr-btn.sm{padding:3px 8px;font-size:calc(var(--fs-base)*.71);}
.tr-btn:disabled{opacity:.5;cursor:not-allowed;}

/* ═══ Forms ═══ */
.tr-fg{display:flex;flex-direction:column;gap:3px;margin-bottom:12px;}
.tr-fg label{font-size:calc(var(--fs-base)*.71);color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;}
.tr-fg input,.tr-fg select,.tr-fg textarea{padding:7px 11px;border:1px solid var(--border-color);border-radius:var(--btn-radius);font-size:var(--fs-sm);background:var(--hover-bg);color:var(--text-body);font-family:inherit;}
.tr-fg input:focus,.tr-fg select:focus{outline:none;border-color:var(--c-secondary);}
.tr-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
.tr-row3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;}

/* ═══ Preview ═══ */
.tr-preview{background:var(--hover-bg);border:1px solid var(--border-color);border-radius:var(--btn-radius);padding:16px;font-family:monospace;font-size:calc(var(--fs-base)*.86);white-space:pre-wrap;max-height:400px;overflow-y:auto;line-height:1.5;}
.tr-preview-info{font-size:calc(var(--fs-base)*.71);color:var(--text-muted);margin-top:6px;}

/* ═══ Log table ═══ */
.tr-log{width:100%;border-collapse:collapse;font-size:calc(var(--fs-base)*.79);}
.tr-log th{background:var(--hover-bg);padding:6px 8px;font-weight:700;font-size:calc(var(--fs-base)*.64);text-transform:uppercase;border-bottom:2px solid var(--border-color);text-align:left;}
.tr-log td{padding:6px 8px;border-bottom:1px solid var(--border-light);vertical-align:top;}
.tr-log .st-sent{color:var(--c-success);font-weight:600;} .tr-log .st-failed{color:var(--c-danger);font-weight:600;}

/* ═══ Modal ═══ */
.tr-modal-bg{position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.5);z-index:9998;display:none;align-items:center;justify-content:center;}
.tr-modal-bg.show{display:flex;}
.tr-modal{background:var(--card-bg);border-radius:var(--card-radius);padding:24px;max-width:600px;width:90%;max-height:85vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.2);}
.tr-modal h3{margin:0 0 16px;font-size:calc(var(--fs-base)*1.14);font-weight:700;}
.tr-msg{padding:8px 12px;border-radius:var(--btn-radius);margin-bottom:12px;font-size:calc(var(--fs-base)*.86);display:none;}
.tr-msg.ok{display:block;background:var(--c-success-light);color:var(--c-success);} .tr-msg.err{display:block;background:var(--c-danger-light);color:var(--c-danger);} .tr-msg.info{display:block;background:var(--c-secondary-light);color:var(--c-secondary);}

/* ═══ Empty ═══ */
.tr-empty{text-align:center;padding:40px;color:var(--text-muted);font-size:calc(var(--fs-base)*.93);}

</style>
@endpush

@section('content')
<h2><span class="dot"></span> Telegram Reports</h2>

<!-- Tabs -->
<div class="tr-tabs">
    <div class="tr-tab active" onclick="switchTab('subs')"><i class="fa-solid fa-paper-plane"></i> Subscriptions</div>
    <div class="tr-tab" onclick="switchTab('catalog')"><i class="fa-solid fa-book"></i> Report Catalog</div>
    <div class="tr-tab" onclick="switchTab('log')"><i class="fa-solid fa-list"></i> Send Log</div>
</div>

<!-- ═══ Tab 1: Subscriptions ═══ -->
<div class="tr-panel active" id="panel-subs">
    <div class="tr-box">
        <h4><i class="fa-solid fa-paper-plane" style="color:var(--c-secondary);"></i> Active Subscriptions <span class="tag" id="subCount">loading...</span>
            <span style="margin-left:auto;display:flex;gap:6px;flex-wrap:wrap;">
                <button class="tr-btn sm" onclick="bulkAction('pause_all')" title="Pause all"><i class="fa-solid fa-pause"></i> Pause All</button>
                <button class="tr-btn sm" onclick="bulkAction('enable_all')" title="Enable all"><i class="fa-solid fa-play"></i> Enable All</button>
                <button class="tr-btn primary" onclick="openSubModal()"><i class="fa-solid fa-plus"></i> New Subscription</button>
            </span>
        </h4>
        <div class="sub-list" id="subList">
            <div class="tr-empty"><i class="fa-solid fa-spinner fa-spin"></i> Loading...</div>
        </div>
    </div>
</div>

<!-- ═══ Tab 2: Report Catalog ═══ -->
<div class="tr-panel" id="panel-catalog">
    <div class="tr-box">
        <h4><i class="fa-solid fa-book" style="color:var(--c-warning);"></i> Available Reports <span class="tag" id="rptCount">loading...</span>
            <span style="margin-left:auto;"><button class="tr-btn primary" onclick="openCustomReportModal()"><i class="fa-solid fa-plus"></i> Create Custom Report</button></span>
        </h4>
        <div id="rptCatalog"><div class="tr-empty"><i class="fa-solid fa-spinner fa-spin"></i> Loading...</div></div>
    </div>
    <div class="tr-box" id="unregBox" style="display:none;">
        <h4><i class="fa-solid fa-puzzle-piece" style="color:var(--c-info);"></i> Unregistered Reports <span class="tag">code exists but not registered</span></h4>
        <div id="unregList"></div>
    </div>
</div>

<!-- ═══ Tab 3: Log ═══ -->
<div class="tr-panel" id="panel-log">
    <div class="tr-box">
        <h4><i class="fa-solid fa-list" style="color:var(--text-muted);"></i> Send History <span class="tag">last 100 entries</span>
            <span style="margin-left:auto;"><button class="tr-btn" onclick="loadLog()"><i class="fa-solid fa-sync"></i> Refresh</button></span>
        </h4>
        <div style="display:flex;gap:10px;margin-bottom:12px;flex-wrap:wrap;align-items:end;">
            <div class="tr-fg" style="margin-bottom:0;min-width:120px;"><label>Status</label>
                <select id="logStatus" onchange="loadLog()" style="padding:5px 8px;font-size:calc(var(--fs-base)*.79);">
                    <option value="">All</option><option value="sent">Sent</option><option value="failed">Failed</option>
                </select>
            </div>
            <div class="tr-fg" style="margin-bottom:0;min-width:120px;"><label>Report</label>
                <select id="logReport" onchange="loadLog()" style="padding:5px 8px;font-size:calc(var(--fs-base)*.79);">
                    <option value="">All</option>
                </select>
            </div>
            <div class="tr-fg" style="margin-bottom:0;min-width:120px;"><label>Target</label>
                <input type="text" id="logTarget" placeholder="Search..." onchange="loadLog()" style="padding:5px 8px;font-size:calc(var(--fs-base)*.79);width:120px;">
            </div>
        </div>
        <div style="overflow-x:auto;">
            <table class="tr-log">
                <thead><tr><th>Time</th><th>Type</th><th>Report</th><th>Target</th><th>Status</th><th>Duration</th><th>Preview</th></tr></thead>
                <tbody id="logBody"><tr><td colspan="7" class="tr-empty">Click "Refresh" to load</td></tr></tbody>
            </table>
        </div>
    </div>
</div>

<!-- ═══ Subscription Modal ═══ -->
<div class="tr-modal-bg" id="subModal">
    <div class="tr-modal">
        <h3 id="subModalTitle"><i class="fa-solid fa-plus"></i> New Subscription</h3>
        <div class="tr-msg" id="subMsg"></div>
        <input type="hidden" id="subEditId" value="">
        <div class="tr-row">
            <div class="tr-fg"><label>Report</label><select id="subReport" onchange="onReportChange()"><option value="">Select report...</option></select></div>
            <div class="tr-fg"><label>Target</label><select id="subTarget"><option value="">Select target...</option></select></div>
        </div>
        <div class="tr-fg"><label>Name</label><input type="text" id="subName" placeholder="e.g. Morning Brief → Marketing"></div>
        <div class="tr-row3">
            <div class="tr-fg"><label>Schedule</label>
                <select id="subSchedule" onchange="onScheduleChange()">
                    <option value="manual">Manual only</option>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                    <option value="hourly">Hourly</option>
                </select>
            </div>
            <div class="tr-fg" id="fgTime" style="display:none;"><label>Time</label><input type="time" id="subTime" value="09:00"></div>
            <div class="tr-fg" id="fgDay" style="display:none;"><label>Day</label>
                <select id="subDay">
                    <option value="1">Monday</option><option value="2">Tuesday</option><option value="3">Wednesday</option>
                    <option value="4">Thursday</option><option value="5">Friday</option><option value="6">Saturday</option><option value="7">Sunday</option>
                </select>
            </div>
        </div>
        <div class="tr-fg" id="fgParams"><label>Date Range</label>
            <select id="subDateRange">
                <option value="today">Today</option>
                <option value="yesterday" selected>Yesterday</option>
                <option value="last_7d">Last 7 Days</option>
                <option value="last_30d">Last 30 Days</option>
                <option value="this_month">This Month</option>
            </select>
        </div>

        <!-- Preview -->
        <div id="previewBox" style="display:none;margin-bottom:16px;">
            <div class="tr-preview" id="previewText"></div>
            <div class="tr-preview-info" id="previewInfo"></div>
        </div>

        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <button class="tr-btn" onclick="previewReport()"><i class="fa-solid fa-eye"></i> Preview</button>
            <button class="tr-btn primary" onclick="saveSub()"><i class="fa-solid fa-check"></i> Save</button>
            <button class="tr-btn" onclick="closeSubModal()">Cancel</button>
        </div>
    </div>
</div>

<!-- ═══ Preview Modal (from catalog) ═══ -->
<div class="tr-modal-bg" id="previewModal">
    <div class="tr-modal">
        <h3 id="prevModalTitle">📊 Preview</h3>
        <div class="tr-fg"><label>Date Range</label>
            <select id="prevDateRange" style="max-width:200px;">
                <option value="today">Today</option><option value="yesterday" selected>Yesterday</option>
                <option value="last_7d">Last 7 Days</option><option value="last_30d">Last 30 Days</option>
            </select>
        </div>
        <div style="display:flex;gap:8px;margin-bottom:12px;">
            <button class="tr-btn primary" onclick="runPreview()"><i class="fa-solid fa-eye"></i> Generate Preview</button>
            <button class="tr-btn success" onclick="sendFromPreview()" id="btnSendPreview" style="display:none;"><i class="fa-solid fa-paper-plane"></i> Send</button>
        </div>
        <div id="prevTargetWrap" style="display:none;margin-bottom:12px;">
            <div class="tr-fg"><label>Send To</label><select id="prevTarget"></select></div>
        </div>
        <div class="tr-preview" id="prevText" style="min-height:100px;">Click "Generate Preview" to see the report.</div>
        <div class="tr-preview-info" id="prevInfo"></div>
        <div class="tr-msg" id="prevMsg" style="margin-top:10px;"></div>
        <div style="margin-top:16px;"><button class="tr-btn" onclick="closePreviewModal()">Close</button></div>
    </div>
</div>

<!-- ═══ Custom Report Modal ═══ -->
<div class="tr-modal-bg" id="customRptModal">
    <div class="tr-modal" style="max-width:800px;">
        <h3 id="customRptTitle"><i class="fa-solid fa-code"></i> Create Custom Report</h3>
        <div class="tr-msg" id="customRptMsg"></div>
        <input type="hidden" id="customRptEditId" value="">

        <div class="tr-row">
            <div class="tr-fg"><label>Report Name</label><input type="text" id="crName" placeholder="e.g. Top Depositors"></div>
            <div class="tr-fg"><label>Slug (unique key)</label><input type="text" id="crSlug" placeholder="e.g. top_depositors" style="font-family:monospace;"></div>
        </div>
        <div class="tr-row3">
            <div class="tr-fg"><label>Icon</label><input type="text" id="crIcon" value="📊" style="width:60px;text-align:center;font-size:1.4em;"></div>
            <div class="tr-fg"><label>Category</label>
                <select id="crCategory"><option value="marketing">Marketing</option><option value="operations">Operations</option><option value="traffic">Traffic</option><option value="alerts">Alerts</option><option value="custom" selected>Custom</option></select>
            </div>
            <div class="tr-fg"><label>Date Range</label>
                <select id="crDateRange"><option value="today">Today</option><option value="yesterday" selected>Yesterday</option><option value="last_7d">Last 7D</option><option value="last_30d">Last 30D</option></select>
            </div>
        </div>
        <div class="tr-fg"><label>Description</label><input type="text" id="crDesc" placeholder="What this report shows..."></div>

        <div class="tr-fg">
            <label>SQL Query <span style="font-weight:400;text-transform:none;">(SELECT only — use @{{date_from}}, @{{date_to}} for date range)</span></label>
            <textarea id="crQuery" rows="8" style="font-family:monospace;font-size:calc(var(--fs-base)*.86);line-height:1.4;resize:vertical;" placeholder="SELECT
  COUNT(*) as total_records,
  MAX(created_at) as latest_entry
FROM tbl_your_table
WHERE created_at BETWEEN @{{date_from}} AND @{{date_to}}"></textarea>
        </div>

        <div style="display:flex;gap:8px;margin-bottom:12px;">
            <button class="tr-btn" onclick="testCustomQuery()"><i class="fa-solid fa-play"></i> Test Query</button>
            <span id="crQueryResult" style="font-size:calc(var(--fs-base)*.79);color:var(--text-muted);align-self:center;"></span>
        </div>
        <div id="crQueryPreview" style="display:none;margin-bottom:12px;max-height:200px;overflow:auto;border:1px solid var(--border-color);border-radius:var(--btn-radius);font-size:calc(var(--fs-base)*.71);"></div>

        <div class="tr-fg">
            <label>Message Template <span style="font-weight:400;text-transform:none;">(Telegram Markdown — use @{{column_name}} from query results)</span></label>
            <textarea id="crTemplate" rows="10" style="font-family:monospace;font-size:calc(var(--fs-base)*.86);line-height:1.4;resize:vertical;" placeholder="📊 *My Custom Report*
📅 @{{date_label}}

*Total Registrations:* @{{total_regs_fmt}}
*Total FTD:* @{{total_ftd}}
*FTD Rate:* @{{ftd_rate}}%

_{{ \App\Models\Configuration::get('portal_name', 'Admin Portal') }} • custom report_"></textarea>
        </div>

        <div class="tr-fg">
            <label>Computed Fields <span style="font-weight:400;text-transform:none;">(optional — JSON: {"field_name": "formula"})</span></label>
            <textarea id="crComputed" rows="3" style="font-family:monospace;font-size:calc(var(--fs-base)*.86);resize:vertical;" placeholder='{"ftd_rate": "total_ftd / total_regs * 100", "cost_per_ftd": "spend / total_ftd"}'></textarea>
        </div>

        <div style="display:flex;gap:8px;margin-bottom:12px;">
            <button class="tr-btn" onclick="previewCustomReport()"><i class="fa-solid fa-eye"></i> Preview Output</button>
        </div>
        <div id="crPreviewBox" style="display:none;margin-bottom:12px;">
            <div class="tr-preview" id="crPreviewText"></div>
            <div class="tr-preview-info" id="crPreviewInfo"></div>
        </div>

        <div style="display:flex;gap:8px;">
            <button class="tr-btn primary" onclick="saveCustomReport()"><i class="fa-solid fa-check"></i> Save Report</button>
            <button class="tr-btn" onclick="closeCustomRptModal()">Cancel</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')

<script>

const baseUrl = '';
const csrf = document.querySelector('meta[name="csrf-token"]').content;
let allReports = [], allTargets = [], allSubs = [];
let previewSlug = '';

// ═══ HELPERS ═══
function xss(s){const d=document.createElement('div');d.textContent=s;return d.innerHTML;}
function fmt(n){return n!==null&&n!==undefined?Number(n).toLocaleString():'—';}

function showMsg(id,msg,type){const el=document.getElementById(id);el.className='tr-msg '+type;el.textContent=msg;}
function hideMsg(id){document.getElementById(id).className='tr-msg';}

function switchTab(tab){
    document.querySelectorAll('.tr-tab').forEach(t=>t.classList.remove('active'));
    document.querySelectorAll('.tr-panel').forEach(p=>p.classList.remove('active'));
    document.querySelector(`.tr-tab[onclick="switchTab('${tab}')"]`).classList.add('active');
    document.getElementById('panel-'+tab).classList.add('active');
    if(tab==='log')loadLog();
}

function targetIcon(type){return type==='personal'?'📱':type==='channel'?'📢':'👥';}

// ═══ LOAD DATA ═══
function loadAll(){loadReports();loadSubscriptions();}

function loadReports(){
    fetch('/telegram/reports/list',{headers:{Accept:'application/json'}})
    .then(r=>{if(!r.ok)throw new Error('HTTP '+r.status);return r.json();})
    .then(d=>{
        allReports=d.reports||[];
        renderCatalog(d.reports,d.unregistered,d.orphaned);
        populateReportSelect();
        populateLogReportFilter();
        document.getElementById('rptCount').textContent=allReports.length+' reports';
    }).catch(e=>console.error('loadReports',e));
}

function loadSubscriptions(){
    fetch('/telegram/reports',{headers:{Accept:'application/json'}})
    .then(r=>{if(!r.ok)throw new Error('HTTP '+r.status);return r.json();})
    .then(d=>{
        allSubs=d.subscriptions||[];
        allTargets=d.targets||[];
        renderSubs(allSubs);
        populateTargetSelect();
        document.getElementById('subCount').textContent=allSubs.length+' active';
    }).catch(e=>{console.error('loadSubs',e);document.getElementById('subList').innerHTML='<div class="tr-empty" style="color:var(--c-danger);">Error loading: '+xss(e.message)+'</div>';});
}

// ═══ RENDER SUBSCRIPTIONS ═══
function renderSubs(subs){
    const el=document.getElementById('subList');
    if(!subs.length){el.innerHTML='<div class="tr-empty"><i class="fa-solid fa-inbox" style="font-size:2em;opacity:.3;display:block;margin-bottom:10px;"></i>No subscriptions yet. Create one to start sending reports automatically.</div>';return;}
    el.innerHTML=subs.map(s=>{
        const last=s.last_sent_at?new Date(s.last_sent_at).toLocaleString():'Never';
        const stCls=s.last_status==='sent'?'ok':s.last_status==='failed'?'fail':'never';
        const stIcon=s.last_status==='sent'?'✓':s.last_status==='failed'?'✗':'—';
        const schedLabel=s.schedule_type==='daily'?s.schedule_time:s.schedule_type==='weekly'?['','Mon','Tue','Wed','Thu','Fri','Sat','Sun'][s.schedule_day||0]+' '+s.schedule_time:s.schedule_type;
        const failWarn=s.consecutive_fails>=3?'<span style="color:var(--c-danger);font-weight:700;margin-left:4px;">⚠ '+s.consecutive_fails+'/5 fails</span>':'';
        return `<div class="sub-card ${s.enabled?'':'disabled'}">
            <div class="sub-icon">${xss(s.report_icon||'📊')}</div>
            <div class="sub-info">
                <div class="sub-name">${xss(s.name)}${failWarn}</div>
                <div class="sub-meta">
                    <span>${xss(s.report_name)} → ${targetIcon(s.target_type)} ${xss(s.target_name)}</span>
                    <span class="sub-badge ${s.schedule_type}">${schedLabel}</span>
                    <span class="sub-status ${stCls}">Last: ${stIcon} ${last}</span>
                    <span>Sent: ${s.send_count||0} | Fail: ${s.fail_count||0}</span>
                </div>
            </div>
            <div class="sub-actions">
                <button class="tr-btn sm" onclick="sendSubNow(${s.id})" title="Send now"><i class="fa-solid fa-paper-plane"></i></button>
                <button class="tr-btn sm" onclick="cloneSub(${s.id})" title="Clone"><i class="fa-solid fa-copy"></i></button>
                <button class="tr-btn sm" onclick="editSub(${s.id})" title="Edit"><i class="fa-solid fa-pen"></i></button>
                <button class="tr-btn sm" onclick="toggleSub(${s.id},${s.enabled?0:1})" title="${s.enabled?'Pause':'Enable'}">${s.enabled?'<i class="fa-solid fa-pause"></i>':'<i class="fa-solid fa-play"></i>'}</button>
                <button class="tr-btn sm danger" onclick="deleteSub(${s.id},'${xss(s.name).replace(/'/g,"\\'")}')" title="Delete"><i class="fa-solid fa-trash"></i></button>
            </div>
        </div>`;
    }).join('');
}

// ═══ RENDER CATALOG ═══
function renderCatalog(reports,unreg,orphaned){
    const el=document.getElementById('rptCatalog');
    const cats={};
    reports.forEach(r=>{if(!cats[r.category])cats[r.category]=[];cats[r.category].push(r);});
    let h='';
    for(const [cat,rpts] of Object.entries(cats)){
        h+=`<div class="cat-label">${xss(cat)}</div><div class="rpt-grid">`;
        rpts.forEach(r=>{
            const subCnt=allSubs.filter(s=>s.report_slug===r.slug).length;
            h+=`<div class="rpt-card ${r.enabled?'':'disabled'}" style="position:relative;">
                <div onclick="openPreviewModal('${r.slug}','${xss(r.name).replace(/'/g,"\\'")}')">
                    <div class="rpt-icon">${r.icon||'📊'}</div>
                    <div class="rpt-name">${xss(r.name)}</div>
                    <div class="rpt-desc">${xss(r.description||'')}</div>
                    <div class="rpt-subs">${subCnt} subscription${subCnt!==1?'s':''}</div>
                </div>
                <div style="margin-top:8px;display:flex;gap:4px;justify-content:center;">
                    <button class="tr-btn sm ${r.enabled?'':'success'}" onclick="toggleReport(${r.id})" title="${r.enabled?'Disable':'Enable'}">${r.enabled?'<i class="fa-solid fa-eye-slash"></i>':'<i class="fa-solid fa-eye"></i>'}</button>
                    <button class="tr-btn sm" onclick="openPreviewModal('${r.slug}','${xss(r.name).replace(/'/g,"\\'")}')" title="Preview"><i class="fa-solid fa-eye"></i></button>
                    ${r.report_type==='template'?'<button class="tr-btn sm" onclick="openCustomReportModal('+r.id+')" title="Edit query/template"><i class="fa-solid fa-code"></i></button>':''}
                </div>
            </div>`;
        });
        h+='</div>';
    }
    el.innerHTML=h||'<div class="tr-empty">No reports registered.</div>';

    // Unregistered
    const uBox=document.getElementById('unregBox');
    if(unreg&&unreg.length){
        uBox.style.display='';
        document.getElementById('unregList').innerHTML=unreg.map(u=>`<div style="display:flex;align-items:center;gap:10px;padding:8px;border-bottom:1px solid var(--border-light);">
            <code style="font-size:calc(var(--fs-base)*.79);color:var(--c-info);">${xss(u.slug)}</code>
            <span style="color:var(--text-muted);font-size:calc(var(--fs-base)*.79);">→ ${xss(u.method)}</span>
            <button class="tr-btn sm success" style="margin-left:auto;" onclick="registerReport('${xss(u.slug)}')">Register</button>
        </div>`).join('');
    } else uBox.style.display='none';
}

// ═══ SUBSCRIPTION MODAL ═══
function populateReportSelect(){
    const sel=document.getElementById('subReport');
    sel.innerHTML='<option value="">Select report...</option>'+allReports.filter(r=>r.enabled).map(r=>`<option value="${r.id}" data-slug="${r.slug}">${r.icon} ${xss(r.name)}</option>`).join('');
}
function populateTargetSelect(){
    const html=allTargets.map(t=>`<option value="${t.id}">${targetIcon(t.type)} ${xss(t.name)} (${t.chat_id})</option>`).join('');
    document.getElementById('subTarget').innerHTML='<option value="">Select target...</option>'+html;
    document.getElementById('prevTarget').innerHTML=html;
}

function openSubModal(editId){
    document.getElementById('subEditId').value=editId||'';
    document.getElementById('subModalTitle').innerHTML=editId?'<i class="fa-solid fa-pen"></i> Edit Subscription':'<i class="fa-solid fa-plus"></i> New Subscription';
    hideMsg('subMsg');
    document.getElementById('previewBox').style.display='none';

    if(editId){
        const s=allSubs.find(x=>x.id===editId);
        if(s){
            document.getElementById('subReport').value=s.report_id;
            document.getElementById('subTarget').value=s.target_id;
            document.getElementById('subName').value=s.name;
            document.getElementById('subSchedule').value=s.schedule_type;
            document.getElementById('subTime').value=s.schedule_time||'09:00';
            document.getElementById('subDay').value=s.schedule_day||1;
            const p=typeof s.params==='string'?JSON.parse(s.params||'{}'):s.params||{};
            document.getElementById('subDateRange').value=p.date_range||'yesterday';
        }
    } else {
        ['subName'].forEach(id=>document.getElementById(id).value='');
        document.getElementById('subSchedule').value='manual';
        document.getElementById('subDateRange').value='yesterday';
    }
    onScheduleChange();
    document.getElementById('subModal').classList.add('show');
}
function closeSubModal(){document.getElementById('subModal').classList.remove('show');}
function editSub(id){openSubModal(id);}

function onScheduleChange(){
    const v=document.getElementById('subSchedule').value;
    document.getElementById('fgTime').style.display=['daily','weekly','monthly'].includes(v)?'':'none';
    document.getElementById('fgDay').style.display=['weekly','monthly'].includes(v)?'':'none';
}

function onReportChange(){
    const opt=document.getElementById('subReport').selectedOptions[0];
    if(opt&&opt.value){
        const rpt=allReports.find(r=>r.id==opt.value);
        const tgt=document.getElementById('subTarget').selectedOptions[0];
        if(rpt&&!document.getElementById('subName').value){
            document.getElementById('subName').value=rpt.name+(tgt&&tgt.value?' → '+tgt.textContent.replace(/^[📱👥📢]\s*/,''):'');
        }
    }
}

function saveSub(){
    const editId=document.getElementById('subEditId').value;
    const reportId=document.getElementById('subReport').value;
    const targetId=document.getElementById('subTarget').value;
    const name=document.getElementById('subName').value.trim();
    if(!reportId||!targetId||!name){showMsg('subMsg','Report, Target, and Name are required.','err');return;}

    const body={
        name, report_id:reportId, target_id:targetId,
        schedule_type:document.getElementById('subSchedule').value,
        schedule_time:document.getElementById('subTime').value,
        schedule_day:document.getElementById('subDay').value,
        params:JSON.stringify({date_range:document.getElementById('subDateRange').value}),
        enabled:1,
    };

    const url=editId?'/telegram/reports/'+editId:'/telegram/reports';
    const method=editId?'PUT':'POST';

    fetch(url,{method,headers:{'X-CSRF-TOKEN':csrf,Accept:'application/json','Content-Type':'application/json'},body:JSON.stringify(body)})
    .then(r=>{if(!r.ok)return r.text().then(t=>{throw new Error('HTTP '+r.status+': '+t.substring(0,200));});return r.json();})
    .then(d=>{
        if(d.success){showMsg('subMsg','✓ '+xss(d.message),'ok');setTimeout(()=>{closeSubModal();loadSubscriptions();},800);}
        else showMsg('subMsg','✗ '+(d.error||'Failed'),'err');
    }).catch(e=>showMsg('subMsg','✗ '+xss(e.message),'err'));
}

function toggleSub(id,enable){
    fetch('/telegram/reports/'+id+'/toggle',{method:'POST',headers:{'X-CSRF-TOKEN':csrf,Accept:'application/json'}})
    .then(r=>r.json()).then(d=>{if(d.success)loadSubscriptions();}).catch(e=>console.error(e));
}

function deleteSub(id,name){
    confirmAction('Delete "'+name+'"?','This subscription will be permanently removed.','danger',()=>{
        fetch('/telegram/reports/'+id,{method:'DELETE',headers:{'X-CSRF-TOKEN':csrf,Accept:'application/json'}})
        .then(r=>r.json()).then(d=>{if(d.success){toast('Deleted','ok');loadSubscriptions();}else toast(d.error||'Failed','err');})
        .catch(()=>toast('Error','err'));
    });
}

function sendSubNow(id){
    const btn=event.target.closest('button');
    btn.disabled=true;btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i>';
    fetch('/telegram/reports/'+id+'/send',{method:'POST',headers:{'X-CSRF-TOKEN':csrf,Accept:'application/json'}})
    .then(r=>r.json()).then(d=>{
        btn.disabled=false;btn.innerHTML='<i class="fa-solid fa-paper-plane"></i>';
        if(d.success)toast(d.message||'Sent!','ok');
        else toast(d.error||'Failed','err');
        loadSubscriptions();
    }).catch(e=>{btn.disabled=false;btn.innerHTML='<i class="fa-solid fa-paper-plane"></i>';toast(e.message,'err');});
}

function cloneSub(id){
    fetch('/telegram/reports/'+id+'/clone',{method:'POST',headers:{'X-CSRF-TOKEN':csrf,Accept:'application/json'}})
    .then(r=>r.json()).then(d=>{
        if(d.success){toast(d.message||'Cloned!','ok');loadSubscriptions();}
        else toast(d.error||'Failed','err');
    }).catch(e=>toast(e.message,'err'));
}

function bulkAction(action){
    const label=action==='pause_all'?'Pause all subscriptions?':'Enable all subscriptions?';
    confirmAction(label,'','warning',()=>{
        fetch('/telegram/reports/bulk',{method:'POST',headers:{'X-CSRF-TOKEN':csrf,Accept:'application/json','Content-Type':'application/json'},
            body:JSON.stringify({action})
        }).then(r=>r.json()).then(d=>{
            if(d.success){toast(d.message,'ok');loadSubscriptions();}
            else toast(d.error||'Failed','err');
        }).catch(e=>toast(e.message,'err'));
    });
}

// ═══ PREVIEW (from subscription modal) ═══
function previewReport(){
    const opt=document.getElementById('subReport').selectedOptions[0];
    if(!opt||!opt.value){showMsg('subMsg','Select a report first.','err');return;}
    const slug=opt.dataset.slug;
    const dr=document.getElementById('subDateRange').value;
    document.getElementById('previewBox').style.display='';
    document.getElementById('previewText').textContent='Loading...';
    document.getElementById('previewInfo').textContent='';

    fetch('/telegram/reports/'+slug+'/preview?date_range='+dr,{headers:{Accept:'application/json'}})
    .then(r=>r.json()).then(d=>{
        document.getElementById('previewText').textContent=d.text||'No content';
        document.getElementById('previewInfo').textContent=d.chars+' chars | '+d.duration_ms+'ms';
    }).catch(e=>{document.getElementById('previewText').textContent='Error: '+e.message;});
}

// ═══ PREVIEW MODAL (from catalog) ═══
function openPreviewModal(slug,name){
    previewSlug=slug;
    document.getElementById('prevModalTitle').textContent='📊 Preview: '+name;
    document.getElementById('prevText').textContent='Click "Generate Preview" to see the report.';
    document.getElementById('prevInfo').textContent='';
    hideMsg('prevMsg');
    document.getElementById('btnSendPreview').style.display='none';
    document.getElementById('prevTargetWrap').style.display='none';
    document.getElementById('previewModal').classList.add('show');
}
function closePreviewModal(){document.getElementById('previewModal').classList.remove('show');}

function runPreview(){
    const dr=document.getElementById('prevDateRange').value;
    document.getElementById('prevText').textContent='⏳ Generating...';
    fetch('/telegram/reports/'+previewSlug+'/preview?date_range='+dr,{headers:{Accept:'application/json'}})
    .then(r=>r.json()).then(d=>{
        document.getElementById('prevText').textContent=d.text||'No content';
        document.getElementById('prevInfo').textContent=d.chars+' chars | '+d.duration_ms+'ms'+(d.chars>4096?' ⚠️ Will be split into multiple messages':'');
        document.getElementById('btnSendPreview').style.display='';
        document.getElementById('prevTargetWrap').style.display='';
    }).catch(e=>{document.getElementById('prevText').textContent='Error: '+e.message;});
}

function sendFromPreview(){
    const targetId=document.getElementById('prevTarget').value;
    if(!targetId){showMsg('prevMsg','Select a target.','err');return;}
    const dr=document.getElementById('prevDateRange').value;
    const btn=document.getElementById('btnSendPreview');
    btn.disabled=true;btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Sending...';
    fetch('/telegram/reports/'+previewSlug+'/send',{method:'POST',headers:{'X-CSRF-TOKEN':csrf,Accept:'application/json','Content-Type':'application/json'},
        body:JSON.stringify({target_id:targetId,params:{date_range:dr}})
    }).then(r=>r.json()).then(d=>{
        btn.disabled=false;btn.innerHTML='<i class="fa-solid fa-paper-plane"></i> Send';
        showMsg('prevMsg',d.success?'✓ '+(d.message||'Sent!'):'✗ '+(d.error||'Failed'),d.success?'ok':'err');
    }).catch(e=>{btn.disabled=false;btn.innerHTML='<i class="fa-solid fa-paper-plane"></i> Send';showMsg('prevMsg','✗ '+e.message,'err');});
}

// ═══ REPORT MANAGEMENT ═══
function toggleReport(id){
    fetch('/telegram/reports/'+id+'/toggle',{method:'POST',headers:{'X-CSRF-TOKEN':csrf,Accept:'application/json'}})
    .then(r=>r.json()).then(d=>{
        if(d.success){toast(d.message||'Toggled','ok');loadReports();loadSubscriptions();}
        else toast(d.error||'Failed','err');
    }).catch(e=>toast(e.message,'err'));
}

// ═══ REGISTER REPORT ═══
function registerReport(slug){
    const name=prompt('Report name for "'+slug+'"?',slug.replace(/_/g,' ').replace(/\b\w/g,l=>l.toUpperCase()));
    if(!name)return;
    const cat=prompt('Category? (marketing, operations, traffic, alerts)','general');
    fetch('/telegram/reports/register',{method:'POST',headers:{'X-CSRF-TOKEN':csrf,Accept:'application/json','Content-Type':'application/json'},
        body:JSON.stringify({slug,name,category:cat||'general',icon:'📊',description:''})
    }).then(r=>r.json()).then(d=>{
        if(d.success){toast('Registered: '+name,'ok');loadReports();}else toast(d.error||'Failed','err');
    }).catch(e=>toast(e.message,'err'));
}

// ═══ LOG ═══
function loadLog(){
    const params=new URLSearchParams();
    const status=document.getElementById('logStatus').value;
    const report=document.getElementById('logReport').value;
    const target=document.getElementById('logTarget').value;
    if(status)params.set('status',status);
    if(report)params.set('report_slug',report);
    if(target)params.set('target',target);

    document.getElementById('logBody').innerHTML='<tr><td colspan="7" class="tr-empty"><i class="fa-solid fa-spinner fa-spin"></i> Loading...</td></tr>';
    fetch('/telegram/log?'+params,{headers:{Accept:'application/json'}})
    .then(r=>r.json()).then(d=>{
        const logs=d.logs||[];
        if(!logs.length){document.getElementById('logBody').innerHTML='<tr><td colspan="7" class="tr-empty">No log entries.</td></tr>';return;}
        document.getElementById('logBody').innerHTML=logs.map(l=>`<tr>
            <td style="white-space:nowrap;font-size:calc(var(--fs-base)*.71);color:var(--text-muted);">${l.sent_at||'—'}</td>
            <td>${xss(l.type||'—')}</td>
            <td><code>${xss(l.report_slug||'—')}</code></td>
            <td>${xss(l.target||'—')}</td>
            <td class="st-${l.status}">${l.status==='sent'?'✓ Sent':'✗ '+(l.error?xss(l.error).substring(0,60):'Failed')}</td>
            <td>${l.duration_ms?l.duration_ms+'ms':'—'}</td>
            <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:calc(var(--fs-base)*.71);color:var(--text-muted);" title="${xss(l.message||'')}">${xss((l.message||'').substring(0,80))}</td>
        </tr>`).join('');
    }).catch(e=>{document.getElementById('logBody').innerHTML='<tr><td colspan="7" class="tr-empty" style="color:var(--c-danger);">Error: '+xss(e.message)+'</td></tr>';});
}

function populateLogReportFilter(){
    const sel=document.getElementById('logReport');
    sel.innerHTML='<option value="">All</option>'+allReports.map(r=>`<option value="${r.slug}">${r.icon} ${xss(r.name)}</option>`).join('');
}

// ═══ CUSTOM REPORT BUILDER ═══
function openCustomReportModal(editId){
    document.getElementById('customRptEditId').value=editId||'';
    document.getElementById('customRptTitle').innerHTML=editId?'<i class="fa-solid fa-pen"></i> Edit Custom Report':'<i class="fa-solid fa-code"></i> Create Custom Report';
    hideMsg('customRptMsg');
    document.getElementById('crQueryPreview').style.display='none';
    document.getElementById('crPreviewBox').style.display='none';
    document.getElementById('crQueryResult').textContent='';

    if(editId){
        const r=allReports.find(x=>x.id===editId);
        if(r){
            document.getElementById('crName').value=r.name;
            document.getElementById('crSlug').value=r.slug;
            document.getElementById('crSlug').disabled=true;
            document.getElementById('crIcon').value=r.icon||'📊';
            document.getElementById('crCategory').value=r.category||'custom';
            document.getElementById('crDesc').value=r.description||'';
            document.getElementById('crQuery').value=r.query||'';
            document.getElementById('crTemplate').value=r.template||'';
            document.getElementById('crComputed').value=r.computed_fields?JSON.stringify(r.computed_fields,null,2):'';
        }
    } else {
        ['crName','crSlug','crDesc','crQuery','crTemplate','crComputed'].forEach(id=>document.getElementById(id).value='');
        document.getElementById('crSlug').disabled=false;
        document.getElementById('crIcon').value='📊';
        document.getElementById('crCategory').value='custom';
    }
    document.getElementById('customRptModal').classList.add('show');
}
function closeCustomRptModal(){document.getElementById('customRptModal').classList.remove('show');}

function testCustomQuery(){
    const query=document.getElementById('crQuery').value.trim();
    if(!query){showMsg('customRptMsg','Enter a SQL query first.','err');return;}
    const dr=document.getElementById('crDateRange').value;
    document.getElementById('crQueryResult').innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Running...';

    fetch('/telegram/reports/test-query',{method:'POST',headers:{'X-CSRF-TOKEN':csrf,Accept:'application/json','Content-Type':'application/json'},
        body:JSON.stringify({query,date_range:dr})
    }).then(r=>r.json()).then(d=>{
        if(d.success){
            document.getElementById('crQueryResult').innerHTML='<span style="color:var(--c-success);">✓ '+d.count+' row(s) in '+d.duration_ms+'ms</span>';
            // Show result table
            if(d.rows&&d.rows.length){
                let h='<table style="width:100%;border-collapse:collapse;"><thead><tr>'+d.columns.map(c=>'<th style="padding:3px 6px;background:var(--hover-bg);border:1px solid var(--border-color);font-size:calc(var(--fs-base)*.64);">'+xss(c)+'</th>').join('')+'</tr></thead><tbody>';
                d.rows.slice(0,10).forEach(r=>{
                    h+='<tr>'+d.columns.map(c=>'<td style="padding:3px 6px;border:1px solid var(--border-light);font-family:monospace;font-size:calc(var(--fs-base)*.71);">'+xss(String(r[c]??'NULL'))+'</td>').join('')+'</tr>';
                });
                h+='</tbody></table>';
                if(d.truncated)h+='<div style="color:var(--text-muted);font-size:calc(var(--fs-base)*.64);padding:4px;">Showing first 10 of '+d.count+' rows</div>';
                h+='<div style="color:var(--c-info);font-size:calc(var(--fs-base)*.64);padding:4px;">Available placeholders: '+d.columns.map(c=>'{{'+c+'}}').join(', ')+', {{col_fmt}}, {{col_usd}}</div>';
                document.getElementById('crQueryPreview').innerHTML=h;
                document.getElementById('crQueryPreview').style.display='';
            }
        } else {
            document.getElementById('crQueryResult').innerHTML='<span style="color:var(--c-danger);">✗ '+xss(d.error||'Failed')+'</span>';
            document.getElementById('crQueryPreview').style.display='none';
        }
    }).catch(e=>{document.getElementById('crQueryResult').innerHTML='<span style="color:var(--c-danger);">✗ '+xss(e.message)+'</span>';});
}

function previewCustomReport(){
    const editId=document.getElementById('customRptEditId').value;
    const slug=editId?document.getElementById('crSlug').value:'_preview_';
    const query=document.getElementById('crQuery').value.trim();
    const template=document.getElementById('crTemplate').value.trim();
    if(!query||!template){showMsg('customRptMsg','Query and template are required.','err');return;}

    // For existing reports, use the preview endpoint
    if(editId){
        const dr=document.getElementById('crDateRange').value;
        document.getElementById('crPreviewBox').style.display='';
        document.getElementById('crPreviewText').textContent='Loading...';
        fetch('/telegram/reports/'+slug+'/preview?date_range='+dr,{headers:{Accept:'application/json'}})
        .then(r=>r.json()).then(d=>{
            document.getElementById('crPreviewText').textContent=d.text||'No content';
            document.getElementById('crPreviewInfo').textContent=(d.chars||0)+' chars | '+(d.duration_ms||0)+'ms';
        }).catch(e=>{document.getElementById('crPreviewText').textContent='Error: '+e.message;});
        return;
    }

    // For new reports, save temporarily then preview
    showMsg('customRptMsg','Save the report first, then use Preview from the catalog.','info');
}

function saveCustomReport(){
    const editId=document.getElementById('customRptEditId').value;
    const name=document.getElementById('crName').value.trim();
    const slug=document.getElementById('crSlug').value.trim();
    const query=document.getElementById('crQuery').value.trim();
    const template=document.getElementById('crTemplate').value.trim();

    if(!name||!slug){showMsg('customRptMsg','Name and Slug are required.','err');return;}
    if(!query||!template){showMsg('customRptMsg','Query and Template are required.','err');return;}

    const body={
        name, slug, report_type:'template',
        icon:document.getElementById('crIcon').value||'📊',
        category:document.getElementById('crCategory').value||'custom',
        description:document.getElementById('crDesc').value||'',
        query, template,
        computed_fields:document.getElementById('crComputed').value||null,
        default_params:JSON.stringify({date_range:document.getElementById('crDateRange').value||'yesterday'}),
    };

    const url=editId?'/telegram/reports/'+editId:'/telegram/reports/register';
    const method=editId?'PUT':'POST';

    fetch(url,{method,headers:{'X-CSRF-TOKEN':csrf,Accept:'application/json','Content-Type':'application/json'},body:JSON.stringify(body)})
    .then(r=>{if(!r.ok)return r.text().then(t=>{throw new Error('HTTP '+r.status+': '+t.substring(0,200));});return r.json();})
    .then(d=>{
        if(d.success){showMsg('customRptMsg','✓ '+xss(d.message),'ok');setTimeout(()=>{closeCustomRptModal();loadReports();},800);}
        else showMsg('customRptMsg','✗ '+(d.error||'Failed'),'err');
    }).catch(e=>showMsg('customRptMsg','✗ '+xss(e.message),'err'));
}

// ═══ ESC to close modals ═══
document.addEventListener('keydown',e=>{if(e.key==='Escape'){closeSubModal();closePreviewModal();closeCustomRptModal();}});
document.getElementById('subModal').addEventListener('click',e=>{if(e.target===e.currentTarget)closeSubModal();});
document.getElementById('previewModal').addEventListener('click',e=>{if(e.target===e.currentTarget)closePreviewModal();});
document.getElementById('customRptModal').addEventListener('click',e=>{if(e.target===e.currentTarget)closeCustomRptModal();});

// ═══ INIT ═══
document.addEventListener('DOMContentLoaded', loadAll);

</script>
@endpush
