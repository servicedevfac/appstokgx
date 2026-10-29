<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Services\EntrepriseContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isSuperAdmin() || $user->can('afficher journal audit'), 403);

        $query = AuditLog::with(['user', 'entreprise'])->orderByDesc('created_at')->orderByDesc('id');

        if (!$user->isSuperAdmin()) {
            $entrepriseId = EntrepriseContext::currentId();
            $query->where('entreprise_id', $entrepriseId);
        } elseif ($request->filled('entreprise_id')) {
            $query->where('entreprise_id', (int) $request->input('entreprise_id'));
        }

        if ($request->filled('action')) {
            $query->where('action', 'like', '%' . $request->input('action') . '%');
        }

        $logs = $query->paginate(50)->withQueryString();

        return view('admin.audit.index', compact('logs'));
    }
}

