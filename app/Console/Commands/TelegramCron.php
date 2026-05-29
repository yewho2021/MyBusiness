<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\TelegramReportBuilder;

class TelegramCron extends Command
{
    protected $signature = 'telegram:cron';
    protected $description = 'Send scheduled Telegram reports';

    public function handle()
    {
        $now = now();
        $mins = intval($now->format('i'));
        $roundedMins = floor($mins / 5) * 5;
        $matchTime = $now->format('H') . ':' . str_pad($roundedMins, 2, '0', STR_PAD_LEFT);
        $currentDay = intval($now->format('N'));
        $currentDate = intval($now->format('j'));

        $this->info("[{$now->format('Y-m-d H:i:s')}] Cron started. Match: {$matchTime}");

        $token = DB::table('tbl_telegram_config')->where('key_name', 'bot_token')->value('value');
        if (!$token) { $this->error('No bot token!'); return 1; }

        $reports = DB::table('tbl_telegram_reports as r')
            ->join('tbl_telegram_targets as t', 't.id', '=', 'r.target_id')
            ->where('r.enabled', 1)
            ->where('r.schedule_type', '!=', 'manual')
            ->whereNotNull('r.target_id')
            ->select('r.*', 't.chat_id', 't.name as target_name')
            ->get();

        $this->info("Found {$reports->count()} scheduled reports");
        if ($reports->isEmpty()) return 0;

        $builder = new TelegramReportBuilder();
        $sent = 0;

        foreach ($reports as $report) {
            $schedTime = substr($report->schedule_time ?? '00:00', 0, 5);

            // Check if due
            $shouldRun = match($report->schedule_type) {
                'every5m'  => true,
                'every15m' => ($mins % 15 < 5),
                'every30m' => ($mins % 30 < 5),
                'hourly'   => (intval(substr($report->schedule_time ?? '00:00', 3, 2)) === intval(floor($mins / 5) * 5)),
                'daily'    => ($matchTime === $schedTime),
                'weekday'  => ($matchTime === $schedTime && $currentDay <= 5),
                'weekly'   => ($matchTime === $schedTime && $currentDay == intval($report->schedule_day)),
                'monthly'  => ($matchTime === $schedTime && $currentDate === 1),
                default    => false,
            };

            if (!$shouldRun) { $this->line("  SKIP {$report->slug}: not due"); continue; }

            // Anti-duplicate
            if ($report->last_sent_at) {
                $diff = \Carbon\Carbon::parse($report->last_sent_at)->diffInMinutes($now);
                $minGap = match($report->schedule_type) {
                    'every5m' => 4, 'every15m' => 13, 'every30m' => 28,
                    'hourly' => 55, default => 30,
                };
                if ($diff < $minGap) { $this->line("  SKIP {$report->slug}: sent {$diff}min ago"); continue; }
            }

            $this->info("  RUN {$report->slug} → {$report->target_name}");

            try {
                $params = $report->params ? json_decode($report->params, true) : [];
                $defaults = $report->default_params ? json_decode($report->default_params, true) : [];
                $params = array_merge($defaults ?: [], $params ?: [], ['timezone' => $report->timezone ?? \App\Models\Configuration::get('default_timezone', config('app.timezone', 'UTC'))]);

                $start = microtime(true);
                $text = $builder->generate($report->slug, $params);
                $ms = round((microtime(true) - $start) * 1000);

                if (str_starts_with($text, '__SKIP__')) { $this->line("    SKIPPED (condition not met)"); continue; }
                if (str_starts_with($text, '❌')) {
                    $this->error("    ERROR: {$text}");
                    DB::table('tbl_telegram_reports')->where('id', $report->id)->update([
                        'last_status' => 'failed', 'last_error' => substr($text, 0, 500),
                        'fail_count' => DB::raw('fail_count + 1'),
                        'consecutive_fails' => DB::raw('consecutive_fails + 1'),
                    ]);
                    continue;
                }

                // Send via Telegram API
                $parts = str_contains($text, '__SPLIT__')
                    ? array_values(array_filter(explode('__SPLIT__', $text), fn($p) => trim($p) !== ''))
                    : (mb_strlen($text) <= 4096 ? [$text] : $this->splitMsg($text, 4000));

                $allOk = true;
                $totalChunks = 0;
                foreach ($parts as $part) {
                    $subs = mb_strlen(trim($part)) <= 4096 ? [trim($part)] : $this->splitMsg(trim($part), 4000);
                    foreach ($subs as $sub) {
                        if (empty(trim($sub))) continue;
                        $ok = $this->sendTelegram($token, $report->chat_id, trim($sub));
                        if (!$ok) $allOk = false;
                        $totalChunks++;
                        usleep(300000);
                    }
                }

                DB::table('tbl_telegram_reports')->where('id', $report->id)->update([
                    'last_sent_at' => $now, 'last_status' => $allOk ? 'sent' : 'partial',
                    'last_error' => null, 'send_count' => DB::raw('send_count + 1'),
                    'consecutive_fails' => 0,
                ]);

                DB::table('tbl_telegram_log')->insert([
                    'report_slug' => $report->slug, 'target' => $report->target_name,
                    'chat_id' => $report->chat_id, 'type' => 'scheduled',
                    'status' => $allOk ? 'sent' : 'partial',
                    'chars' => mb_strlen($text), 'chunks' => $totalChunks,
                    'duration_ms' => $ms, 'sent_at' => $now,
                ]);

                $sent++;
                $this->info("    SENT ({$totalChunks} msgs, {$ms}ms)");

            } catch (\Throwable $e) {
                $this->error("    EXCEPTION: {$e->getMessage()}");
                DB::table('tbl_telegram_reports')->where('id', $report->id)->update([
                    'last_status' => 'failed', 'last_error' => substr($e->getMessage(), 0, 500),
                    'fail_count' => DB::raw('fail_count + 1'),
                    'consecutive_fails' => DB::raw('consecutive_fails + 1'),
                ]);
            }
        }

        $this->info("Done. Sent: {$sent}");
        return 0;
    }

    private function sendTelegram(string $token, string $chatId, string $text): bool
    {
        $url = "https://api.telegram.org/bot{$token}/sendMessage";

        $try = function($parseMode = null) use ($url, $chatId, $text) {
            $payload = ['chat_id' => $chatId, 'text' => $text, 'disable_web_page_preview' => true];
            if ($parseMode) $payload['parse_mode'] = $parseMode;
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15,
            ]);
            $resp = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return $code === 200;
        };

        return $try('Markdown') || $try(null);
    }

    private function splitMsg(string $text, int $maxLen): array
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
