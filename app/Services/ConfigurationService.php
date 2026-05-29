<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

class ConfigurationService
{
    protected string $uploadPath = 'uploads/brand';

    /**
     * Get the full public path for uploads.
     */
    protected function getUploadDir(): string
    {
        $dir = public_path($this->uploadPath);
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        return $dir;
    }

    /**
     * Upload an image and return the relative path (from public/).
     */
    public function uploadImage(UploadedFile $file, string $prefix = 'img'): string
    {
        $dir = $this->getUploadDir();
        $extension = $file->getClientOriginalExtension() ?: 'png';
        $filename = $prefix . '_' . time() . '_' . mt_rand(100, 999) . '.' . $extension;
        $file->move($dir, $filename);

        return $this->uploadPath . '/' . $filename;
    }

    /**
     * Delete an uploaded image by its relative path.
     */
    public function deleteImage(?string $relativePath): bool
    {
        if (!$relativePath) return false;

        $fullPath = public_path($relativePath);
        if (File::exists($fullPath)) {
            return File::delete($fullPath);
        }

        return false;
    }

    /**
     * Handle image upload for a config key — uploads new, deletes old.
     * Returns the new relative path.
     */
    public function handleUpload(UploadedFile $file, ?string $oldPath, string $prefix = 'img'): string
    {
        // Delete old file if exists
        $this->deleteImage($oldPath);

        // Upload new
        return $this->uploadImage($file, $prefix);
    }
}
