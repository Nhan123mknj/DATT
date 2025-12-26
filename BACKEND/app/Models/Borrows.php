<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Borrows extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'borrows';
    protected $fillable = [
        'borrower_id',
        'borrowed_date',
        'expected_return_date',
        'status',
        'notes',
        'commitment_file',
        'returned_by_staff_id',
        'created_by_user_id',
        'issued_by_user_id',
        'issued_at',
    ];

    public function borrower()
    {
        return $this->belongsTo(User::class, 'borrower_id');
    }

    public function details()
    {
        return $this->hasMany(BorrowsDetail::class, 'borrow_id');
    }

    public function returnedByStaff()
    {
        return $this->belongsTo(User::class, 'returned_by_staff_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by_user_id');
    }

    /**
     * Relationship: Borrow has many ReturnSlips
     */
    public function returnSlips()
    {
        return $this->hasMany(ReturnSlip::class, 'borrow_id');
    }

    /**
     * Relationship: Get the latest return slip (singular)
     * Useful for checking if a borrow has been returned
     */
    public function returnSlip()
    {
        return $this->hasOne(ReturnSlip::class, 'borrow_id')->latestOfMany();
    }

    /**
     * Get latest return slip
     */
    public function latestReturnSlip()
    {
        return $this->hasOne(ReturnSlip::class, 'borrow_id')->latest();
    }

    /**
     * Check if all borrowed devices have been returned
     */
    public function isFullyReturned(): bool
    {
        $totalBorrowed = $this->details()->count();

        $totalReturned = ReturnSlipDetail::whereIn(
            'return_slip_id',
            $this->returnSlips()->pluck('id')
        )->distinct('device_unit_id')->count();

        return $totalBorrowed === $totalReturned;
    }
}
