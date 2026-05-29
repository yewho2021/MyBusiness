<?php

namespace App\Services;

/**
 * Shared SQL parsing utilities.
 *
 * Extracted from DatabaseController + SystemPatchController to eliminate
 * 130+ lines of duplicated code. Both controllers now call these static methods.
 *
 * @since 2026-03-29 P0/P1 patch
 */
class SqlParser
{
    /**
     * Split a SQL dump into individual executable statements.
     *
     * Handles: single-quoted strings, double-quoted strings, backtick identifiers,
     * escaped quotes (\' and ''), -- line comments, # comments, block comments,
     * MySQL conditional comments (/*!40101 ... * /), and DELIMITER commands.
     */
    public static function splitSql(string $sql): array
    {
        $stmts = [];
        $len = strlen($sql);
        $cur = '';
        $delimiter = ';';
        $i = 0;

        while ($i < $len) {
            $c = $sql[$i];

            // ── DELIMITER command (e.g. "DELIMITER $$" or "DELIMITER ;") ──
            if ($cur === '' || trim($cur) === '') {
                $remaining = substr($sql, $i, 20);
                if (preg_match('/^DELIMITER\s+(\S+)/i', $remaining, $dm)) {
                    $delimiter = $dm[1];
                    $i += strlen($dm[0]);
                    $cur = '';
                    // Skip to end of line
                    while ($i < $len && $sql[$i] !== "\n") $i++;
                    if ($i < $len) $i++; // skip \n

                    continue;
                }
            }

            // ── Single-line comment: -- ──
            if ($c === '-' && $i + 1 < $len && $sql[$i + 1] === '-') {
                // Skip to end of line
                while ($i < $len && $sql[$i] !== "\n") $i++;
                if ($i < $len) $i++; // skip \n
                // Add a space to prevent token merging
                if (trim($cur) !== '') $cur .= ' ';
                continue;
            }

            // ── Single-line comment: # ──
            if ($c === '#') {
                while ($i < $len && $sql[$i] !== "\n") $i++;
                if ($i < $len) $i++;
                if (trim($cur) !== '') $cur .= ' ';
                continue;
            }

            // ── Block comment: /* ... */ ──
            if ($c === '/' && $i + 1 < $len && $sql[$i + 1] === '*') {
                // Check for MySQL conditional: /*!40101 ... */
                $isConditional = ($i + 2 < $len && $sql[$i + 2] === '!');
                $i += 2; // skip /*
                $commentBody = '';
                while ($i < $len) {
                    if ($sql[$i] === '*' && $i + 1 < $len && $sql[$i + 1] === '/') {
                        $i += 2; // skip */
                        break;
                    }
                    $commentBody .= $sql[$i];
                    $i++;
                }
                // For conditional comments like /*!40101 SET ... */, extract the SQL
                if ($isConditional && preg_match('/^!\d+\s+(.+)$/s', $commentBody, $cm)) {
                    $cur .= $cm[1];
                }
                continue;
            }

            // ── Quoted string: ' or " ──
            if ($c === '\'' || $c === '"') {
                $quote = $c;
                $cur .= $c;
                $i++;
                while ($i < $len) {
                    $sc = $sql[$i];
                    $cur .= $sc;
                    if ($sc === '\\') {
                        // Escaped char — consume next char unconditionally
                        $i++;
                        if ($i < $len) {
                            $cur .= $sql[$i];
                        }
                    } elseif ($sc === $quote) {
                        // Check for doubled quote escape: '' or ""
                        if ($i + 1 < $len && $sql[$i + 1] === $quote) {
                            $cur .= $sql[$i + 1];
                            $i++; // skip the doubled quote
                        } else {
                            break; // end of string
                        }
                    }
                    $i++;
                }
                $i++;
                continue;
            }

            // ── Backtick identifier ──
            if ($c === '`') {
                $cur .= $c;
                $i++;
                while ($i < $len) {
                    $cur .= $sql[$i];
                    if ($sql[$i] === '`') {
                        // Check for doubled backtick: ``
                        if ($i + 1 < $len && $sql[$i + 1] === '`') {
                            $cur .= $sql[$i + 1];
                            $i++;
                        } else {
                            break;
                        }
                    }
                    $i++;
                }
                $i++;
                continue;
            }

            // ── Delimiter check ──
            $delimLen = strlen($delimiter);
            if (substr($sql, $i, $delimLen) === $delimiter) {
                $stmt = trim($cur);
                if ($stmt !== '') {
                    $stmts[] = $stmt;
                }
                $cur = '';
                $i += $delimLen;
                continue;
            }

            // ── Regular character ──
            $cur .= $c;
            $i++;
        }

        // Remaining buffer
        $stmt = trim($cur);
        if ($stmt !== '') {
            $stmts[] = $stmt;
        }

        return $stmts;
    }

    /**
     * Build a short human-readable preview of a SQL statement.
     */
    public static function statementPreview(string $stmt): string
    {
        // Normalize whitespace
        $clean = preg_replace('/\s+/', ' ', trim($stmt));

        if (preg_match('/^(CREATE\s+TABLE(?:\s+IF\s+NOT\s+EXISTS)?)\s+`?(\w+)`?/i', $clean, $m)) {
            return strtoupper($m[1]) . " `{$m[2]}`";
        }
        if (preg_match('/^(DROP\s+TABLE(?:\s+IF\s+EXISTS)?)\s+`?(\w+)`?/i', $clean, $m)) {
            return strtoupper($m[1]) . " `{$m[2]}`";
        }
        if (preg_match('/^(ALTER\s+TABLE)\s+`?(\w+)`?/i', $clean, $m)) {
            return "ALTER TABLE `{$m[2]}`";
        }
        if (preg_match('/^(INSERT\s+INTO)\s+`?(\w+)`?/i', $clean, $m)) {
            // Count approximate rows
            $rows = substr_count($clean, '),(') + 1;
            return "INSERT INTO `{$m[2]}` ({$rows} row" . ($rows > 1 ? 's' : '') . ")";
        }
        if (preg_match('/^(UPDATE)\s+`?(\w+)`?/i', $clean, $m)) {
            return "UPDATE `{$m[2]}`";
        }
        if (preg_match('/^(DELETE\s+FROM)\s+`?(\w+)`?/i', $clean, $m)) {
            return "DELETE FROM `{$m[2]}`";
        }
        if (preg_match('/^(TRUNCATE(?:\s+TABLE)?)\s+`?(\w+)`?/i', $clean, $m)) {
            return "TRUNCATE `{$m[2]}`";
        }
        if (preg_match('/^(CREATE\s+(?:UNIQUE\s+)?INDEX)\s+`?(\w+)`?\s+ON\s+`?(\w+)`?/i', $clean, $m)) {
            return "CREATE INDEX `{$m[2]}` ON `{$m[3]}`";
        }
        if (preg_match('/^SET\s+/i', $clean)) {
            return mb_substr($clean, 0, 60) . (mb_strlen($clean) > 60 ? '...' : '');
        }

        // Fallback: first 80 chars
        return mb_substr($clean, 0, 80) . (mb_strlen($clean) > 80 ? '...' : '');
    }

    /**
     * Format file size in human-readable form.
     */
    public static function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return number_format($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }
}
