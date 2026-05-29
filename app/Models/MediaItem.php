<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaItem extends Model implements HasMedia
{
    use InteractsWithMedia;

    /**
     * This model doesn't have its own table.
     * It uses a virtual record (model_type=App\Models\MediaItem, model_id=0)
     * as a holder for general/standalone media uploads.
     * 
     * We set the table to tbl_admin just to avoid errors,
     * but we never actually query this model by its own table.
     */
    protected $table = 'tbl_admin';

    /**
     * Register media collections.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('general');
        $this->addMediaCollection('avatars');
        $this->addMediaCollection('documents');
        $this->addMediaCollection('logos');
    }

    /**
     * Register media conversions.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10)
            ->nonQueued();

        $this->addMediaConversion('preview')
            ->width(800)
            ->height(600)
            ->nonQueued();
    }
}
