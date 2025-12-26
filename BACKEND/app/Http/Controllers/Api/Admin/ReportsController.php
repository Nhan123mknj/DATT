<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use App\Models\DeviceUnits;
use App\Models\Borrows;
use App\Models\DeviceReservation;
use App\Models\ReturnSlip;
use App\Models\User;
use Carbon\Carbon;

class ReportsController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function getDashboardStats()
    {
        try {
            $thirtyDaysAgo = Carbon::now()->subDays(30);
            $sevenDaysAgo = Carbon::now()->subDays(7);

            $totalDamages = Activity::where('log_name', 'device-damaged')
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->count();

            $totalActivities = Activity::where('created_at', '>=', $sevenDaysAgo)
                ->count();

            $activeUsers = Activity::where('created_at', '>=', $sevenDaysAgo)
                ->distinct('causer_id')
                ->count('causer_id');

            $totalDevices = DeviceUnits::where('status', '!=', 'retired')->count();
            return response()->json([
                'total_damages' => $totalDamages,
                'total_activities' => $totalActivities,
                'active_users' => $activeUsers,
                'total_devices' => $totalDevices,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to get dashboard stats: ' . $e->getMessage()], 500);
        }
    }


    public function getDeviceDamageReports(Request $request)
    {
        try {
            $filters = $request->only(['period', 'from_date', 'to_date', 'category_id']);

            $query = Activity::where('log_name', 'device-damaged');

            if (isset($filters['period']) && $filters['period'] !== 'custom') {
                $days = match ($filters['period']) {
                    '7days' => 7,
                    '30days' => 30,
                    '3months' => 90,
                    '6months' => 180,
                    '1year' => 365,
                    default => 30,
                };
                $query->where('created_at', '>=', Carbon::now()->subDays($days));
            } elseif (isset($filters['from_date']) && isset($filters['to_date'])) {
                $query->whereBetween('created_at', [$filters['from_date'], $filters['to_date']]);
            }

            $damageActivities = $query->with('causer')->orderBy('created_at', 'desc')->get();

            if (isset($filters['category_id']) && $filters['category_id']) {
                $damageActivities = $damageActivities->filter(function ($activity) use ($filters) {
                    $deviceUnit = DeviceUnits::find($activity->subject_id);
                    return $deviceUnit && $deviceUnit->device && $deviceUnit->device->category_id == $filters['category_id'];
                });
            }

            $totalDamages = $damageActivities->count();
            $totalCost = $damageActivities->sum(fn($a) => $a->properties['damage_fee'] ?? 0);
            $affectedDevices = $damageActivities->pluck('subject_id')->unique()->count();
            $avgCost = $totalDamages > 0 ? $totalCost / $totalDamages : 0;

            $allDamages = $damageActivities->map(function ($activity) {
                $props = $activity->properties;
                return [
                    'id' => $activity->id,
                    'damage_date' => $activity->created_at->format('d/m/Y H:i:s'),
                    'damage_date_human' => $activity->created_at->diffForHumans(),
                    'device_name' => $props['device_name'] ?? '',
                    'serial_number' => $props['serial_number'] ?? '',
                    'damage_description' => $props['damage_description'] ?? '',
                    'damage_level' => $props['damage_level'] ?? '',
                    'damage_fee' => $props['damage_fee'] ?? 0,
                    'caused_by' => [
                        'id' => $props['caused_by_user_id'] ?? null,
                        'name' => $props['caused_by_user_name'] ?? '',
                        'code' => $props['caused_by_user_code'] ?? '',
                    ],
                ];
            })->values();

            $topDamagedDevices = $damageActivities
                ->groupBy('subject_id')
                ->map(function ($activities, $deviceUnitId) {
                    $first = $activities->first();
                    return [
                        'device_unit_id' => $deviceUnitId,
                        'device_name' => $first->properties['device_name'] ?? '',
                        'serial_number' => $first->properties['serial_number'] ?? '',
                        'damage_count' => $activities->count(),
                        'total_cost' => $activities->sum(fn($a) => $a->properties['damage_fee'] ?? 0),
                    ];
                })
                ->sortByDesc('damage_count')
                ->take(10)
                ->values();

            $topDamagingUsers = $damageActivities
                ->groupBy(fn($a) => $a->properties['caused_by_user_id'] ?? 0)
                ->map(function ($activities, $userId) {
                    $first = $activities->first();
                    return [
                        'id' => $userId,
                        'name' => $first->properties['caused_by_user_name'] ?? '',
                        'code' => $first->properties['caused_by_user_code'] ?? '',
                        'email' => $first->properties['caused_by_user_email'] ?? '',
                        'damage_count' => $activities->count(),
                        'total_cost' => $activities->sum(fn($a) => $a->properties['damage_fee'] ?? 0),
                    ];
                })
                ->sortByDesc('damage_count')
                ->take(10)
                ->values();

            $damagesByCategory = $damageActivities
                ->groupBy(function ($activity) {
                    $deviceUnit = DeviceUnits::find($activity->subject_id);
                    return $deviceUnit && $deviceUnit->device ? $deviceUnit->device->category_id : 0;
                })
                ->map(function ($activities, $categoryId) use ($totalDamages) {
                    $deviceUnit = DeviceUnits::find($activities->first()->subject_id);
                    $category = $deviceUnit && $deviceUnit->device ? $deviceUnit->device->category : null;

                    $count = $activities->count();
                    return [
                        'id' => $categoryId,
                        'name' => $category ? $category->name : 'Không xác định',
                        'damage_count' => $count,
                        'total_cost' => $activities->sum(fn($a) => $a->properties['damage_fee'] ?? 0),
                        'percentage' => $totalDamages > 0 ? round(($count / $totalDamages) * 100, 1) : 0,
                    ];
                })
                ->sortByDesc('damage_count')
                ->values();

            return response()->json([
                'summary' => [
                    'total_damages' => $totalDamages,
                    'total_cost' => $totalCost,
                    'affected_devices' => $affectedDevices,
                    'avg_cost' => $avgCost,
                ],
                'all_damages' => $allDamages,
                'top_damaged_devices' => $topDamagedDevices,
                'top_damaging_users' => $topDamagingUsers,
                'damages_by_category' => $damagesByCategory,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to get damage reports: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get single device damage detail
     */
    public function getDeviceDamageDetail($id)
    {
        try {
            $activity = Activity::where('log_name', 'device-damaged')
                ->where('id', $id)
                ->with('causer')
                ->first();

            if (!$activity) {
                return response()->json(['error' => 'Damage report not found'], 404);
            }

            $props = $activity->properties;
            $deviceUnit = DeviceUnits::withTrashed()->find($activity->subject_id);

            $damageDetail = [
                'id' => $activity->id,
                'damage_date' => $activity->created_at->format('d/m/Y H:i:s'),
                'damage_date_human' => $activity->created_at->diffForHumans(),
                'device_unit_id' => $activity->subject_id,
                'device_name' => $props['device_name'] ?? '',
                'serial_number' => $props['serial_number'] ?? '',
                'damage_description' => $props['damage_description'] ?? '',
                'damage_level' => $props['damage_level'] ?? '',
                'damage_fee' => $props['damage_fee'] ?? 0,
                'caused_by' => [
                    'id' => $props['caused_by_user_id'] ?? null,
                    'name' => $props['caused_by_user_name'] ?? '',
                    'code' => $props['caused_by_user_code'] ?? '',
                    'email' => $props['caused_by_user_email'] ?? '',
                ],
                'device_unit' => $deviceUnit ? [
                    'id' => $deviceUnit->id,
                    'serial_number' => $deviceUnit->serial_number,
                    'status' => $deviceUnit->status,
                    'device' => $deviceUnit->device ? [
                        'id' => $deviceUnit->device->id,
                        'name' => $deviceUnit->device->name,
                        'category' => $deviceUnit->device->category,
                    ] : null,
                ] : null,
            ];

            return response()->json(['data' => $damageDetail], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to get damage detail: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get all damage history for a specific device unit
     */
    public function getDeviceDamageHistory($deviceUnitId)
    {
        try {
            $deviceUnit = DeviceUnits::withTrashed()
                ->with('device')
                ->find($deviceUnitId);

            if (!$deviceUnit) {
                return response()->json([
                    'error' => 'Device unit not found',
                    'message' => "Không tìm thấy thiết bị với ID: {$deviceUnitId}"
                ], 404);
            }

            $damageActivities = Activity::where('log_name', 'device-damaged')
                ->where('subject_id', $deviceUnitId)
                ->with('causer')
                ->orderBy('created_at', 'desc')
                ->get();

            $totalDamages = $damageActivities->count();
            $totalCost = $damageActivities->sum(fn($a) => $a->properties['damage_fee'] ?? 0);

            $damages = $damageActivities->map(function ($activity) {
                $props = $activity->properties;
                return [
                    'id' => $activity->id,
                    'damage_date' => $activity->created_at->format('d/m/Y H:i:s'),
                    'damage_date_human' => $activity->created_at->diffForHumans(),
                    'damage_description' => $props['damage_description'] ?? '',
                    'damage_level' => $props['damage_level'] ?? '',
                    'damage_fee' => $props['damage_fee'] ?? 0,
                    'caused_by' => [
                        'id' => $props['caused_by_user_id'] ?? null,
                        'name' => $props['caused_by_user_name'] ?? '',
                        'code' => $props['caused_by_user_code'] ?? '',
                        'email' => $props['caused_by_user_email'] ?? '',
                    ],
                ];
            });

            return response()->json([
                'device_unit' => [
                    'id' => $deviceUnit->id,
                    'serial_number' => $deviceUnit->serial_number,
                    'status' => $deviceUnit->status,
                    'device' => $deviceUnit->device ? [
                        'id' => $deviceUnit->device->id,
                        'name' => $deviceUnit->device->name,
                        'category' => $deviceUnit->device->category ?? null,
                    ] : null,
                ],
                'summary' => [
                    'total_damages' => $totalDamages,
                    'total_cost' => $totalCost,
                ],
                'damages' => $damages,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to get device damage history: ' . $e->getMessage(),
                'device_unit_id' => $deviceUnitId
            ], 500);
        }
    }

    /**
     * Get user activity report
     */
    public function getUserActivityReport($userId)
    {
        try {
            $user = User::find($userId);

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            // Get all activities caused by this user
            $activities = Activity::where('causer_id', $userId)
                ->orderBy('created_at', 'desc')
                ->paginate(50);

            // Get damage activities
            $damageActivities = Activity::where('log_name', 'device-damaged')
                ->where('causer_id', $userId)
                ->get();

            $totalDamages = $damageActivities->count();
            $totalDamageCost = $damageActivities->sum(fn($a) => $a->properties['damage_fee'] ?? 0);

            // Get borrow statistics
            $borrows = Borrows::where('borrower_id', $userId)->get();
            $totalBorrows = $borrows->count();
            $completedBorrows = $borrows->where('status', 'returned')->count();
            $lateBorrows = $borrows->filter(function ($borrow) {
                return $borrow->status === 'returned' &&
                    $borrow->latestReturnSlip &&
                    $borrow->latestReturnSlip->return_date > $borrow->expected_return_date;
            })->count();

            // Get reservations
            $reservations = DeviceReservation::where('borrower_id', $userId)->get();
            $totalReservations = $reservations->count();

            // Format activities
            $formattedActivities = $activities->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'log_name' => $activity->log_name,
                    'event' => $activity->event,
                    'description' => $activity->description,
                    'subject_type' => $activity->subject_type,
                    'subject_id' => $activity->subject_id,
                    'properties' => $activity->properties,
                    'created_at' => $activity->created_at->format('d/m/Y H:i:s'),
                    'created_at_human' => $activity->created_at->diffForHumans(),
                ];
            });

            // Activity breakdown by type
            $activityBreakdown = Activity::where('causer_id', $userId)
                ->select('log_name', \DB::raw('COUNT(*) as count'))
                ->groupBy('log_name')
                ->get()
                ->pluck('count', 'log_name');

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'code' => $user->getUserCode(),
                    'role' => $user->role,
                    'credit_score' => $user->credit_score,
                ],
                'summary' => [
                    'total_activities' => Activity::where('causer_id', $userId)->count(),
                    'total_damages' => $totalDamages,
                    'total_damage_cost' => $totalDamageCost,
                    'total_borrows' => $totalBorrows,
                    'completed_borrows' => $completedBorrows,
                    'late_borrows' => $lateBorrows,
                    'total_reservations' => $totalReservations,
                ],
                'activity_breakdown' => $activityBreakdown,
                'activities' => $formattedActivities,
                'pagination' => [
                    'current_page' => $activities->currentPage(),
                    'last_page' => $activities->lastPage(),
                    'per_page' => $activities->perPage(),
                    'total' => $activities->total(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to get user activity report: ' . $e->getMessage()], 500);
        }
    }

    public function getBorrowStatistics(Request $request)
    {
        try {
            $filters = $request->only(['period', 'from_date', 'to_date']);

            $query = Borrows::query();

            if (isset($filters['period']) && $filters['period'] !== 'custom') {
                $days = match ($filters['period']) {
                    '7days' => 7,
                    '30days' => 30,
                    '3months' => 90,
                    '6months' => 180,
                    '1year' => 365,
                    default => 30,
                };
                $query->where('created_at', '>=', Carbon::now()->subDays($days));
            } elseif (isset($filters['from_date']) && isset($filters['to_date'])) {
                $query->whereBetween('created_at', [$filters['from_date'], $filters['to_date']]);
            }

            $borrows = $query->with([
                'borrower:id,name,email,role',
                'details.deviceUnit' => function ($query) {
                    $query->withTrashed()->with('device');
                },
                'latestReturnSlip'
            ])->get();

            $totalBorrows = $borrows->count();
            $issuedBorrows = $borrows->whereIn('status', ['approved', 'completed', 'returned', 'overdue'])->count();
            $completedBorrows = $borrows->where('status', 'returned')->count();
            $onTimeBorrows = $borrows->filter(function ($borrow) {
                return $borrow->status === 'returned' &&
                    $borrow->latestReturnSlip &&
                    $borrow->latestReturnSlip->return_date <= $borrow->expected_return_date;
            })->count();
            $lateBorrows = $borrows->filter(function ($borrow) {
                return $borrow->status === 'returned' &&
                    $borrow->latestReturnSlip &&
                    $borrow->latestReturnSlip->return_date > $borrow->expected_return_date;
            })->count();
            $onTimeRate = $completedBorrows > 0 ? round(($onTimeBorrows / $completedBorrows) * 100, 1) : 0;

            $pendingReservations = DeviceReservation::where('status', 'pending')->with('details')->get();
            $pendingReservationsCount = $pendingReservations->count();
            $pendingReservationDevices = $pendingReservations->sum(fn($r) => $r->details->count());

            $borrowsByStatus = $borrows->groupBy('status')->map(fn($items) => $items->count());

            $mostBorrowedDevices = \DB::table('borrow_details')
                ->join('device_units', 'borrow_details.device_unit_id', '=', 'device_units.id')
                ->join('devices', 'device_units.device_id', '=', 'devices.id')
                ->select(
                    'devices.id',
                    'devices.name',
                    \DB::raw('COUNT(*) as borrow_count')
                )
                ->groupBy('devices.id', 'devices.name')
                ->orderByDesc('borrow_count')
                ->limit(10)
                ->get();

            $topBorrowers = $borrows->groupBy('borrower_id')
                ->map(function ($items, $userId) {
                    $user = $items->first()->borrower;
                    return [
                        'id' => $userId,
                        'name' => $user->name,
                        'code' => $user->getUserCode(),
                        'email' => $user->email,
                        'borrow_count' => $items->count(),
                        'on_time_count' => $items->filter(
                            fn($b) =>
                            $b->status === 'returned' && $b->latestReturnSlip && $b->latestReturnSlip->return_date <= $b->expected_return_date
                        )->count(),
                    ];
                })
                ->sortByDesc('borrow_count')
                ->take(10)
                ->values();

            $borrowsOverTime = [];
            for ($i = 7; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i)->format('Y-m-d');
                $count = $borrows->filter(fn($b) => Carbon::parse($b->created_at)->format('Y-m-d') === $date)->count();
                $borrowsOverTime[] = [
                    'date' => $date,
                    'count' => $count,
                ];
            }

            return response()->json([
                'summary' => [
                    'total_borrows' => $totalBorrows,
                    'issued_borrows' => $issuedBorrows,
                    'completed_borrows' => $completedBorrows,
                    'on_time_borrows' => $onTimeBorrows,
                    'late_borrows' => $lateBorrows,
                    'on_time_rate' => $onTimeRate,
                    'pending_reservations' => $pendingReservationsCount,
                    'pending_reservation_devices' => $pendingReservationDevices,
                ],
                'borrows_by_status' => $borrowsByStatus,
                'most_borrowed_devices' => $mostBorrowedDevices,
                'top_borrowers' => $topBorrowers,
                'borrows_over_time' => $borrowsOverTime,
                'borrows' => $borrows->map(function ($borrow) {
                    return [
                        'id' => $borrow->id,
                        'borrower' => $borrow->borrower ? [
                            'id' => $borrow->borrower->id,
                            'name' => $borrow->borrower->name,
                            'email' => $borrow->borrower->email,
                        ] : null,
                        'details' => $borrow->details ? $borrow->details->map(function ($detail) {
                            return [
                                'id' => $detail->id,
                                'device_unit' => $detail->deviceUnit ? [
                                    'id' => $detail->deviceUnit->id,
                                    'name' => $detail->deviceUnit->device ? $detail->deviceUnit->device->name : null,
                                    'device' => $detail->deviceUnit->device,
                                ] : null,
                            ];
                        })->toArray() : [],
                        'borrowed_date' => $borrow->borrowed_date,
                        'expected_return_date' => $borrow->expected_return_date,
                        'status' => $borrow->status,
                    ];
                })->toArray(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to get borrow statistics: ' . $e->getMessage()], 500);
        }
    }
    public function getDetailBorrowed(Request $request)
    {
        $borrows = Borrows::with(['borrower', 'details.deviceUnit'])
            ->whereNotNull('issued_at')
            ->whereDoesntHave('returnSlip')
            ->get()
            ->groupBy('borrower_id')
            ->map(function ($items) {
                return [
                    'borrower' => $items->first()->borrower,
                    'borrow_count' => $items->count(),
                    'total_devices' => $items->sum(
                        fn($b) => $b->details->count()
                    ),
                    'details' => $items->flatMap->details,
                ];
            })
            ->values();

        return response()->json([
            'count' => $borrows->count(),
            'data' => $borrows
        ], 200);
    }
    public function getReserveList(Request $request)
    {
        $reservations = DeviceReservation::with('user', 'details.deviceUnit')
            ->where('status', 'pending')
            ->get()
            ->groupBy('borrower_id')
            ->map(function ($items) {
                return [
                    'user' => $items->first()->user,
                    'reservation_count' => $items->count(),
                    'total_devices' => $items->sum(
                        fn($r) => $r->details->count()
                    ),
                    'details' => $items->flatMap->details,
                ];
            })
            ->values();

        return response()->json([
            'data' => $reservations
        ], 200);
    }

    // public function getBorrowedByUser(Request $request)
    // {
    //     $borrows = Borrows::with(['borrower', 'details.deviceUnit'])
    //         ->whereNotNull('issued_at')
    //         ->whereDoesntHave('returnSlip')
    //         ->where('borrower_id', $request->user()->id)
    //         ->get()->map(function ($item) {
    //             return [
    //                 'count' => $item->sum(fn($b) => $b->details->count()),
    //                 'data' => $item,
    //             ];
    //         });
    //     return response()->json([
    //         'data' => $borrows
    //     ], 200);
    // }
    public function getActivityLogs(Request $request)
    {
        try {
            $filters = $request->only(['period', 'from_date', 'to_date', 'log_name', 'causer_id', 'search', 'event']);

            $query = Activity::with('causer');

            if (isset($filters['period']) && $filters['period'] !== 'custom') {
                $days = match ($filters['period']) {
                    '7days' => 7,
                    '30days' => 30,
                    '3months' => 90,
                    default => 7,
                };
                $query->where('created_at', '>=', Carbon::now()->subDays($days));
            } elseif (isset($filters['from_date']) && isset($filters['to_date'])) {
                $query->whereBetween('created_at', [$filters['from_date'], $filters['to_date']]);
            }


            if (isset($filters['log_name']) && $filters['log_name']) {
                $query->where('log_name', $filters['log_name']);
            }


            if (isset($filters['event']) && $filters['event']) {
                $query->where('event', $filters['event']);
            }


            if (isset($filters['causer_id']) && $filters['causer_id']) {
                $query->where('causer_id', $filters['causer_id']);
            }


            if (isset($filters['search']) && $filters['search']) {
                $query->where(function ($q) use ($filters) {
                    $q->where('description', 'like', '%' . $filters['search'] . '%')
                        ->orWhereHas('causer', function ($q2) use ($filters) {
                            $q2->where('name', 'like', '%' . $filters['search'] . '%')
                                ->orWhere('email', 'like', '%' . $filters['search'] . '%');
                        });
                });
            }

            $activities = $query->orderBy('created_at', 'desc')->paginate(50);

            $formattedActivities = $activities->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'log_name' => $activity->log_name,
                    'event' => $activity->event,
                    'description' => $activity->description,
                    'subject_type' => $activity->subject_type,
                    'subject_id' => $activity->subject_id,
                    'causer' => $activity->causer ? [
                        'id' => $activity->causer->id,
                        'name' => $activity->causer->name,
                        'email' => $activity->causer->email,
                        'role' => $activity->causer->role,
                    ] : null,
                    'properties' => $activity->properties,
                    'created_at' => $activity->created_at->format('d/m/Y H:i:s'),
                    'created_at_human' => $activity->created_at->diffForHumans(),
                ];
            });

            $activityTypes = Activity::select('log_name', \DB::raw('COUNT(*) as count'))
                ->groupBy('log_name')
                ->get()
                ->pluck('count', 'log_name');

            return response()->json([
                'activities' => $formattedActivities,
                'pagination' => [
                    'current_page' => $activities->currentPage(),
                    'last_page' => $activities->lastPage(),
                    'per_page' => $activities->perPage(),
                    'total' => $activities->total(),
                ],
                'activity_types' => $activityTypes,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to get activity logs: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get stock report (Báo cáo kho)
     */
    public function getStockReport(Request $request)
    {
        try {
            $startDate = $request->get('start_date')
                ? Carbon::parse($request->get('start_date'))->startOfDay()
                : Carbon::now()->startOfMonth();

            $endDate = $request->get('end_date')
                ? Carbon::parse($request->get('end_date'))->endOfDay()
                : Carbon::now()->endOfMonth();

            $overview = [
                'total_devices' => DeviceUnits::count(),
                'in_stock' => DeviceUnits::where('status', 'available')->count(),
                'borrowed' => DeviceUnits::where('status', 'borrowed')->count(),
                'under_maintenance' => DeviceUnits::where('status', 'maintenance')->count(),
                'retired' => DeviceUnits::where('status', 'retired')->withTrashed()->count(),
            ];
            $overview['stock_percentage'] = $overview['total_devices'] > 0
                ? round(($overview['in_stock'] / $overview['total_devices']) * 100, 1)
                : 0;

            $movements = [];

            $newDevices = DeviceUnits::whereBetween('created_at', [$startDate, $endDate])
                ->with(['device'])
                ->get()
                ->map(function ($unit) {
                    return [
                        'date' => $unit->created_at->format('Y-m-d H:i:s'),
                        'type' => 'NHẬP KHO',
                        'reason' => 'Mua mới',
                        'device' => $unit->device->name ?? 'N/A',
                        'serial' => $unit->serial_number,
                        'person' => 'Admin',
                        'quantity' => 1,
                    ];
                });

            $returns = ReturnSlip::whereBetween('return_date', [$startDate, $endDate])
                ->with(['borrow.borrower', 'details.deviceUnit' => function ($query) {
                    $query->withTrashed()->with('device');
                }])
                ->get()
                ->flatMap(function ($return) {
                    return $return->details->map(function ($detail) use ($return) {
                        return [
                            'date' => $return->return_date->format('Y-m-d H:i:s'),
                            'type' => 'NHẬP KHO',
                            'reason' => 'Trả lại',
                            'device' => $detail->deviceUnit->device->name ?? 'N/A',
                            'serial' => $detail->deviceUnit->serial_number ?? 'N/A',
                            'person' => $return->borrow->borrower->name ?? 'N/A',
                            'quantity' => 1,
                        ];
                    });
                });

            $borrows = Borrows::whereBetween('issued_at', [$startDate, $endDate])
                ->whereIn('status', ['completed', 'returned'])
                ->with(['borrower', 'details.deviceUnit' => function ($query) {
                    $query->withTrashed()->with('device');
                }])
                ->get()
                ->flatMap(function ($borrow) {
                    return $borrow->details->map(function ($detail) use ($borrow) {
                        return [
                            'date' => $borrow->issued_at,
                            'type' => 'XUẤT KHO',
                            'reason' => 'Cho mượn',
                            'device' => $detail->deviceUnit->device->name ?? 'N/A',
                            'serial' => $detail->deviceUnit->serial_number ?? 'N/A',
                            'person' => $borrow->borrower->name ?? 'N/A',
                            'quantity' => $detail->quantity ?? 1,
                        ];
                    });
                });

            $retired = DeviceUnits::withTrashed()
                ->whereBetween('retired_at', [$startDate, $endDate])
                ->where('status', 'retired')
                ->with('device', 'retiredBy')
                ->get()
                ->map(function ($unit) {
                    return [
                        'date' => $unit->retired_at,
                        'type' => 'XUẤT KHO',
                        'reason' => 'Thanh lý',
                        'device' => $unit->device->name ?? 'N/A',
                        'serial' => $unit->serial_number,
                        'admin' => $unit->retiredBy->name ?? 'N/A',
                        'retire_reason' => $unit->retire_reason,
                        'quantity' => 1,
                    ];
                });

            $movements = $newDevices->concat($returns)->concat($borrows)->concat($retired)
                ->sortByDesc('date')
                ->values();

            $monthlyStats = [];
            for ($i = 5; $i >= 0; $i--) {
                $monthStart = now()->subMonths($i)->startOfMonth();
                $monthEnd = now()->subMonths($i)->endOfMonth();

                $newDevicesCount = DeviceUnits::whereBetween('created_at', [$monthStart, $monthEnd])->count();
                $returnsCount = ReturnSlip::whereBetween('return_date', [$monthStart, $monthEnd])->count();

                $borrowsCount = Borrows::whereBetween('issued_at', [$monthStart, $monthEnd])
                    ->whereIn('status', ['completed', 'returned'])->count();
                $retiredCount = DeviceUnits::whereBetween('retired_at', [$monthStart, $monthEnd])
                    ->where('status', 'retired')->count();

                $totalStockIn = $newDevicesCount + $returnsCount;
                $totalStockOut = $borrowsCount + $retiredCount;

                $monthlyStats[] = [
                    'month' => $monthStart->format('Y-m'),
                    'month_name' => $monthStart->format('M Y'),
                    // Tổng
                    'stock_in' => $totalStockIn,
                    'stock_out' => $totalStockOut,
                    'net_change' => $totalStockIn - $totalStockOut,
                    // Chi tiết NHẬP
                    'new_devices' => $newDevicesCount,
                    'returns' => $returnsCount,
                    // Chi tiết XUẤT
                    'borrows' => $borrowsCount,
                    'retired' => $retiredCount,
                ];
            }

            return response()->json([
                'overview' => $overview,
                'movements' => $movements,
                'monthly_stats' => $monthlyStats,
                'period' => [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to get stock report: ' . $e->getMessage()], 500);
        }
    }
    public function getDetailBorrows(Request $request)
    {
        $borrows = Borrows::with(['borrower', 'details.deviceUnit.device'])
            ->whereNotNull('issued_by_user_id')
            ->get();

        return response()->json([
            'data' => $borrows
        ], 200);
    }
}
