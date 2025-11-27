<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceReservationDetail extends Model
{
    protected $fillable = ['reservation_id', 'device_unit_id', 'status', 'notes'];
    protected $table = 'device_reservation_details';
    public function reservation()
    {
        return $this->belongsTo(DeviceReservation::class);
    }
    public function deviceUnit()
    {
        return $this->belongsTo(DeviceUnits::class, 'device_unit_id');
    }
}
