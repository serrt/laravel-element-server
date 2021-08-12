<?php

namespace App\Models;

use Spatie\Permission\Models\Role as BaseRole;
use EloquentFilter\Filterable;

class Role extends BaseRole
{
    use Filterable;
    
    protected $fillable = ['name', 'guard_name', 'display_name'];

    protected $attributes = [
        'guard_name' => 'admin'
    ];
}
