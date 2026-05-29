<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Configuration;
use Symfony\Component\HttpFoundation\Response;

class CheckLoginAccess
{
    /**
     * Check if the visitor's IP is allowed to access the login page.
     * If restriction is enabled and IP doesn't match → redirect to Google.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $enabled = Configuration::get('login_restriction_enabled', 'disabled');
        } catch (\Exception $e) {
            // Table may not exist yet — allow access
            return $next($request);
        }

        if ($enabled !== 'enabled') {
            return $next($request);
        }

        $type  = Configuration::get('login_restriction_type', 'ipv4');
        $value = trim(Configuration::get('login_restriction_value', ''));

        // If no value configured, skip restriction (don't lock everyone out)
        if ($value === '') {
            return $next($request);
        }

        $visitorIp = $request->ip();

        if ($this->isAllowed($visitorIp, $type, $value)) {
            return $next($request);
        }

        // Blocked — redirect away (configurable, defaults to homepage)
        $blockedUrl = Configuration::get('login_blocked_redirect', 'https://google.com');
        return redirect()->away($blockedUrl);
    }

    /**
     * Check whether the visitor IP matches the allowed source.
     */
    protected function isAllowed(string $visitorIp, string $type, string $allowedValue): bool
    {
        if ($type === 'ddns') {
            return $this->matchDdns($visitorIp, $allowedValue);
        }

        // Direct IP comparison (ipv4 or ipv6)
        return $this->normalizeIp($visitorIp) === $this->normalizeIp($allowedValue);
    }

    /**
     * Resolve a DDNS hostname and compare against visitor IP.
     */
    protected function matchDdns(string $visitorIp, string $hostname): bool
    {
        // Resolve hostname to all IP addresses
        $resolved = gethostbynamel($hostname);

        if ($resolved === false) {
            // Also try IPv6 via dns_get_record
            $resolved = [];
            $records = @dns_get_record($hostname, DNS_A | DNS_AAAA);
            if ($records) {
                foreach ($records as $record) {
                    if (isset($record['ip']))   $resolved[] = $record['ip'];
                    if (isset($record['ipv6'])) $resolved[] = $record['ipv6'];
                }
            }
        }

        if (empty($resolved)) {
            return false; // Can't resolve → deny for safety
        }

        $normalizedVisitor = $this->normalizeIp($visitorIp);

        foreach ($resolved as $ip) {
            if ($normalizedVisitor === $this->normalizeIp($ip)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalize an IP address for comparison.
     * IPv6 addresses are expanded to full form; IPv4 trimmed.
     */
    protected function normalizeIp(string $ip): string
    {
        $ip = trim($ip);

        // Try to parse with inet_pton / inet_ntop for canonical form
        $packed = @inet_pton($ip);
        if ($packed !== false) {
            return inet_ntop($packed);
        }

        return strtolower($ip);
    }
}
