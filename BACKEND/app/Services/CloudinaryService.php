<?php

namespace App\Services;

use Cloudinary\Transformation\Resize;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Exception;

class CloudinaryService
{
    /**
     * Upload a single file to Cloudinary
     */
    public function upload(UploadedFile $file, string $folder = 'uploads'): array
    {
        try {
            $result = Cloudinary::uploadApi()->upload($file->getRealPath(), [
                'folder' => $folder,
                'resource_type' => 'auto',
                'unique_filename' => true,
            ]);

            return [
                'success' => true,
                'public_id' => $result['public_id'],
                'url' => $result['secure_url'],
            ];
        } catch (Exception $e) {
            Log::error('Cloudinary upload failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Upload avatar with automatic optimization
     */
    public function delete(string $publicId): bool
    {
        try {
            $result = Cloudinary::uploadApi()->destroy($publicId);
            return $result['result'] === 'ok';
        } catch (Exception $e) {
            Log::error('Cloudinary delete failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Upload multiple images (for galleries, multiple files, etc.)
     */
    public function getUrl(string $publicId, ?int $width = null, ?int $height = null): string
    {
        try {
            if (!$width || !$height) {
                return cloudinary()->getUrl($publicId);
            }
            $image = cloudinary()->image($publicId);
            if ($width && $height) {
                $image = $image->resize(Resize::scale()->width($width)->height($height));
            }

            return $image->toUrl();
        } catch (Exception $e) {
            Log::error('Cloudinary URL generation failed: ' . $e->getMessage());
            return '';
        }
    }
}
