<?php 

namespace App\ModelFilters;

class AdminUserFilter extends BaseFilter
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
            $q->where('username', 'like', $condition)->orWhere('name', 'like', $condition);
        });
    }
}
