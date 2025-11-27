<?php

namespace App\Traits;

use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

trait HasMedia
{
    /**
     * Get all media for this model
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('sort_order');
    }

    /**
     * Get media by type
     * 
     * @param string $type
     * @return MorphMany
     */
    public function mediaOfType(string $type): MorphMany
    {
        return $this->media()->where('type', $type);
    }

    /**
     * Get single media by type (for avatar, thumbnail, etc.)
     * 
     * @param string $type
     * @return Media|null
     */
    public function getMediaByType(string $type): ?Media
    {
        return $this->media()->where('type', $type)->first();
    }

    /**
     * Upload and attach a single media file
     * 
     * @param UploadedFile $file
     * @param string $type
     * @param string|null $folder
     * @param array $options
     * @return Media|null
     */
    public function uploadMedia(
        UploadedFile $file,
        string $type = 'default',
        ?string $folder = null,
        array $options = []
    ): ?Media {
        $mediaService = app(MediaService::class);
        return $mediaService->attachMedia($this, $file, $type, $folder, $options);
    }

    /**
     * Upload and attach multiple media files
     * 
     * @param array $files
     * @param string $type
     * @param string|null $folder
     * @return Collection
     */
    public function uploadMultipleMedia(array $files, string $type = 'gallery', ?string $folder = null): Collection
    {
        $mediaService = app(MediaService::class);
        return $mediaService->attachMultipleMedia($this, $files, $type, $folder);
    }

    /**
     * Delete media by type
     * 
     * @param string $type
     * @return bool
     */
    public function deleteMediaByType(string $type): bool
    {
        $mediaService = app(MediaService::class);
        return $mediaService->deleteMediaByType($this, $type);
    }

    /**
     * Boot trait - delete media when model is deleted
     */
    protected static function bootHasMedia(): void
    {
        static::deleting(function ($model) {
            $mediaService = app(MediaService::class);
            $model->media->each(function ($media) use ($mediaService) {
                $mediaService->deleteMedia($media);
            });
        });
    }
}
