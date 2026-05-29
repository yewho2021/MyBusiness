INSERT INTO `tbl_changelog` (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`) VALUES 
('apps', '1.2.0', 'SaaS Dashboard Layout', 
'### Added (Major — Full Layout Overhaul)
- **Layout system**: 4 new Blade partials (app, menu_left, menu_upper, menu_footer)
- **Sidebar**: Professional dark sidebar with 9 collapsible menu sections
- **Top bar**: Breadcrumb, notifications, wallet balance, profile dropdown with company info
- **Dashboard**: Quick action cards (Add Product, Invite Agent, View Orders, Reports)
- **40+ routes**: All menu items wired with placeholder Coming Soon pages
- **Responsive**: Mobile sidebar toggle with overlay

### Sections
1. Dashboard
2. Products (All, Add, Categories, Attributes, Stock)
3. Agent Network (All, Pending, Invite, Commission, Performance, Payouts)
4. Orders (All, Pending, Processing, Completed, Cancelled, Refunds)
5. Commission & Wallet (Ledger, Payable, History, Withdrawals)
6. Finance & e-Invoice (Invoices, e-Invoice, SST, Tax, LHDN)
7. Company Profile (Info, Verification, Bank, Tax, Branding)
8. Reports (Sales, Agent, Product, Commission)
9. Settings (General, Notifications, Payment, API, Security)', 
'{"layout_files":["app.blade.php","menu_left.blade.php","menu_upper.blade.php","menu_footer.blade.php"],"total_routes":40,"sections":9}', 
'2026-02-23 11:30:00');
