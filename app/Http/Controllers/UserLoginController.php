<?php

namespace App\Http\Controllers;

use App\Models\UserLogin;
use Illuminate\Support\Facades\Auth;

class UserLoginController extends Controller
{
    /**
     * Vérification des permissions
     */
    private function checkAdminPermission(): void
    {
        if (!Auth::user()->hasAnyRole(['Administrateur', 'super admin'])) {
            abort(403, 'Accès refusé. Seul un administrateur peut consulter le journal des connexions.');
        }
    }

    /**
     * Journal des connexions
     */
    public function index()
    {
        $this->checkAdminPermission();

        $logins = UserLogin::with('user')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('user-logins.index', compact('logins'));
    }

    /**
     * Suppression d'une entrée du journal
     */
    public function destroy(UserLogin $userLogin)
    {
        $this->checkAdminPermission();

        $userLogin->delete();

        return redirect()->route('user-logins.index')->with('success', 'Entrée du journal supprimée avec succès.');
    }
}
