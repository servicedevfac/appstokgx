<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'current_password',
        'remember_token',
        'token',
        'api_token',
        'secret',
        'client_secret',
        '_token',
    ];

    /**
     * Enregistre une entrée immuable dans le journal d'audit.
     * Supporte les deux conventions d'appel :
     * 1) AuditService::log($action, $objetModelOrString, $objetId, $contexteArray, $motif, $entrepriseId, $userId)
     * 2) AuditService::log($action, $descriptionMotifString, $objetModelOrNull, $oldArray, $newArray, $entrepriseId)
     */
    public static function log(
        string $action,
        Model|string $objet,
        mixed $objetId = null,
        mixed $contexte = [],
        mixed $motif = null,
        ?int $entrepriseId = null,
        ?int $userId = null
    ): AuditLog {
        // Détection automatique de la convention d'appel 2 : ($action, $description, $modelInstance, $old, $new, $entrepriseId)
        if (is_string($objet) && ($objetId instanceof Model || is_array($contexte) || $contexte === null) && (is_array($motif) || $motif === null)) {
            if ($objetId instanceof Model || $contexte === null || is_array($motif)) {
                $description = $objet;
                $model = $objetId instanceof Model ? $objetId : null;
                $oldValues = is_array($contexte) ? $contexte : null;
                $newValues = is_array($motif) ? $motif : null;

                $objetType = $model ? class_basename($model) : 'System';
                $resolvedObjetId = $model?->getKey();
                $entrepriseId = $entrepriseId ?? ($model?->getAttribute('entreprise_id') ?: null);
                $motifStr = $description;
                $ctxArray = array_filter([
                    'old' => $oldValues,
                    'new' => $newValues,
                ], fn ($v) => $v !== null);

                return self::persistLog($action, $objetType, $resolvedObjetId, $ctxArray, $motifStr, $entrepriseId, $userId);
            }
        }

        if ($objet instanceof Model) {
            $objetType = class_basename($objet);
            $resolvedObjetId = $objetId ?? $objet->getKey();
            $entrepriseId = $entrepriseId ?? ($objet->getAttribute('entreprise_id') ?: null);
        } else {
            $objetType = $objet;
            $resolvedObjetId = $objetId;
        }

        $ctxArray = is_array($contexte) ? $contexte : [];
        $motifStr = is_string($motif) ? $motif : null;

        return self::persistLog($action, $objetType, $resolvedObjetId, $ctxArray, $motifStr, $entrepriseId, $userId);
    }

    protected static function persistLog(
        string $action,
        string $objetType,
        mixed $objetId,
        array $contexte,
        ?string $motif,
        ?int $entrepriseId,
        ?int $userId
    ): AuditLog {
        $user = Auth::user();
        $resolvedUserId = $userId ?? $user?->id;
        $resolvedEntrepriseId = $entrepriseId ?? EntrepriseContext::currentId() ?? $user?->current_entreprise_id;

        $sanitizedContext = self::sanitize($contexte);

        return AuditLog::create([
            'entreprise_id' => $resolvedEntrepriseId,
            'user_id'       => $resolvedUserId,
            'action'        => $action,
            'objet_type'    => $objetType,
            'objet_id'      => $objetId !== null ? (string) $objetId : null,
            'motif'         => $motif,
            'contexte'      => $sanitizedContext,
            'ip_address'    => request()?->ip(),
            'user_agent'    => request()?->userAgent(),
            'created_at'    => now(),
        ]);
    }

    public static function sanitize(array $data): array
    {
        $clean = [];
        foreach ($data as $key => $value) {
            if (in_array(strtolower((string) $key), self::SENSITIVE_KEYS, true)) {
                continue;
            }
            if (is_array($value)) {
                $clean[$key] = self::sanitize($value);
            } else {
                $clean[$key] = $value;
            }
        }
        return $clean;
    }
}
