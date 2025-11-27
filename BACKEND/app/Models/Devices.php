<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Devices extends Model
{
    /** @use HasFactory<\Database\Factories\DevicesFactory> */
    use HasFactory, SoftDeletes;
    protected $table = 'devices';
    protected $fillable = [
        'name',
        'category_id',
        'manufacturer',
        'specifications',
        'is_active',
        'total_units',
        'model',
        'code',
        'description',
        'price',
        'quantity',
    ];

    public function category()
    {
        return $this->belongsTo(CategoriesDevice::class, 'category_id');
    }

    public function getDeviceTypeAttribute()
    {
        return $this->category->type;
    }

    public function getDepositRateAttribute()
    {
        return $this->category->deposit_rate;
    }

    public function getMaxBorrowDurationAttribute()
    {
        return $this->category->max_borrow_duration;
    }

    public function requiresApproval()
    {
        return $this->category->requires_approval;
    }

    public function units()
    {
        return $this->hasMany(DeviceUnits::class, 'device_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    public function getAvailableUnitsAttribute()
    {
        return $this->units()->where('status', 'available')->count();
    }
}
