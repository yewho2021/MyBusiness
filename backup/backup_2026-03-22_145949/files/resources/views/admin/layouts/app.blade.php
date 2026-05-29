<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Admin Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --sidebar-width: 260px;
            --header-height: 60px;
            --primary-color: #2563eb;
            --sidebar-bg: #111111;
            --sidebar-hover: #1a1a1a;
            --sidebar-active: #dc2626;
            --text-muted: #6b7280;
        }
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; min-height: 100vh; }
        .admin-wrapper { display: flex; min-height: 100vh; }
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            z-index: 1000;
            overflow-y: auto;
        }
        .main-wrapper {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .header {
            height: var(--header-height);
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .main-content { flex: 1; padding: 24px; }
        .footer {
            height: 50px;
            background: #fff;
            border-top: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            font-size: 13px;
            color: #64748b;
        }
        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); transition: transform 0.3s ease; }
            .sidebar.show { transform: translateX(0); }
            .main-wrapper { margin-left: 0; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="admin-wrapper">
        @include('admin.partials.menu_left')
        <div class="main-wrapper">
            @include('admin.partials.menu_upper')
            <main class="main-content">
                @yield('content')
            </main>
            @include('admin.partials.menu_footer')
        </div>
    </div>
    <script>
        document.querySelectorAll('.nav-link.has-submenu').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const submenu = this.nextElementSibling;
                this.classList.toggle('expanded');
                submenu.classList.toggle('show');
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
