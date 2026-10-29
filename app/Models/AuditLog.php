<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $table = 'audit_logs';

    protected $fillable = [
        'entreprise_id',
        'user_id',
        'action',
        'objet_type',
        'objet_id',
        'motif',
        'contexte',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'contexte'   => 'array',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function () {
            throw new LogicException('Les entrées du journal d\'audit sont immuables et ne peuvent pas être modifiées.');
        });

        static::deleting(function () {
            throw new LogicException('Les entrées du journal d\'audit sont protégées et ne peuvent pas être supprimées.');
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class, 'entreprise_id');
    }

    public function getDescriptionAttribute(): string
    {
        return (string) ($this->motif ?: $this->action);
    }
}
