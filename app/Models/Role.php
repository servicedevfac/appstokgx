<?php

namespace App\Models;
use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;

class Role extends SpatieRole
{
    protected $fillable = [
        'name',
    ];
    protected $attributes = [
    'guard_name' => 'web',
];
    // Pas besoin de redéfinir la relation permissions()
}

