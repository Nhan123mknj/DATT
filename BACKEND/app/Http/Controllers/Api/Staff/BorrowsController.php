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
        $result = $this->borrowService->getBorrowingSlipById($id);
        return response()->json([
            'borrowSlip' => $result
        ]);
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
            'return_items' => 'required|array|min:1',
            'return_items.*.device_unit_id' => 'required|integer|exists:device_units,id',
            'return_items.*.condition_at_return' => 'required|in:excellent,good,fair,damaged,broken',
            'return_items.*.photos' => 'nullable|array|max:5',
            'return_items.*.photos.*' => 'image|max:5120', 
            'signatures' => 'required|array',
            'signatures.staff' => 'required|string', 
            'signatures.borrower' => 'required|string',
            'notes' => 'nullable|string|max:1000',
        ]);

        $returnItems = collect($request->return_items)->map(function ($item, $index) use ($request) {
            if ($request->hasFile("return_items.{$index}.photos")) {
                $photos = [];
                foreach ($request->file("return_items.{$index}.photos") as $photo) {
                    $path = $photo->store('return_photos', 'public');
                    $photos[] = $path;
                }
                $item['photos'] = $photos;
            }
            return $item;
        })->toArray();

        // Process return
        $result = $this->borrowService->createReturnSlip(
            $id,
            $returnItems,
            $request->signatures,
            $request->notes
        );

        return response()->json([
            'message' => 'Đã xử lý trả thiết bị thành công',
            'borrowSlip' => $result,
            'pdf_url' => $result->return_slip_pdf_path
                ? asset('storage/' . $result->return_slip_pdf_path)
                : null
        ]);
    }
}
