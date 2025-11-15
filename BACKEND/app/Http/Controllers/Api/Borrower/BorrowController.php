<?php

namespace App\Http\Controllers\Api\Borrower;

use App\Http\Controllers\Controller;
use App\Http\Requests\BorrowRequest;
use App\Models\Borrows;
use App\Services\BorrowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BorrowController extends Controller
{
    protected BorrowService $borrowService;

    public function __construct(BorrowService $borrowService)
    {
        $this->borrowService = $borrowService;
    }
    /**
     * Display a listing of the resource.
     */
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(BorrowRequest $request)
    {
        Gate::authorize('create', Borrows::class);
        $borrow = $this->borrowService->createBorrowingSlip($request->all());

        return response()->json([
            'messeger' => 'Phiếu mượn đã được tạo thành công',
            'borrowSlip'  => $borrow,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $result = Borrows::with([
            'details:id,borrow_id,device_unit_id',
            'details.deviceUnit:id,device_id,serial_number',
            'details.deviceUnit.device:id,name'
        ])->findOrFail($id);

        $this->authorize($result, 'view');

        return response()->json($result, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
