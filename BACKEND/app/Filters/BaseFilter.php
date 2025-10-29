<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

abstract class BaseFilter
{
    protected Builder $query;
    protected array $filters;

    public function __construct(Builder $query, array $filters = [])
    {
        $this->query = $query;
        $this->filters = $filters;
    }


    public function apply(): Builder
    {
        foreach ($this->filters as $name => $value) {

            if (method_exists($this, $name) && $value !== null && $value !== '') {
                $this->$name($value);
            }
        }

        return $this->query;
    }
}
