<?php

namespace App\Services;

use App\Models\Entreprise;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class EntrepriseContext
{
    private static ?int $overrideEntrepriseId = null;
    private static bool $bypassGlobalScope = false;

    public static function setOverride(?int $entrepriseId): void
    {
        self::$overrideEntrepriseId = $entrepriseId;
    }

    public static function setCurrentId(?int $entrepriseId): void
    {
        self::$overrideEntrepriseId = $entrepriseId;
    }

    public static function withoutScope(callable $callback): mixed
    {
        $previous = self::$bypassGlobalScope;
        self::$bypassGlobalScope = true;
        try {
            return $callback();
        } finally {
            self::$bypassGlobalScope = $previous;
        }
    }

    public static function isBypassed(): bool
    {
        return self::$bypassGlobalScope;
    }

    public static function reset(): void
    {
        self::$overrideEntrepriseId = null;
        self::$bypassGlobalScope = false;
    }

    public static function currentId(?User $user = null): ?int
    {
        if (self::$overrideEntrepriseId !== null) {
            return self::$overrideEntrepriseId;
        }

        $user = $user ?? Auth::user();
        if (! $user) {
            return null;
        }

        // 1. Session active entreprise (vérifiée côté serveur)
        $sessionId = session('active_entreprise_id') ?? session('current_entreprise_id');
        if ($sessionId && $user->canAccessEntreprise((int) $sessionId)) {
            return (int) $sessionId;
        }

        // 2. Colonne current_entreprise_id sur l'utilisateur (vérifiée côté serveur)
        if ($user->current_entreprise_id && $user->canAccessEntreprise((int) $user->current_entreprise_id)) {
            return (int) $user->current_entreprise_id;
        }

        // 3. Première entreprise autorisée
        $accessibleIds = $user->accessibleEntrepriseIds();
        if (! empty($accessibleIds)) {
            return (int) $accessibleIds[0];
        }

        return null;
    }

    public static function current(?User $user = null): ?Entreprise
    {
        $id = self::currentId($user);
        return $id ? Entreprise::find($id) : null;
    }
}
