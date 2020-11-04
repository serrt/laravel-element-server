<?php

namespace App\Models;

use Spatie\Permission\Models\Role as BaseRole;

class Role extends BaseRole
{
    protected $fillable = ['name', 'guard_name', 'display_name'];

    protected $attributes = [
        'guard_name' => 'admin'
    ];
}
