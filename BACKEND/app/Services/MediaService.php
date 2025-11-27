<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class MediaService
{
    protected CloudinaryService $cloudinaryService;

    public function __construct(CloudinaryService $cloudinaryService)
    {
        $this->cloudinaryService = $cloudinaryService;
    }

    /**
     * Upload and attach a single media to a model
     * 
     * @param Model $model
     * @param UploadedFile $file
     * @param string $type Media type (avatar, document, gallery, etc.)
     * @param string|null $folder Custom folder path
     * @param array $options Additional Cloudinary options
     * @return Media|null
     */
    public function attachMedia(
        Model $model,
        UploadedFile $file,
        string $type = 'default',
        ?string $folder = null,
        array $options = []
    ): ?Media {
        // Auto-generate folder if not provided
        if (!$folder) {
            $folder = strtolower(class_basename($model)) . 's/' . $type;
            if (method_exists($model, 'getKey')) {
                $folder .= '/id_' . $model->getKey();
            }
        }

        // Handle special types
        if ($type === 'avatar') {
            $uploadResult = $this->cloudinaryService->uploadAvatar($file, $model->getKey());
        } else {
            $uploadResult = $this->cloudinaryService->upload($file, $folder, $options);
        }

        if (!$uploadResult['success']) {
            return null;
        }

        // Delete old media of same type if it's a single-type media (like avatar)
        if (in_array($type, ['avatar', 'thumbnail'])) {
            $this->deleteMediaByType($model, $type);
        }

        // Create media record
        return Media::create([
            'public_id' => $uploadResult['public_id'],
            'url' => $uploadResult['url'],
            'secure_url' => $uploadResult['secure_url'],
            'resource_type' => $uploadResult['resource_type'],
            'format' => $uploadResult['format'],
            'size' => $uploadResult['size'],
            'width' => $uploadResult['width'] ?? null,
            'height' => $uploadResult['height'] ?? null,
            'folder' => $uploadResult['folder'],
            'type' => $type,
            'mime_type' => $file->getMimeType(),
            'mediable_type' => get_class($model),
            'mediable_id' => $model->getKey(),
            'sort_order' => $this->getNextSortOrder($model, $type),
        ]);
    }

    /**
     * Upload and attach multiple media files
     * 
     * @param Model $model
     * @param array $files
     * @param string $type
     * @param string|null $folder
     * @return Collection
     */
    public function attachMultipleMedia(
        Model $model,
        array $files,
        string $type = 'gallery',
        ?string $folder = null
    ): Collection {
        $mediaCollection = collect();

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $media = $this->attachMedia($model, $file, $type, $folder);
                if ($media) {
                    $mediaCollection->push($media);
                }
            }
        }

        return $mediaCollection;
    }

    /**
     * Delete a media record and file from Cloudinary
     * 
     * @param Media $media
     * @return bool
     */
    public function deleteMedia(Media $media): bool
    {
        // Delete from Cloudinary
        $this->cloudinaryService->delete($media->public_id, $media->resource_type);

        // Delete from database
        return $media->delete();
    }

    /**
     * Delete all media of a specific type for a model
     * 
     * @param Model $model
     * @param string $type
     * @return bool
     */
    public function deleteMediaByType(Model $model, string $type): bool
    {
        $media = Media::where('mediable_type', get_class($model))
            ->where('mediable_id', $model->getKey())
            ->where('type', $type)
            ->get();

        foreach ($media as $m) {
            $this->deleteMedia($m);
        }

        return true;
    }

    /**
     * Get media by type for a model
     * 
     * @param Model $model
     * @param string|null $type
     * @return Collection
     */
    public function getMedia(Model $model, ?string $type = null): Collection
    {
        $query = Media::where('mediable_type', get_class($model))
            ->where('mediable_id', $model->getKey())
            ->orderBy('sort_order');

        if ($type) {
            $query->where('type', $type);
        }

        return $query->get();
    }

    /**
     * Get single media by type (for avatar, thumbnail, etc.)
     * 
     * @param Model $model
     * @param string $type
     * @return Media|null
     */
    public function getMediaByType(Model $model, string $type): ?Media
    {
        return Media::where('mediable_type', get_class($model))
            ->where('mediable_id', $model->getKey())
            ->where('type', $type)
            ->first();
    }

    /**
     * Get next sort order for media
     * 
     * @param Model $model
     * @param string $type
     * @return int
     */
    protected function getNextSortOrder(Model $model, string $type): int
    {
        $maxOrder = Media::where('mediable_type', get_class($model))
            ->where('mediable_id', $model->getKey())
            ->where('type', $type)
            ->max('sort_order');

        return ($maxOrder ?? 0) + 1;
    }

    /**
     * Reorder media
     * 
     * @param array $mediaIds Array of media IDs in desired order
     * @return bool
     */
    public function reorderMedia(array $mediaIds): bool
    {
        foreach ($mediaIds as $index => $mediaId) {
            Media::where('id', $mediaId)->update(['sort_order' => $index + 1]);
        }

        return true;
    }
}
