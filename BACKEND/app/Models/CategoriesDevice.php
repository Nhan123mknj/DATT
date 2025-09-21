<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoriesDevice extends Model
{
    /** @use HasFactory<\Database\Factories\CategoriesDeviceFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'device_categories';
    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'deleted_at',
    ];
    public function devices()
    {
        return $this->hasMany(Devices::class, 'category_id');
    }
}
