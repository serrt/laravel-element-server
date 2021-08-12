<?php 

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;
use Carbon\Carbon;

class BaseFilter extends ModelFilter
{
    public function include($value)
    {
        $value = is_array($value) ? $value : explode(',', $value);
        $this->with($value);
    }

    public function includeCount($value)
    {
        $value = is_array($value) ? $value : explode(',', $value);
        $this->withCount($value);
    }

    public function sort($sort)
    {
        $this->orderBy($sort, $this->input('sort_by', 'asc'));
    }

    public function createdStart($time)
    {
        $time = Carbon::createFromTimestamp(strtotime($time))->startOfDay();
        $this->where('created_at', '>=', $time);
    }

    public function createdEnd($time)
    {
        $time = Carbon::createFromTimestamp(strtotime($time))->endOfDay();
        $this->where('created_at', '<=', $time);
    }
}
