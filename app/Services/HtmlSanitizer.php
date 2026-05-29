<?php

namespace App\Services;

/**
 * Sanitizes user-supplied HTML/CSS content from Configuration.
 * Prevents XSS via custom_css, custom_head_html, and footer_text fields.
 */
class HtmlSanitizer
{
    /**
     * Sanitize CSS input — strip anything that isn't a CSS rule.
     * Removes: url(), expression(), behavior(), @import, javascript:, </style>
     */
    public static function sanitizeCSS(?string $css): string
    {
        if (empty($css)) return '';

        // Remove attempts to break out of <style> tag
        $css = preg_replace('/<\/style>/i', '', $css);

        // Remove dangerous CSS functions
        $css = preg_replace('/url\s*\([^)]*\)/i', '/* url blocked */', $css);
        $css = preg_replace('/expression\s*\(/i', '/* blocked */', $css);
        $css = preg_replace('/behavior\s*:/i', '/* blocked */', $css);

        // Remove @import (can load external CSS with malicious content)
        $css = preg_replace('/@import\s+[^;]+;?/i', '/* import blocked */', $css);

        // Remove any HTML tags that might have snuck in
        $css = strip_tags($css);

        return trim($css);
    }

    /**
     * Sanitize head HTML — only allow safe meta and link tags.
     * Removes: script, iframe, object, embed, form, style with expressions.
     */
    public static function sanitizeHeadHtml(?string $html): string
    {
        if (empty($html)) return '';

        // Only allow <meta> and <link> tags (most common head additions)
        $html = strip_tags($html, '<meta><link>');

        // Remove any event handlers that might have survived
        $html = preg_replace('/\s+on\w+\s*=\s*["\'][^"\']*["\']/i', '', $html);

        // Remove javascript: in href/src attributes
        $html = preg_replace('/(?:href|src)\s*=\s*["\']javascript:[^"\']*["\']/i', '', $html);

        return trim($html);
    }

    /**
     * Sanitize rich text — allow basic formatting only.
     * Used for footer text, changelog details, etc.
     */
    public static function sanitizeRichText(?string $html): string
    {
        if (empty($html)) return '';

        // Allow basic formatting tags only
        $html = strip_tags($html, '<b><strong><i><em><a><br><p><ul><ol><li><span><code>');

        // Remove event handlers from allowed tags
        $html = preg_replace('/\s+on\w+\s*=\s*["\'][^"\']*["\']/i', '', $html);

        // Remove javascript: in href attributes
        $html = preg_replace('/href\s*=\s*["\']javascript:[^"\']*["\']/i', 'href="#"', $html);

        return trim($html);
    }
}
