<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEntreprise;
use Illuminate\Database\Eloquent\Model;

class ImportBatch extends Model
{
    use BelongsToEntreprise;

    protected $table = 'import_batches';

    protected $fillable = [
        'entreprise_id',
        'user_id',
        'type_import',
        'nom_fichier',
        'statut',
        'lignes_valides',
        'lignes_rejetees',
        'lignes_ignorees',
        'resume',
    ];

    protected $casts = [
        'lignes_valides'  => 'array',
        'lignes_rejetees' => 'array',
        'lignes_ignorees' => 'array',
        'resume'          => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getTypeAttribute(): string
    {
        return (string) $this->type_import;
    }

    public function getFilenameAttribute(): string
    {
        return (string) $this->nom_fichier;
    }

    public function getTotalRowsAttribute(): int
    {
        return (int) ($this->resume['total_analyse'] ?? 0);
    }

    public function getImportedRowsAttribute(): int
    {
        return (int) ($this->resume['importees'] ?? 0);
    }

    public function getRejectedRowsAttribute(): int
    {
        return (int) (($this->resume['rejetees'] ?? 0) + ($this->resume['ignorees'] ?? 0));
    }

    public function getPreviewDataAttribute(): array
    {
        return array_map(fn ($item) => [
            'line' => $item['ligne'] ?? 0,
            'data' => $item['data'] ?? [],
        ], $this->lignes_valides ?? []);
    }

    public function getErrorsAttribute(): array
    {
        $all = array_merge($this->lignes_rejetees ?? [], $this->lignes_ignorees ?? []);
        return array_map(fn ($item) => [
            'line'     => $item['ligne'] ?? 0,
            'messages' => [$item['raison'] ?? 'Anomalie détectée'],
        ], $all);
    }
}
