<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\TelegramReport;
use App\Services\TelegramReportBuilder;

class TelegramReportController extends Controller
{
    public function index()
    {
        return view('admin.pages.telegram.reports');
    }


    private function token(): string
    {
        $val = DB::table('tbl_telegram_config')->where('key_name', 'bot_token')->value('value');
        if ($val) { try { return \Illuminate\Support\Facades\Crypt::decryptString($val); } catch (\Throwable $e) { return $val; } }
        return '';
    }

    // ══════════════════════════════════════════════
    // LIST — all reports with target info
    // ══════════════════════════════════════════════
    public function list()
    {

        $reports = DB::table('tbl_telegram_reports as r')
            ->leftJoin('tbl_telegram_targets as t', 't.id', '=', 'r.target_id')
            ->select('r.*', 't.name as target_name', 't.type as target_type', 't.chat_id')
            ->orderBy('r.sort_order')->orderBy('r.name')
            ->get();

        $builder = new TelegramReportBuilder();
        $unregistered = $builder->getUnregistered();

        return response()->json(['reports' => $reports, 'unregistered' => $unregistered]);
    }

    // ══════════════════════════════════════════════
    // GET — single report for editor
    // ══════════════════════════════════════════════
    public function get(int $id)
    {

        $report = DB::table('tbl_telegram_reports as r')
            ->leftJoin('tbl_telegram_targets as t', 't.id', '=', 'r.target_id')
            ->select('r.*', 't.name as target_name')
            ->where('r.id', $id)->first();

        if (!$report) return response()->json(['success' => false, 'error' => 'Not found.'], 404);

        $targets = DB::table('tbl_telegram_targets')->orderBy('name')->get();
        return response()->json(['success' => true, 'report' => $report, 'targets' => $targets]);
    }

    // ══════════════════════════════════════════════
    // STORE — create new report
    // ══════════════════════════════════════════════
    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:100',
            'report_type' => 'required|in:code,template',
        ]);

        $slug = $request->input('slug', '');
        if (!$slug) $slug = \Illuminate\Support\Str::slug($request->name, '_');

        // Ensure unique slug
        $baseSlug = $slug;
        $i = 1;
        while (DB::table('tbl_telegram_reports')->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '_' . $i++;
        }

        // Validate SQL for template type
        if ($request->report_type === 'template' && $request->filled('query')) {
            $builder = new TelegramReportBuilder();
            $safety = $builder->validateQuery($request->input('query'));
            if ($safety !== true) return response()->json(['success' => false, 'error' => "SQL blocked: {$safety}"]);
        }

        $report = TelegramReport::create([
            'name'           => $request->name,
            'slug'           => $slug,
            'report_type'    => $request->report_type,
            'icon'           => $request->input('icon', '📊'),
            'category'       => $request->input('category', 'custom'),
            'description'    => $request->input('description', ''),
            'query'          => $request->input('query'),
            'template'       => $request->input('template'),
            'computed_fields' => $request->input('computed_fields') ? json_decode($request->input('computed_fields'), true) : null,
            'default_params' => $request->input('default_params') ? json_decode($request->input('default_params'), true) : null,
            'target_id'      => $request->input('target_id') ?: null,
            'schedule_type'  => $request->input('schedule_type', 'manual'),
            'schedule_time'  => $request->input('schedule_time'),
            'schedule_day'   => $request->input('schedule_day'),
            'timezone'       => $request->input('timezone', 'Asia/Singapore'),
            'params'         => $request->input('params') ? json_decode($request->input('params'), true) : null,
            'enabled'        => $request->boolean('enabled', true),
            'is_system'      => false,
            'sort_order'     => (TelegramReport::max('sort_order') ?? 0) + 10,
        ]);

        return response()->json(['success' => true, 'report' => $report, 'message' => "Report '{$request->name}' created."]);
    }

    // ══════════════════════════════════════════════
    // UPDATE
    // ══════════════════════════════════════════════
    public function update(Request $request, int $id)
    {

        $report = TelegramReport::findOrFail($id);

        // Validate SQL for template type
        if (($request->input('report_type', $report->report_type) === 'template') && $request->filled('query')) {
            $builder = new TelegramReportBuilder();
            $safety = $builder->validateQuery($request->input('query'));
            if ($safety !== true) return response()->json(['success' => false, 'error' => "SQL blocked: {$safety}"]);
        }

        $fields = $request->only([
            'name', 'icon', 'category', 'description', 'report_type',
            'query', 'template', 'php_code',
            'target_id', 'schedule_type', 'schedule_time', 'schedule_day', 'timezone',
            'enabled',
        ]);

        // Handle JSON fields
        if ($request->has('computed_fields')) {
            $fields['computed_fields'] = $request->input('computed_fields') ? json_decode($request->input('computed_fields'), true) : null;
        }
        if ($request->has('params')) {
            $fields['params'] = $request->input('params') ? json_decode($request->input('params'), true) : null;
        }
        if ($request->has('default_params')) {
            $fields['default_params'] = $request->input('default_params') ? json_decode($request->input('default_params'), true) : null;
        }

        // Handle nullable target_id
        if (array_key_exists('target_id', $fields) && empty($fields['target_id'])) {
            $fields['target_id'] = null;
        }

        $report->update($fields);
        return response()->json(['success' => true, 'report' => $report->fresh(), 'message' => 'Saved.']);
    }

    // ══════════════════════════════════════════════
    // DELETE
    // ══════════════════════════════════════════════
    public function destroy(int $id)
    {

        $report = TelegramReport::findOrFail($id);
        if ($report->is_system) return response()->json(['success' => false, 'error' => 'Cannot delete system reports.']);
        $report->delete();
        return response()->json(['success' => true, 'message' => 'Deleted.']);
    }

    // ══════════════════════════════════════════════
    // CLONE
    // ══════════════════════════════════════════════
    public function clone(int $id)
    {

        $original = TelegramReport::findOrFail($id);
        $clone = $original->replicate();
        $clone->name = $original->name . ' (copy)';
        $clone->slug = $original->slug . '_' . time();
        $clone->is_system = false;
        $clone->enabled = false;
        $clone->last_sent_at = null;
        $clone->last_status = null;
        $clone->last_error = null;
        $clone->send_count = 0;
        $clone->fail_count = 0;
        $clone->consecutive_fails = 0;
        $clone->save();

        return response()->json(['success' => true, 'report' => $clone, 'message' => "Cloned as '{$clone->name}'."]);
    }

    // ══════════════════════════════════════════════
    // TOGGLE
    // ══════════════════════════════════════════════
    public function toggle(int $id)
    {

        $report = TelegramReport::findOrFail($id);
        $report->enabled = !$report->enabled;
        if ($report->enabled) $report->consecutive_fails = 0;
        $report->save();
        return response()->json(['success' => true, 'enabled' => $report->enabled]);
    }

    // ══════════════════════════════════════════════
    // PREVIEW
    // ══════════════════════════════════════════════
    public function preview(Request $request, int $id)
    {

        $report = TelegramReport::findOrFail($id);
        $builder = new TelegramReportBuilder();

        // Diagnostic: check if method is registered
        $availableSlugs = $builder->availableSlugs();
        if ($report->report_type === 'code' && !in_array($report->slug, $availableSlugs)) {
            return response()->json([
                'success' => false,
                'text' => "❌ METHOD NOT FOUND\n\n"
                    . "Slug: '{$report->slug}'\n"
                    . "This slug is NOT registered in TelegramReportBuilder::\$reports array.\n\n"
                    . "Available slugs:\n" . implode(', ', $availableSlugs) . "\n\n"
                    . "FIX: Open app/Services/TelegramReportBuilder.php and add:\n"
                    . "'{$report->slug}' => 'buildMethodName',\n"
                    . "to the \$reports array at the top of the class.",
                'duration_ms' => 0,
                'chars' => 0,
                'diagnostic' => [
                    'slug' => $report->slug,
                    'type' => $report->report_type,
                    'registered' => false,
                    'available' => $availableSlugs,
                ],
            ]);
        }

        $defaults = $report->default_params ?? [];
        $overrides = $report->params ?? [];
        $runtime = $request->only(['date_range', 'date_from', 'date_to']);
        $params = array_merge($defaults, $overrides, $runtime, ['timezone' => $report->timezone ?? 'Asia/Singapore']);

        $start = microtime(true);
        $text = $builder->generate($report->slug, $params);
        $ms = round((microtime(true) - $start) * 1000);

        return response()->json([
            'success' => true,
            'text' => $text,
            'duration_ms' => $ms,
            'chars' => mb_strlen($text),
            'diagnostic' => [
                'slug' => $report->slug,
                'type' => $report->report_type,
                'registered' => true,
            ],
        ]);
    }

    // ══════════════════════════════════════════════
    // DIAGNOSTIC — check report health
    // ══════════════════════════════════════════════
    public function diagnose(int $id)
    {

        $report = TelegramReport::findOrFail($id);
        $builder = new TelegramReportBuilder();

        $result = [
            'report_id' => $id,
            'name' => $report->name,
            'slug' => $report->slug,
            'type' => $report->report_type,
            'checks' => [],
        ];

        // Check 1: slug registered in code
        $available = $builder->availableSlugs();
        $registered = in_array($report->slug, $available);
        $result['checks'][] = [
            'test' => 'Slug in $reports array',
            'pass' => $registered,
            'detail' => $registered
                ? "'{$report->slug}' found in TelegramReportBuilder::\$reports"
                : "'{$report->slug}' NOT found. Available: " . implode(', ', $available),
        ];

        // Check 2: for template type, query exists
        if ($report->report_type === 'template') {
            $hasQuery = !empty($report->query);
            $result['checks'][] = ['test' => 'SQL query defined', 'pass' => $hasQuery, 'detail' => $hasQuery ? mb_strlen($report->query) . ' chars' : 'Empty'];
            $hasTemplate = !empty($report->template);
            $result['checks'][] = ['test' => 'Template defined', 'pass' => $hasTemplate, 'detail' => $hasTemplate ? mb_strlen($report->template) . ' chars' : 'Empty'];

            if ($hasQuery) {
                $safety = $builder->validateQuery($report->query);
                $result['checks'][] = ['test' => 'SQL safety check', 'pass' => $safety === true, 'detail' => $safety === true ? 'SELECT only, safe' : "Blocked: {$safety}"];
            }
        }

        // Check 3: target configured
        $hasTarget = !empty($report->target_id);
        $targetName = $hasTarget ? DB::table('tbl_telegram_targets')->where('id', $report->target_id)->value('name') : null;
        $result['checks'][] = [
            'test' => 'Target configured',
            'pass' => $hasTarget && $targetName,
            'detail' => $hasTarget ? ($targetName ? "→ {$targetName}" : "ID {$report->target_id} NOT FOUND in DB") : 'No target set',
        ];

        // Check 4: bot token
        $token = DB::table('tbl_telegram_config')->where('key_name', 'bot_token')->value('value');
        $result['checks'][] = ['test' => 'Bot token configured', 'pass' => !empty($token), 'detail' => !empty($token) ? 'Token set (' . strlen($token) . ' chars)' : 'MISSING — go to Setup tab'];

        // Check 5: try generating
        if ($registered || $report->report_type === 'template') {
            try {
                $params = array_merge($report->default_params ?? [], $report->params ?? [], ['timezone' => $report->timezone ?? 'Asia/Singapore']);
                $start = microtime(true);
                $text = $builder->generate($report->slug, $params);
                $ms = round((microtime(true) - $start) * 1000);
                $ok = !str_starts_with($text, '❌');
                $result['checks'][] = [
                    'test' => 'Generate report',
                    'pass' => $ok,
                    'detail' => $ok ? mb_strlen($text) . " chars in {$ms}ms" : $text,
                ];
            } catch (\Throwable $e) {
                $result['checks'][] = ['test' => 'Generate report', 'pass' => false, 'detail' => 'EXCEPTION: ' . $e->getMessage()];
            }
        }

        $allPass = collect($result['checks'])->every(fn($c) => $c['pass']);
        $result['status'] = $allPass ? 'healthy' : 'issues_found';

        return response()->json($result);
    }

    // ══════════════════════════════════════════════
    // SEND
    // ══════════════════════════════════════════════
    public function send(Request $request, int $id)
    {

        $report = TelegramReport::findOrFail($id);

        $targetId = $request->input('target_id', $report->target_id);
        if (!$targetId) return response()->json(['success' => false, 'error' => 'No target selected. Set a target in the report settings or pick one from the dropdown.']);

        $target = DB::table('tbl_telegram_targets')->find($targetId);
        if (!$target) return response()->json(['success' => false, 'error' => "Target ID {$targetId} not found in database."]);

        $token = $this->token();
        if (!$token) return response()->json(['success' => false, 'error' => 'No bot token configured. Go to Setup tab → enter your bot token.']);

        $builder = new TelegramReportBuilder();
        $defaults = $report->default_params ?? [];
        $overrides = $report->params ?? [];
        $runtime = $request->input('params', []);
        $params = array_merge($defaults, $overrides, $runtime, ['timezone' => $report->timezone ?? 'Asia/Singapore']);

        $start = microtime(true);
        $text = $builder->generate($report->slug, $params);
        $ms = round((microtime(true) - $start) * 1000);

        if (str_starts_with($text, '❌')) return response()->json(['success' => false, 'error' => $text]);
        if (str_starts_with($text, '__SKIP__')) return response()->json(['success' => false, 'error' => 'Alert condition not met: ' . str_replace('__SKIP__', '', $text)]);

        $result = $this->sendTelegram($token, $target->chat_id, $text, 'manual', $target->name, $report->slug, $report->id, $ms);

        if ($result['success']) {
            $report->update(['last_sent_at' => now(), 'last_status' => 'sent', 'last_error' => null, 'send_count' => DB::raw('send_count + 1'), 'consecutive_fails' => 0]);
        } else {
            $report->update(['last_status' => 'failed', 'last_error' => $result['error'] ?? 'Unknown', 'fail_count' => DB::raw('fail_count + 1'), 'consecutive_fails' => DB::raw('consecutive_fails + 1')]);
        }

        // Return extra debug info
        $result['debug'] = [
            'report' => $report->name,
            'slug' => $report->slug,
            'target' => $target->name,
            'chat_id' => $target->chat_id,
            'chars' => mb_strlen($text),
            'duration_ms' => $ms,
        ];

        return response()->json($result);
    }

    // ══════════════════════════════════════════════
    // BULK SEND
    // ══════════════════════════════════════════════
    public function sendBulk(Request $request)
    {

        $ids = $request->input('ids', []);
        if (empty($ids)) return response()->json(['success' => false, 'error' => 'No reports selected.']);

        $token = $this->token();
        if (!$token) return response()->json(['success' => false, 'error' => 'No bot token.']);

        $sent = 0; $failed = 0; $skipped = 0;
        $builder = new TelegramReportBuilder();

        foreach ($ids as $id) {
            $report = DB::table('tbl_telegram_reports as r')
                ->leftJoin('tbl_telegram_targets as t', 't.id', '=', 'r.target_id')
                ->select('r.*', 't.chat_id', 't.name as target_name')
                ->where('r.id', $id)->first();

            if (!$report || !$report->chat_id) { $skipped++; continue; }

            $params = array_merge(
                json_decode($report->default_params ?? '{}', true) ?: [],
                json_decode($report->params ?? '{}', true) ?: [],
                ['timezone' => $report->timezone ?? 'Asia/Singapore']
            );

            $text = $builder->generate($report->slug, $params);
            if (str_starts_with($text, '❌') || str_starts_with($text, '__SKIP__')) { $skipped++; continue; }

            $result = $this->sendTelegram($token, $report->chat_id, $text, 'bulk', $report->target_name, $report->slug, $report->id, 0);

            if ($result['success']) {
                $sent++;
                DB::table('tbl_telegram_reports')->where('id', $id)->update(['last_sent_at' => now(), 'last_status' => 'sent', 'last_error' => null, 'send_count' => DB::raw('send_count + 1'), 'consecutive_fails' => 0]);
            } else { $failed++; }

            usleep(300000); // rate limit
        }

        return response()->json(['success' => true, 'message' => "Sent: {$sent}, Failed: {$failed}, Skipped: {$skipped}"]);
    }

    // ══════════════════════════════════════════════
    // TEST QUERY (for custom SQL)
    // ══════════════════════════════════════════════
    public function testQuery(Request $request)
    {

        $query = $request->input('query', '');
        if (!$query) return response()->json(['success' => false, 'error' => 'No query.']);

        $builder = new TelegramReportBuilder();
        $result = $builder->testQuery($query, $request->only(['date_range', 'timezone']));

        if (isset($result['rows']) && count($result['rows']) > 20) {
            $result['rows'] = array_slice($result['rows'], 0, 20);
            $result['truncated'] = true;
        }
        return response()->json($result);
    }

    // ══════════════════════════════════════════════
    // SOURCE CODE (for PHP viewer)
    // ══════════════════════════════════════════════
    public function source(string $slug)
    {

        $file = app_path('Services/TelegramReportBuilder.php');
        if (!file_exists($file)) return response()->json(['success' => false, 'source' => '// File not found']);

        $content = file_get_contents($file);

        // Find the specific method
        $builder = new TelegramReportBuilder();
        $slugs = $builder->availableSlugs();
        $registry = [];
        // Parse the $reports array to find method name
        if (preg_match_all("/'([^']+)'\s*=>\s*'([^']+)'/", $content, $matches)) {
            foreach ($matches[1] as $i => $s) {
                $registry[$s] = $matches[2][$i];
            }
        }

        $method = $registry[$slug] ?? null;
        if (!$method) return response()->json(['success' => true, 'source' => "// Method for '{$slug}' not found in registry"]);

        // Extract the method body
        if (preg_match('/private function ' . preg_quote($method) . '\(.*?\{(.+?)^\s{4}\}/ms', $content, $m)) {
            $source = "private function {$method}(array \$params): string\n{" . $m[1] . "}";
        } else {
            $source = "// Could not extract method '{$method}'.\n// Check TelegramReportBuilder.php";
        }

        return response()->json(['success' => true, 'source' => $source, 'method' => $method]);
    }

    // ══════════════════════════════════════════════
    // SAVE SOURCE CODE
    // ══════════════════════════════════════════════
    public function saveSource(Request $request, string $slug)
    {

        if (($request->attributes->get('admin_role_id') ?? 0) != 1) {
            return response()->json(['success' => false, 'error' => 'Only superadmin can edit code.']);
        }

        $code = $request->input('code', '');
        if (empty(trim($code))) {
            return response()->json(['success' => false, 'error' => 'Code is empty.']);
        }

        $file = app_path('Services/TelegramReportBuilder.php');
        if (!file_exists($file)) {
            return response()->json(['success' => false, 'error' => 'TelegramReportBuilder.php not found.']);
        }

        $content = file_get_contents($file);

        // Find method name from slug registry
        $registry = [];
        if (preg_match_all("/'([^']+)'\s*=>\s*'([^']+)'/", $content, $matches)) {
            foreach ($matches[1] as $i => $s) {
                $registry[$s] = $matches[2][$i];
            }
        }

        $methodName = $registry[$slug] ?? null;
        if (!$methodName) {
            return response()->json(['success' => false, 'error' => "Slug '{$slug}' not found in \$reports array. Available: " . implode(', ', array_keys($registry))]);
        }

        $code = trim($code);
        if (!str_contains($code, "function {$methodName}(")) {
            return response()->json(['success' => false, 'error' => "Code must contain: function {$methodName}(). Found method name doesn't match slug."]);
        }

        // Find method boundaries using brace counting (more robust than regex)
        $lines = explode("\n", $content);
        $methodStart = null;
        $methodEnd = null;

        // Step 1: Find the line with "function methodName("
        foreach ($lines as $i => $line) {
            if (str_contains($line, "function {$methodName}(")) {
                $methodStart = $i;
                break;
            }
        }

        if ($methodStart === null) {
            return response()->json(['success' => false, 'error' => "Could not find function {$methodName}() in file."]);
        }

        // Step 2: Go backwards to include comment block above the method
        $commentStart = $methodStart;
        for ($i = $methodStart - 1; $i >= 0; $i--) {
            $trimmed = trim($lines[$i]);
            if ($trimmed === '' || str_starts_with($trimmed, '//') || str_starts_with($trimmed, '*') || str_starts_with($trimmed, '/*')) {
                $commentStart = $i;
            } else {
                break;
            }
        }

        // Step 3: Count braces from the function line to find the matching close
        $depth = 0;
        $foundOpen = false;
        for ($i = $methodStart; $i < count($lines); $i++) {
            $depth += substr_count($lines[$i], '{');
            if ($depth > 0) $foundOpen = true;
            $depth -= substr_count($lines[$i], '}');
            if ($foundOpen && $depth === 0) {
                $methodEnd = $i;
                break;
            }
        }

        if ($methodEnd === null) {
            return response()->json(['success' => false, 'error' => "Could not find closing brace for {$methodName}(). Brace mismatch in file."]);
        }

        // Backup
        $backupDir = storage_path('app/code_backups');
        if (!is_dir($backupDir)) mkdir($backupDir, 0755, true);
        $backupFile = $backupDir . '/TelegramReportBuilder_' . date('Ymd_His') . '.php';
        copy($file, $backupFile);

        // Build new file: before + new code + after
        $before = array_slice($lines, 0, $commentStart);
        $after = array_slice($lines, $methodEnd + 1);
        $indentedCode = "    " . trim($code);

        $newContent = implode("\n", $before) . "\n" . $indentedCode . "\n" . implode("\n", $after);

        // Brace balance check on entire file
        $opens = substr_count($newContent, '{');
        $closes = substr_count($newContent, '}');
        if ($opens !== $closes) {
            return response()->json(['success' => false, 'error' => "Brace mismatch: {$opens} open vs {$closes} close. Code NOT saved. Fix your curly braces {{ }}."]);
        }

        file_put_contents($file, $newContent);

        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($file, true);
        }

        return response()->json([
            'success' => true,
            'message' => "✓ Code saved! Method: {$methodName}() | Backup: " . basename($backupFile),
        ]);
    }

    // ══════════════════════════════════════════════
    // LOG
    // ══════════════════════════════════════════════
    public function log(Request $request)
    {

        $query = DB::table('tbl_telegram_log')->orderByDesc('sent_at');
        if ($request->filled('report_slug')) $query->where('report_slug', $request->report_slug);
        if ($request->filled('status'))      $query->where('status', $request->status);
        if ($request->filled('target'))      $query->where('target', 'like', '%' . $request->target . '%');
        if ($request->filled('type'))        $query->where('type', $request->type);

        return response()->json(['success' => true, 'logs' => $query->limit(100)->get()]);
    }

    // ══════════════════════════════════════════════
    // CRON TEST (run from browser with debug output)
    // ══════════════════════════════════════════════
    public function cronTest(Request $request)
    {

        $dryRun = $request->has('dry');

        $now = now();
        $mins = intval($now->format('i'));
        $roundedMins = floor($mins / 5) * 5;
        $matchTime = $now->format('H') . ':' . str_pad($roundedMins, 2, '0', STR_PAD_LEFT);
        $currentDay = intval($now->format('N'));
        $currentDate = intval($now->format('j'));

        $out = "🔧 CRON TEST" . ($dryRun ? ' (DRY RUN — no sending)' : ' (LIVE — will send!)') . "\n";
        $out .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $out .= "Time: " . $now->format('Y-m-d H:i:s') . "\n";
        $out .= "Match time: {$matchTime}\n";
        $out .= "Day: {$currentDay} (1=Mon..7=Sun)\n";
        $out .= "Date: {$currentDate}\n\n";

        // Token
        $token = DB::table('tbl_telegram_config')->where('key_name', 'bot_token')->value('value');
        $out .= "Bot token: " . ($token ? '✅ found (' . strlen($token) . ' chars)' : '❌ MISSING') . "\n\n";

        if (!$token) {
            return response($out, 200)->header('Content-Type', 'text/plain; charset=utf-8');
        }

        // Reports
        $reports = DB::table('tbl_telegram_reports as r')
            ->leftJoin('tbl_telegram_targets as t', 't.id', '=', 'r.target_id')
            ->where('r.enabled', 1)
            ->select('r.*', 't.chat_id', 't.name as target_name')
            ->orderBy('r.name')
            ->get();

        $out .= "REPORTS ({$reports->count()} enabled)\n";
        $out .= str_repeat('─', 50) . "\n";

        $builder = new TelegramReportBuilder();
        $sent = 0;

        foreach ($reports as $report) {
            $schedTime = substr($report->schedule_time ?? '00:00', 0, 5);
            $schedType = $report->schedule_type ?? 'manual';

            // Check if due
            $shouldRun = false;
            switch ($schedType) {
                case 'every5m':  $shouldRun = true; break;
                case 'every15m': $shouldRun = ($mins % 15 < 5); break;
                case 'every30m': $shouldRun = ($mins % 30 < 5); break;
                case 'hourly':   $shouldRun = (intval(substr($report->schedule_time ?? '00:00', 3, 2)) === intval(floor($mins / 5) * 5)); break;
                case 'daily':    $shouldRun = ($matchTime === $schedTime); break;
                case 'weekday':  $shouldRun = ($matchTime === $schedTime && $currentDay <= 5); break;
                case 'weekly':   $shouldRun = ($matchTime === $schedTime && $currentDay == intval($report->schedule_day)); break;
                case 'monthly':  $shouldRun = ($matchTime === $schedTime && $currentDate === 1); break;
            }

            $out .= "\n📋 {$report->name}\n";
            $out .= "   slug: {$report->slug}\n";
            $out .= "   schedule: {$schedType}" . ($schedTime !== '00:00' ? " at {$schedTime}" : "") . "\n";
            $out .= "   target: " . ($report->target_name ?? '❌ NONE') . " (id: " . ($report->target_id ?? 'null') . ")\n";
            $out .= "   chat_id: " . ($report->chat_id ?? '❌ NONE') . "\n";
            $out .= "   php_code: " . ($report->php_code ? strlen($report->php_code) . ' chars' : 'empty (uses file method)') . "\n";
            $out .= "   last_sent: " . ($report->last_sent_at ?? 'never') . "\n";

            if ($schedType === 'manual') {
                $out .= "   ⏸ MANUAL — skip\n";
                continue;
            }

            if (!$report->target_id || !$report->chat_id) {
                $out .= "   ❌ NO TARGET — skip\n";
                continue;
            }

            // Anti-duplicate
            if ($report->last_sent_at) {
                $lastSent = \Carbon\Carbon::parse($report->last_sent_at);
                $minGap = match($schedType) {
                    'every5m' => 4, 'every15m' => 13, 'every30m' => 28,
                    'hourly' => 55, default => 30,
                };
                $diff = $lastSent->diffInMinutes($now);
                if (!$shouldRun) {
                    $out .= "   ⏳ NOT DUE (schedule: {$schedType}, match: {$matchTime} vs {$schedTime})\n";
                    continue;
                }
                if ($diff < $minGap) {
                    $out .= "   ⏳ TOO SOON — sent {$diff}min ago (gap: {$minGap}min)\n";
                    continue;
                }
            } elseif (!$shouldRun) {
                $out .= "   ⏳ NOT DUE\n";
                continue;
            }

            $out .= "   ✅ DUE — ";

            if ($dryRun) {
                $out .= "DRY RUN (would generate & send)\n";

                // Still try generating to check for errors
                try {
                    $params = $report->params ? json_decode($report->params, true) : [];
                    $defaults = $report->default_params ? json_decode($report->default_params, true) : [];
                    $params = array_merge($defaults ?: [], $params ?: [], ['timezone' => $report->timezone ?? 'Asia/Singapore']);
                    $text = $builder->generate($report->slug, $params);
                    $parts = str_contains($text, '__SPLIT__') ? count(array_filter(explode('__SPLIT__', $text))) : 1;
                    $out .= "   📝 Generated OK: " . mb_strlen($text) . " chars, {$parts} msgs\n";
                    if (str_starts_with($text, '❌')) $out .= "   ⚠️ OUTPUT: {$text}\n";
                } catch (\Throwable $e) {
                    $out .= "   ❌ Generate error: {$e->getMessage()}\n";
                }
                continue;
            }

            // LIVE SEND
            try {
                $params = $report->params ? json_decode($report->params, true) : [];
                $defaults = $report->default_params ? json_decode($report->default_params, true) : [];
                $params = array_merge($defaults ?: [], $params ?: [], ['timezone' => $report->timezone ?? 'Asia/Singapore']);

                $start = microtime(true);
                $text = $builder->generate($report->slug, $params);
                $ms = round((microtime(true) - $start) * 1000);

                if (str_starts_with($text, '❌')) {
                    $out .= "ERROR: {$text}\n";
                    continue;
                }

                $result = $this->sendTelegram($token, $report->chat_id, $text, 'cron-test', $report->target_name ?? 'unknown', $report->slug, $report->id, $ms);
                $out .= ($result['success'] ? "SENT" : "FAILED: " . ($result['error'] ?? '?')) . " ({$ms}ms)\n";

                if ($result['success']) {
                    DB::table('tbl_telegram_reports')->where('id', $report->id)->update([
                        'last_sent_at' => $now, 'last_status' => 'sent', 'last_error' => null,
                        'send_count' => DB::raw('send_count + 1'), 'consecutive_fails' => 0,
                    ]);
                    $sent++;
                }
            } catch (\Throwable $e) {
                $out .= "EXCEPTION: {$e->getMessage()}\n";
            }
        }

        $out .= "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $out .= "Done. Sent: {$sent}\n";
        $out .= "Add ?dry to URL for dry run (no sending)\n";

        return response($out, 200)->header('Content-Type', 'text/plain; charset=utf-8');
    }

    // ══════════════════════════════════════════════
    // TELEGRAM SEND (with chunking)
    // ══════════════════════════════════════════════
    private function sendTelegram(string $token, string $chatId, string $text, string $type, string $target, ?string $slug, ?int $reportId, int $ms): array
    {
        try {
            $oldest = DB::table('tbl_telegram_log')->orderByDesc('sent_at')->skip(499)->take(1)->value('sent_at');
            if ($oldest) DB::table('tbl_telegram_log')->where('sent_at', '<', $oldest)->delete();
        } catch (\Throwable $e) {}

        // Split by explicit delimiter first, then by length
        $parts = str_contains($text, '__SPLIT__') ? array_filter(explode('__SPLIT__', $text), fn($p) => trim($p) !== '') : [];
        if (empty($parts)) {
            $parts = mb_strlen($text) <= 4096 ? [$text] : $this->splitMessage($text, 4000);
        }
        // Safety: if any single part > 4096, split it further
        $chunks = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if (mb_strlen($part) <= 4096) { $chunks[] = $part; }
            else { foreach ($this->splitMessage($part, 4000) as $sub) $chunks[] = $sub; }
        }
        $allOk = true; $lastError = null;

        foreach ($chunks as $i => $chunk) {
            try {
                $resp = Http::timeout(15)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id' => $chatId, 'text' => $chunk, 'parse_mode' => 'Markdown',
                ]);
                $data = $resp->json();
                if (!($data['ok'] ?? false)) { $allOk = false; $lastError = $data['description'] ?? 'Unknown'; }
                if ($i < count($chunks) - 1) usleep(150000);
            } catch (\Throwable $e) { $allOk = false; $lastError = $e->getMessage(); }
        }

        DB::table('tbl_telegram_log')->insert([
            'subscription_id' => $reportId, 'type' => $type, 'report_slug' => $slug,
            'target' => $target, 'chat_id' => $chatId,
            'message' => mb_substr($text, 0, 2000),
            'status' => $allOk ? 'sent' : 'failed',
            'error' => $lastError, 'duration_ms' => $ms, 'sent_at' => now(),
        ]);

        return $allOk
            ? ['success' => true, 'message' => '✓ Sent!' . (count($chunks) > 1 ? ' (' . count($chunks) . ' parts)' : '')]
            : ['success' => false, 'error' => $lastError ?? 'Send failed.'];
    }

    private function splitMessage(string $text, int $maxLen): array
    {
        $chunks = []; $current = '';
        foreach (explode("\n", $text) as $line) {
            if (mb_strlen($current . "\n" . $line) > $maxLen && $current !== '') {
                $chunks[] = trim($current); $current = $line;
            } else { $current .= ($current ? "\n" : '') . $line; }
        }
        if (trim($current)) $chunks[] = trim($current);
        return $chunks ?: [$text];
    }
}
