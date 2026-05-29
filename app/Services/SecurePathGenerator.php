<?php

namespace App\Services;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

/**
 * Secure path generator for Media Library.
 * 
 * Instead of predictable sequential paths like /1/, /2/, /3/
 * this generates hashed paths like /a7f3b2c1d4e5/
 * making it impossible to enumerate or guess file locations.
 */
class SecurePathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        return $this->getHashedFolder($media) . '/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getHashedFolder($media) . '/conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getHashedFolder($media) . '/responsive/';
    }

    /**
     * Generate a unique, non-guessable folder name.
     * Uses the media's UUID (set on creation) for consistency.
     * Falls back to a hash of ID + created_at if UUID is missing.
     */
    private function getHashedFolder(Media $media): string
    {
        if ($media->uuid) {
            // Use first 12 chars of UUID (unique enough, not guessable)
            return substr(str_replace('-', '', $media->uuid), 0, 12);
        }

        // Fallback: hash the ID with a salt
        return substr(md5($media->id . '_' . config('app.name', 'admin') . '_' . $media->created_at), 0, 12);
    }
}
