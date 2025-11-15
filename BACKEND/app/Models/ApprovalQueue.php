<?php

namespace App\Models\Models;

use App\Models\Borrows;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ApprovalQueue extends Model
{
    protected $table = 'approval_queue';
    protected $fillable = [
        'requestable_type',
        'requestable_id',
        'request_data',
        'status',
        'approver_by',
        'approved_at',
        'notes',
        'reason',
        'priority',
    ];
    protected $casts = [
        'request_data' => 'array',
        'approved_at' => 'datetime',
    ];
    protected $dates = ['approved_at'];

    public function borrow()
    {
        return $this->belongsTo(Borrows::class);
    }
    public function approver_by()
    {
        return $this->belongsTo(User::class, 'approver_by');
    }
    public function requester_by()
    {
        return $this->borrow->borrower;
    }
}
