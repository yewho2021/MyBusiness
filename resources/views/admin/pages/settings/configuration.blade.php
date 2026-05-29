@extends('admin.layouts.app')
@section('title', 'System Configuration')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<style>
/* ── Page ── */
.cfg-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px; flex-wrap:wrap; gap:12px; }
.cfg-header-left h1 { font-size:24px; font-weight:700; color:var(--text-heading); margin-bottom:4px; }
.cfg-header-left p { font-size:14px; color:var(--text-muted); }
.cfg-header-right { display:flex; gap:8px; flex-wrap:wrap; }

/* ── Buttons ── */
.btn { padding:10px 20px; border-radius:var(--btn-radius,8px); font-size:14px; font-weight:600; cursor:pointer; border:none; display:inline-flex; align-items:center; gap:7px; text-decoration:none; transition:all .2s; }
.btn:hover { transform:translateY(-1px); box-shadow:0 4px 12px rgba(0,0,0,.1); }
.btn-primary { background:linear-gradient(135deg,var(--c-primary),var(--c-primary-hover)); color:var(--card-bg); }
.btn-primary:hover { opacity:.9; }
.btn-outline { background:var(--card-bg); color:var(--text-secondary); border:1.5px solid var(--border-color); }
.btn-outline:hover { background:var(--hover-bg); border-color:var(--hover-border); transform:none; box-shadow:none; }
.btn-danger-outline { background:var(--card-bg); color:var(--c-danger); border:1.5px solid var(--c-danger-border); }
.btn-danger-outline:hover { background:var(--c-danger-light); }
.btn-sm { padding:8px 14px; font-size:13px; }

/* ── Alert ── */
.alert { padding:14px 18px; border-radius:10px; margin-bottom:18px; font-size:14px; font-weight:500; display:flex; align-items:center; gap:10px; }
.alert-success { background:var(--c-success-light); color:var(--c-success); border:1px solid var(--c-success-border); }
.alert-danger { background:var(--c-danger-light); color:var(--c-danger); border:1px solid var(--c-danger-border); }

/* ── Layout: sidebar tabs + content ── */
.cfg-container { display:flex; gap:0; background:var(--card-bg); border-radius:var(--card-radius); border:1px solid var(--border-color); overflow:hidden; min-height:600px; box-shadow:var(--shadow-sm); }
.cfg-sidebar { width:200px; min-width:200px; background:var(--hover-bg); border-right:1px solid var(--border-color); padding:12px 0; }
.cfg-tab { display:flex; align-items:center; gap:10px; padding:12px 20px; font-size:14px; font-weight:500; color:var(--text-muted); cursor:pointer; border-left:3px solid transparent; transition:all .15s; text-decoration:none; }
.cfg-tab:hover { background:var(--border-light); color:var(--text-primary); }
.cfg-tab.active { background:var(--card-bg); color:var(--c-primary); border-left-color:var(--c-primary); font-weight:600; }
.cfg-tab i { width:18px; text-align:center; font-size:14px; }
.cfg-main { flex:1; padding:28px 32px; overflow-y:auto; max-height:calc(100vh - 200px); }
@media(max-width:768px) { .cfg-container { flex-direction:column; } .cfg-sidebar { width:100%; min-width:100%; flex-direction:row; overflow-x:auto; display:flex; padding:0; border-right:none; border-bottom:1px solid var(--border-color); } .cfg-tab { border-left:none; border-bottom:3px solid transparent; white-space:nowrap; } .cfg-tab.active { border-bottom-color:var(--c-primary); border-left:none; } .cfg-main { max-height:none; } }

/* ── Section title ── */
.cfg-section-title { font-size:18px; font-weight:700; color:var(--text-heading); margin-bottom:4px; }
.cfg-section-desc { font-size:13px; color:var(--text-faint); margin-bottom:24px; }

/* ── Form groups ── */
.cfg-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:20px; }
@media(max-width:900px) { .cfg-grid { grid-template-columns:1fr; } }
.cfg-field { margin-bottom:0; }
.cfg-field.full { grid-column:1/-1; }
.cfg-field label { display:block; font-size:13px; font-weight:600; color:var(--text-primary); margin-bottom:5px; }
.cfg-field .field-help { font-size:12px; color:var(--text-faint); margin-bottom:8px; line-height:1.4; }
.cfg-field input[type="text"],
.cfg-field input[type="number"],
.cfg-field select,
.cfg-field textarea { width:100%; padding:10px 14px; border:1.5px solid var(--border-color); border-radius:var(--input-radius,8px); font-size:14px; color:var(--text-primary); background:var(--card-bg); transition:all .2s; font-family:inherit; }
.cfg-field input:focus,
.cfg-field select:focus,
.cfg-field textarea:focus { outline:none; border-color:var(--c-secondary); box-shadow:0 0 0 3px var(--focus-ring); }
.cfg-field textarea { min-height:80px; resize:vertical; }
.cfg-field textarea.code-input { font-family:var(--font-mono,'JetBrains Mono'),monospace; font-size:13px; min-height:120px; background:var(--code-bg); color:var(--border-color); border-color:var(--text-primary); }
.cfg-field textarea.code-input:focus { border-color:var(--c-secondary); }

/* ── Number with px suffix ── */
.num-input-wrap { display:flex; align-items:center; gap:0; }
.num-input-wrap input { border-radius:var(--input-radius,8px) 0 0 var(--input-radius,8px); border-right:none; text-align:center; width:100px; }
.num-input-wrap .num-suffix { padding:10px 14px; background:var(--hover-bg); border:1.5px solid var(--border-color); border-left:none; border-radius:0 var(--input-radius,8px) var(--input-radius,8px) 0; font-size:13px; color:var(--text-muted); font-weight:500; }

/* ── Color picker ── */
.color-input-row { display:flex; align-items:center; gap:12px; }
.color-swatch-wrap { position:relative; width:48px; height:48px; border-radius:10px; overflow:hidden; border:2px solid var(--border-color); cursor:pointer; transition:border-color .2s; flex-shrink:0; }
.color-swatch-wrap:hover { border-color:var(--c-secondary); }
.color-swatch { position:absolute; inset:-8px; width:calc(100% + 16px); height:calc(100% + 16px); border:none; cursor:pointer; padding:0; background:none; }
.color-hex-wrap { display:flex; align-items:center; border:1.5px solid var(--border-color); border-radius:8px; overflow:hidden; background:var(--card-bg); transition:border-color .2s; width:150px; }
.color-hex-wrap:focus-within { border-color:var(--c-secondary); box-shadow:0 0 0 3px var(--focus-ring); }
.hex-prefix { padding:10px 0 10px 12px; color:var(--text-faint); font-size:14px; font-weight:600; font-family:monospace; user-select:none; }
.color-hex { border:none; outline:none; padding:10px 12px 10px 4px; font-size:14px; font-family:monospace; font-weight:500; width:100%; text-transform:uppercase; letter-spacing:1px; color:var(--text-primary); background:transparent; }
.color-preview { width:48px; height:48px; border-radius:10px; border:2px solid var(--border-color); flex-shrink:0; transition:background .15s; }

/* ── Color-like text inputs (rgba, hex in text fields) ── */
.color-text-row { display:flex; align-items:center; gap:10px; }
.ct-swatch { width:42px; height:42px; border-radius:8px; border:2px solid var(--border-color); flex-shrink:0; transition:background .15s; }
.ct-input { flex:1; padding:10px 14px; border:1.5px solid var(--input-border); border-radius:var(--input-radius); font-size:14px; font-family:var(--font-mono),monospace; color:var(--text-primary); background:var(--card-bg); }
.ct-input:focus { outline:none; border-color:var(--c-secondary); box-shadow:0 0 0 3px var(--focus-ring); }

/* ── Shadow preview ── */
.shadow-text-row { display:flex; flex-direction:column; gap:10px; }
.st-input { width:100%; padding:10px 14px; border:1.5px solid var(--input-border); border-radius:var(--input-radius); font-size:14px; font-family:var(--font-mono),monospace; color:var(--text-primary); background:var(--card-bg); }
.st-input:focus { outline:none; border-color:var(--c-secondary); box-shadow:0 0 0 3px var(--focus-ring); }
.st-preview { width:100%; height:48px; background:var(--card-bg); border-radius:8px; border:1px solid var(--border-light); }

/* ── Font selector ── */
.font-field { position:relative; }
.font-select { display:flex; align-items:center; border:1.5px solid var(--border-color); border-radius:8px; background:var(--card-bg); padding:0 14px; cursor:pointer; transition:border-color .2s; }
.font-select:focus-within { border-color:var(--c-secondary); box-shadow:0 0 0 3px var(--focus-ring); }
.font-search-icon { color:var(--text-faint); font-size:13px; margin-right:10px; }
.font-search { border:none; outline:none; padding:11px 0; font-size:14px; flex:1; font-weight:500; color:var(--text-primary); background:transparent; }
.font-arrow { color:var(--text-faint); font-size:11px; transition:transform .2s; }
.font-select.open .font-arrow { transform:rotate(180deg); }
.font-dropdown { position:absolute; z-index:50; left:0; right:0; margin-top:4px; background:var(--card-bg); border:1.5px solid var(--border-color); border-radius:10px; box-shadow:0 12px 36px rgba(0,0,0,.12); max-height:420px; overflow:hidden; display:none; }
.fp-tabs { display:flex; gap:4px; padding:10px 12px; border-bottom:1px solid var(--border-light); overflow-x:auto; flex-wrap:nowrap; }
.fp-tab { padding:5px 10px; border-radius:6px; border:1px solid var(--border-color); background:var(--card-bg); font-size:12px; font-weight:500; color:var(--text-muted); cursor:pointer; white-space:nowrap; display:inline-flex; align-items:center; gap:4px; transition:all .15s; }
.fp-tab:hover { background:var(--hover-bg); color:var(--text-primary); }
.fp-tab.active { background:var(--c-secondary); color:var(--card-bg); border-color:var(--c-secondary); }
.fp-tab small { opacity:.7; font-size:10px; }
.fp-list { max-height:330px; overflow-y:auto; }
.fp-cat-label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.8px; color:var(--text-faint); padding:12px 16px 6px; border-bottom:1px solid var(--border-light); }
.fp-count { font-size:11px; color:var(--text-faint); text-align:center; padding:8px; border-top:1px solid var(--border-light); }
.fp-loading { color:var(--c-secondary); margin-right:6px; }
.fp-load-more { display:flex; align-items:center; justify-content:center; gap:8px; width:100%; padding:12px; border:none; background:var(--hover-bg); color:var(--c-secondary); font-size:13px; font-weight:600; cursor:pointer; transition:background .15s; }
.fp-load-more:hover { background:var(--border-light); }
.fp-batch-loading { padding:16px; text-align:center; color:var(--c-secondary); font-size:13px; }
.fp-bottom { border-top:1px solid var(--border-light); }
.font-option { display:flex; align-items:center; justify-content:space-between; padding:10px 16px; cursor:pointer; transition:background .1s; border-bottom:1px solid var(--border-light); gap:12px; }
.font-option:hover { background:var(--hover-bg); }
.font-option.selected { background:var(--c-secondary-light); }
.font-option.selected .fo-name { color:var(--c-secondary); font-weight:600; }
.font-option.fp-highlight { background:var(--c-secondary-light); outline:2px solid var(--c-secondary); outline-offset:-2px; }
.fo-left { display:flex; flex-direction:column; gap:2px; min-width:0; }
.fo-name { font-size:14px; font-weight:500; color:var(--text-primary); }
.fo-meta { font-size:10px; color:var(--text-faint); }
.fo-preview { font-size:18px; color:var(--text-secondary); white-space:nowrap; flex-shrink:0; }

/* Font preview classes generated in separate style block below */
.font-preview { margin-top:14px; padding:20px; background:var(--hover-bg); border:1px solid var(--border-color); border-radius:10px; }
.fp-sample { font-size:22px; font-weight:500; color:var(--text-primary); margin-bottom:8px; }
.fp-alpha { font-size:14px; color:var(--text-muted); margin-bottom:2px; letter-spacing:1px; }
.fp-weights { margin-top:12px; padding-top:12px; border-top:1px solid var(--border-color); display:flex; gap:16px; flex-wrap:wrap; }
.fp-weights span { font-size:13px; color:var(--text-body); }

/* ── Image upload ── */
.img-upload-wrap { border:1.5px solid var(--border-color); border-radius:10px; overflow:hidden; }
.img-current { display:flex; align-items:center; gap:14px; padding:14px 16px; background:var(--hover-bg); border-bottom:1px solid var(--border-color); }
.img-current img { max-height:48px; max-width:120px; border-radius:6px; object-fit:contain; border:1px solid var(--border-color); }
.img-current .img-info { flex:1; }
.img-current .img-name { font-size:13px; font-weight:500; color:var(--text-primary); }
.img-current .img-size { font-size:12px; color:var(--text-faint); }
.img-remove { padding:6px 12px; background:var(--c-danger-light); color:var(--c-danger); border:1px solid var(--c-danger-border); border-radius:6px; font-size:12px; font-weight:500; cursor:pointer; transition:all .15s; }
.img-remove:hover { background:var(--c-danger-light); }
.img-dropzone { padding:30px; text-align:center; cursor:pointer; transition:all .2s; }
.img-dropzone:hover { background:var(--hover-bg); }
.img-dropzone i { font-size:28px; color:var(--hover-border); display:block; margin-bottom:8px; }
.img-dropzone p { font-size:13px; color:var(--text-muted); }
.img-dropzone .sub { font-size:12px; color:var(--text-faint); margin-top:4px; }
.img-dropzone.dragover { background:var(--c-secondary-light); border-color:var(--c-secondary); }

/* ── Color preview bar ── */
.color-preview-bar { grid-column:1/-1; padding:20px; background:var(--hover-bg); border-radius:var(--card-radius); border:1px solid var(--border-color); margin-top:8px; }
.cpb-title { font-size:12px; font-weight:600; color:var(--text-faint); text-transform:uppercase; letter-spacing:.5px; margin-bottom:14px; }
.cpb-row { display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
.cpb-btn { padding:8px 16px; border-radius:8px; font-size:13px; font-weight:600; color:var(--card-bg); border:none; cursor:default; }
.cpb-badge { padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600; display:inline-flex; align-items:center; gap:5px; }

/* ── Footer actions ── */
.cfg-footer { padding:20px 0 0; margin-top:24px; border-top:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; }

/* ── Import section ── */
.import-section { grid-column:1/-1; padding:16px; background:var(--hover-bg); border-radius:10px; border:1px solid var(--border-color); }
.import-section h4 { font-size:14px; font-weight:600; color:var(--text-primary); margin-bottom:10px; }

/* ── Login Access Tab ── */
.la-layout { max-width:640px; }
.la-card { background:var(--card-bg); border:1px solid var(--border-color); border-radius:var(--card-radius); padding:24px; margin-bottom:16px; }
.la-card-title { font-size:15px; font-weight:700; color:var(--text-primary); margin-bottom:20px; display:flex; align-items:center; gap:8px; }
.la-card-title i { color:var(--text-muted); font-size:14px; }
.la-settings.la-disabled { opacity:.45; pointer-events:none; filter:grayscale(.3); }

.la-toggle-row { display:flex; align-items:center; justify-content:space-between; gap:20px; }
.la-toggle-info { flex:1; }
.la-toggle-label { font-size:15px; font-weight:700; color:var(--text-primary); margin-bottom:4px; }
.la-toggle-desc { font-size:13px; color:var(--text-muted); line-height:1.5; }
.la-toggle-right { display:flex; align-items:center; gap:14px; flex-shrink:0; }

/* Toggle switch */
.la-switch { position:relative; display:inline-block; width:52px; height:28px; flex-shrink:0; }
.la-switch input { opacity:0; width:0; height:0; }
.la-slider { position:absolute; inset:0; background:var(--input-border); border-radius:28px; cursor:pointer; transition:all .25s; }
.la-slider::before { content:''; position:absolute; width:22px; height:22px; left:3px; top:3px; background:var(--card-bg); border-radius:50%; transition:all .25s; box-shadow:0 1px 3px rgba(0,0,0,.15); }
.la-switch input:checked + .la-slider { background:var(--c-success); }
.la-switch input:checked + .la-slider::before { transform:translateX(24px); }

.la-status { font-size:12px; font-weight:600; padding:4px 10px; border-radius:6px; }
.la-status.on { background:var(--c-success-light); color:var(--c-success); }
.la-status.off { background:var(--border-light); color:var(--text-placeholder); }

.la-field { margin-bottom:20px; }
.la-label { display:block; font-size:14px; font-weight:600; color:var(--text-primary); margin-bottom:4px; }
.la-help { font-size:12px; color:var(--text-faint); margin-bottom:10px; line-height:1.4; }

.la-radio-group { display:flex; flex-direction:column; gap:8px; }
.la-radio { display:flex; align-items:center; gap:12px; padding:14px 16px; border:1.5px solid var(--border-color); border-radius:10px; cursor:pointer; transition:all .15s; }
.la-radio:hover { border-color:var(--hover-border); background:var(--hover-bg); }
.la-radio.selected { border-color:var(--c-secondary); background:var(--c-secondary-light); }
.la-radio input { display:none; }
.la-radio i { width:20px; text-align:center; font-size:16px; color:var(--text-faint); }
.la-radio.selected i { color:var(--c-secondary); }
.la-radio strong { font-size:14px; color:var(--text-primary); display:block; }
.la-radio span { font-size:12px; color:var(--text-faint); }
.la-radio.selected strong { color:var(--c-secondary-hover); }

.la-input-row { display:flex; align-items:center; border:1.5px solid var(--border-color); border-radius:10px; overflow:hidden; background:var(--card-bg); transition:border-color .2s; }
.la-input-row:focus-within { border-color:var(--c-secondary); box-shadow:0 0 0 3px var(--focus-ring); }
.la-input-icon { padding:12px 14px; background:var(--hover-bg); border-right:1px solid var(--border-color); color:var(--text-faint); font-size:14px; }
.la-input { flex:1; border:none; outline:none; padding:12px 14px; font-size:14px; color:var(--text-primary); font-family:inherit; }

.la-info-box { display:flex; align-items:flex-start; gap:12px; padding:14px 16px; background:var(--c-secondary-light); border:1px solid var(--c-info-border); border-radius:10px; margin-top:8px; }
.la-info-icon { color:var(--c-secondary); font-size:14px; margin-top:2px; }
.la-info-box strong { font-size:13px; color:var(--text-primary); display:block; margin-bottom:2px; }
.la-info-box code { font-size:13px; background:var(--c-secondary-light); color:var(--c-secondary-hover); padding:2px 8px; border-radius:4px; font-family:monospace; font-weight:600; }

.la-warning { display:flex; align-items:flex-start; gap:12px; padding:14px 16px; background:var(--c-warning-light); border:1px solid var(--c-warning-border); border-radius:10px; font-size:13px; color:var(--c-warning); line-height:1.5; }
.la-warning i { color:var(--c-warning); font-size:16px; margin-top:1px; flex-shrink:0; }
.la-warning strong { display:block; margin-bottom:2px; }
.la-warning code { font-size:12px; background:var(--c-warning-light); padding:1px 5px; border-radius:3px; }

/* ── Email Settings Tab ── */
.em-layout { max-width:640px; }
.em-card { background:var(--card-bg); border:1px solid var(--border-color); border-radius:var(--card-radius); padding:24px; margin-bottom:16px; }
.em-card-title { font-size:15px; font-weight:700; color:var(--text-primary); margin-bottom:18px; display:flex; align-items:center; gap:8px; }
.em-card-title i { color:var(--text-muted); font-size:14px; }

.em-field { margin-bottom:18px; }
.em-field:last-child { margin-bottom:0; }
.em-label { display:block; font-size:14px; font-weight:600; color:var(--text-primary); margin-bottom:4px; }
.em-help { font-size:12px; color:var(--text-faint); margin-bottom:8px; line-height:1.4; }

.em-row { display:flex; gap:12px; }
.em-grow { flex:1; }
.em-shrink { width:110px; flex-shrink:0; }

.em-radio-group { display:flex; gap:8px; }
.em-radio { flex:1; display:flex; align-items:center; gap:10px; padding:12px 14px; border:1.5px solid var(--border-color); border-radius:10px; cursor:pointer; transition:all .15s; }
.em-radio:hover { border-color:var(--hover-border); background:var(--hover-bg); }
.em-radio.selected { border-color:var(--c-secondary); background:var(--c-secondary-light); }
.em-radio input { display:none; }
.em-radio i { font-size:16px; color:var(--text-faint); }
.em-radio.selected i { color:var(--c-secondary); }
.em-radio strong { font-size:13px; color:var(--text-primary); display:block; }
.em-radio span { font-size:11px; color:var(--text-faint); }
.em-radio.selected strong { color:var(--c-secondary-hover); }

.em-enc-group { display:flex; gap:8px; }
.em-enc { flex:1; text-align:center; padding:10px 14px; border:1.5px solid var(--border-color); border-radius:10px; cursor:pointer; transition:all .15s; font-size:14px; font-weight:600; color:var(--text-muted); }
.em-enc:hover { border-color:var(--hover-border); background:var(--hover-bg); }
.em-enc.selected { border-color:var(--c-secondary); background:var(--c-secondary-light); color:var(--c-secondary-hover); }
.em-enc input { display:none; }

.em-input-icon-wrap { display:flex; align-items:center; border:1.5px solid var(--border-color); border-radius:10px; overflow:hidden; background:var(--card-bg); transition:border-color .2s; }
.em-input-icon-wrap:focus-within { border-color:var(--c-secondary); box-shadow:0 0 0 3px var(--focus-ring); }
.em-input-icon { padding:12px 14px; background:var(--hover-bg); border-right:1px solid var(--border-color); color:var(--text-faint); font-size:14px; }
.em-input { flex:1; border:none; outline:none; padding:12px 14px; font-size:14px; color:var(--text-primary); font-family:inherit; background:transparent; }
.em-port-input { border:1.5px solid var(--border-color); border-radius:10px; text-align:center; font-weight:600; font-size:16px; }
.em-port-input:focus { border-color:var(--c-secondary); box-shadow:0 0 0 3px var(--focus-ring); outline:none; }

.em-eye { background:none; border:none; padding:8px 14px; color:var(--text-faint); cursor:pointer; font-size:14px; }
.em-eye:hover { color:var(--text-muted); }

.em-btn-test { background:linear-gradient(135deg,var(--c-secondary),var(--c-secondary-hover)); color:var(--card-bg); padding:12px 28px; border-radius:10px; font-size:14px; font-weight:600; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:8px; transition:all .2s; }
.em-btn-test:hover { box-shadow:0 4px 12px rgba(37,99,235,.3); transform:translateY(-1px); }
.em-btn-test:disabled { opacity:.6; cursor:wait; transform:none; box-shadow:none; }

.em-result-header { margin-bottom:12px; }
.em-result-ok { padding:14px 18px; background:var(--c-success-light); border:1px solid var(--c-success-border); border-radius:10px; color:var(--c-success); font-size:14px; font-weight:600; display:flex; align-items:center; gap:10px; }
.em-result-fail { padding:14px 18px; background:var(--c-danger-light); border:1px solid var(--c-danger-border); border-radius:10px; color:var(--c-danger); font-size:14px; font-weight:600; display:flex; align-items:center; gap:10px; line-height:1.5; }
.em-result-pending { padding:14px 18px; background:var(--c-secondary-light); border:1px solid var(--c-info-border); border-radius:10px; color:var(--c-secondary-hover); font-size:14px; display:flex; align-items:center; gap:10px; }

.em-steps { background:var(--hover-bg); border:1px solid var(--border-color); border-radius:10px; overflow:hidden; }
.em-step { display:flex; align-items:flex-start; gap:10px; padding:10px 16px; border-bottom:1px solid var(--border-light); font-size:13px; line-height:1.5; }
.em-step:last-child { border-bottom:none; }
.em-step i { margin-top:3px; font-size:12px; flex-shrink:0; }
.em-step strong { color:var(--text-primary); min-width:90px; flex-shrink:0; }
.em-step span { color:var(--text-muted); word-break:break-all; font-family:var(--font-mono,'JetBrains Mono'),monospace; font-size:12px; }
.em-step.step-ok i { color:var(--c-success); }
.em-step.step-err i { color:var(--c-danger); }
.em-step.step-err span { color:var(--c-danger); }
.em-step.step-info i { color:var(--text-faint); }

.em-hint-box { background:var(--hover-bg); border:1px solid var(--border-color); border-radius:10px; padding:16px 20px; }
.em-hint-title { font-size:13px; font-weight:600; color:var(--text-muted); margin-bottom:10px; display:flex; align-items:center; gap:6px; }
.em-hint-title i { color:var(--c-warning); }
.em-hint-grid { display:grid; grid-template-columns:70px 1fr; gap:6px 14px; font-size:13px; }
.em-hint-grid strong { color:var(--text-primary); }
.em-hint-grid div:nth-child(even) { color:var(--text-muted); }

/* ── Cache Tab ── */
.cc-summary-strip { display:flex; gap:24px; margin-bottom:20px; padding:14px 20px; background:var(--hover-bg); border:1px solid var(--border-color); border-radius:10px; }
.cc-summary-item { display:flex; align-items:center; gap:8px; font-size:14px; color:var(--text-muted); }
.cc-summary-item strong { color:var(--text-primary); font-size:18px; }
.cc-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:12px; }
@media(max-width:900px) { .cc-grid { grid-template-columns:1fr; } }
.cc-card { background:var(--card-bg); border:1px solid var(--border-color); border-radius:var(--card-radius); padding:16px 18px; transition:all .15s; display:flex; flex-direction:column; gap:10px; }
.cc-card:hover { border-color:var(--hover-border); box-shadow:0 1px 4px rgba(0,0,0,.04); }
.cc-card-top { display:flex; align-items:center; gap:12px; }
.cc-icon { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:15px; flex-shrink:0; }
.cc-info { flex:1; min-width:0; }
.cc-label { font-size:14px; font-weight:600; color:var(--text-primary); }
.cc-desc { font-size:12px; color:var(--text-faint); margin-top:1px; }
.cc-card-bottom { display:flex; align-items:center; justify-content:space-between; padding-top:8px; border-top:1px solid var(--border-light); }
.cc-card-bottom code { background:var(--border-light); padding:2px 8px; border-radius:4px; font-size:11px; color:var(--text-muted); }
.cc-stat { font-size:12px; font-weight:600; color:var(--text-secondary); }
.cc-btn-clear { width:32px; height:32px; border-radius:8px; border:1px solid var(--border-color); background:var(--card-bg); color:var(--text-faint); cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:12px; flex-shrink:0; transition:all .15s; }
.cc-btn-clear:hover:not(:disabled) { background:var(--c-danger-light); color:var(--c-danger); border-color:var(--c-danger-border); }
.cc-btn-clear:disabled { opacity:.3; cursor:not-allowed; }
.cc-btn-clear-all { background:linear-gradient(135deg,var(--c-primary),var(--c-primary-hover)); color:var(--card-bg); border:none; padding:10px 24px; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:8px; transition:all .2s; }
.cc-btn-clear-all:hover:not(:disabled) { box-shadow:0 4px 12px rgba(220,38,38,.3); }
.cc-btn-clear-all:disabled { opacity:.5; cursor:not-allowed; }
</style>
<style>
@php
    $fontMap = \App\Models\Configuration::fontUrlMap();
    foreach ($fontMap as $name => $url) {
        $cls = 'fpf-' . \Illuminate\Support\Str::slug($name);
        $isMono = str_contains(strtolower($url), 'mono');
        $fallback = $isMono ? 'monospace' : 'sans-serif';
        echo ".{$cls}{font-family:'{$name}',{$fallback} !important}\n";
    }
@endphp
</style>
@endpush

@section('content')

@if(session('success'))
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
@endif

<div class="cfg-header">
    <div class="cfg-header-left">
        <h1>System Configuration</h1>
        <p>Customize your portal appearance, branding, and behavior</p>
    </div>
    <div class="cfg-header-right">
        <a href="{{ route('admin.settings.configuration.export') }}" class="btn btn-outline btn-sm">
            <i class="fas fa-download"></i> Export JSON
        </a>
        @if($activeTab !== 'cache')
        <button type="submit" form="configForm" class="btn btn-primary btn-sm">
            <i class="fas fa-save"></i> Save All
        </button>
        @endif
    </div>
</div>

<form id="configForm" method="POST" action="{{ route('admin.settings.configuration.update') }}">
    @csrf
    <input type="hidden" name="_tab" value="{{ $activeTab }}">

    <div class="cfg-container">
        {{-- Sidebar tabs --}}
        <div class="cfg-sidebar">
            @foreach($groups as $groupKey => $groupMeta)
                <a href="{{ route('admin.settings.configuration', ['tab' => $groupKey]) }}"
                   class="cfg-tab {{ $activeTab === $groupKey ? 'active' : '' }}">
                    <i class="{{ $groupMeta['icon'] }}"></i>
                    {{ $groupMeta['label'] }}
                </a>
            @endforeach
        </div>

        {{-- Main content --}}
        <div class="cfg-main">
            @php $rows = $configData[$activeTab] ?? collect(); @endphp

            <div class="cfg-section-title">{{ $groups[$activeTab]['label'] ?? ucfirst($activeTab) }} Settings</div>
            <div class="cfg-section-desc">
                @switch($activeTab)
                    @case('brand')    Manage your portal identity — name, logo, and footer @break
                    @case('colors')   Customize the color scheme across the entire portal @break
                    @case('sidebar')  Adjust sidebar appearance — colors, width, and active states @break
                    @case('header')   Configure the top header bar appearance @break
                    @case('typography') Choose fonts and text sizes @break
                    @case('layout')   Set spacing, border radius, and surface colors @break
                    @case('login')    Customize the login page appearance @break
                    @case('login_access') Configure login page access restrictions and IP/DDNS filtering @break
                    @case('email')        Configure the portal default mail server connection @break
                    @case('cache')        Clear compiled views, application cache, sessions, logs, and temp files @break
                    @case('advanced') Custom CSS, date formats, and system settings @break
                @endswitch
            </div>

            @if($activeTab === 'login_access')
            {{-- ═══ Custom Login Access Layout ═══ --}}
            @php
                $laEnabled = $configData['login_access']->firstWhere('key', 'login_restriction_enabled');
                $laType    = $configData['login_access']->firstWhere('key', 'login_restriction_type');
                $laValue   = $configData['login_access']->firstWhere('key', 'login_restriction_value');
                $isEnabled = ($laEnabled->value ?? 'disabled') === 'enabled';
            @endphp
            <div class="la-layout">
                {{-- Toggle Row --}}
                <div class="la-card">
                    <div class="la-toggle-row">
                        <div class="la-toggle-info">
                            <div class="la-toggle-label">Login Page Restriction</div>
                            <div class="la-toggle-desc">When enabled, only the configured source can access the login page. All other visitors are silently redirected to Google.</div>
                        </div>
                        <div class="la-toggle-right">
                            <span class="la-status {{ $isEnabled ? 'on' : 'off' }}" id="laStatusBadge">{{ $isEnabled ? 'Active' : 'Inactive' }}</span>
                            <input type="hidden" name="login_access[login_restriction_enabled]" id="laEnabledInput" value="{{ $isEnabled ? 'enabled' : 'disabled' }}">
                            <label class="la-switch">
                                <input type="checkbox" id="laToggle" {{ $isEnabled ? 'checked' : '' }} onchange="toggleLoginRestriction(this)">
                                <span class="la-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Settings (shown when enabled) --}}
                <div class="la-card la-settings {{ $isEnabled ? '' : 'la-disabled' }}" id="laSettingsCard">
                    <div class="la-card-title"><i class="fas fa-filter"></i> Restriction Rules</div>

                    <div class="la-field">
                        <label class="la-label">Source Type</label>
                        <p class="la-help">What type of allowed source to check against.</p>
                        <div class="la-radio-group" id="laTypeGroup">
                            @foreach(['ipv4' => ['IPv4 Address', 'fas fa-network-wired', 'e.g. 203.0.113.50'], 'ipv6' => ['IPv6 Address', 'fas fa-project-diagram', 'e.g. 2001:db8::1'], 'ddns' => ['DDNS Hostname', 'fas fa-globe', 'e.g. myoffice.ddns.net']] as $typeKey => [$typeLabel, $typeIcon, $typeExample])
                            <label class="la-radio {{ ($laType->value ?? 'ipv4') === $typeKey ? 'selected' : '' }}">
                                <input type="radio" name="login_access[login_restriction_type]" value="{{ $typeKey }}"
                                       {{ ($laType->value ?? 'ipv4') === $typeKey ? 'checked' : '' }}
                                       onchange="selectSourceType(this)">
                                <i class="{{ $typeIcon }}"></i>
                                <div>
                                    <strong>{{ $typeLabel }}</strong>
                                    <span>{{ $typeExample }}</span>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="la-field">
                        <label class="la-label">Allowed Source</label>
                        <p class="la-help">Only this IP address or hostname will be allowed to access the login page.</p>
                        <div class="la-input-row">
                            <div class="la-input-icon"><i class="fas fa-map-marker-alt"></i></div>
                            <input type="text" name="login_access[login_restriction_value]"
                                   class="la-input" id="laValueInput"
                                   value="{{ $laValue->value ?? '' }}"
                                   placeholder="{{ ($laType->value ?? 'ipv4') === 'ddns' ? 'myoffice.ddns.net' : (($laType->value ?? 'ipv4') === 'ipv6' ? '2001:db8::1' : '203.0.113.50') }}">
                        </div>
                    </div>

                    {{-- Info box --}}
                    <div class="la-info-box">
                        <div class="la-info-icon"><i class="fas fa-info-circle"></i></div>
                        <div>
                            <strong>Your current IP address</strong>
                            <code class="la-current-ip">{{ request()->ip() }}</code>
                        </div>
                    </div>
                </div>

                {{-- Warning --}}
                @if($isEnabled)
                <div class="la-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong>Restriction is active.</strong> If you save a wrong IP, you may lock yourself out. To recover, update <code>tbl_configuration</code> directly via phpMyAdmin and set <code>login_restriction_enabled</code> to <code>disabled</code>.
                    </div>
                </div>
                @endif
            </div>

            <script>
            function toggleLoginRestriction(cb) {
                const enabled = cb.checked;
                document.getElementById('laEnabledInput').value = enabled ? 'enabled' : 'disabled';
                const badge = document.getElementById('laStatusBadge');
                badge.className = 'la-status ' + (enabled ? 'on' : 'off');
                badge.textContent = enabled ? 'Active' : 'Inactive';
                document.getElementById('laSettingsCard').classList.toggle('la-disabled', !enabled);
            }
            function selectSourceType(radio) {
                document.querySelectorAll('#laTypeGroup .la-radio').forEach(r => r.classList.remove('selected'));
                radio.closest('.la-radio').classList.add('selected');
                const placeholders = { ipv4: '203.0.113.50', ipv6: '2001:db8::1', ddns: 'myoffice.ddns.net' };
                document.getElementById('laValueInput').placeholder = placeholders[radio.value] || '';
            }
            </script>
            @elseif($activeTab === 'email')
            {{-- ═══ Custom Email Settings Layout ═══ --}}
            @php
                $emHost   = $configData['email']->firstWhere('key', 'mail_host');
                $emPort   = $configData['email']->firstWhere('key', 'mail_port');
                $emEnc    = $configData['email']->firstWhere('key', 'mail_encryption');
                $emAuth   = $configData['email']->firstWhere('key', 'mail_auth_enabled');
                $emUser   = $configData['email']->firstWhere('key', 'mail_username');
                $emPass   = $configData['email']->firstWhere('key', 'mail_password');
                $emFrom   = $configData['email']->firstWhere('key', 'mail_from_address');
                $emName   = $configData['email']->firstWhere('key', 'mail_from_name');
                $emRcvOn  = $configData['email']->firstWhere('key', 'mail_receive_enabled');
                $emRcvPro = $configData['email']->firstWhere('key', 'mail_receive_protocol');
                $emRcvHost= $configData['email']->firstWhere('key', 'mail_receive_host');
                $emRcvPort= $configData['email']->firstWhere('key', 'mail_receive_port');
                $emRcvEnc = $configData['email']->firstWhere('key', 'mail_receive_encryption');
                $authOn   = ($emAuth->value ?? 'enabled') === 'enabled';
                $rcvOn    = ($emRcvOn->value ?? 'disabled') === 'enabled';
            @endphp
            {{-- Keep protocol hidden as smtp --}}
            <input type="hidden" name="email[mail_protocol]" value="smtp">

            <div class="em-layout">

                {{-- SMTP Server Card --}}
                <div class="em-card">
                    <div class="em-card-title"><i class="fas fa-paper-plane"></i> SMTP Server <span style="font-size:11px;color:var(--text-faint);font-weight:400;margin-left:8px">Outgoing Mail</span></div>

                    <div class="em-row">
                        <div class="em-field em-grow">
                            <label class="em-label">Mail Host</label>
                            <div class="em-input-icon-wrap">
                                <div class="em-input-icon"><i class="fas fa-globe"></i></div>
                                <input type="text" name="email[mail_host]" class="em-input" id="emHost"
                                       value="{{ $emHost->value ?? '' }}" placeholder="mail.example.com">
                            </div>
                        </div>
                        <div class="em-field em-shrink">
                            <label class="em-label">Port</label>
                            <input type="number" name="email[mail_port]" class="em-input em-port-input" id="emPort"
                                   value="{{ $emPort->value ?? '587' }}" min="1" max="65535" placeholder="587">
                        </div>
                    </div>

                    <div class="em-field">
                        <label class="em-label">Encryption</label>
                        <div class="em-enc-group" id="emEncGroup">
                            @foreach(['none' => 'None', 'ssl' => 'SSL', 'tls' => 'TLS'] as $eKey => $eLabel)
                            <label class="em-enc {{ ($emEnc->value ?? 'tls') === $eKey ? 'selected' : '' }}">
                                <input type="radio" name="email[mail_encryption]" value="{{ $eKey }}"
                                       {{ ($emEnc->value ?? 'tls') === $eKey ? 'checked' : '' }}
                                       onchange="emSelectEnc(this)">
                                <span>{{ $eLabel }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Authentication Card --}}
                <div class="em-card">
                    <div class="em-card-title" style="margin-bottom:12px"><i class="fas fa-key"></i> Authentication</div>
                    <div class="la-toggle-row" style="margin-bottom:16px">
                        <label class="la-switch">
                            <input type="checkbox" id="emAuthToggle" {{ $authOn ? 'checked' : '' }}
                                   onchange="emToggleAuth(this.checked)">
                            <span class="la-slider"></span>
                        </label>
                        <input type="hidden" name="email[mail_auth_enabled]" id="emAuthInput" value="{{ $authOn ? 'enabled' : 'disabled' }}">
                        <span style="font-size:14px;font-weight:500;color:var(--text-primary)">My server requires authentication</span>
                        <span class="la-status {{ $authOn ? 'active' : '' }}" id="emAuthBadge">{{ $authOn ? 'ON' : 'OFF' }}</span>
                    </div>

                    <div id="emAuthFields" style="{{ $authOn ? '' : 'opacity:.4;pointer-events:none;' }}">
                        <div class="em-field">
                            <label class="em-label">Username</label>
                            <div class="em-input-icon-wrap">
                                <div class="em-input-icon"><i class="fas fa-user"></i></div>
                                <input type="text" name="email[mail_username]" class="em-input" id="emUser"
                                       value="{{ $emUser->value ?? '' }}" placeholder="user@example.com" autocomplete="off">
                            </div>
                        </div>

                        <div class="em-field">
                            <label class="em-label">Password</label>
                            <div class="em-input-icon-wrap">
                                <div class="em-input-icon"><i class="fas fa-lock"></i></div>
                                <input type="password" name="email[mail_password]" class="em-input" id="emPass"
                                       value="{{ $emPass->value ?? '' }}" placeholder="••••••••" autocomplete="new-password">
                                <button type="button" class="em-eye" onclick="emTogglePass(this)" tabindex="-1">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sender Defaults Card --}}
                <div class="em-card">
                    <div class="em-card-title"><i class="fas fa-id-card"></i> Sender Defaults</div>

                    <div class="em-field">
                        <label class="em-label">From Address</label>
                        <p class="em-help">Default sender email for outgoing mail.</p>
                        <div class="em-input-icon-wrap">
                            <div class="em-input-icon"><i class="fas fa-at"></i></div>
                            <input type="text" name="email[mail_from_address]" class="em-input"
                                   value="{{ $emFrom->value ?? '' }}" placeholder="noreply@example.com">
                        </div>
                    </div>

                    <div class="em-field">
                        <label class="em-label">From Name</label>
                        <div class="em-input-icon-wrap">
                            <div class="em-input-icon"><i class="fas fa-user-tag"></i></div>
                            <input type="text" name="email[mail_from_name]" class="em-input"
                                   value="{{ $emName->value ?? '' }}" placeholder="Admin Portal">
                        </div>
                    </div>
                </div>

                {{-- Receive Mail (Optional) Card --}}
                <div class="em-card">
                    <div class="em-card-title" style="margin-bottom:12px"><i class="fas fa-inbox"></i> Receive Mail <span style="font-size:11px;color:var(--text-faint);font-weight:400;margin-left:6px">Optional</span></div>
                    <div class="la-toggle-row" style="margin-bottom:16px">
                        <label class="la-switch">
                            <input type="checkbox" id="emRcvToggle" {{ $rcvOn ? 'checked' : '' }}
                                   onchange="emToggleRcv(this.checked)">
                            <span class="la-slider"></span>
                        </label>
                        <input type="hidden" name="email[mail_receive_enabled]" id="emRcvInput" value="{{ $rcvOn ? 'enabled' : 'disabled' }}">
                        <span style="font-size:14px;font-weight:500;color:var(--text-primary)">Enable IMAP / POP3 receive server</span>
                        <span class="la-status {{ $rcvOn ? 'active' : '' }}" id="emRcvBadge">{{ $rcvOn ? 'ON' : 'OFF' }}</span>
                    </div>

                    <div id="emRcvFields" style="{{ $rcvOn ? '' : 'opacity:.4;pointer-events:none;' }}">
                        <div class="em-field">
                            <label class="em-label">Protocol</label>
                            <div class="em-enc-group" id="emRcvProtoGroup">
                                @foreach(['imap' => 'IMAP', 'pop3' => 'POP3'] as $rKey => $rLabel)
                                <label class="em-enc {{ ($emRcvPro->value ?? 'imap') === $rKey ? 'selected' : '' }}" style="flex:1;">
                                    <input type="radio" name="email[mail_receive_protocol]" value="{{ $rKey }}"
                                           {{ ($emRcvPro->value ?? 'imap') === $rKey ? 'checked' : '' }}
                                           onchange="emSelectRcvProto(this)">
                                    <span>{{ $rLabel }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="em-row">
                            <div class="em-field em-grow">
                                <label class="em-label">Host</label>
                                <div class="em-input-icon-wrap">
                                    <div class="em-input-icon"><i class="fas fa-globe"></i></div>
                                    <input type="text" name="email[mail_receive_host]" class="em-input" id="emRcvHost"
                                           value="{{ $emRcvHost->value ?? '' }}" placeholder="imap.example.com">
                                </div>
                            </div>
                            <div class="em-field em-shrink">
                                <label class="em-label">Port</label>
                                <input type="number" name="email[mail_receive_port]" class="em-input em-port-input" id="emRcvPort"
                                       value="{{ $emRcvPort->value ?? '993' }}" min="1" max="65535" placeholder="993">
                            </div>
                        </div>

                        <div class="em-field">
                            <label class="em-label">Encryption</label>
                            <div class="em-enc-group" id="emRcvEncGroup">
                                @foreach(['none' => 'None', 'ssl' => 'SSL', 'tls' => 'TLS'] as $eKey => $eLabel)
                                <label class="em-enc {{ ($emRcvEnc->value ?? 'ssl') === $eKey ? 'selected' : '' }}">
                                    <input type="radio" name="email[mail_receive_encryption]" value="{{ $eKey }}"
                                           {{ ($emRcvEnc->value ?? 'ssl') === $eKey ? 'checked' : '' }}
                                           onchange="emSelectRcvEnc(this)">
                                    <span>{{ $eLabel }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Test Connection --}}
                <div class="em-card">
                    <div class="em-card-title"><i class="fas fa-vial"></i> Test Connection</div>
                    <p class="em-help" style="margin-bottom:16px;">
                        Tests the SMTP server connection using the settings <strong>currently shown above</strong> (not yet saved).
                    </p>
                    <button type="button" class="em-btn-test" id="emTestBtn" onclick="emTestConnection()">
                        <i class="fas fa-plug"></i> Test SMTP Connection
                    </button>

                    <div id="emTestResult" style="display:none;margin-top:16px;">
                        <div class="em-result-header" id="emResultHeader"></div>
                        <div class="em-steps" id="emSteps"></div>
                    </div>
                </div>

                {{-- Port reference --}}
                <div class="em-hint-box">
                    <div class="em-hint-title"><i class="fas fa-lightbulb"></i> Common Ports</div>
                    <div class="em-hint-grid">
                        <div><strong>SMTP</strong></div><div>587 (TLS) · 465 (SSL) · 25 (None)</div>
                        <div><strong>IMAP</strong></div><div>993 (SSL) · 143 (None / TLS)</div>
                        <div><strong>POP3</strong></div><div>995 (SSL) · 110 (None / TLS)</div>
                    </div>
                </div>
            </div>

            <script>
            const CSRF = document.querySelector('meta[name="csrf-token"]').content;

            const smtpPortMap = { none: '25',  ssl: '465', tls: '587' };
            const rcvPortMap  = { imap: { none:'143', ssl:'993', tls:'143' }, pop3: { none:'110', ssl:'995', tls:'110' } };

            function emSelectEnc(radio) {
                document.querySelectorAll('#emEncGroup .em-enc').forEach(r => r.classList.remove('selected'));
                radio.closest('.em-enc').classList.add('selected');
                document.getElementById('emPort').value = smtpPortMap[radio.value] || '587';
            }
            function emSelectRcvProto(radio) {
                document.querySelectorAll('#emRcvProtoGroup .em-enc').forEach(r => r.classList.remove('selected'));
                radio.closest('.em-enc').classList.add('selected');
                emAutoRcvPort();
            }
            function emSelectRcvEnc(radio) {
                document.querySelectorAll('#emRcvEncGroup .em-enc').forEach(r => r.classList.remove('selected'));
                radio.closest('.em-enc').classList.add('selected');
                emAutoRcvPort();
            }
            function emAutoRcvPort() {
                const proto = document.querySelector('#emRcvProtoGroup input:checked')?.value || 'imap';
                const enc   = document.querySelector('#emRcvEncGroup input:checked')?.value || 'ssl';
                document.getElementById('emRcvPort').value = rcvPortMap[proto]?.[enc] || '993';
            }
            function emToggleAuth(on) {
                document.getElementById('emAuthInput').value = on ? 'enabled' : 'disabled';
                document.getElementById('emAuthFields').style.opacity = on ? '' : '.4';
                document.getElementById('emAuthFields').style.pointerEvents = on ? '' : 'none';
                const badge = document.getElementById('emAuthBadge');
                badge.textContent = on ? 'ON' : 'OFF';
                badge.classList.toggle('active', on);
            }
            function emToggleRcv(on) {
                document.getElementById('emRcvInput').value = on ? 'enabled' : 'disabled';
                document.getElementById('emRcvFields').style.opacity = on ? '' : '.4';
                document.getElementById('emRcvFields').style.pointerEvents = on ? '' : 'none';
                const badge = document.getElementById('emRcvBadge');
                badge.textContent = on ? 'ON' : 'OFF';
                badge.classList.toggle('active', on);
            }
            function emTogglePass(btn) {
                const input = btn.parentElement.querySelector('input');
                const icon  = btn.querySelector('i');
                if (input.type === 'password') { input.type = 'text'; icon.className = 'fas fa-eye-slash'; }
                else { input.type = 'password'; icon.className = 'fas fa-eye'; }
            }

            async function emTestConnection() {
                const btn = document.getElementById('emTestBtn');
                const resultDiv = document.getElementById('emTestResult');
                const headerDiv = document.getElementById('emResultHeader');
                const stepsDiv  = document.getElementById('emSteps');

                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';
                resultDiv.style.display = 'block';
                headerDiv.innerHTML = '<div class="em-result-pending"><i class="fas fa-circle-notch fa-spin"></i> Connecting to SMTP server...</div>';
                stepsDiv.innerHTML = '';

                const authOn = document.getElementById('emAuthToggle').checked;
                const data = {
                    protocol:     'smtp',
                    host:         document.getElementById('emHost').value,
                    port:         parseInt(document.getElementById('emPort').value) || 587,
                    encryption:   document.querySelector('#emEncGroup input:checked')?.value || 'tls',
                    auth_enabled: authOn,
                    username:     authOn ? document.getElementById('emUser').value : '',
                    password:     authOn ? document.getElementById('emPass').value : '',
                };

                try {
                    const res = await fetch("{{ route('admin.settings.configuration.test-email') }}", {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        body: JSON.stringify(data)
                    });
                    const result = await res.json();

                    if (result.success) {
                        headerDiv.innerHTML = '<div class="em-result-ok"><i class="fas fa-check-circle"></i> Connection successful</div>';
                    } else {
                        headerDiv.innerHTML = '<div class="em-result-fail"><i class="fas fa-times-circle"></i> Connection failed: ' + (result.error || 'Unknown error') + '</div>';
                    }

                    if (result.steps && result.steps.length) {
                        let html = '';
                        result.steps.forEach(s => {
                            const isErr  = s.step === 'Error';
                            const isOk   = s.step === 'Success' || s.step === 'Authenticated';
                            const cls    = isErr ? 'step-err' : (isOk ? 'step-ok' : 'step-info');
                            const icon   = isErr ? 'times-circle' : (isOk ? 'check-circle' : 'chevron-right');
                            html += '<div class="em-step ' + cls + '">';
                            html += '<i class="fas fa-' + icon + '"></i>';
                            html += '<strong>' + s.step + '</strong>';
                            html += '<span>' + s.detail + '</span>';
                            html += '</div>';
                        });
                        stepsDiv.innerHTML = html;
                    }

                } catch (err) {
                    headerDiv.innerHTML = '<div class="em-result-fail"><i class="fas fa-times-circle"></i> Request failed: ' + err.message + '</div>';
                }

                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-plug"></i> Test SMTP Connection';
            }
            </script>
            @elseif($activeTab === 'cache')
            {{-- ═══ Custom Cache Management Layout ═══ --}}
            @php
                $cachePaths = [
                    'views'    => ['label' => 'Compiled Blade Views',  'icon' => 'fas fa-eye',       'desc' => 'Compiled .php from Blade templates',      'path' => 'storage/framework/views/', 'color' => 'var(--c-secondary)'],
                    'cache'    => ['label' => 'Application Cache',     'icon' => 'fas fa-database',  'desc' => 'File-based cache entries & config cache',  'path' => 'storage/framework/cache/', 'color' => 'var(--c-purple)'],
                    'sessions' => ['label' => 'Session Files',         'icon' => 'fas fa-users',     'desc' => 'Active and expired session files',         'path' => 'storage/framework/sessions/', 'color' => 'var(--c-info)'],
                    'config'   => ['label' => 'Config & Route Cache',  'icon' => 'fas fa-cogs',      'desc' => 'Cached config, routes, and services',     'path' => 'bootstrap/cache/',           'color' => 'var(--c-warning)'],
                    'logs'     => ['label' => 'Log Files',             'icon' => 'fas fa-file-alt',  'desc' => 'Laravel log files (laravel.log)',          'path' => 'storage/logs/',              'color' => 'var(--c-danger)'],
                    'patch_backups' => ['label' => 'Patch Backups',    'icon' => 'fas fa-archive',   'desc' => 'Backups from System Patch module',         'path' => 'storage/app/patch_backups/', 'color' => 'var(--text-muted)'],
                    'opcache'  => ['label' => 'PHP OPcache',           'icon' => 'fas fa-bolt',      'desc' => 'Compiled PHP bytecode cache (per-worker)', 'path' => '(PHP memory)',               'color' => 'var(--c-success)'],
                    'db_cache' => ['label' => 'Database Cache Table',  'icon' => 'fas fa-table',     'desc' => 'cache + cache_locks tables',               'path' => 'tbl: cache, cache_locks',    'color' => 'var(--c-secondary)'],
                ];
                $cacheStats = [];
                foreach ($cachePaths as $key => $info) {
                    if ($key === 'opcache') {
                        $scripts = 0; $mem = 0;
                        if (function_exists('opcache_get_status')) {
                            $st = @opcache_get_status(false);
                            if ($st) { $scripts = $st['opcache_statistics']['num_cached_scripts'] ?? 0; $mem = $st['memory_usage']['used_memory'] ?? 0; }
                        }
                        $cacheStats[$key] = ['files' => $scripts, 'size' => $mem];
                        continue;
                    }
                    if ($key === 'db_cache') {
                        $rows = \Illuminate\Support\Facades\DB::table('cache')->count();
                        $cacheStats[$key] = ['files' => $rows, 'size' => 0];
                        continue;
                    }
                    $fullPath = base_path($info['path']);
                    $fileCount = 0; $totalSz = 0;
                    if (is_dir($fullPath)) {
                        $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($fullPath, FilesystemIterator::SKIP_DOTS));
                        foreach ($iter as $f) { if ($f->isFile() && $f->getFilename() !== '.gitignore') { $fileCount++; $totalSz += $f->getSize(); } }
                    }
                    $cacheStats[$key] = ['files' => $fileCount, 'size' => $totalSz];
                }
                $totalFiles = array_sum(array_column($cacheStats, 'files'));
                $totalSize  = array_sum(array_column($cacheStats, 'size'));
                $fmtTotal = $totalSize >= 1048576 ? number_format($totalSize/1048576,1).' MB' : number_format($totalSize/1024,1).' KB';
            @endphp

            {{-- Summary strip --}}
            <div class="cc-summary-strip">
                <div class="cc-summary-item">
                    <i class="fas fa-folder-open" style="color:var(--c-secondary)"></i>
                    <span><strong id="ccTotalFiles">{{ $totalFiles }}</strong> cached files</span>
                </div>
                <div class="cc-summary-item">
                    <i class="fas fa-hdd" style="color:var(--c-purple)"></i>
                    <span><strong id="ccTotalSize">{{ $fmtTotal }}</strong> disk usage</span>
                </div>
            </div>

            {{-- 2-column grid --}}
            <div class="cc-grid">
                @foreach($cachePaths as $key => $info)
                <div class="cc-card" id="cc-card-{{ $key }}">
                    <div class="cc-card-top">
                        <div class="cc-icon" style="background:{{ $info['color'] }}12;color:{{ $info['color'] }}"><i class="{{ $info['icon'] }}"></i></div>
                        <div class="cc-info">
                            <div class="cc-label">{{ $info['label'] }}</div>
                            <div class="cc-desc">{{ $info['desc'] }}</div>
                        </div>
                        <button type="button" class="cc-btn-clear" data-target="{{ $key }}" onclick="ccClear('{{ $key }}')" {{ $cacheStats[$key]['files'] === 0 ? 'disabled' : '' }}>
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                    <div class="cc-card-bottom">
                        <code>{{ $info['path'] }}</code>
                        <span class="cc-stat" id="cc-stat-{{ $key }}">{{ $cacheStats[$key]['files'] }} {{ $key === 'opcache' ? 'script' : ($key === 'db_cache' ? 'row' : 'file') }}{{ $cacheStats[$key]['files'] !== 1 ? 's' : '' }}{{ $cacheStats[$key]['size'] > 0 ? ' · ' . ($cacheStats[$key]['size'] >= 1048576 ? number_format($cacheStats[$key]['size']/1048576,2).' MB' : number_format($cacheStats[$key]['size']/1024,1).' KB') : '' }}</span>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Result + log (full width) --}}
            <div id="ccResult" style="margin-top:16px;display:none;"></div>
            <div id="ccLogWrap" style="margin-top:12px;display:none;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                    <span style="font-size:12px;font-weight:600;color:var(--text-muted);"><i class="fas fa-list"></i> Execution Log</span>
                    <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('ccLog').value)" style="padding:4px 10px;border-radius:6px;border:1px solid var(--border-color);background:var(--card-bg);font-size:11px;color:var(--text-muted);cursor:pointer;"><i class="fas fa-copy"></i> Copy</button>
                </div>
                <textarea id="ccLog" readonly spellcheck="false" style="width:100%;height:280px;padding:14px;border:1px solid var(--text-heading);border-radius:10px;background:var(--code-bg);color:var(--border-color);font-family:'JetBrains Mono',monospace;font-size:11px;line-height:1.7;resize:vertical;outline:none;white-space:pre;overflow:auto;"></textarea>
            </div>

            {{-- Footer: Clear Everything button --}}
            <div class="cfg-footer">
                <div style="font-size:12px;color:var(--text-faint);"><i class="fas fa-info-circle"></i> <code>.gitignore</code> files are preserved in all directories</div>
                <button type="button" class="cc-btn-clear-all" onclick="ccClearAll()">
                    <i class="fas fa-broom"></i> Clear Everything
                </button>
            </div>

            <script>
            (function(){
                const CSRF_CC = document.querySelector('meta[name="csrf-token"]').content;
                function fmtSize(b) { return b>=1048576?(b/1048576).toFixed(2)+' MB':b>=1024?(b/1024).toFixed(1)+' KB':b+' B'; }

                window.ccClear = function(target) { ccSend([target]); };
                window.ccClearAll = function() { ccSend(['views','cache','sessions','config','logs','patch_backups','opcache','db_cache']); };

                async function ccSend(targets) {
                    document.querySelectorAll('.cc-btn-clear,.cc-btn-clear-all').forEach(b=>b.disabled=true);
                    targets.forEach(t=>{const b=document.querySelector('.cc-btn-clear[data-target="'+t+'"]');if(b)b.innerHTML='<i class="fas fa-spinner fa-spin"></i>';});
                    const rd=document.getElementById('ccResult');
                    const logWrap=document.getElementById('ccLogWrap');
                    const logEl=document.getElementById('ccLog');
                    rd.style.display='none'; logWrap.style.display='none';

                    try {
                        const res=await fetch("{{ route('admin.settings.configuration.clear-cache') }}",{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_CC,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({targets:targets})});
                        const data=await res.json();

                        // Update card stats
                        let remainFiles=0, remainSize=0;
                        if(data.results)data.results.forEach(r=>{
                            const s=document.getElementById('cc-stat-'+r.target);
                            if(s)s.textContent='0 files · 0 KB';
                            const b=document.querySelector('.cc-btn-clear[data-target="'+r.target+'"]');
                            if(b)b.disabled=true;
                        });
                        document.querySelectorAll('.cc-stat').forEach(s=>{
                            const p=s.textContent.match(/(\d+)\s*file/);
                            if(p)remainFiles+=parseInt(p[1]);
                            const sm=s.textContent.match(/([\d.]+)\s*(KB|MB|B)/);
                            if(sm){let v=parseFloat(sm[1]);if(sm[2]==='MB')v*=1048576;else if(sm[2]==='KB')v*=1024;remainSize+=v;}
                        });
                        document.getElementById('ccTotalFiles').textContent=remainFiles;
                        document.getElementById('ccTotalSize').textContent=fmtSize(remainSize);

                        let h='<div style="padding:14px 18px;border-radius:10px;font-size:13px;display:flex;align-items:center;gap:10px;';
                        h+=data.success?'background:var(--c-success-light);border:1px solid var(--c-success-border);color:var(--c-success);"><i class="fas fa-check-circle"></i> <strong>Cleared '+data.total_cleared+' files</strong> · Freed '+data.total_freed:'background:var(--c-danger-light);border:1px solid var(--c-danger-border);color:var(--c-danger);"><i class="fas fa-exclamation-circle"></i> <strong>Completed with errors</strong>';
                        rd.innerHTML=h+'</div>'; rd.style.display='block';

                        let log='════════════════════════════════════════════════════════════\n';
                        log+=' CACHE CLEAR LOG\n';
                        log+=' Time: '+new Date().toLocaleString()+'\n';
                        log+='════════════════════════════════════════════════════════════\n\n';
                        if(data.results){data.results.forEach(r=>{
                            log+='── '+(r.label||r.target)+' ──\n';
                            log+='   Status: '+(r.status==='ok'?'✓ Success':'✗ Error')+'\n';
                            log+='   Cleared: '+r.cleared+' file(s)   Freed: '+fmtSize(r.size_freed)+'\n';
                            if(r.error)log+='   Error: '+r.error+'\n';
                            if(r.files&&r.files.length>0){r.files.forEach(f=>{log+='   '+(f.ok?'✓':'✗')+' '+f.path+' ('+fmtSize(f.size)+')\n';});}
                            else{log+='   (no files found)\n';}
                            if(r.dirs_removed&&r.dirs_removed.length>0){r.dirs_removed.forEach(d=>{log+='   ✓ rmdir '+d+'\n';});}
                            log+='\n';
                        });}
                        log+='════════════════════════════════════════════════════════════\n';
                        log+=' Total: '+data.total_cleared+' files cleared · '+data.total_freed+' freed\n';
                        log+='════════════════════════════════════════════════════════════\n';
                        logEl.value=log; logWrap.style.display='block';

                    }catch(e){
                        rd.innerHTML='<div style="padding:14px;border-radius:10px;background:var(--c-danger-light);border:1px solid var(--c-danger-border);color:var(--c-danger);font-size:13px;"><i class="fas fa-times-circle"></i> '+e.message+'</div>';
                        rd.style.display='block';
                    }finally{
                        document.querySelectorAll('.cc-btn-clear').forEach(b=>{b.innerHTML='<i class="fas fa-trash-alt"></i>';});
                        document.querySelectorAll('.cc-btn-clear-all').forEach(b=>b.disabled=false);
                    }
                }
            })();
            </script>
            @else
            <div class="cfg-grid">
                @foreach($rows as $row)
                    @switch($row->type)

                        {{-- COLOR input --}}
                        @case('color')
                            <div class="cfg-field">
                                <label>{{ $row->label }}</label>
                                @if($row->description)<p class="field-help">{{ $row->description }}</p>@endif
                                <div class="color-input-row">
                                    <div class="color-swatch-wrap">
                                        <input type="color" name="{{ $row->group }}[{{ $row->key }}]"
                                               value="{{ $row->value ?? $row->default_value }}"
                                               class="color-swatch" data-sync="{{ $row->key }}">
                                    </div>
                                    <div class="color-hex-wrap">
                                        <span class="hex-prefix">#</span>
                                        <input type="text" class="color-hex" data-sync="{{ $row->key }}"
                                               value="{{ strtoupper(ltrim($row->value ?? $row->default_value, '#')) }}"
                                               maxlength="6" pattern="[0-9a-fA-F]{6}">
                                    </div>
                                    <div class="color-preview" data-sync="{{ $row->key }}"
                                         style="background:{{ $row->value ?? $row->default_value }}"></div>
                                </div>
                            </div>
                            @break

                        {{-- SELECT input --}}
                        @case('select')
                            @php $opts = $row->getOptionsArray(); @endphp
                            @if($row->key === 'font_family' || $row->key === 'font_mono')
                                {{-- Font selector --}}
                                <div class="cfg-field full font-field" data-field="{{ $row->key }}">
                                    <label>{{ $row->label }}</label>
                                    @if($row->description)<p class="field-help">{{ $row->description }}</p>@endif
                                    <input type="hidden" name="{{ $row->group }}[{{ $row->key }}]"
                                           value="{{ $row->value ?? $row->default_value }}" class="font-value">
                                    <div class="font-select" onclick="FontPicker.toggle(this)">
                                        <i class="fas fa-search font-search-icon"></i>
                                        <input type="text" class="font-search" placeholder="Search fonts..."
                                               value="{{ $row->value ?? $row->default_value }}"
                                               oninput="FontPicker.filter(this)" onkeydown="FontPicker.onKey(event,this)" onclick="event.stopPropagation(); FontPicker.toggle(this.parentElement)">
                                        <i class="fas fa-chevron-down font-arrow"></i>
                                    </div>
                                    <div class="font-dropdown"></div>
                                    <div class="font-preview" style="font-family:'{{ $row->value ?? $row->default_value }}',{{ $row->key === 'font_mono' ? 'monospace' : 'sans-serif' }}">
                                        <div class="fp-sample">The quick brown fox jumps over the lazy dog</div>
                                        <div class="fp-alpha">ABCDEFGHIJKLMNOPQRSTUVWXYZ</div>
                                        <div class="fp-alpha">abcdefghijklmnopqrstuvwxyz  0123456789</div>
                                        <div class="fp-weights">
                                            <span style="font-weight:400">Regular 400</span>
                                            <span style="font-weight:500">Medium 500</span>
                                            <span style="font-weight:600">SemiBold 600</span>
                                            <span style="font-weight:700">Bold 700</span>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="cfg-field">
                                    <label>{{ $row->label }}</label>
                                    @if($row->description)<p class="field-help">{{ $row->description }}</p>@endif
                                    <select name="{{ $row->group }}[{{ $row->key }}]">
                                        @foreach($opts as $k => $v)
                                            @php $optKey = is_int($k) ? $v : $k; @endphp
                                            <option value="{{ $optKey }}" {{ ($row->value ?? $row->default_value) === $optKey ? 'selected' : '' }}>{{ $v }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            @break

                        {{-- NUMBER input --}}
                        @case('number')
                            <div class="cfg-field">
                                <label>{{ $row->label }}</label>
                                @if($row->description)<p class="field-help">{{ $row->description }}</p>@endif
                                <div class="num-input-wrap">
                                    <input type="number" name="{{ $row->group }}[{{ $row->key }}]"
                                           value="{{ $row->value ?? $row->default_value }}"
                                           min="0" step="1">
                                    <span class="num-suffix">{{ in_array($row->key, ['session_lifetime_minutes','records_per_page']) ? '' : 'px' }}</span>
                                </div>
                            </div>
                            @break

                        {{-- IMAGE upload --}}
                        @case('image')
                            <div class="cfg-field full">
                                <label>{{ $row->label }}</label>
                                @if($row->description)<p class="field-help">{{ $row->description }}</p>@endif
                                <div class="img-upload-wrap" data-key="{{ $row->key }}">
                                    @if($row->value)
                                    <div class="img-current" id="img-current-{{ $row->key }}">
                                        <img src="{{ asset($row->value) }}" alt="{{ $row->label }}">
                                        <div class="img-info">
                                            <div class="img-name">{{ basename($row->value) }}</div>
                                        </div>
                                        <button type="button" class="img-remove" onclick="removeImage('{{ $row->key }}')">
                                            <i class="fas fa-trash-alt"></i> Remove
                                        </button>
                                    </div>
                                    @endif
                                    <div class="img-dropzone" onclick="this.querySelector('input[type=file]').click()"
                                         ondragover="event.preventDefault();this.classList.add('dragover')"
                                         ondragleave="this.classList.remove('dragover')"
                                         ondrop="event.preventDefault();this.classList.remove('dragover');handleDrop(event,'{{ $row->key }}')">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <p>Click or drag to upload</p>
                                        <p class="sub">PNG, JPG, SVG, ICO · Max 2MB</p>
                                        <input type="file" accept=".png,.jpg,.jpeg,.gif,.svg,.ico,.webp" style="display:none"
                                               onchange="uploadImage(this.files[0],'{{ $row->key }}')">
                                    </div>
                                </div>
                            </div>
                            @break

                        {{-- CODE input --}}
                        @case('code')
                            <div class="cfg-field full">
                                <label>{{ $row->label }}</label>
                                @if($row->description)<p class="field-help">{{ $row->description }}</p>@endif
                                <textarea name="{{ $row->group }}[{{ $row->key }}]"
                                          class="code-input" rows="6"
                                          placeholder="{{ $row->key === 'custom_css' ? '/* Your custom CSS here */' : '<!-- Your custom HTML here -->' }}">{{ $row->value }}</textarea>
                            </div>
                            @break

                        {{-- TEXTAREA --}}
                        @case('textarea')
                            <div class="cfg-field full">
                                <label>{{ $row->label }}</label>
                                @if($row->description)<p class="field-help">{{ $row->description }}</p>@endif
                                <textarea name="{{ $row->group }}[{{ $row->key }}]" rows="3">{{ $row->value ?? $row->default_value }}</textarea>
                            </div>
                            @break

                        {{-- TEXT (default) --}}
                        @default
                            @php
                                $val = $row->value ?? $row->default_value;
                                $isColorLike = preg_match('/^(#[0-9a-fA-F]{3,8}|rgba?\(|hsla?\()/', trim($val));
                                $isShadow = str_contains($val, 'px ') && str_contains($val, 'rgba');
                            @endphp
                            <div class="cfg-field {{ in_array($row->key, ['footer_text']) ? 'full' : '' }}">
                                <label>{{ $row->label }}</label>
                                @if($row->description)<p class="field-help">{{ $row->description }}</p>@endif
                                @if($isColorLike && !$isShadow)
                                    {{-- Color-like text value (rgba, hex) — show swatch + text input --}}
                                    <div class="color-text-row">
                                        <div class="ct-swatch" id="ct-swatch-{{ $row->key }}" style="background:{{ $val }}"></div>
                                        <input type="text" name="{{ $row->group }}[{{ $row->key }}]"
                                               value="{{ $val }}" class="ct-input"
                                               oninput="document.getElementById('ct-swatch-{{ $row->key }}').style.background=this.value">
                                    </div>
                                @elseif($isShadow)
                                    {{-- Shadow value — show preview box --}}
                                    <div class="shadow-text-row">
                                        <input type="text" name="{{ $row->group }}[{{ $row->key }}]"
                                               value="{{ $val }}" class="st-input"
                                               oninput="document.getElementById('st-preview-{{ $row->key }}').style.boxShadow=this.value">
                                        <div class="st-preview" id="st-preview-{{ $row->key }}" style="box-shadow:{{ $val }}"></div>
                                    </div>
                                @else
                                    <input type="text" name="{{ $row->group }}[{{ $row->key }}]"
                                           value="{{ $val }}">
                                @endif
                            </div>

                    @endswitch
                @endforeach

                {{-- Color preview bar (only on colors tab) --}}
                @if($activeTab === 'colors')
                <div class="color-preview-bar">
                    <div class="cpb-title">Live Preview</div>
                    <div class="cpb-row">
                        <button type="button" class="cpb-btn" id="cpb-primary" style="background:{{ $configData['colors']->firstWhere('key','primary')->value ?? 'var(--c-danger)' }}">Primary</button>
                        <button type="button" class="cpb-btn" id="cpb-secondary" style="background:{{ $configData['colors']->firstWhere('key','secondary')->value ?? 'var(--c-secondary)' }}">Secondary</button>
                        <span class="cpb-badge" id="cpb-success" style="background:{{ $configData['colors']->firstWhere('key','success_light')->value ?? 'var(--c-success-light)' }};color:{{ $configData['colors']->firstWhere('key','success')->value ?? 'var(--c-success)' }}"><i class="fas fa-check-circle"></i> Success</span>
                        <span class="cpb-badge" id="cpb-warning" style="background:{{ $configData['colors']->firstWhere('key','warning_light')->value ?? 'var(--c-warning-light)' }};color:{{ $configData['colors']->firstWhere('key','warning')->value ?? 'var(--c-warning)' }}"><i class="fas fa-exclamation-triangle"></i> Warning</span>
                        <span class="cpb-badge" id="cpb-danger" style="background:{{ $configData['colors']->firstWhere('key','danger_light')->value ?? 'var(--c-danger-light)' }};color:{{ $configData['colors']->firstWhere('key','danger')->value ?? 'var(--c-danger)' }}"><i class="fas fa-times-circle"></i> Danger</span>
                        <span class="cpb-badge" id="cpb-info" style="background:{{ $configData['colors']->firstWhere('key','info_light')->value ?? 'var(--c-info-light)' }};color:{{ $configData['colors']->firstWhere('key','info')->value ?? 'var(--c-info)' }}"><i class="fas fa-info-circle"></i> Info</span>
                    </div>
                </div>
                @endif

                {{-- Import section (only on advanced tab) --}}
                @if($activeTab === 'advanced')
                <div class="import-section">
                    <h4><i class="fas fa-file-import"></i> Import Configuration</h4>
                    <form method="POST" action="{{ route('admin.settings.configuration.import') }}" enctype="multipart/form-data" style="display:flex;gap:10px;align-items:flex-end;">
                        @csrf
                        <div style="flex:1;">
                            <label style="font-size:12px;color:var(--text-muted);display:block;margin-bottom:4px;">Select JSON file</label>
                            <input type="file" name="config_file" accept=".json,.txt" style="font-size:13px;">
                        </div>
                        <button type="submit" class="btn btn-outline btn-sm"><i class="fas fa-upload"></i> Import</button>
                    </form>
                </div>
                @endif
            </div>
            @endif

            {{-- Footer actions --}}
            @if($activeTab !== 'cache')
            <div class="cfg-footer">
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <form method="POST" action="{{ route('admin.settings.configuration.reset') }}"
                          onsubmit="return confirm('Reset all {{ $groups[$activeTab]['label'] ?? $activeTab }} settings to their defaults? This cannot be undone.')">
                        @csrf
                        <input type="hidden" name="group" value="{{ $activeTab }}">
                        <button type="submit" class="btn btn-danger-outline btn-sm">
                            <i class="fas fa-undo"></i> Reset to Defaults
                        </button>
                    </form>
                    @if($activeTab === 'email')
                    <form method="POST" action="{{ route('admin.settings.configuration.reset') }}"
                          onsubmit="return confirm('Clear ALL email/SMTP settings to blank? You will need to re-enter everything.')" style="display:inline;">
                        @csrf
                        <input type="hidden" name="group" value="email">
                        <input type="hidden" name="mode" value="blank">
                        <button type="submit" class="btn btn-danger-outline btn-sm" style="border-color:var(--c-danger);color:var(--c-danger);">
                            <i class="fas fa-trash-can"></i> Clear SMTP Settings
                        </button>
                    </form>
                    @endif
                </div>
                <button type="submit" form="configForm" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save All Settings
                </button>
            </div>
            @endif
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

// ═══════════════════════════════════════════
// COLOR PICKER — bidirectional sync
// ═══════════════════════════════════════════
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('color-swatch')) {
        const key = e.target.dataset.sync;
        const hex = e.target.value.replace('#','').toUpperCase();
        const hexInput = document.querySelector('.color-hex[data-sync="'+key+'"]');
        const preview = document.querySelector('.color-preview[data-sync="'+key+'"]');
        if (hexInput) hexInput.value = hex;
        if (preview) preview.style.background = e.target.value;
        updateColorPreview(key, e.target.value);
    }
    if (e.target.classList.contains('color-hex')) {
        const key = e.target.dataset.sync;
        let hex = e.target.value.replace(/[^0-9a-fA-F]/g,'').substring(0,6);
        e.target.value = hex.toUpperCase();
        if (hex.length === 6) {
            const full = '#' + hex;
            const swatch = document.querySelector('.color-swatch[data-sync="'+key+'"]');
            const preview = document.querySelector('.color-preview[data-sync="'+key+'"]');
            if (swatch) swatch.value = full;
            if (preview) preview.style.background = full;
            updateColorPreview(key, full);
        }
    }
});

function updateColorPreview(key, color) {
    // Update the live preview bar buttons/badges
    const map = {
        'primary':       () => { const el = document.getElementById('cpb-primary'); if(el) el.style.background = color; },
        'secondary':     () => { const el = document.getElementById('cpb-secondary'); if(el) el.style.background = color; },
        'success':       () => { const el = document.getElementById('cpb-success'); if(el) el.style.color = color; },
        'success_light': () => { const el = document.getElementById('cpb-success'); if(el) el.style.background = color; },
        'warning':       () => { const el = document.getElementById('cpb-warning'); if(el) el.style.color = color; },
        'warning_light': () => { const el = document.getElementById('cpb-warning'); if(el) el.style.background = color; },
        'danger':        () => { const el = document.getElementById('cpb-danger'); if(el) el.style.color = color; },
        'danger_light':  () => { const el = document.getElementById('cpb-danger'); if(el) el.style.background = color; },
        'info':          () => { const el = document.getElementById('cpb-info'); if(el) el.style.color = color; },
        'info_light':    () => { const el = document.getElementById('cpb-info'); if(el) el.style.background = color; },
    };
    if (map[key]) map[key]();
}

// ═══════════════════════════════════════════
// FONT PICKER
// ═══════════════════════════════════════════
const GOOGLE_FONTS = [
    // Popular
    {name:'Inter',url:'Inter:wght@300;400;500;600;700',cat:'popular'},
    {name:'Roboto',url:'Roboto:wght@300;400;500;700',cat:'popular'},
    {name:'Poppins',url:'Poppins:wght@300;400;500;600;700',cat:'popular'},
    {name:'Open Sans',url:'Open+Sans:wght@300;400;500;600;700',cat:'popular'},
    {name:'Lato',url:'Lato:wght@300;400;700',cat:'popular'},
    {name:'Montserrat',url:'Montserrat:wght@300;400;500;600;700',cat:'popular'},
    {name:'Nunito',url:'Nunito:wght@300;400;500;600;700',cat:'popular'},
    {name:'Raleway',url:'Raleway:wght@300;400;500;600;700',cat:'popular'},
    // Geometric / Modern
    {name:'DM Sans',url:'DM+Sans:wght@300;400;500;600;700',cat:'geometric'},
    {name:'Plus Jakarta Sans',url:'Plus+Jakarta+Sans:wght@300;400;500;600;700',cat:'geometric'},
    {name:'Outfit',url:'Outfit:wght@300;400;500;600;700',cat:'geometric'},
    {name:'Figtree',url:'Figtree:wght@300;400;500;600;700',cat:'geometric'},
    {name:'Manrope',url:'Manrope:wght@300;400;500;600;700',cat:'geometric'},
    {name:'Space Grotesk',url:'Space+Grotesk:wght@300;400;500;600;700',cat:'geometric'},
    {name:'Sora',url:'Sora:wght@300;400;500;600;700',cat:'geometric'},
    {name:'Urbanist',url:'Urbanist:wght@300;400;500;600;700',cat:'geometric'},
    {name:'Albert Sans',url:'Albert+Sans:wght@300;400;500;600;700',cat:'geometric'},
    {name:'Lexend',url:'Lexend:wght@300;400;500;600;700',cat:'geometric'},
    {name:'Red Hat Display',url:'Red+Hat+Display:wght@300;400;500;600;700',cat:'geometric'},
    {name:'Josefin Sans',url:'Josefin+Sans:wght@300;400;500;600;700',cat:'geometric'},
    {name:'Barlow',url:'Barlow:wght@300;400;500;600;700',cat:'geometric'},
    {name:'Jost',url:'Jost:wght@300;400;500;600;700',cat:'geometric'},
    // Humanist / Friendly
    {name:'Source Sans 3',url:'Source+Sans+3:wght@300;400;500;600;700',cat:'humanist'},
    {name:'Work Sans',url:'Work+Sans:wght@300;400;500;600;700',cat:'humanist'},
    {name:'Noto Sans',url:'Noto+Sans:wght@300;400;500;600;700',cat:'humanist'},
    {name:'Rubik',url:'Rubik:wght@300;400;500;600;700',cat:'humanist'},
    {name:'Karla',url:'Karla:wght@300;400;500;600;700',cat:'humanist'},
    {name:'Cabin',url:'Cabin:wght@400;500;600;700',cat:'humanist'},
    {name:'Overpass',url:'Overpass:wght@300;400;500;600;700',cat:'humanist'},
    {name:'Hind',url:'Hind:wght@300;400;500;600;700',cat:'humanist'},
    {name:'Mulish',url:'Mulish:wght@300;400;500;600;700',cat:'humanist'},
    {name:'Libre Franklin',url:'Libre+Franklin:wght@300;400;500;600;700',cat:'humanist'},
    {name:'Assistant',url:'Assistant:wght@300;400;500;600;700',cat:'humanist'},
    {name:'IBM Plex Sans',url:'IBM+Plex+Sans:wght@300;400;500;600;700',cat:'humanist'},
    {name:'Exo 2',url:'Exo+2:wght@300;400;500;600;700',cat:'humanist'},
    {name:'Mukta',url:'Mukta:wght@300;400;500;600;700',cat:'humanist'},
    // Rounded / Soft
    {name:'Quicksand',url:'Quicksand:wght@300;400;500;600;700',cat:'rounded'},
    {name:'Comfortaa',url:'Comfortaa:wght@300;400;500;600;700',cat:'rounded'},
    {name:'Varela Round',url:'Varela+Round:wght@400',cat:'rounded'},
    {name:'ABeeZee',url:'ABeeZee:wght@400',cat:'rounded'},
    {name:'Nunito Sans',url:'Nunito+Sans:wght@300;400;500;600;700',cat:'rounded'},
    // Display / Bold
    {name:'Oswald',url:'Oswald:wght@300;400;500;600;700',cat:'display'},
    {name:'Bebas Neue',url:'Bebas+Neue:wght@400',cat:'display'},
    {name:'Anton',url:'Anton:wght@400',cat:'display'},
    {name:'Archivo',url:'Archivo:wght@300;400;500;600;700',cat:'display'},
    {name:'Titillium Web',url:'Titillium+Web:wght@300;400;600;700',cat:'display'},
    {name:'Saira',url:'Saira:wght@300;400;500;600;700',cat:'display'},
    {name:'Lexend Deca',url:'Lexend+Deca:wght@300;400;500;600;700',cat:'display'},
    // Serif
    {name:'Playfair Display',url:'Playfair+Display:wght@400;500;600;700',cat:'serif'},
    {name:'Merriweather',url:'Merriweather:wght@300;400;700',cat:'serif'},
    {name:'Lora',url:'Lora:wght@400;500;600;700',cat:'serif'},
    {name:'PT Serif',url:'PT+Serif:wght@400;700',cat:'serif'},
    {name:'Noto Serif',url:'Noto+Serif:wght@400;500;600;700',cat:'serif'},
    {name:'Source Serif 4',url:'Source+Serif+4:wght@300;400;500;600;700',cat:'serif'},
    {name:'Libre Baskerville',url:'Libre+Baskerville:wght@400;700',cat:'serif'},
    {name:'Crimson Text',url:'Crimson+Text:wght@400;600;700',cat:'serif'},
    {name:'EB Garamond',url:'EB+Garamond:wght@400;500;600;700',cat:'serif'},
    {name:'Cormorant Garamond',url:'Cormorant+Garamond:wght@300;400;500;600;700',cat:'serif'},
    {name:'DM Serif Display',url:'DM+Serif+Display:wght@400',cat:'serif'},
    {name:'Bitter',url:'Bitter:wght@300;400;500;600;700',cat:'serif'},
];
const GOOGLE_MONO_FONTS = [
    {name:'JetBrains Mono',url:'JetBrains+Mono:wght@400;500;600;700',cat:'mono'},
    {name:'Fira Code',url:'Fira+Code:wght@400;500;600;700',cat:'mono'},
    {name:'Source Code Pro',url:'Source+Code+Pro:wght@400;500;600;700',cat:'mono'},
    {name:'IBM Plex Mono',url:'IBM+Plex+Mono:wght@400;500;600;700',cat:'mono'},
    {name:'Roboto Mono',url:'Roboto+Mono:wght@400;500;600;700',cat:'mono'},
    {name:'Ubuntu Mono',url:'Ubuntu+Mono:wght@400;700',cat:'mono'},
    {name:'Space Mono',url:'Space+Mono:wght@400;700',cat:'mono'},
    {name:'Inconsolata',url:'Inconsolata:wght@300;400;500;600;700',cat:'mono'},
    {name:'Red Hat Mono',url:'Red+Hat+Mono:wght@400;500;600;700',cat:'mono'},
    {name:'DM Mono',url:'DM+Mono:wght@400;500',cat:'mono'},
    {name:'Overpass Mono',url:'Overpass+Mono:wght@400;500;600;700',cat:'mono'},
    {name:'Noto Sans Mono',url:'Noto+Sans+Mono:wght@400;500;600;700',cat:'mono'},
];
const FONT_CATEGORIES = {popular:'Popular',geometric:'Modern / Geometric',humanist:'Humanist',rounded:'Rounded',display:'Display',serif:'Serif',mono:'Monospace'};
function fontSlug(name) { return 'fpf-' + name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, ''); }

const FontPicker = {
    init() {
        console.log('[FontPicker] Init — Lazy batch loading (8 fonts per batch)');
        console.log('[FontPicker] Total sans:', GOOGLE_FONTS.length, '| mono:', GOOGLE_MONO_FONTS.length);
    },
    toggle(selectEl) {
        const field = selectEl.closest('.font-field');
        const dropdown = field.querySelector('.font-dropdown');
        const isOpen = dropdown.style.display !== 'none';
        document.querySelectorAll('.font-dropdown').forEach(d => d.style.display = 'none');
        document.querySelectorAll('.font-select').forEach(s => s.classList.remove('open'));
        if (!isOpen) {
            dropdown.style.display = 'block';
            selectEl.classList.add('open');
            this.populate(field, 'all');
        }
    },
    populate(field, cat) {
        const dropdown = field.querySelector('.font-dropdown');
        const currentValue = field.querySelector('.font-value').value;
        const isMono = field.dataset.field === 'font_mono';
        const fonts = isMono ? GOOGLE_MONO_FONTS : GOOGLE_FONTS;
        const fallback = isMono ? 'monospace' : 'sans-serif';
        const BATCH = 8;

        // Category tabs (sans-serif picker only)
        let tabsHtml = '';
        if (!isMono) {
            const cats = ['all','popular','geometric','humanist','rounded','display','serif'];
            const catLabels = {all:'All',popular:'★ Popular',geometric:'Modern',humanist:'Humanist',rounded:'Rounded',display:'Display',serif:'Serif'};
            tabsHtml = '<div class="fp-tabs">';
            cats.forEach(c => {
                const active = c === cat ? ' active' : '';
                const count = c === 'all' ? fonts.length : fonts.filter(f => f.cat === c).length;
                tabsHtml += '<button type="button" class="fp-tab' + active + '" onclick="FontPicker.filterCat(this,\'' + c + '\')">' + catLabels[c] + ' <small>' + count + '</small></button>';
            });
            tabsHtml += '</div>';
        }

        // Filter by category
        const filtered = cat === 'all' ? fonts : fonts.filter(f => f.cat === cat);

        // Store on field for Load More
        field._fpFiltered = filtered;
        field._fpShown = 0;
        field._fpCat = cat;

        dropdown.innerHTML = tabsHtml + '<div class="fp-list" id="fpList-' + (isMono?'mono':'sans') + '"></div>'
            + '<div class="fp-bottom" id="fpBottom-' + (isMono?'mono':'sans') + '"></div>';

        // Auto-load more on scroll near bottom
        const listEl = document.getElementById('fpList-' + (isMono?'mono':'sans'));
        listEl.addEventListener('scroll', () => {
            if (listEl.scrollTop + listEl.clientHeight >= listEl.scrollHeight - 60) {
                const remaining = field._fpFiltered.length - field._fpShown;
                if (remaining > 0 && !field._fpLoading) {
                    field._fpLoading = true;
                    this.loadBatch(field, 8);
                }
            }
        });

        // Reset keyboard highlight index
        field._fpHighlight = -1;

        // Load first batch
        this.loadBatch(field, BATCH);
    },
    loadBatch(field, count) {
        const isMono = field.dataset.field === 'font_mono';
        const key = isMono ? 'mono' : 'sans';
        const list = document.getElementById('fpList-' + key);
        const bottom = document.getElementById('fpBottom-' + key);
        const filtered = field._fpFiltered;
        const currentValue = field.querySelector('.font-value').value;
        const fallback = isMono ? 'monospace' : 'sans-serif';
        const cat = field._fpCat;
        const start = field._fpShown;
        const end = Math.min(start + count, filtered.length);
        const batch = filtered.slice(start, end);

        if (batch.length === 0) return;

        // Show loading placeholder
        const loadingId = 'fp-batch-loading-' + start;
        list.insertAdjacentHTML('beforeend', '<div id="' + loadingId + '" class="fp-batch-loading"><i class="fas fa-circle-notch fa-spin"></i> Loading ' + batch.length + ' fonts...</div>');

        // Step 1: Load Google Font stylesheet
        const batchUrls = batch.map(f => f.url).join('&family=');
        const link = document.createElement('link');
        link.href = 'https://fonts.googleapis.com/css2?family=' + batchUrls + '&display=swap';
        link.rel = 'stylesheet';
        console.log('[FontPicker] Loading batch', Math.floor(start/count)+1, ':', batch.map(f=>f.name).join(', '));

        // Step 2: Wait for stylesheet to load, THEN trigger font file downloads
        link.onload = () => {
            console.log('[FontPicker] Stylesheet loaded, triggering font downloads...');

            // Step 3: Force browser to download actual font files
            const fontPromises = batch.map(f => {
                return document.fonts.load('18px "' + f.name + '"').catch(err => {
                    console.warn('[FontPicker] Failed to load:', f.name, err);
                    return null;
                });
            });

            Promise.all(fontPromises).then(() => {
                const loadingEl = document.getElementById(loadingId);
                if (loadingEl) loadingEl.remove();

                let lastCat = start > 0 ? (filtered[start-1]?.cat || '') : '';
                let html = '';
                batch.forEach(f => {
                    if (cat === 'all' && f.cat !== lastCat) {
                        lastCat = f.cat;
                        html += '<div class="fp-cat-label">' + (FONT_CATEGORIES[f.cat] || f.cat) + '</div>';
                    }
                    const sel = f.name === currentValue ? 'selected' : '';
                    const weights = (f.url.match(/wght@([\d;]+)/)||[,''])[1].split(';').length;
                    const isLoaded = document.fonts.check('18px "' + f.name + '"');
                    html += '<div class="font-option ' + sel + '" data-font="' + f.name + '" data-cat="' + f.cat + '" onclick="FontPicker.select(this,\'' + f.name.replace(/'/g,"\\'") + '\')">';
                    html += '<div class="fo-left"><span class="fo-name">' + (sel ? '✓ ' : '') + f.name + '</span>';
                    html += '<span class="fo-meta">' + weights + ' wt · ' + (FONT_CATEGORIES[f.cat]||'');
                    html += (isLoaded ? ' · <span style="color:green">✓</span>' : ' · <span style="color:orange">✗</span>');
                    html += '</span></div>';
                    html += '<span class="fo-preview" data-fontname="' + f.name + '" data-fallback="' + fallback + '">Aa Bb Cc 123</span>';
                    html += '</div>';
                });
                list.insertAdjacentHTML('beforeend', html);

                // Force font-family via DOM
                list.querySelectorAll('.fo-preview[data-fontname]').forEach(span => {
                    if (!span._fontApplied) {
                        span.style.setProperty('font-family', "'" + span.dataset.fontname + "', " + span.dataset.fallback, 'important');
                        span._fontApplied = true;
                    }
                });

                // Debug: verify computed styles
                const previews = list.querySelectorAll('.fo-preview[data-fontname]');
                const lastFew = Array.from(previews).slice(-Math.min(3, batch.length));
                lastFew.forEach(p => {
                    const comp = getComputedStyle(p).fontFamily;
                    const check = document.fonts.check('18px "' + p.dataset.fontname + '"');
                    console.log('[FontPicker] ' + p.dataset.fontname + ': computed=' + comp + ', check=' + check);
                });

                field._fpShown = end;
                field._fpLoading = false;
                const remaining = filtered.length - end;
                if (remaining > 0) {
                    bottom.innerHTML = '<button type="button" class="fp-load-more" onclick="FontPicker.loadMore(this)">'
                        + '<i class="fas fa-chevron-down"></i> Load More (' + remaining + ' remaining)</button>'
                        + '<div class="fp-count">' + end + ' / ' + filtered.length + ' fonts</div>';
                } else {
                    bottom.innerHTML = '<div class="fp-count">All ' + filtered.length + ' fonts loaded</div>';
                }
            });
        };

        link.onerror = () => {
            console.error('[FontPicker] Failed to load stylesheet for batch');
            const loadingEl = document.getElementById(loadingId);
            if (loadingEl) loadingEl.innerHTML = '<span style="color:red">Failed to load fonts</span>';
        };

        // Append link AFTER setting onload (ensure event fires)
        document.head.appendChild(link);
    },
    loadMore(btn) {
        const field = btn.closest('.font-field');
        this.loadBatch(field, 8);
    },
    onKey(e, input) {
        const field = input.closest('.font-field');
        const dropdown = field.querySelector('.font-dropdown');
        if (dropdown.style.display === 'none') return;

        const isMono = field.dataset.field === 'font_mono';
        const key = isMono ? 'mono' : 'sans';
        const list = document.getElementById('fpList-' + key);
        if (!list) return;

        const options = list.querySelectorAll('.font-option');
        if (!options.length) return;

        let idx = field._fpHighlight ?? -1;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            idx = Math.min(idx + 1, options.length - 1);
            this.highlightAt(field, list, options, idx);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            idx = Math.max(idx - 1, 0);
            this.highlightAt(field, list, options, idx);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (idx >= 0 && idx < options.length) {
                const fontName = options[idx].dataset.font;
                this.select(options[idx], fontName);
            }
        }
    },
    highlightAt(field, list, options, idx) {
        // Remove old highlight
        list.querySelectorAll('.font-option.fp-highlight').forEach(el => el.classList.remove('fp-highlight'));

        // Set new highlight
        field._fpHighlight = idx;
        const opt = options[idx];
        if (!opt) return;
        opt.classList.add('fp-highlight');

        // Scroll into view
        opt.scrollIntoView({ block: 'nearest', behavior: 'smooth' });

        // Auto-load more if near bottom
        if (idx >= options.length - 2) {
            const remaining = (field._fpFiltered?.length || 0) - (field._fpShown || 0);
            if (remaining > 0 && !field._fpLoading) {
                field._fpLoading = true;
                this.loadBatch(field, 8);
            }
        }

        // Live preview — update the sample area below
        const fontName = opt.dataset.font;
        const isMono = field.dataset.field === 'font_mono';
        const fallback = isMono ? 'monospace' : 'sans-serif';
        const preview = field.querySelector('.font-preview');
        if (preview) {
            preview.style.setProperty('font-family', "'" + fontName + "', " + fallback, 'important');
        }
        // Update search input to show current selection
        field.querySelector('.font-search').value = fontName;
    },
    filterCat(btn, cat) {
        const field = btn.closest('.font-field');
        field.querySelectorAll('.fp-tab').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
        this.populate(field, cat);
    },
    select(optionEl, fontName) {
        const field = optionEl.closest('.font-field');
        const isMono = field.dataset.field === 'font_mono';
        const fallback = isMono ? 'monospace' : 'sans-serif';
        field.querySelector('.font-value').value = fontName;
        field.querySelector('.font-search').value = fontName;
        field._fpHighlight = -1;
        const preview = field.querySelector('.font-preview');
        if (preview) {
            preview.style.setProperty('font-family', "'" + fontName + "', " + fallback, 'important');
            // Update preview weights based on available weights
            const fonts = isMono ? GOOGLE_MONO_FONTS : GOOGLE_FONTS;
            const font = fonts.find(f => f.name === fontName);
            if (font) {
                const weightStr = (font.url.match(/wght@([\d;]+)/)||[,''])[1];
                const weights = weightStr.split(';').map(Number);
                const weightNames = {300:'Light',400:'Regular',500:'Medium',600:'SemiBold',700:'Bold'};
                const wpHtml = weights.map(w => '<span style="font-weight:'+w+'">' + (weightNames[w]||w) + ' ' + w + '</span>').join('');
                const wp = preview.querySelector('.fp-weights');
                if (wp) wp.innerHTML = wpHtml;
            }
        }
        field.querySelector('.font-dropdown').style.display = 'none';
        field.querySelector('.font-select').classList.remove('open');
    },
    filter(input) {
        const q = input.value.toLowerCase();
        const field = input.closest('.font-field');
        const dropdown = field.querySelector('.font-dropdown');
        if (dropdown.style.display === 'none') {
            dropdown.style.display = 'block';
            input.closest('.font-select').classList.add('open');
        }
        
        if (!q) {
            // Empty search — repopulate with batch
            this.populate(field, field._fpCat || 'all');
            return;
        }

        // Search all fonts, show all matches (load their CSS)
        const isMono = field.dataset.field === 'font_mono';
        const fonts = isMono ? GOOGLE_MONO_FONTS : GOOGLE_FONTS;
        const fallback = isMono ? 'monospace' : 'sans-serif';
        const currentValue = field.querySelector('.font-value').value;
        const matches = fonts.filter(f => f.name.toLowerCase().includes(q));
        const key = isMono ? 'mono' : 'sans';

        // Load matching font CSS
        if (matches.length > 0 && matches.length <= 20) {
            const batchUrls = matches.map(f => f.url).join('&family=');
            const link = document.createElement('link');
            link.href = 'https://fonts.googleapis.com/css2?family=' + batchUrls + '&display=swap';
            link.rel = 'stylesheet';
            document.head.appendChild(link);
        }

        // Build results
        let list = document.getElementById('fpList-' + key);
        let bottom = document.getElementById('fpBottom-' + key);
        if (!list) {
            // Tabs haven't been created yet
            this.populate(field, 'all');
            list = document.getElementById('fpList-' + key);
            bottom = document.getElementById('fpBottom-' + key);
        }

        let html = '';
        matches.forEach(f => {
            const sel = f.name === currentValue ? 'selected' : '';
            const weights = (f.url.match(/wght@([\d;]+)/)||[,''])[1].split(';').length;
            html += '<div class="font-option ' + sel + '" data-font="' + f.name + '" data-cat="' + f.cat + '" onclick="FontPicker.select(this,\'' + f.name.replace(/'/g,"\\'") + '\')">';
            html += '<div class="fo-left"><span class="fo-name">' + (sel ? '✓ ' : '') + f.name + '</span>';
            html += '<span class="fo-meta">' + weights + ' weights · ' + (FONT_CATEGORIES[f.cat]||'') + '</span></div>';
            html += '<span class="fo-preview" data-fontname="' + f.name + '" data-fallback="' + fallback + '">Aa Bb Cc 123</span>';
            html += '</div>';
        });

        list.innerHTML = html || '<div style="padding:30px;text-align:center;color:var(--text-faint)"><i class="fas fa-search" style="font-size:24px;margin-bottom:8px;display:block"></i>No fonts match "' + q + '"</div>';

        // Force-set font-family via JS DOM
        list.querySelectorAll('.fo-preview[data-fontname]').forEach(span => {
            span.style.setProperty('font-family', "'" + span.dataset.fontname + "', " + span.dataset.fallback, 'important');
        });
        bottom.innerHTML = '<div class="fp-count">' + matches.length + ' results</div>';
    }
};

document.addEventListener('DOMContentLoaded', () => FontPicker.init());

// Debug: Check font loading — run FontDebug() in console
window.FontDebug = function() {
    const allFonts = GOOGLE_FONTS.concat(GOOGLE_MONO_FONTS);
    const loaded = [], notLoaded = [];
    allFonts.forEach(f => {
        if (document.fonts.check('16px "' + f.name + '"')) {
            loaded.push(f.name);
        } else {
            notLoaded.push(f.name);
        }
    });
    console.log('✅ Loaded:', loaded.length, loaded);
    console.log('❌ Not loaded:', notLoaded.length, notLoaded);
    // Check link tags
    const fontLinks = document.querySelectorAll('link[href*="fonts.googleapis"]');
    console.log('📎 Font link tags:', fontLinks.length);
    fontLinks.forEach((l,i) => {
        const families = (l.href.match(/family=([^&]+)/g)||[]).length;
        console.log('  Link', i+1, ':', families, 'families,', l.href.substring(0,120)+'...');
    });
    return {loaded: loaded.length, total: allFonts.length, notLoaded};
};
document.addEventListener('click', (e) => {
    if (!e.target.closest('.font-field')) {
        document.querySelectorAll('.font-dropdown').forEach(d=>d.style.display='none');
        document.querySelectorAll('.font-select').forEach(s=>s.classList.remove('open'));
    }
});

// ═══════════════════════════════════════════
// IMAGE UPLOAD — AJAX
// ═══════════════════════════════════════════
async function uploadImage(file, key) {
    if (!file) return;
    if (file.size > 2 * 1024 * 1024) { alert('File too large. Max 2MB.'); return; }

    const formData = new FormData();
    formData.append('file', file);
    formData.append('key', key);

    try {
        const res = await fetch("{{ route('admin.settings.configuration.upload') }}", {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF },
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            // Refresh the page to show the new image
            window.location.reload();
        } else {
            alert(data.message || 'Upload failed.');
        }
    } catch(e) { alert('Upload error: ' + e.message); }
}

function handleDrop(event, key) {
    const file = event.dataTransfer.files[0];
    if (file) uploadImage(file, key);
}

async function removeImage(key) {
    if (!confirm('Remove this image?')) return;
    try {
        const res = await fetch("{{ route('admin.settings.configuration.remove-image') }}", {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
            body: JSON.stringify({ key: key })
        });
        const data = await res.json();
        if (data.success) window.location.reload();
        else alert(data.message || 'Remove failed.');
    } catch(e) { alert('Error: ' + e.message); }
}
</script>
@endpush
