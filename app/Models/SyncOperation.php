<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEntreprise;
use Illuminate\Database\Eloquent\Model;

class SyncOperation extends Model
{
    use BelongsToEntreprise;

    protected $table = 'sync_operations';

    protected $fillable = [
        'entreprise_id',
        'user_id',
        'client_operation_uuid',
        'operation_type',
        'payload',
        'status',
        'conflict_details',
        'synced_at',
    ];

    protected $casts = [
        'payload'          => 'array',
        'conflict_details' => 'array',
        'synced_at'        => 'datetime',
    ];
}

