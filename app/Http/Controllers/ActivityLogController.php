<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = ActivityLog::with('user:id,name,role')
            ->when($request->action, fn($q) => $q->action($request->action))
            ->when($request->model_type, fn($q) => $q->forModel($request->model_type))
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->date_debut, fn($q) => $q->whereDate('created_at', '>=', $request->date_debut))
            ->when($request->date_fin, fn($q) => $q->whereDate('created_at', '<=', $request->date_fin))
            ->when($request->search, function ($q) use ($request) {
                $search = $request->search;
                $termes = explode(' ', trim($search));

                $q->where(function ($subQ) use ($termes) {
                    foreach ($termes as $terme) {
                        $terme = trim($terme);
                        if (strlen($terme) >= 2) {
                            $subQ->where('description', 'ILIKE', "%{$terme}%")
                                 ->orWhereHas('user', function ($userQ) use ($terme) {
                                     $userQ->where('name', 'ILIKE', "%{$terme}%")
                                           ->orWhere('role', 'ILIKE', "%{$terme}%");
                                 });
                        }
                    }
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(50)
            ->withQueryString();

        // Statistiques
        $stats = [
            'total' => ActivityLog::count(),
            'today' => ActivityLog::whereDate('created_at', today())->count(),
            'creations' => ActivityLog::action('create')->count(),
            'modifications' => ActivityLog::action('update')->count(),
            'suppressions' => ActivityLog::action('delete')->count(),
            'phase_changes' => ActivityLog::action('phase_change')->count(),
        ];

        $actions = ['create', 'update', 'delete', 'phase_change', 'login', 'logout', 'password_reset', 'password_reset_request'];
        $modelTypes = ActivityLog::distinct('model_type')->whereNotNull('model_type')->pluck('model_type');

        return Inertia::render('Historique/Index', [
            'logs' => $logs,
            'stats' => $stats,
            'actions' => $actions,
            'modelTypes' => $modelTypes,
            'filters' => $request->only(['action', 'model_type', 'user_id', 'date_debut', 'date_fin', 'search']),
        ]);
    }

    public function show(ActivityLog $log)
    {
        $log->load('user:id,name,role');

        return Inertia::render('Historique/Show', [
            'log' => $log,
        ]);
    }
} 