<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceReservation extends Model
{
    protected $table = 'device_reservation';
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
        'notes'
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
}
