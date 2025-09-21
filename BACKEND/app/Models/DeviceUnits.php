<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeviceUnits extends Model
{
    /** @use HasFactory<\Database\Factories\DeviceUnitsFactory> */
    use HasFactory, SoftDeletes;
    protected $table = 'device_units';
    protected $fillable = [
        'device_id',
        'serial_number',
        'status',
        'purchase_date',
        'warranty_end',
        'notes',
        'deleted_at',
    ];
    protected $dates = ['purchase_date', 'warranty_end'];

    public function device()
    {
        return $this->belongsTo(Devices::class, 'device_id');
    }


    public function units()
    {
        return $this->hasMany(DeviceUnits::class, 'device_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    public function isAvailable()
    {
        return $this->status === 'available';
    }
}
