<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview: {{ $template->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f5f5f5; color: #333; }
        .toolbar { background: #1a1a2e; color: #fff; padding: 0.75rem 1.5rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem; }
        .toolbar .meta span { margin-right: 1.5rem; }
        .toolbar code { background: rgba(255,255,255,0.15); padding: 0.15rem 0.5rem; border-radius: 3px; }
        .preview { max-width: 700px; margin: 2rem auto; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }
        .subject-bar { background: #f8f9fa; padding: 1rem 1.5rem; border-bottom: 1px solid #eee; }
        .subject-bar strong { color: #555; }
        .content { padding: 2rem 1.5rem; line-height: 1.7; }
    </style>
</head>
<body>
    <div class="toolbar">
        <div class="meta">
            <span>Slug: <code>{{ $template->slug }}</code></span>
            <span>Scope: {{ $template->isGlobal() ? 'System Default' : ($template->company->company_name ?? '-') }}</span>
            <span>SMTP: {{ $template->smtp->name ?? 'Default' }}</span>
        </div>
        <div>
            @if($template->email_to)<span>To: {{ $template->email_to }}</span>@endif
            @if($template->email_cc)<span>CC: {{ $template->email_cc }}</span>@endif
        </div>
    </div>
    <div class="preview">
        <div class="subject-bar">
            <strong>Subject:</strong> {{ $template->subject }}
        </div>
        <div class="content">
            {!! $template->content !!}
        </div>
    </div>
</body>
</html>
