<?php 

namespace App\ModelFilters;

class RoleFilter extends BaseFilter
{
    /**
    * Related Models that have ModelFilters as well as the method on the ModelFilter
    * As [relationMethod => [input_key1, input_key2]].
    *
    * @var array
    */
    public $relations = [];

    public function key($key)
    {
        $this->where(function ($q) use ($key) {
            $condition = '%'.$key.'%';
            $q->where('display_name', 'like', $condition)->orWhere('name', 'like', $condition);
        });
    }

    public function guard($guard)
    {
        $this->where('guard', $guard);
    }
}
