<?php
    // Use singleton from middleware (Item #8)
    $currentAdmin = request()->attributes->get('admin');
    if (!$currentAdmin) {
        $adminId = request()->cookie('admin_id');
        try { $adminId = decrypt($adminId); } catch (\Exception $e) { if (!is_numeric($adminId)) $adminId = null; }
        $currentAdmin = $adminId ? \App\Models\Admin::with('role')->find($adminId) : null;
    }
?>

<header class="header">
    <div class="header-left">
        <button class="hamburger" onclick="openSidebar()" aria-label="Open menu"><i class="fas fa-bars"></i></button>
        <h1 class="page-title"><?php echo $__env->yieldContent('title', 'Dashboard'); ?></h1>
    </div>
    <div class="header-right">
        
        <button class="search-trigger" onclick="SpotlightSearch.open()" title="Search (Ctrl+K)">
            <i class="fas fa-search"></i>
            <span class="search-hint">Ctrl+K</span>
        </button>

        <div class="user-dropdown">
            <button class="user-btn" id="userDropdownBtn">
                <div class="user-avatar"><?php echo e($currentAdmin ? strtoupper(substr($currentAdmin->name, 0, 1)) : 'A'); ?></div>
                <div class="user-info">
                    <div class="user-name"><?php echo e($currentAdmin->name ?? 'Admin'); ?></div>
                    <div class="user-role"><?php echo e($currentAdmin->role->name ?? 'User'); ?></div>
                </div>
            </button>
            <div class="dropdown-menu" id="userDropdownMenu">
                <a href="<?php echo e(route('admin.profile.index')); ?>" class="dropdown-item">
                    <i class="fas fa-user-circle"></i> My Profile
                </a>
                <div style="border-top:1px solid var(--border-light);margin:4px 0;"></div>
                <form action="<?php echo e(route('admin.logout')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="dropdown-item" style="color:var(--c-primary-hover);">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

<style>
    .header-left { display: flex; align-items: center; }
    .page-title { font-size: 18px; font-weight: 600; color: var(--header-text, var(--text-heading)); }
    .header-right { display: flex; align-items: center; }
    .user-dropdown { position: relative; }
    .user-btn { display: flex; align-items: center; gap: 10px; padding: 6px 12px; background: var(--table-header-bg); border: 1px solid var(--border-color, var(--border-color)); border-radius: 10px; cursor: pointer; }
    .user-avatar { width: 36px; height: 36px; background: var(--header-avatar-bg, var(--c-danger)); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 600; }
    .user-info { text-align: left; }
    .user-name { font-size: 13px; font-weight: 600; color: var(--header-text, var(--text-heading)); }
    .user-role { font-size: 11px; color: var(--text-muted); }
    .dropdown-menu { position: absolute; top: 100%; right: 0; margin-top: 8px; background: #fff; border: 1px solid var(--border-color, var(--border-color)); border-radius: 8px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); display: none; min-width: 170px; z-index:200; }
    .dropdown-menu.show { display: block; }
    .dropdown-item { display: flex; align-items: center; gap: 8px; padding: 12px 16px; color: var(--text-secondary); border: none; background: none; width: 100%; cursor: pointer; font-size: 14px; text-decoration:none; }
    .dropdown-item:hover { background: var(--table-header-bg); }
</style>

<script>
    document.getElementById('userDropdownBtn').addEventListener('click', function(e) {
        e.stopPropagation();
        document.getElementById('userDropdownMenu').classList.toggle('show');
    });
    document.addEventListener('click', function() {
        document.getElementById('userDropdownMenu').classList.remove('show');
    });
</script>


<div id="spotlightOverlay" class="spotlight-overlay" onclick="SpotlightSearch.close()">
    <div class="spotlight-modal" onclick="event.stopPropagation()">
        <div class="spotlight-input-wrap">
            <i class="fas fa-search spotlight-icon"></i>
            <input type="text" id="spotlightInput" class="spotlight-input" placeholder="Search pages, admins, tables, config..." autocomplete="off" spellcheck="false">
            <kbd class="spotlight-esc">ESC</kbd>
        </div>
        <div id="spotlightResults" class="spotlight-results"></div>
    </div>
</div>

<style>
    .search-trigger{display:flex;align-items:center;gap:8px;padding:7px 14px;background:var(--table-header-bg);border:1px solid var(--border-color,var(--border-color));border-radius:8px;cursor:pointer;margin-right:12px;color:var(--text-muted);font-size:13px;transition:all .15s}
    .search-trigger:hover{background:var(--border-light);border-color:var(--hover-border);color:var(--text-heading)}
    .search-trigger i{font-size:13px}
    .search-hint{font-size:11px;padding:2px 6px;background:var(--border-color);border-radius:4px;color:var(--text-muted);font-family:monospace}
    .spotlight-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:flex-start;justify-content:center;padding-top:min(20vh,160px)}
    .spotlight-overlay.show{display:flex}
    .spotlight-modal{background:#fff;border-radius:14px;width:100%;max-width:580px;box-shadow:0 20px 60px rgba(0,0,0,.25);overflow:hidden}
    .spotlight-input-wrap{display:flex;align-items:center;padding:0 18px;border-bottom:1px solid var(--border-light)}
    .spotlight-icon{color:var(--text-faint);font-size:16px;margin-right:12px}
    .spotlight-input{flex:1;padding:16px 0;border:none;outline:none;font-size:16px;color:var(--text-heading);background:transparent}
    .spotlight-input::placeholder{color:var(--hover-border)}
    .spotlight-esc{font-size:10px;padding:3px 6px;background:var(--border-light);border:1px solid var(--border-color);border-radius:4px;color:var(--text-faint);font-family:monospace}
    .spotlight-results{max-height:360px;overflow-y:auto}
    .spotlight-results:empty{display:none}
    .spotlight-group{padding:6px 18px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-faint);background:var(--hover-bg)}
    .spotlight-item{display:flex;align-items:center;gap:12px;padding:10px 18px;cursor:pointer;text-decoration:none;color:var(--text-heading);transition:background .1s}
    .spotlight-item:hover,.spotlight-item.active{background:var(--border-light)}
    .spotlight-item-icon{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0}
    .spotlight-item-icon.page{background:var(--c-secondary-light);color:var(--c-secondary)}
    .spotlight-item-icon.admin{background:var(--c-success-light);color:var(--c-success)}
    .spotlight-item-icon.table{background:var(--c-purple-light);color:var(--c-purple)}
    .spotlight-item-icon.changelog{background:var(--c-warning-light);color:var(--c-warning)}
    .spotlight-item-icon.config{background:var(--table-header-bg);color:var(--text-muted)}
    .spotlight-item-text{flex:1;min-width:0}
    .spotlight-item-title{font-size:14px;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .spotlight-item-meta{font-size:11px;color:var(--text-faint);margin-top:1px}
    .spotlight-empty{padding:30px;text-align:center;color:var(--text-faint);font-size:14px}
    .spotlight-loading{padding:20px;text-align:center;color:var(--text-faint);font-size:13px}
</style>

<script>
const SpotlightSearch = {
    timer: null,
    activeIdx: -1,

    open() {
        document.getElementById('spotlightOverlay').classList.add('show');
        const input = document.getElementById('spotlightInput');
        input.value = '';
        input.focus();
        document.getElementById('spotlightResults').innerHTML = '';
        this.activeIdx = -1;
    },

    close() {
        document.getElementById('spotlightOverlay').classList.remove('show');
    },

    async search(q) {
        const results = document.getElementById('spotlightResults');
        if (q.length < 2) { results.innerHTML = ''; return; }

        results.innerHTML = '<div class="spotlight-loading"><i class="fas fa-circle-notch fa-spin"></i> Searching...</div>';

        try {
            const searchUrl = '<?php echo e(url("global-search")); ?>';
            const res = await fetch(searchUrl + '?q=' + encodeURIComponent(q), {
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            });
            const data = await res.json();
            this.render(data.results || []);
        } catch(e) {
            results.innerHTML = '<div class="spotlight-empty">Search failed</div>';
        }
    },

    render(items) {
        const results = document.getElementById('spotlightResults');
        if (!items.length) {
            results.innerHTML = '<div class="spotlight-empty"><i class="fas fa-search" style="font-size:20px;display:block;margin-bottom:8px"></i>No results found</div>';
            return;
        }

        // Group by type
        const groups = {};
        const labels = {page:'Pages',admin:'Admins',table:'Tables',changelog:'Changelog',config:'Settings'};
        items.forEach(item => {
            if (!groups[item.type]) groups[item.type] = [];
            groups[item.type].push(item);
        });

        let html = '';
        let idx = 0;
        for (const [type, typeItems] of Object.entries(groups)) {
            html += `<div class="spotlight-group">${labels[type] || type}</div>`;
            typeItems.forEach(item => {
                html += `<a href="${item.url}" class="spotlight-item" data-idx="${idx}" onmouseenter="SpotlightSearch.activeIdx=${idx};SpotlightSearch.highlight()">
                    <div class="spotlight-item-icon ${item.type}"><i class="${item.icon}"></i></div>
                    <div class="spotlight-item-text">
                        <div class="spotlight-item-title">${this.esc(item.title)}</div>
                        <div class="spotlight-item-meta">${this.esc(item.meta)}</div>
                    </div>
                    <i class="fas fa-arrow-right" style="color:var(--hover-border);font-size:11px"></i>
                </a>`;
                idx++;
            });
        }
        results.innerHTML = html;
        this.activeIdx = -1;
    },

    highlight() {
        document.querySelectorAll('.spotlight-item').forEach((el, i) => {
            el.classList.toggle('active', i === this.activeIdx);
        });
    },

    navigate(dir) {
        const items = document.querySelectorAll('.spotlight-item');
        if (!items.length) return;
        this.activeIdx = Math.max(-1, Math.min(items.length - 1, this.activeIdx + dir));
        if (this.activeIdx < 0) this.activeIdx = items.length - 1;
        if (this.activeIdx >= items.length) this.activeIdx = 0;
        this.highlight();
        items[this.activeIdx]?.scrollIntoView({block:'nearest'});
    },

    go() {
        const items = document.querySelectorAll('.spotlight-item');
        if (this.activeIdx >= 0 && items[this.activeIdx]) {
            window.location.href = items[this.activeIdx].href;
        }
    },

    esc(s) {
        const d = document.createElement('div'); d.textContent = s; return d.innerHTML;
    }
};

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl+K or Cmd+K to open
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        SpotlightSearch.open();
    }
    // ESC to close
    if (e.key === 'Escape' && document.getElementById('spotlightOverlay').classList.contains('show')) {
        SpotlightSearch.close();
    }
});

// Input handler with debounce
document.getElementById('spotlightInput').addEventListener('input', function(e) {
    clearTimeout(SpotlightSearch.timer);
    SpotlightSearch.timer = setTimeout(() => SpotlightSearch.search(e.target.value), 250);
});

// Arrow keys + Enter in search
document.getElementById('spotlightInput').addEventListener('keydown', function(e) {
    if (e.key === 'ArrowDown') { e.preventDefault(); SpotlightSearch.navigate(1); }
    if (e.key === 'ArrowUp') { e.preventDefault(); SpotlightSearch.navigate(-1); }
    if (e.key === 'Enter') { e.preventDefault(); SpotlightSearch.go(); }
});
</script>
<?php /**PATH /home/mybusiness/office.mybusiness.com.my/resources/views/admin/partials/menu_upper.blade.php ENDPATH**/ ?>