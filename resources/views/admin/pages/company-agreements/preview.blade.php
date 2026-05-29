<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $agreement->title }} — v{{ $agreement->version }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; background: #f5f5f5; color: #333; line-height: 1.7; }
        .container { max-width: 800px; margin: 2rem auto; background: #fff; padding: 3rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 2px solid #eee; }
        .header h1 { font-size: 1.5rem; color: #1a1a1a; margin-bottom: 0.25rem; }
        .header .meta { font-size: 0.85rem; color: #888; }
        .content { font-size: 0.95rem; }
        .content h1, .content h2, .content h3 { margin: 1.5rem 0 0.75rem; color: #1a1a1a; }
        .content p { margin-bottom: 1rem; }
        .content ul, .content ol { margin: 0.5rem 0 1rem 1.5rem; }
        .content li { margin-bottom: 0.35rem; }
        .badge { display: inline-block; padding: 0.15rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; }
        .badge--active { background: #d4edda; color: #155724; }
        .badge--inactive { background: #f8d7da; color: #721c24; }
        @media print { body { background: #fff; } .container { box-shadow: none; margin: 0; padding: 1.5rem; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $agreement->title }}</h1>
            <div class="meta">
                Version {{ $agreement->version }}
                &bull; Created {{ $agreement->created_at?->format('d M Y') }}
                &bull; <span class="badge badge--{{ $agreement->is_active ? 'active' : 'inactive' }}">{{ $agreement->is_active ? 'Active' : 'Inactive' }}</span>
            </div>
        </div>
        <div class="content">
            {!! $agreement->content !!}
        </div>
    </div>
</body>
</html>
