<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_unit_id',
        'action',
        'actor_id',
        'target_id',
        'previous_status',
        'new_status',
        'notes',
    ];

    public function deviceUnit()
    {
        return $this->belongsTo(DeviceUnits::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function target()
    {
        return $this->belongsTo(User::class, 'target_id');
    }
}
