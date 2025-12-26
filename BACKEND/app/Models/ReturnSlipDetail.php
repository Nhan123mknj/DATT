<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnSlipDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_slip_id',
        'borrow_detail_id',
        'device_unit_id',
        'condition_status',
        'condition_notes',
        'damage_description',
    ];

    /**
     * Relationship: Detail belongs to ReturnSlip
     */
    public function returnSlip()
    {
        return $this->belongsTo(ReturnSlip::class);
    }

    /**
     * Relationship: Detail belongs to BorrowDetail
     */
    public function borrowDetail()
    {
        return $this->belongsTo(BorrowsDetail::class, 'borrow_detail_id');
    }

    /**
     * Relationship: Detail belongs to DeviceUnit
     */
    public function deviceUnit()
    {
        return $this->belongsTo(DeviceUnits::class, 'device_unit_id');
    }
}
