<?php

namespace App\Http\Controllers\Api\Borrower;

use App\Http\Controllers\Controller;
use App\Models\ReturnSlip;
use Illuminate\Http\Request;

class ReturnSlipController extends Controller
{
    public function index(Request $request)
    {
        $returnslip = ReturnSlip::with([
            'returnedByStaff:id,name,email',
            'borrow:id,borrower_id,borrowed_date,expected_return_date,status,notes,created_by_user_id,issued_by_user_id',
            'borrow.borrower:id,name,email',
            'borrow.createdBy:id,name,email',
            'borrow.issuedBy:id,name,email'
        ])
            ->whereHas('borrow', function ($query) {
                $query->where('borrower_id', auth('api')->id());
            });
        if ($request->has('status')) {
            $returnslip->whereIn('status', (array) $request->status);
        }
        $perPage = $request->get('per_page', 15);
        $result = $returnslip->latest()->paginate($perPage);
        return response()->json($result);
    }
    public function show(string $id)
    {
        $returnslip = ReturnSlip::with([
            'returnedByStaff:id,name,email',
            'borrow:id,borrower_id,borrowed_date,expected_return_date,status,notes,created_by_user_id,issued_by_user_id',
            'borrow.borrower:id,name,email',
            'borrow.createdBy:id,name,email',
            'borrow.issuedBy:id,name,email',
            'borrow.details.deviceUnit.device:id,name',
            'details.deviceUnit.device:id,name',
            'details.deviceUnit:id,serial_number,device_id'
        ])
            ->whereHas('borrow', function ($query) {
                $query->where('borrower_id', auth('api')->id());
            })
            ->findOrFail($id);
        return response()->json($returnslip);
    }
}
