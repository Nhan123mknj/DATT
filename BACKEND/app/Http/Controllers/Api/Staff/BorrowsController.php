<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Models\CategoriesDevice;
use App\Models\User;
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
        $perPage = $request->get('per_page', 15);
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

    public function create()
    {
        // Return data needed for create form
        $categories = CategoriesDevice::all();
        $borrowers = User::where('role', 'borrower')
            ->select('id', 'name', 'email')
            ->get();

        return response()->json([
            'categories' => $categories,
            'borrowers' => $borrowers,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'borrower_id' => 'required|exists:users,id',
            'expected_return_date' => 'required|date|after:today',
            'devices' => 'required|array|min:1',
            'devices.*.device_unit_id' => 'required|exists:device_units,id',
            'devices.*.condition_at_borrow' => 'nullable|string',
            'notes' => 'nullable|string|max:1000',
            'commitment_file' => 'nullable|string',
        ]);

        // Staff creates borrow with auto-approve
        $data = $request->all();
        $data['auto_approve'] = true; // Auto-approve for staff quick creation

        $borrow = $this->borrowService->createBorrowingSlip($data);

        return response()->json([
            'message' => 'Đã tạo phiếu mượn thành công',
            'borrowSlip' => $borrow
        ], 201);
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

    // Methods for API routes
    public function approve(string $id)
    {
        $result = $this->borrowService->approveBorrowRequest($id);
        return response()->json([
            'message' => 'Phiếu mượn đã được duyệt thành công',
            'borrowSlip' => $result
        ]);
    }

    public function reject(Request $request, string $id)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);

        $result = $this->borrowService->rejectBorrowRequest($id);
        return response()->json([
            'message' => 'Phiếu mượn đã bị từ chối',
            'borrowSlip' => $result
        ]);
    }

    public function processReturn(Request $request, string $id)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
            'return_items' => 'nullable|array'
        ]);

        // Call the service method to process return
        $returnItems = $request->input('return_items', []);
        $result = $this->borrowService->createReturnSlip($id, $returnItems);

        return response()->json([
            'message' => 'Đã xử lý trả thiết bị thành công',
            'borrowSlip' => $result
        ]);
    }
}
