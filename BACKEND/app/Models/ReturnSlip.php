<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnSlip extends Model
{
    use HasFactory;

    protected $fillable = [
        'borrow_id',
        'return_date',
        'returned_by_staff_id',
        'overall_condition',
        'condition_notes',
        'late_days',
        'late_fee',
        'total_penalty',
        'credit_score_change',
    ];

    protected $casts = [
        'return_date' => 'datetime',
    ];


    public function borrow()
    {
        return $this->belongsTo(Borrows::class, 'borrow_id');
    }


    public function returnedByStaff()
    {
        return $this->belongsTo(User::class, 'returned_by_staff_id');
    }


    public function details()
    {
        return $this->hasMany(ReturnSlipDetail::class);
    }
}
