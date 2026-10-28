<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UtilisateurController extends Controller
{
      public function index()
    {
        $users = User::orderByDesc('created_at')->paginate(15);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
        'nom' => 'required|string',
        'prenom' => 'required|string',
        'email' => 'required|email|unique:users',
        'password' => 'required|string|min:6',
        'telephone' => 'required|string',

        ]);

        User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'telephone'=>$request->telephone,
            'role'=>$request->role,
        ]);

        return redirect()->route('users.index')->with('success', 'Utilisateur ajouté.');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $user->id,//.$user->id,
            'telephone' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        $user->update($request->only('nom','prenom', 'email','telephone','role'));

        return redirect()->route('users.index')->with('success', 'Utilisateur modifié.');
    }
    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    public function destroy(User $user)
    {
        if ($user->ventes()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer cet utilisateur. Il a déjà effectué des ventes.');
        }
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Utilisateur supprimé.');
    }
    public function block(User $user)
    {
        $user->update(['is_blocked' => true]);
        return redirect()->route('users.index')->with('success', 'Utilisateur bloqué.');
    }
    public function unblock(User $user)
    {
        $user->update(['is_blocked' => false]);
        return redirect()->route('users.index')->with('success', 'Utilisateur débloqué.');
    }
}
