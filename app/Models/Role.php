<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    public function getDisplayLabelAttribute(): string
    {
        return User::roleLabel($this->name);
    }
}
