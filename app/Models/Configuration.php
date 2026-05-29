<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Configuration extends Model
{
    use LogsActivity;

    protected $table = 'tbl_configuration';

    public $timestamps = false;

    protected $fillable = [
        'group', 'key', 'value', 'type', 'label', 'description',
        'options', 'default_value', 'sort_order', 'is_active',
        'updated_at', 'updated_by',
    ];

    // Cache key and TTL
    protected static string $cacheKey = 'config_all';
    protected static int $cacheTtl = 300; // 5 minutes

    // Keys whose values are stored encrypted in the database.
    protected static array $sensitiveKeys = [
        'mail_password',
        'mail_username',
        'sp_db_password',
        'smtp_password',
        'api_key',
        'webhook_secret',
    ];

    // ── Activity Log Options ─────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['key', 'value', 'group'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('config')
            ->setDescriptionForEvent(fn(string $eventName) => "Configuration '{$this->key}' was {$eventName}");
    }

    // ── Request-level + cross-request cache ───────────────

    protected static ?array $requestCache = null;

    /**
     * Clear both request-level and cross-request caches.
     * Called after any config save/update/reset.
     */
    public static function clearCache(): void
    {
        static::$requestCache = null;
        Cache::forget(static::$cacheKey);
    }

    /**
     * Load all active config rows.
     * Uses cross-request cache (5-min TTL) + request-level static cache.
     */
    public static function getAll(): array
    {
        // Request-level cache: avoids even a Cache::get call on repeated reads
        if (static::$requestCache !== null) {
            return static::$requestCache;
        }

        try {
            static::$requestCache = Cache::remember(static::$cacheKey, static::$cacheTtl, function () {
                $rows = static::where('is_active', 1)->get();
                $map = [];
                foreach ($rows as $row) {
                    $map[$row->key] = $row->value ?? $row->default_value;
                }
                return $map;
            });
        } catch (\Exception $e) {
            // Table might not exist yet (during first migration)
            static::$requestCache = [];
        }

        return static::$requestCache;
    }

    /**
     * Get a single config value with fallback.
     * Auto-decrypts values for keys in $sensitiveKeys.
     */
    public static function get(string $key, $default = null)
    {
        $all = static::getAll();
        $value = $all[$key] ?? $default;

        if (in_array($key, static::$sensitiveKeys) && $value !== null && $value !== '') {
            try { $value = decrypt($value); } catch (\Exception $e) { /* still plaintext */ }
        }

        return $value;
    }

    /**
     * Get all keys in a group as key => value.
     */
    public static function getGroup(string $group): array
    {
        try {
            return static::where('group', $group)
                ->where('is_active', 1)
                ->orderBy('sort_order')
                ->get()
                ->mapWithKeys(fn($r) => [$r->key => $r->value ?? $r->default_value])
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get all rows in a group (full objects for the settings form).
     */
    public static function getGroupRows(string $group)
    {
        return static::where('group', $group)
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Encrypt value if the key is in the sensitive keys list.
     */
    public static function encryptIfSensitive(string $key, $value)
    {
        if (in_array($key, static::$sensitiveKeys) && $value !== null && $value !== '') {
            return encrypt($value);
        }
        return $value;
    }

    /**
     * Update a single key.
     */
    public static function set(string $key, $value, ?int $updatedBy = null): bool
    {
        $row = static::where('key', $key)->first();
        if (!$row) return false;

        if (in_array($key, static::$sensitiveKeys) && $value !== null && $value !== '') {
            $value = encrypt($value);
        }

        $row->value = $value;
        $row->updated_at = now();
        $row->updated_by = $updatedBy;
        $row->save();

        static::clearCache();
        return true;
    }

    /**
     * Bulk update from form submission.
     */
    public static function setMany(array $data, ?int $updatedBy = null): int
    {
        $count = 0;
        foreach ($data as $key => $value) {
            $row = static::where('key', $key)->first();
            if ($row) {
                if (in_array($key, static::$sensitiveKeys) && $value !== null && $value !== '') {
                    $value = encrypt($value);
                }
                $row->value = $value;
                $row->updated_at = now();
                $row->updated_by = $updatedBy;
                $row->save();
                $count++;
            }
        }
        static::clearCache();
        return $count;
    }

    /**
     * Reset a group to default values.
     */
    public static function resetGroup(string $group): int
    {
        $rows = static::where('group', $group)->get();
        $count = 0;
        foreach ($rows as $row) {
            if ($row->type !== 'image') {
                $row->value = $row->default_value;
            } else {
                $row->value = null;
            }
            $row->updated_at = now();
            $row->save();
            $count++;
        }
        static::clearCache();
        return $count;
    }

    /**
     * Get parsed options array for select fields.
     */
    public function getOptionsArray(): array
    {
        if (!$this->options) return [];
        $decoded = json_decode($this->options, true);
        return is_array($decoded) ? $decoded : [];
    }

    // ── Google Fonts ──────────────────────────────

    public static function fontUrlMap(): array
    {
        return [
            // ── Sans-Serif: Popular ──
            'Inter'             => 'Inter:wght@300;400;500;600;700',
            'Roboto'            => 'Roboto:wght@300;400;500;700',
            'Poppins'           => 'Poppins:wght@300;400;500;600;700',
            'Open Sans'         => 'Open+Sans:wght@300;400;500;600;700',
            'Lato'              => 'Lato:wght@300;400;700',
            'Montserrat'        => 'Montserrat:wght@300;400;500;600;700',
            'Nunito'            => 'Nunito:wght@300;400;500;600;700',
            'Raleway'           => 'Raleway:wght@300;400;500;600;700',

            // ── Sans-Serif: Modern / Geometric ──
            'DM Sans'           => 'DM+Sans:wght@300;400;500;600;700',
            'Plus Jakarta Sans' => 'Plus+Jakarta+Sans:wght@300;400;500;600;700',
            'Outfit'            => 'Outfit:wght@300;400;500;600;700',
            'Figtree'           => 'Figtree:wght@300;400;500;600;700',
            'Manrope'           => 'Manrope:wght@300;400;500;600;700',
            'Space Grotesk'     => 'Space+Grotesk:wght@300;400;500;600;700',
            'Sora'              => 'Sora:wght@300;400;500;600;700',
            'Urbanist'          => 'Urbanist:wght@300;400;500;600;700',
            'Albert Sans'       => 'Albert+Sans:wght@300;400;500;600;700',
            'Lexend'            => 'Lexend:wght@300;400;500;600;700',
            'Red Hat Display'   => 'Red+Hat+Display:wght@300;400;500;600;700',
            'Josefin Sans'      => 'Josefin+Sans:wght@300;400;500;600;700',
            'Barlow'            => 'Barlow:wght@300;400;500;600;700',
            'Jost'              => 'Jost:wght@300;400;500;600;700',

            // ── Sans-Serif: Humanist / Friendly ──
            'Source Sans 3'     => 'Source+Sans+3:wght@300;400;500;600;700',
            'Work Sans'         => 'Work+Sans:wght@300;400;500;600;700',
            'Noto Sans'         => 'Noto+Sans:wght@300;400;500;600;700',
            'Rubik'             => 'Rubik:wght@300;400;500;600;700',
            'Karla'             => 'Karla:wght@300;400;500;600;700',
            'Cabin'             => 'Cabin:wght@400;500;600;700',
            'Overpass'          => 'Overpass:wght@300;400;500;600;700',
            'Hind'              => 'Hind:wght@300;400;500;600;700',
            'Mulish'            => 'Mulish:wght@300;400;500;600;700',
            'Libre Franklin'    => 'Libre+Franklin:wght@300;400;500;600;700',
            'Assistant'         => 'Assistant:wght@300;400;500;600;700',
            'IBM Plex Sans'     => 'IBM+Plex+Sans:wght@300;400;500;600;700',
            'Exo 2'             => 'Exo+2:wght@300;400;500;600;700',
            'Mukta'             => 'Mukta:wght@300;400;500;600;700',

            // ── Sans-Serif: Rounded / Soft ──
            'Quicksand'         => 'Quicksand:wght@300;400;500;600;700',
            'Comfortaa'         => 'Comfortaa:wght@300;400;500;600;700',
            'Varela Round'      => 'Varela+Round:wght@400',
            'ABeeZee'           => 'ABeeZee:wght@400',
            'Nunito Sans'       => 'Nunito+Sans:wght@300;400;500;600;700',

            // ── Sans-Serif: Display / Bold ──
            'Oswald'            => 'Oswald:wght@300;400;500;600;700',
            'Bebas Neue'        => 'Bebas+Neue:wght@400',
            'Anton'             => 'Anton:wght@400',
            'Archivo'           => 'Archivo:wght@300;400;500;600;700',
            'Titillium Web'     => 'Titillium+Web:wght@300;400;600;700',
            'Saira'             => 'Saira:wght@300;400;500;600;700',
            'Lexend Deca'       => 'Lexend+Deca:wght@300;400;500;600;700',

            // ── Serif ──
            'Playfair Display'  => 'Playfair+Display:wght@400;500;600;700',
            'Merriweather'      => 'Merriweather:wght@300;400;700',
            'Lora'              => 'Lora:wght@400;500;600;700',
            'PT Serif'          => 'PT+Serif:wght@400;700',
            'Noto Serif'        => 'Noto+Serif:wght@400;500;600;700',
            'Source Serif 4'    => 'Source+Serif+4:wght@300;400;500;600;700',
            'Libre Baskerville' => 'Libre+Baskerville:wght@400;700',
            'Crimson Text'      => 'Crimson+Text:wght@400;600;700',
            'EB Garamond'       => 'EB+Garamond:wght@400;500;600;700',
            'Cormorant Garamond'=> 'Cormorant+Garamond:wght@300;400;500;600;700',
            'DM Serif Display'  => 'DM+Serif+Display:wght@400',
            'Bitter'            => 'Bitter:wght@300;400;500;600;700',

            // ── Monospace ──
            'JetBrains Mono'    => 'JetBrains+Mono:wght@400;500;600;700',
            'Fira Code'         => 'Fira+Code:wght@400;500;600;700',
            'Source Code Pro'   => 'Source+Code+Pro:wght@400;500;600;700',
            'IBM Plex Mono'     => 'IBM+Plex+Mono:wght@400;500;600;700',
            'Roboto Mono'       => 'Roboto+Mono:wght@400;500;600;700',
            'Ubuntu Mono'       => 'Ubuntu+Mono:wght@400;700',
            'Space Mono'        => 'Space+Mono:wght@400;700',
            'Inconsolata'       => 'Inconsolata:wght@300;400;500;600;700',
            'Red Hat Mono'      => 'Red+Hat+Mono:wght@400;500;600;700',
            'DM Mono'           => 'DM+Mono:wght@400;500',
            'Overpass Mono'     => 'Overpass+Mono:wght@400;500;600;700',
            'Noto Sans Mono'    => 'Noto+Sans+Mono:wght@400;500;600;700',
        ];
    }

    public static function googleFontUrl(): string
    {
        $cfg = static::getAll();
        $fontFamily = $cfg['font_family'] ?? 'Inter';
        $fontMono   = $cfg['font_mono'] ?? 'JetBrains Mono';
        $map = static::fontUrlMap();

        $families = [];
        if (isset($map[$fontFamily])) $families[] = $map[$fontFamily];
        if (isset($map[$fontMono]))   $families[] = $map[$fontMono];

        if (empty($families)) {
            return 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap';
        }

        return 'https://fonts.googleapis.com/css2?family=' . implode('&family=', $families) . '&display=swap';
    }

    // ── CSS Variable Output ──────────────────────

    public static function cssVariables(): string
    {
        $c = static::getAll();
        $v = function($key, $default) use ($c) { return $c[$key] ?? $default; };

        return "
    /* ── Theme Colors ─────────────────────── */
    --c-primary: {$v('primary','#dc2626')};
    --c-primary-hover: {$v('primary_hover','#b91c1c')};
    --c-primary-light: {$v('primary_light','#fef2f2')};
    --c-secondary: {$v('secondary','#2563eb')};
    --c-secondary-hover: {$v('secondary_hover','#1d4ed8')};
    --c-secondary-light: {$v('secondary_light','#eff6ff')};
    --c-secondary-border: {$v('secondary_border','#bfdbfe')};
    --c-success: {$v('success','#16a34a')};
    --c-success-light: {$v('success_light','#f0fdf4')};
    --c-success-border: {$v('success_border','#bbf7d0')};
    --c-warning: {$v('warning','#d97706')};
    --c-warning-light: {$v('warning_light','#fffbeb')};
    --c-warning-border: {$v('warning_border','#fde68a')};
    --c-danger: {$v('danger','#dc2626')};
    --c-danger-light: {$v('danger_light','#fef2f2')};
    --c-danger-border: {$v('danger_border','#fecaca')};
    --c-info: {$v('info','#0ea5e9')};
    --c-info-light: {$v('info_light','#f0f9ff')};
    --c-info-border: {$v('info_border','#bae6fd')};
    --c-purple: {$v('purple','#7c3aed')};
    --c-purple-light: {$v('purple_light','#f3e8ff')};

    /* ── Text Colors ─────────────────────── */
    --text-heading: {$v('text_heading','#0f172a')};
    --text-primary: {$v('text_primary','#1e293b')};
    --text-body: {$v('text_body','#334155')};
    --text-secondary: {$v('text_secondary','#475569')};
    --text-muted: {$v('text_muted','#64748b')};
    --text-faint: {$v('text_faint','#94a3b8')};
    --text-placeholder: {$v('text_placeholder','#9ca3af')};

    /* ── Sidebar ──────────────────────────── */
    --sidebar-bg: {$v('sidebar_bg','#111111')};
    --sidebar-text: {$v('sidebar_text','#d1d5db')};
    --sidebar-text-muted: {$v('sidebar_text_muted','#6b7280')};
    --sidebar-hover-bg: {$v('sidebar_hover_bg','rgba(220,38,38,0.1)')};
    --sidebar-active-bg: {$v('sidebar_active_bg','#dc2626')};
    --sidebar-active-text: {$v('sidebar_active_text','#ffffff')};
    --sidebar-width: {$v('sidebar_width','260')}px;
    --sidebar-logo-bg: {$v('sidebar_logo_bg','#dc2626')};
    --sidebar-border: {$v('sidebar_border_color','rgba(255,255,255,0.08)')};
    --sidebar-header-bg: {$v('sidebar_header_bg','')};

    /* ── Header ───────────────────────────── */
    --header-bg: {$v('header_bg','#ffffff')};
    --header-text: {$v('header_text','#1e293b')};
    --header-height: {$v('header_height','60')}px;
    --header-border: {$v('header_border','#e2e8f0')};
    --header-avatar-bg: {$v('header_avatar_bg','#dc2626')};

    /* ── Typography ───────────────────────── */
    --font-family: '{$v('font_family','Inter')}', sans-serif;
    --font-mono: '{$v('font_mono','JetBrains Mono')}', monospace;
    --fs-base: {$v('font_size_base','14')}px;
    --fs-sm: {$v('font_size_sm','13')}px;
    --fs-xs: {$v('font_size_xs','12')}px;
    --fs-lg: {$v('font_size_lg','16')}px;
    --fs-h1: {$v('font_size_h1','24')}px;
    --fs-h2: {$v('font_size_h2','20')}px;
    --fs-h3: {$v('font_size_h3','16')}px;

    /* ── Layout & Surfaces ────────────────── */
    --body-bg: {$v('body_bg','#f1f5f9')};
    --card-bg: {$v('card_bg','#ffffff')};
    --card-radius: {$v('card_radius','12')}px;
    --card-border: {$v('card_border','#e2e8f0')};
    --btn-radius: {$v('button_radius','8')}px;
    --input-radius: {$v('input_radius','8')}px;
    --content-padding: {$v('content_padding','24')}px;
    --table-header-bg: {$v('table_header_bg','#f8fafc')};
    --border-color: {$v('border_color','#e2e8f0')};
    --border-light: {$v('border_light','#f1f5f9')};
    --input-border: {$v('input_border','#d1d5db')};
    --hover-border: {$v('hover_border','#cbd5e1')};
    --hover-bg: {$v('hover_bg','#f8fafc')};
    --focus-ring: {$v('focus_ring','rgba(37,99,235,0.1)')};
    --shadow-sm: {$v('shadow_sm','0 1px 3px rgba(0,0,0,0.08)')};
    --shadow-md: {$v('shadow_md','0 4px 12px rgba(0,0,0,0.06)')};
    --shadow-lg: {$v('shadow_lg','0 10px 15px rgba(0,0,0,0.1)')};
    --modal-backdrop: {$v('modal_backdrop','rgba(15,23,42,0.6)')};

    /* ── Code Blocks ─────────────────────── */
    --code-bg: {$v('code_bg','#0f172a')};
    --code-text: {$v('code_text','#e2e8f0')};
";
    }
}
