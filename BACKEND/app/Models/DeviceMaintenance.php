<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeviceMaintenance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'device_unit_id',
        'reported_by',
        'type',
        'status',
        'priority',
        'start_date',
        'end_date',
        'description',
        'cost',
        'notes',
        'next_maintenance_date'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'next_maintenance_date' => 'date',
        'cost' => 'decimal:2'
    ];

    public function deviceUnit()
    {
        return $this->belongsTo(DeviceUnits::class, 'device_unit_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
