<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeviceUnits extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'device_units';
    protected $fillable = [
        'device_id',
        'serial_number',
        'status',
        'purchase_date',
        'warranty_end',
        'notes',
        'retired_at',
        'retired_by',
        'retire_reason',
        'retirement_value',
        'retirement_method',
        'buyer_info',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'retired_at' => 'datetime',
        'purchase_date' => 'datetime',
        'warranty_end' => 'datetime',
        'retirement_value' => 'decimal:2',
    ];



    public function device()
    {
        return $this->belongsTo(Devices::class, 'device_id');
    }


    public function units()
    {
        return $this->hasMany(DeviceUnits::class, 'device_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }
    public function borrowDetail()
    {
        return $this->hasMany(BorrowsDetail::class, 'device_unit_id');
    }

    public function logs()
    {
        return $this->hasMany(DeviceLog::class, 'device_unit_id');
    }

    public function maintenances()
    {
        return $this->hasMany(DeviceMaintenance::class, 'device_unit_id');
    }


    public function getSuggestedRetirementValueAttribute(): float
    {
        if (!$this->device || !$this->device->price || !$this->purchase_date) {
            return 0;
        }

        $yearsUsed = $this->purchase_date->diffInYears(now());
        $depreciationYears = 5;

        $depreciationRate = min($yearsUsed / $depreciationYears, 1);

        $remainingValue = $this->device->price * (1 - $depreciationRate);

        return round(max($remainingValue, 0));
    }

 
    public function getDepreciationPercentageAttribute(): float
    {
        if (!$this->purchase_date) {
            return 0;
        }

        $yearsUsed = $this->purchase_date->diffInYears(now());
        $depreciationYears = 5;

        return round(min(($yearsUsed / $depreciationYears) * 100, 100), 2);
    }


    public function getProfitLossAttribute(): float
    {
        if (!$this->device || !$this->device->price) {
            return 0;
        }

        $retirementValue = $this->retirement_value ?? 0;
        return $retirementValue - $this->device->price;
    }


    public function getProfitLossPercentageAttribute(): float
    {
        if (!$this->device || !$this->device->price || $this->device->price == 0) {
            return 0;
        }

        return round(($this->profit_loss / $this->device->price) * 100, 2);
    }

    /**
     * Kiểm tra có lời không
     */
    public function isProfitAttribute(): bool
    {
        return $this->profit_loss >= 0;
    }

    public function retiredBy()
    {
        return $this->belongsTo(User::class, 'retired_by');
    }
}
