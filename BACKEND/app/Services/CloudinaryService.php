<?php

namespace App\Services;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Exception;

class CloudinaryService
{
    /**
     * Upload a single file to Cloudinary
     */
    public function upload(UploadedFile $file, string $folder = 'uploads', array $options = []): array
    {
        try {
            $defaultOptions = [
                'folder' => $folder,
                'resource_type' => 'auto', // auto-detect image, video, etc.
                'use_filename' => true,
                'unique_filename' => true,
                'eager' => [], // eager transformations
                'eager_async' => false,
            ];

            $uploadOptions = array_merge($defaultOptions, $options);

            // Uses Cloudinary PHP SDK v3: upload via UploadApi
            $result = Cloudinary::uploadApi()->upload($file->getRealPath(), $uploadOptions);


            return [
                'success' => true,
                'public_id' => $result['public_id'] ?? null,
                'url' => $result['secure_url'] ?? $result['url'] ?? null,
                'secure_url' => $result['secure_url'] ?? null,
                'resource_type' => $result['resource_type'] ?? null,
                'format' => $result['format'] ?? null,
                'size' => $result['bytes'] ?? null,
                'width' => $result['width'] ?? null,
                'height' => $result['height'] ?? null,
                'folder' => $folder,
                'full_result' => $result,
            ];
        } catch (Exception $e) {
            Log::error('Cloudinary upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),  // thêm dòng này
                'file' => $file->getClientOriginalName(),
                'folder' => $folder,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Upload avatar with automatic optimization
     */
    public function uploadAvatar(UploadedFile $file, ?int $userId = null): array
    {
        $folder = $userId ? "avatars/user_{$userId}" : "avatars";

        $options = [
            'folder' => $folder,
            'transformation' => [
                [
                    'width' => 400,
                    'height' => 400,
                    'crop' => 'fill',
                    'gravity' => 'face', // Auto-detect face and center
                    'quality' => 'auto:good',
                    'fetch_format' => 'auto',
                ],
            ],
            'eager' => [
                [
                    'width' => 200,
                    'height' => 200,
                    'crop' => 'thumb',
                    'gravity' => 'face',
                    'quality' => 'auto:good',
                ],
                [
                    'width' => 100,
                    'height' => 100,
                    'crop' => 'thumb',
                    'gravity' => 'face',
                    'quality' => 'auto:good',
                ],
            ],
        ];

        return $this->upload($file, $folder, $options);
    }

    /**
     * Upload multiple images (for galleries, multiple files, etc.)
     */
    public function uploadMultiple(array $files, string $folder = 'uploads', array $options = []): array
    {
        $results = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $results[] = $this->upload($file, $folder, $options);
            }
        }

        return $results;
    }

    /**
     * Delete a file from Cloudinary by public_id
     */
    public function delete(string $publicId, string $resourceType = 'image'): array
    {
        try {
            $result =  Cloudinary::uploadApi()->destroy($publicId, [
                'resource_type' => $resourceType,
            ]);

            return [
                'success' => $result->result === 'ok',
                'result' => $result->result,
            ];
        } catch (Exception $e) {
            Log::error('Cloudinary delete failed', [
                'error' => $e->getMessage(),
                'public_id' => $publicId,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete multiple files
     */
    public function deleteMultiple(array $publicIds, string $resourceType = 'image'): array
    {
        try {
            $result = Cloudinary::destroy($publicIds, [
                'resource_type' => $resourceType,
            ]);

            return [
                'success' => true,
                'result' => $result,
            ];
        } catch (Exception $e) {
            Log::error('Cloudinary batch delete failed', [
                'error' => $e->getMessage(),
                'public_ids' => $publicIds,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get optimized URL with transformations
     */
    public function getOptimizedUrl(string $publicId, array $transformations = []): string
    {
        try {
            // Use Cloudinary facade to generate URL
            if (empty($transformations)) {
                return Cloudinary::getUrl($publicId);
            }

            // Build transformation string for Cloudinary
            return Cloudinary::getUrl($publicId, $transformations);
        } catch (Exception $e) {
            Log::error('Cloudinary URL generation failed', [
                'error' => $e->getMessage(),
                'public_id' => $publicId,
            ]);

            return '';
        }
    }

    /**
     * Get thumbnail URL
     */
    public function getThumbnailUrl(string $publicId, int $width = 200, int $height = 200): string
    {
        return $this->getOptimizedUrl($publicId, [
            'width' => $width,
            'height' => $height,
            'crop' => 'fill',
            'quality' => 'auto:good',
            'fetch_format' => 'auto',
        ]);
    }
}
