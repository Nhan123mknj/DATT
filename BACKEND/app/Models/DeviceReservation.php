<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeviceReservation extends Model
{
    use SoftDeletes;
    protected $table = 'device_reservations';
    protected $fillable = [
        'user_id',
        'reserved_from',
        'reserved_until',
        'status',
        'approved_by',
        'approved_at',
        'checked_in_at',
        'is_no_show',
        'no_show_notes',
        'notes',
        'deleted_at',
        'cancelled_by',
        'cancelled_at',
    ];

    protected $casts = [
        'reserved_from' => 'datetime',
        'reserved_until' => 'datetime',
        'approved_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];
    public function details()
    {
        return $this->hasMany(DeviceReservationDetail::class, 'reservation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function canceller()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}
