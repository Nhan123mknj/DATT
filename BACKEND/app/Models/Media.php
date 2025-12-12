<?php

namespace App\Models;

use App\Services\CloudinaryService;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Media extends Model
{
    protected $fillable = [
        'public_id',
        'url',
        'type',
        'mediable_type',
        'mediable_id',
    ];

    protected $hidden = [
        'mediable_type',
        'mediable_id',
    ];

    /**
     * Polymorphic relationship
     */
    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get URL với transformation
     */
    public function getUrl(?int $width = null, ?int $height = null): string
    {
        if (!$width || !$height) {
            return $this->url;
        }

        return app(CloudinaryService::class)->getUrl($this->public_id, $width, $height);
    }

    /**
     * Accessor cho thumbnail
     */
    protected function thumbnail(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn() => $this->getUrl(200, 200),
        );
    }
}
