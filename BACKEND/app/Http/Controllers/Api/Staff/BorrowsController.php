<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Models\CategoriesDevice;
use App\Models\Student;
use App\Models\Teacher;
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
        $result = $this->borrowService->getDetailBorrowingSlip($id);
        return response()->json($result, 200);
    }

    public function create()
    {
        $categories = CategoriesDevice::all();
        $borrowers = User::whereIn('role', ['student', 'teacher'])
            ->select('id', 'name', 'email')
            ->with([
                'student:user_id,student_code,class_name',
                'teacher:user_id,teacher_code,department'
            ])
            ->get();

        return response()->json([
            'categories' => $categories,
            'borrowers' => $borrowers,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'borrower_id' => 'nullable|exists:users,id',
            'borrower_code' => 'required_without:borrower_id|string',
            'expected_return_date' => 'required|date|after:today',
            'devices' => 'required|array|min:1',
            'devices.*.device_unit_id' => 'required|exists:device_units,id',
            'devices.*.condition_at_borrow' => 'nullable|string',
            'notes' => 'nullable|string|max:1000',
            'commitment_file' => 'nullable|string',
        ]);

        if ($request->has('borrower_id') && $request->borrower_id) {
            $borrowerId = $request->borrower_id;
        } else {
            $borrowerCode = $request->borrower_code;
            $student = Student::where('student_code', $borrowerCode)->first();

            if ($student) {
                $borrowerId = $student->user_id;
            } else {
                $teacher = Teacher::where('teacher_code', $borrowerCode)->first();
                if ($teacher) {
                    $borrowerId = $teacher->user_id;
                } else {
                    return response()->json([
                        'message' => 'Không tìm thấy học sinh hoặc giáo viên với mã: ' . $borrowerCode
                    ], 404);
                }
            }
        }

        $data = $request->all();
        $data['borrower_id'] = $borrowerId;
        $data['auto_approve'] = true;

        $borrow = $this->borrowService->createBorrowingSlip($data);

        return response()->json([
            'message' => 'Đã tạo phiếu mượn thành công',
            'borrowSlip' => $borrow
        ], 201);
    }
    // public function approveBorrowRequest(string $id)
    // {
    //     $result = $this->borrowService->approveBorrowRequest($id);
    //     return response()->json([
    //         'message' => 'Phiếu mượn đã được duyệt thành công',
    //         'borrowSlip' => $result
    //     ]);
    // }
    public function rejectBorrowRequest(string $id)
    {
        $result = $this->borrowService->rejectBorrowRequest($id);
        return response()->json([
            'message' => 'Phiếu mượn đã bị từ chối',
            'borrowSlip' => $result
        ]);
    }

    // public function approve(string $id)
    // {
    //     $result = $this->borrowService->approveBorrowRequest($id);
    //     return response()->json([
    //         'message' => 'Phiếu mượn đã được duyệt thành công',
    //         'borrowSlip' => $result
    //     ]);
    // }

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

    public function cancel(string $id)
    {
        $result = $this->borrowService->cancelBorrow($id);
        return response()->json([
            'message' => 'Đã hủy phiếu mượn thành công',
            'borrowSlip' => $result
        ]);
    }

    public function issue(Request $request, string $id)
    {
        $otp = $request->input('otp');
        $result = $this->borrowService->issueBorrow($id, $otp);
        return response()->json([
            'message' => 'Xuất thiết bị thành công',
            'borrowSlip' => $result
        ]);
    }

    public function sendOtp(string $id)
    {
        $result = $this->borrowService->sendIssueOtp($id);
        return response()->json($result);
    }

    public function sendReturnOtp(string $id)
    {
        $returnSlipService = app(\App\Services\ReturnSlipService::class);
        $result = $returnSlipService->sendReturnOtp($id);
        return response()->json($result);
    }

    public function processReturn(Request $request, string $id)
    {
        $request->validate([
            'devices' => 'required|array|min:1',
            'devices.*.borrow_detail_id' => 'required|exists:borrow_details,id',
            'devices.*.device_unit_id' => 'required|exists:device_units,id',
            'devices.*.condition_status' => 'required|in:good,minor_damage,major_damage,broken',
            'devices.*.condition_notes' => 'nullable|string',
            'devices.*.damage_description' => 'nullable|string',
            'devices.*.damage_fee' => 'nullable|numeric|min:0',
            'overall_condition' => 'nullable|in:good,minor_damage,major_damage,broken',
            'condition_notes' => 'nullable|string',
            'otp' => 'required|string|size:6',
        ]);


        $returnSlipService = app(\App\Services\ReturnSlipService::class);


        $verifyResult = $returnSlipService->verifyReturnOtp($id, $request->otp);
        if (!$verifyResult['success']) {
            return response()->json([
                'message' => $verifyResult['message']
            ], 400);
        }

        $returnSlip = $returnSlipService->createReturnSlip([
            'borrow_id' => $id,
            'overall_condition' => $request->overall_condition ?? 'good',
            'condition_notes' => $request->condition_notes,
            'devices' => $request->devices,
        ]);

        return response()->json([
            'message' => 'Đã xử lý trả thiết bị thành công',
            'return_slip' => $returnSlip,
        ]);
    }

    public function export(Request $request)
    {
        try {
            $filters = $request->only(['status']);

            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\BorrowsExport($filters),
                'phieu-muon-' . now()->format('Y-m-d') . '.xlsx'
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Không thể xuất file Excel: ' . $e->getMessage()
            ], 500);
        }
    }
}
