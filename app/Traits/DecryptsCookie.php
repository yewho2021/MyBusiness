<?php

namespace App\Traits;

trait DecryptsCookie
{
    /**
     * Decrypt the admin_id cookie.
     * Returns null if invalid or tampered.
     */
    protected function decryptCookie($value): ?int
    {
        if (!$value) return null;

        try {
            return (int) decrypt($value);
        } catch (\Exception $e) {
            // Invalid or tampered cookie — reject
            return null;
        }
    }
}
