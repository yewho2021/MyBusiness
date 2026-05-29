{{--
    Shared Base CSS Components
    Usage: @include('admin.partials.base_css')
    
    Uses CSS variables from tbl_configuration for full theme compliance.
    New pages should @include this in @push('styles') instead of 
    hardcoding hex values.
    
    Provides: page-header, card, data-table, btn, alert, badge, 
    stat-card, modal, form, pagination, empty-state
--}}
<style>
/* ── Page Header ── */
.page-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:var(--content-padding, 24px); flex-wrap:wrap; gap:12px; }
.page-header h2 { font-size:var(--fs-h1, 22px); font-weight:700; color:var(--code-bg); }
.page-header p { font-size:var(--fs-base, 14px); color:var(--text-muted); margin-top:4px; }

/* ── Buttons ── */
.btn { padding:10px 20px; border-radius:var(--btn-radius, 8px); font-size:var(--fs-base, 14px); font-weight:600; cursor:pointer; border:none; display:inline-flex; align-items:center; gap:7px; transition:all .2s; }
.btn-primary { background:linear-gradient(135deg, var(--c-primary, var(--c-danger)), var(--c-primary-hover, var(--c-primary-hover))); color:#fff; }
.btn-primary:hover { opacity:.9; }
.btn-secondary { background:var(--c-secondary, var(--c-secondary)); color:#fff; }
.btn-secondary:hover { background:var(--c-secondary-hover, var(--c-secondary)); }
.btn-success { background:var(--c-success, var(--c-success)); color:#fff; }
.btn-danger { background:var(--c-danger, var(--c-danger)); color:#fff; }
.btn-outline { background:#fff; border:1px solid var(--card-border, var(--border-color)); color:var(--text-body); }
.btn-outline:hover { background:var(--table-header-bg); border-color:var(--input-border); }
.btn-sm { padding:7px 14px; font-size:var(--fs-sm, 13px); }
.btn-xs { padding:5px 10px; font-size:var(--fs-xs, 12px); }

/* ── Cards ── */
.card { background:var(--card-bg, #fff); border-radius:var(--card-radius, 12px); border:1px solid var(--card-border, var(--border-color)); overflow:hidden; }
.card-head { padding:18px 22px; border-bottom:1px solid var(--border-light, var(--border-light)); display:flex; justify-content:space-between; align-items:center; }
.card-head h3 { font-size:var(--fs-h3, 16px); font-weight:700; color:var(--code-bg); display:flex; align-items:center; gap:8px; }
.card-head h3 i { color:var(--text-muted); font-size:var(--fs-base, 14px); }
.card-body { padding:0 22px 18px; }

/* ── Tables ── */
.data-table { width:100%; border-collapse:collapse; }
.data-table th { text-align:left; padding:13px 18px; font-size:var(--fs-xs, 12px); font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.5px; background:var(--table-header-bg, var(--table-header-bg)); border-bottom:2px solid var(--border-light, var(--border-light)); }
.data-table td { padding:14px 18px; font-size:var(--fs-base, 14px); color:var(--text-body); border-bottom:1px solid var(--border-light, var(--border-light)); }
.data-table tbody tr:hover td { background:var(--table-header-bg); }

/* ── Alerts ── */
.alert { padding:14px 18px; border-radius:var(--btn-radius, 10px); margin-bottom:18px; font-size:var(--fs-base, 14px); font-weight:500; display:flex; align-items:center; gap:10px; }
.alert-success { background:var(--c-success-light, var(--c-success-light)); color:var(--c-success); border:1px solid var(--c-success-border); }
.alert-danger { background:var(--c-danger-light, var(--c-danger-light)); color:var(--c-primary-hover); border:1px solid var(--c-danger-border); }
.alert-warning { background:var(--c-warning-light, var(--c-warning-light)); color:var(--c-warning); border:1px solid var(--c-warning-border); }
.alert-info { background:var(--c-info-light, var(--c-info-light)); color:var(--c-info); border:1px solid var(--c-info-border); }

/* ── Badges ── */
.badge { display:inline-flex; align-items:center; gap:6px; padding:5px 12px; border-radius:20px; font-size:var(--fs-xs, 12px); font-weight:600; }
.badge.green { background:var(--c-success-light, var(--c-success-light)); color:var(--c-success); border:1px solid var(--c-success-border); }
.badge.red { background:var(--c-danger-light, var(--c-danger-light)); color:var(--c-primary-hover); border:1px solid var(--c-danger-border); }
.badge.blue { background:var(--c-secondary-light, var(--c-secondary-light)); color:var(--c-secondary); border:1px solid var(--c-secondary-border); }
.badge.amber { background:var(--c-warning-light, var(--c-warning-light)); color:var(--c-warning); border:1px solid var(--c-warning-border); }
.badge.gray { background:var(--hover-bg); color:var(--text-body); border:1px solid var(--border-color); }

/* ── Stat Cards ── */
.stats-row { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:var(--content-padding, 24px); }
.stat-card { background:var(--card-bg, #fff); border-radius:var(--card-radius, 12px); padding:20px 24px; border:1px solid var(--card-border, var(--border-color)); }
.stat-icon { width:44px; height:44px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:18px; }
.stat-value { font-size:28px; font-weight:800; color:var(--code-bg); }
.stat-label { font-size:var(--fs-sm, 13px); color:var(--text-muted); }
@media(max-width:1200px) { .stats-row { grid-template-columns:repeat(2,1fr); } }
@media(max-width:640px) { .stats-row { grid-template-columns:1fr; } }

/* ── Modals ── */
.modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:9998; display:none; justify-content:center; align-items:center; backdrop-filter:blur(3px); }
.modal-overlay.show { display:flex; }
.modal { background:var(--card-bg, #fff); border-radius:14px; width:100%; max-width:540px; max-height:90vh; overflow:auto; }
.modal-head { padding:20px 24px; border-bottom:1px solid var(--card-border, var(--border-color)); display:flex; justify-content:space-between; align-items:center; }
.modal-body { padding:24px; }
.modal-foot { padding:16px 24px; border-top:1px solid var(--card-border, var(--border-color)); display:flex; justify-content:flex-end; gap:8px; }

/* ── Forms ── */
.form-group { margin-bottom:16px; }
.form-label { display:block; font-size:var(--fs-base, 14px); font-weight:600; color:var(--text-body); margin-bottom:6px; }
.form-input, .form-select { width:100%; padding:10px 12px; border:1px solid var(--input-border); border-radius:var(--input-radius, 8px); font-size:15px; outline:none; background:#fff; }
.form-input:focus, .form-select:focus { border-color:var(--c-secondary, var(--c-secondary)); box-shadow:0 0 0 3px rgba(37,99,235,.1); }
.form-input:read-only { background:var(--table-header-bg); color:var(--text-muted); }
.form-hint { font-size:var(--fs-xs, 12px); color:var(--text-faint); margin-top:4px; }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
@media(max-width:640px) { .form-row { grid-template-columns:1fr; } }

/* ── Empty State ── */
.empty-state { padding:70px 20px; text-align:center; }
.empty-state i { font-size:52px; color:var(--hover-border); display:block; margin-bottom:18px; }
.empty-state p { font-size:15px; color:var(--text-muted); }

/* ── Divider ── */
.divider { border:none; border-top:1px solid var(--border-light, var(--border-light)); margin:20px 0; }
</style>
