<?php

namespace App\Http\Controllers;

use App\Services\DemoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemoController extends Controller
{
    /**
     * Connecte l'utilisateur à l'environnement de démonstration isolé (RM-075 à RM-077).
     */
    public function loginDemo(Request $request)
    {
        $data = DemoService::resetDemoEnvironment();
        $user = $data['user'];
        $entreprise = $data['entreprise'];

        Auth::login($user);
        $request->session()->regenerate();
        session(['active_entreprise_id' => $entreprise->id]);

        return redirect()->route('dashboard')->with(
            'success',
            'Bienvenue dans le mode démonstration STOKGX. Toutes les données sont isolées des entreprises réelles.'
        );
    }

    /**
     * Réinitialise les données de l'entreprise de démonstration sans affecter la production.
     */
    public function resetDemo(Request $request)
    {
        $user = Auth::user();
        abort_unless($user && ($user->is_demo || $user->isSuperAdmin()), 403);

        $data = DemoService::resetDemoEnvironment();
        session(['active_entreprise_id' => $data['entreprise']->id]);

        return redirect()->route('dashboard')->with(
            'success',
            'Les données de démonstration ont été réinitialisées avec succès.'
        );
    }
}
