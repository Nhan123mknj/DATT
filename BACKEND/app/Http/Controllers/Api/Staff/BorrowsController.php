<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Services\BorrowService;
use Illuminate\Http\Request;

class BorrowsController extends Controller
{
    protected BorrowService $borrowService;

    public function __construct(BorrowService $borrowService)
    {
        $this->borrowService = $borrowService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['status']);
        $perPage = $request->get('page', 15);
        $borrowSlip = $this->borrowService->showBorrowingSlip($filters, $perPage);
        if ($borrowSlip->isEmpty()) {
            return response()->json(['message' => 'Không có phiếu mượn nào'], 404);
        }
        return response()->json([
            'borrowSlip' => $borrowSlip
        ]);
    }
    public function show(string $id)
    {
        $result = $this->borrowService->getBorrowingSlipById($id);
        return response()->json([
            'borrowSlip' => $result
        ]);
    }
    public function approveBorrowRequest(string $id)
    {
        $result = $this->borrowService->approveBorrowRequest($id);
        return response()->json([
            'message' => 'Phiếu mượn đã được duyệt thành công',
            'borrowSlip' => $result
        ]);
    }
    public function rejectBorrowRequest(string $id)
    {
        $result = $this->borrowService->rejectBorrowRequest($id);
        return response()->json([
            'message' => 'Phiếu mượn đã bị từ chối',
            'borrowSlip' => $result
        ]);
    }
}
