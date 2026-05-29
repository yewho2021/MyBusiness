<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaHolder extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'tbl_media_holder';

    protected $fillable = [];

    /**
     * Register media collections.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('general');
        $this->addMediaCollection('images');
        $this->addMediaCollection('documents');
        $this->addMediaCollection('logos');
        $this->addMediaCollection('avatars');
    }

    /**
     * Register media conversions (thumbnails).
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(5)
            ->nonQueued();

        $this->addMediaConversion('preview')
            ->width(800)
            ->height(800)
            ->sharpen(3)
            ->nonQueued();
    }
}
