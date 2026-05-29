<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Configuration;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class TelegramController extends Controller
{
    /** Keys that are encrypted in tbl_telegram_config */
    private array $encryptedKeys = ['bot_token'];

    private function cfg(): array
    {
        $raw = DB::table('tbl_telegram_config')->pluck('value', 'key_name')->toArray();
        // Decrypt sensitive keys
        foreach ($this->encryptedKeys as $key) {
            if (!empty($raw[$key])) {
                try { $raw[$key] = Crypt::decryptString($raw[$key]); }
                catch (\Exception $e) { /* already plaintext (legacy) */ }
            }
        }
        return $raw;
    }

    private function cfgSet(string $key, ?string $value): void
    {
        $storeValue = $value ?? '';
        // Encrypt sensitive keys before storing
        if (in_array($key, $this->encryptedKeys) && $storeValue !== '') {
            $storeValue = Crypt::encryptString($storeValue);
        }
        DB::table('tbl_telegram_config')->updateOrInsert(
            ['key_name' => $key],
            ['value' => $storeValue, 'updated_at' => now()]
        );
    }

    private function token(): string
    {
        return trim($this->cfg()['bot_token'] ?? '');
    }

    // ── Index ──────────────────────────────────────────────────────────────
    public function index()
    {
        $config  = $this->cfg();
        $targets = DB::table('tbl_telegram_targets')->orderBy('is_default', 'desc')->orderBy('name')->get();
        return view('admin.pages.telegram.index', [
            'config'  => $config,
            'targets' => $targets,
        ]);
    }

    // ── Save bot token ─────────────────────────────────────────────────────
    public function save(Request $request)
    {
        $token = $request->input('bot_token', '');

        if (empty(trim($token)) && $request->boolean('clear_all')) {
            DB::table('tbl_telegram_config')->truncate();
            DB::table('tbl_telegram_targets')->truncate();
            return response()->json(['success' => true, 'message' => 'Bot disconnected. Token and all targets cleared.']);
        }

        foreach (['bot_token'] as $f) {
            $this->cfgSet($f, $request->input($f, ''));
        }
        return response()->json(['success' => true, 'message' => 'Bot token saved.']);
    }

    // ── Targets: list ──────────────────────────────────────────────────────
    public function targetsList()
    {
        $targets = DB::table('tbl_telegram_targets')->orderBy('is_default','desc')->orderBy('name')->get();
        return response()->json(['success' => true, 'targets' => $targets]);
    }

    // ── Targets: store ─────────────────────────────────────────────────────
    public function targetsStore(Request $request)
    {
        $name   = trim($request->input('name', ''));
        $chatId = trim($request->input('chat_id', ''));
        $type   = $request->input('type', 'group');
        $notes  = trim($request->input('notes', ''));

        if (!$name || !$chatId) {
            return response()->json(['success' => false, 'error' => 'Name and Chat ID are required.']);
        }

        // Validate chat ID by sending a test message via Telegram API
        $skipValidate = $request->boolean('skip_validate');
        $token = $this->token();
        $chatTitle = $name;
        $chatType  = $type;

        if (!$skipValidate) {
            if (!$token) {
                return response()->json(['success' => false, 'error' => 'Bot token not configured. Save it first.']);
            }

            try {
                // Try sending a test message directly — most reliable validation method
                $testResp = Http::timeout(10)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id'    => $chatId,
                    'text'       => "🔗 Connected to ".Configuration::get('portal_name', 'Admin Portal')."\n\n📌 Target: {$name}\n📋 Type: {$type}\n\n✅ This chat will receive notifications from the admin portal.",
                ]);
                $testData = $testResp->json();

                \Log::info('Telegram addTarget validation', ['chat_id' => $chatId, 'response' => $testData]);

                if (!($testData['ok'] ?? false)) {
                    $desc = $testData['description'] ?? 'Unknown error';
                    $errorCode = $testData['error_code'] ?? 0;
                    $hint = "\n\n[Error {$errorCode}]";
                    if (str_contains(strtolower($desc), 'chat not found')) {
                        $hint .= "\n💡 Tips:\n• For groups: add bot to the group → send a message in the group → use Discover Chats\n• For personal: user must send /start to the bot first\n• Or check 'Skip validation' to save without testing";
                    } elseif (str_contains(strtolower($desc), 'bot was blocked')) {
                        $hint .= "\n💡 The user has blocked your bot.";
                    } elseif (str_contains(strtolower($desc), 'not enough rights')) {
                        $hint .= "\n💡 Bot needs permission to send messages. Make it admin.";
                    }
                    return response()->json(['success' => false, 'error' => "Telegram: {$desc}{$hint}"]);
                }

                // Get chat info for auto-detect type
                $chatResp = Http::timeout(10)->get("https://api.telegram.org/bot{$token}/getChat", ['chat_id' => $chatId]);
                $chatData = $chatResp->json();
                $chatInfo = $chatData['result'] ?? [];
                $chatTitle = $chatInfo['title'] ?? $chatInfo['first_name'] ?? $name;
                $chatType  = $chatInfo['type'] ?? $type;

            } catch (\Throwable $e) {
                \Log::error('Telegram addTarget error', ['chat_id' => $chatId, 'error' => $e->getMessage()]);
                return response()->json(['success' => false, 'error' => 'Telegram API error: ' . $e->getMessage()]);
            }
        }

        // Auto-detect type from Telegram response
        if ($chatType === 'supergroup' || $chatType === 'group') {
            $type = 'group';
        } elseif ($chatType === 'channel') {
            $type = 'channel';
        } elseif ($chatType === 'private') {
            $type = 'personal';
        }

        // If first target or set as default
        $isDefault = DB::table('tbl_telegram_targets')->count() === 0 ? 1 : ($request->boolean('is_default') ? 1 : 0);
        if ($isDefault) {
            DB::table('tbl_telegram_targets')->update(['is_default' => 0]);
        }

        $id = DB::table('tbl_telegram_targets')->insertGetId([
            'name'       => $name,
            'chat_id'    => $chatId,
            'type'       => in_array($type, ['personal','group','channel']) ? $type : 'group',
            'notes'      => $notes ?: null,
            'is_default' => $isDefault,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $target = DB::table('tbl_telegram_targets')->find($id);
        return response()->json(['success' => true, 'target' => $target, 'message' => "✅ '{$name}' connected & verified! Test message sent to {$chatTitle}."]);
    }

    // ── Update Target ─────────────────────────────────────────────────────
    public function targetsUpdate(Request $request, int $id)
    {
        $target = DB::table('tbl_telegram_targets')->find($id);
        if (!$target) return response()->json(['success' => false, 'error' => 'Target not found.'], 404);

        $name   = trim($request->input('name', ''));
        $chatId = trim($request->input('chat_id', ''));
        $type   = $request->input('type', $target->type);
        $notes  = trim($request->input('notes', ''));

        if (!$name || !$chatId) {
            return response()->json(['success' => false, 'error' => 'Name and Chat ID are required.']);
        }

        // Validate if chat_id changed (skip if same or skip_validate)
        $chatTitle = $name;
        $chatType  = $type;
        $skipValidate = $request->boolean('skip_validate');

        if (!$skipValidate && $chatId !== (string)$target->chat_id) {
            $token = $this->token();
            if (!$token) return response()->json(['success' => false, 'error' => 'Bot token not configured.']);

            try {
                $testResp = Http::timeout(10)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id' => $chatId,
                    'text'    => "🔄 Target updated — {$name}\n\n✅ This chat will receive notifications.",
                ]);
                $testData = $testResp->json();
                \Log::info('Telegram updateTarget validation', ['id' => $id, 'chat_id' => $chatId, 'old_chat_id' => $target->chat_id, 'response' => $testData]);

                if (!($testData['ok'] ?? false)) {
                    $desc = $testData['description'] ?? 'Failed';
                    $errorCode = $testData['error_code'] ?? 0;
                    return response()->json(['success' => false, 'error' => "Telegram [{$errorCode}]: {$desc}\n\n💡 Check 'Skip validation' to save without testing, or use Discover Chats to find the correct Chat ID."]);
                }

                $chatResp = Http::timeout(10)->get("https://api.telegram.org/bot{$token}/getChat", ['chat_id' => $chatId]);
                $chatInfo = $chatResp->json()['result'] ?? [];
                $chatType = $chatInfo['type'] ?? $type;
            } catch (\Throwable $e) {
                \Log::error('Telegram updateTarget error', ['id' => $id, 'chat_id' => $chatId, 'error' => $e->getMessage()]);
                return response()->json(['success' => false, 'error' => 'Telegram API error: ' . $e->getMessage()]);
            }

            // Auto-detect type
            if ($chatType === 'supergroup' || $chatType === 'group') $type = 'group';
            elseif ($chatType === 'channel') $type = 'channel';
            elseif ($chatType === 'private') $type = 'personal';
        }

        DB::table('tbl_telegram_targets')->where('id', $id)->update([
            'name'       => $name,
            'chat_id'    => $chatId,
            'type'       => in_array($type, ['personal','group','channel']) ? $type : 'group',
            'notes'      => $notes ?: null,
            'updated_at' => now(),
        ]);

        $target = DB::table('tbl_telegram_targets')->find($id);
        return response()->json(['success' => true, 'target' => $target, 'message' => "'{$name}' updated."]);
    }

    // ── Targets: set default ───────────────────────────────────────────────
    public function targetsDefault(int $id)
    {
        DB::table('tbl_telegram_targets')->update(['is_default' => 0]);
        DB::table('tbl_telegram_targets')->where('id', $id)->update(['is_default' => 1]);
        return response()->json(['success' => true, 'message' => 'Default target updated.']);
    }

    // ── Targets: delete ────────────────────────────────────────────────────
    public function targetsDelete(int $id)
    {
        $t = DB::table('tbl_telegram_targets')->find($id);
        if (!$t) return response()->json(['success' => false, 'error' => 'Not found.']);
        DB::table('tbl_telegram_targets')->where('id', $id)->delete();
        // If it was default, make next one default
        if ($t->is_default) {
            $next = DB::table('tbl_telegram_targets')->orderBy('id')->first();
            if ($next) DB::table('tbl_telegram_targets')->where('id', $next->id)->update(['is_default' => 1]);
        }
        return response()->json(['success' => true, 'message' => "'{$t->name}' deleted."]);
    }

    // ── Test Connection ────────────────────────────────────────────────────
    // ── Discover available chats from getUpdates ─────────────────────────
    public function discoverChats()
    {
        $token = $this->token();
        if (!$token) return response()->json(['success' => false, 'error' => 'No bot token configured.']);

        try {
            $resp = Http::timeout(10)->get("https://api.telegram.org/bot{$token}/getUpdates", [
                'allowed_updates' => json_encode(['message', 'my_chat_member']),
            ]);
            $data = $resp->json();
            if (!($data['ok'] ?? false)) {
                return response()->json(['success' => false, 'error' => $data['description'] ?? 'Failed']);
            }

            $chats = [];
            $seen = [];
            foreach ($data['result'] ?? [] as $update) {
                // From messages
                $chat = $update['message']['chat'] ?? $update['my_chat_member']['chat'] ?? null;
                if ($chat && !isset($seen[$chat['id']])) {
                    $seen[$chat['id']] = true;
                    $existing = DB::table('tbl_telegram_targets')->where('chat_id', $chat['id'])->exists();
                    $chats[] = [
                        'chat_id'  => $chat['id'],
                        'title'    => $chat['title'] ?? $chat['first_name'] ?? 'Unknown',
                        'type'     => $chat['type'] ?? 'unknown',
                        'username' => $chat['username'] ?? null,
                        'already_added' => $existing,
                    ];
                }
            }

            return response()->json(['success' => true, 'chats' => $chats, 'total_updates' => count($data['result'] ?? [])]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // ── Test Connection ───────────────────────────────────────────────────
    public function testConnection(Request $request)
    {
        $token = trim($request->input('bot_token') ?: $this->token());
        if (!$token) return response()->json(['success' => false, 'error' => 'No bot token. Save it first.']);

        try {
            $resp = Http::timeout(10)->get("https://api.telegram.org/bot{$token}/getMe");
            $data = $resp->json();
            if (!($data['ok'] ?? false)) {
                return response()->json(['success' => false, 'error' => $data['description'] ?? 'Invalid token.']);
            }
            $bot = $data['result'];
            return response()->json(['success' => true, 'bot_name' => $bot['first_name'] ?? '', 'username' => '@'.($bot['username'] ?? ''), 'bot_id' => $bot['id'] ?? '']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => 'Connection failed: '.$e->getMessage()]);
        }
    }

    // ── Test Send ──────────────────────────────────────────────────────────
    // target_id = ID from tbl_telegram_targets, or 'custom' with custom_chat_id
    public function testSend(Request $request)
    {
        $token    = trim($request->input('bot_token') ?: $this->token());
        $targetId = $request->input('target_id');
        $customId = trim($request->input('custom_chat_id', ''));
        $message  = trim($request->input('message', ''));

        if (!$token) return response()->json(['success'=>false,'error'=>'No bot token. Save it first.']);

        // Resolve chat_id and label
        if ($targetId === 'custom') {
            if (!$customId) return response()->json(['success'=>false,'error'=>'Enter a custom Chat ID.']);
            $chatId = $customId;
            $label  = 'custom';
        } else {
            $t = DB::table('tbl_telegram_targets')->find((int)$targetId);
            if (!$t) return response()->json(['success'=>false,'error'=>'Target not found. Add a target first.']);
            $chatId = $t->chat_id;
            $label  = $t->name;
        }

        if (!$message) {
            $message = "👋 Test from *".Configuration::get('portal_name', 'Admin Portal')."*\n📅 ".now()->format('Y-m-d H:i:s')."\n\n✓ Bot is working!";
        }

        $result = $this->sendMessage($token, $chatId, $message, 'test', $label);
        if ($result['success']) $result['message'] = "✓ Sent to *{$label}*!";
        return response()->json($result);
    }

    // ── Get Log ────────────────────────────────────────────────────────────
    public function getLog()
    {
        $logs = DB::table('tbl_telegram_log')->orderByDesc('sent_at')->limit(50)->get();
        return response()->json(['success' => true, 'logs' => $logs]);
    }

    // ── Core sendMessage ───────────────────────────────────────────────────
    private function sendMessage(string $token, string $chatId, string $text, string $type = 'manual', string $target = ''): array
    {
        try {
            $oldest = DB::table('tbl_telegram_log')->orderByDesc('sent_at')->skip(499)->take(1)->value('sent_at');
            if ($oldest) DB::table('tbl_telegram_log')->where('sent_at','<',$oldest)->delete();
        } catch (\Throwable $e) {}

        try {
            $resp = Http::timeout(15)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId, 'text' => $text, 'parse_mode' => 'Markdown',
            ]);
            $data = $resp->json();
            $ok   = $data['ok'] ?? false;

            DB::table('tbl_telegram_log')->insert([
                'type'    => $type, 'target' => $target, 'chat_id' => $chatId,
                'message' => mb_substr($text,0,500),
                'status'  => $ok ? 'sent' : 'failed',
                'error'   => $ok ? null : ($data['description']??'Unknown'), 'sent_at' => now(),
            ]);

            return $ok
                ? ['success'=>true, 'message'=>'✓ Sent!']
                : ['success'=>false, 'error'=>$data['description']??'Telegram rejected the message.'];
        } catch (\Throwable $e) {
            DB::table('tbl_telegram_log')->insert(['type'=>$type,'target'=>$target,'chat_id'=>$chatId,'message'=>mb_substr($text,0,500),'status'=>'failed','error'=>$e->getMessage(),'sent_at'=>now()]);
            return ['success'=>false,'error'=>'Connection error: '.$e->getMessage()];
        }
    }
}
