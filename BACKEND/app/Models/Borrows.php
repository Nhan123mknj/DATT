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
        'actual_return_date',
        'status',
        'notes',
        'commitment_file',
        'staff_signature',
        'borrower_signature',
        'return_notes',
        'returned_by_staff_id',
        'return_slip_pdf_path',
        'return_slip_generated_at'
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
}
