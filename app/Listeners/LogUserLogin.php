<?php

namespace App\Listeners;

use App\Services\AuditService;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;

class LogUserLogin
{
    /**
     * Handle the event synchronously so login history is always recorded reliably.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;
        $entrepriseId = $user->entreprise_id ?? null;

        DB::table('user_logins')->insert([
            'entreprise_id' => $entrepriseId,
            'user_id'       => $user->getAuthIdentifier(),
            'ip_address'    => request()->ip(),
            'user_agent'    => request()->userAgent(),
            'logged_in_at'  => now(),
        ]);

        AuditService::log(
            'auth.login',
            "Connexion de l'utilisateur {$user->email}",
            $user,
            null,
            null,
            $entrepriseId
        );
    }
}
