<?php

namespace App\Filters;

class UserFilter extends BaseFilter
{
    public function name($value)
    {
        $this->query->where('name', 'like', "%{$value}%");
    }

    public function email($value)
    {
        $this->query->where('email', 'like', "%{$value}%");
    }

    public function role($value)
    {
        $this->query->where('role', $value);
    }

    public function is_active($value)
    {
        $this->query->where('is_active', $value);
    }

    public function from_date($value)
    {
        $this->query->whereDate('created_at', '>=', $value);
    }

    public function to_date($value)
    {
        $this->query->whereDate('created_at', '<=', $value);
    }

    public function order_by($field)
    {
        $allowed = ['id', 'name', 'email', 'created_at'];
        $direction = strtolower($this->filters['direction'] ?? 'asc');

        if (in_array($field, $allowed)) {
            $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';
            $this->query->orderBy($field, $direction);
        }
    }
}
