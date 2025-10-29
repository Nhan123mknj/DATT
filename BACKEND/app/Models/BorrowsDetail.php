<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BorrowsDetail extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'borrow_details';
    protected $fillable = [
        'borrow_id',
        'device_unit_id',
        'condition_at_borrow',
        'condition_at_return',
        'notes',
        'returned_at',
        'status',
    ];
    public function borrow()
    {
        return $this->belongsTo(Borrows::class, 'borrow_id');
    }
    public function deviceUnit()
    {
        return $this->belongsTo(DeviceUnits::class, 'device_unit_id');
    }
}
