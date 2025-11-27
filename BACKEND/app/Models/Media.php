<?php

namespace App\Models;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Media extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'public_id',
        'url',
        'secure_url',
        'resource_type',
        'format',
        'size',
        'width',
        'height',
        'folder',
        'type',
        'mime_type',
        'sort_order',
        'mediable_type',
        'mediable_id',
    ];

    protected $casts = [
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Get the parent mediable model (User, Device, etc.)
     */
    public function mediable()
    {
        return $this->morphTo();
    }

    /**
     * Get the Cloudinary URL with optional transformations.
     */
    public function getTransformedUrl(array $transformations = []): string
    {
        if (empty($transformations)) {
            return $this->secure_url ?? $this->url;
        }

        return Cloudinary::getUrl($this->public_id, [
            'transformation' => [$transformations],
            'resource_type' => $this->resource_type ?? 'image',
            'format' => $this->format,
        ]);
    }

    /**
     * Scope for filtering by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for filtering by resource type
     */
    public function scopeImages($query)
    {
        return $query->where('resource_type', 'image');
    }
}
