<?php

namespace App\Services;

/**
 * TOTP Two-Factor Authentication Service
 *
 * Pure PHP implementation — no external packages required.
 * Implements RFC 6238 (TOTP) and RFC 4226 (HOTP).
 * Compatible with Google Authenticator, Microsoft Authenticator, Authy.
 */
class TwoFactorService
{
    /** TOTP time step in seconds */
    protected int $period = 30;

    /** Number of OTP digits */
    protected int $digits = 6;

    /** Hash algorithm */
    protected string $algorithm = 'sha1';

    /** Time window tolerance (±1 step = ±30 seconds) */
    protected int $window = 1;

    /** Base32 alphabet */
    protected const BASE32_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    // ── Secret Generation ─────────────────────────────────────

    /**
     * Generate a random secret key (20 bytes → 32 base32 chars).
     */
    public function generateSecret(int $bytes = 20): string
    {
        $random = random_bytes($bytes);
        return $this->base32Encode($random);
    }

    // ── OTP Verification ──────────────────────────────────────

    /**
     * Verify a TOTP code against a secret.
     * Allows ±window time steps for clock skew tolerance.
     */
    public function verifyCode(string $secret, string $code): bool
    {
        if (strlen($code) !== $this->digits) {
            return false;
        }

        $currentTimestamp = $this->getTimestamp();

        for ($i = -$this->window; $i <= $this->window; $i++) {
            $expectedCode = $this->generateOTP($secret, $currentTimestamp + $i);
            if (hash_equals($expectedCode, $code)) {
                return true;
            }
        }

        return false;
    }

    // ── QR Code URI ───────────────────────────────────────────

    /**
     * Build the otpauth:// URI for authenticator apps.
     *
     * @param string $secret  Base32-encoded secret
     * @param string $label   Account label shown in the app (e.g. "admin@portal")
     * @param string $issuer  Issuer name shown in the app (e.g. "Admin Portal")
     */
    public function getOtpAuthUri(string $secret, string $label, string $issuer): string
    {
        $params = http_build_query([
            'secret'    => $secret,
            'issuer'    => $issuer,
            'algorithm' => strtoupper($this->algorithm),
            'digits'    => $this->digits,
            'period'    => $this->period,
        ], '', '&', PHP_QUERY_RFC3986);

        $label  = rawurlencode($issuer . ':' . $label);

        return "otpauth://totp/{$label}?{$params}";
    }

    // ── Internal TOTP/HOTP ────────────────────────────────────

    /**
     * Generate a TOTP code for the given timestamp.
     */
    protected function generateOTP(string $base32Secret, int $timestamp): string
    {
        $secretBytes = $this->base32Decode($base32Secret);

        // Pack timestamp as 64-bit big-endian
        $time = pack('N*', 0, $timestamp);

        // HMAC-SHA1
        $hash = hash_hmac($this->algorithm, $time, $secretBytes, true);

        // Dynamic truncation (RFC 4226 §5.4)
        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $binary =
            ((ord($hash[$offset])     & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8)  |
            ((ord($hash[$offset + 3]) & 0xFF));

        $otp = $binary % (10 ** $this->digits);

        return str_pad((string) $otp, $this->digits, '0', STR_PAD_LEFT);
    }

    /**
     * Get current TOTP timestamp (unix time / period).
     */
    protected function getTimestamp(): int
    {
        return (int) floor(time() / $this->period);
    }

    // ── Base32 Encoding/Decoding ──────────────────────────────

    /**
     * Encode raw bytes to Base32 (RFC 4648).
     */
    protected function base32Encode(string $data): string
    {
        $binary = '';
        foreach (str_split($data) as $char) {
            $binary .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }

        $result = '';
        $chunks = str_split($binary, 5);
        foreach ($chunks as $chunk) {
            $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            $result .= self::BASE32_CHARS[bindec($chunk)];
        }

        return $result;
    }

    /**
     * Decode Base32 string to raw bytes.
     */
    protected function base32Decode(string $base32): string
    {
        $base32 = strtoupper(rtrim($base32, '='));
        $binary = '';

        foreach (str_split($base32) as $char) {
            $index = strpos(self::BASE32_CHARS, $char);
            if ($index === false) {
                continue; // skip invalid chars
            }
            $binary .= str_pad(decbin($index), 5, '0', STR_PAD_LEFT);
        }

        $bytes = '';
        $chunks = str_split($binary, 8);
        foreach ($chunks as $chunk) {
            if (strlen($chunk) < 8) {
                break; // discard incomplete trailing bits
            }
            $bytes .= chr(bindec($chunk));
        }

        return $bytes;
    }
}
