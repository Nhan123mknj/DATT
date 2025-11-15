<?php

namespace App\Http\Controllers\Api\Borrower;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateReservation;
use App\Services\ReservationService;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    protected $reservationService;
    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateReservation $request)
    {
        $reservation = $this->reservationService->createReservation($request->all());
        return response()->json([
            'message' => 'Đặt trước thiết bị thành công.',
            'reservation' => $reservation,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
