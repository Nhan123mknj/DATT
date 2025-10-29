<?php

namespace App\Filters;

class DeviceCategoryFilter extends BaseFilter
{
    public function is_active($value)
    {
        $this->query->where('is_active', $value);
    }

    public function search($value)
    {
        $this->query->where('name', 'like', "%{$value}%");
    }

    public function order_by($field)
    {
        $allow = [
            'name' => 'name',
            'created_at' => 'created_at',
        ];

        $direction = strtolower($this->filters['direction'] ?? 'asc');

        if (isset($allow[$field])) {
            $this->query->orderBy($allow[$field], in_array($direction, ['asc', 'desc']) ? $direction : 'asc');
        }
    }

    public function with_devices($value)
    {
        if ($value) {
            $this->query->whereHas('devices');
        }
    }
}
