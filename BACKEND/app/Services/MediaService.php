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

    public function attachMedia(
        Model $model,
        UploadedFile $file,
        string $type = 'default',
        ?string $folder = null
    ): ?Media {
        // Auto-generate folder
        if (!$folder) {
            $modelName = strtolower(class_basename($model));
            $folder = "{$modelName}s/{$model->getKey()}/{$type}";
        }

        // Nếu là avatar/thumbnail → Xóa cái cũ trước
        if (in_array($type, ['avatar', 'thumbnail'])) {
            $this->deleteMediaByType($model, $type);
        }
        $result = $this->cloudinaryService->upload($file, $folder);

        if (!$result['success']) {
            return null;
        }

        // Create media record
        return $model->media()->create([
            'public_id' => $result['public_id'],
            'url' => $result['url'],
            'type' => $type,
        ]);
    }

    public function attachMultiple(
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

    public function deleteMedia(Media $media): bool
    {
        $this->cloudinaryService->delete($media->public_id);
        return $media->delete();
    }

    public function deleteMediaByType(Model $model, string $type): void
    {
        $media = $model->media()->where('type', $type)->get();

        foreach ($media as $m) {
            $this->deleteMedia($m);
        }
    }
    public function getMediaByType(Model $model, string $type): Collection
    {
        return $model->media()->where('type', $type)->get();
    }
}
