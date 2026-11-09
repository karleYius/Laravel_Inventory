<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $action = (string) $request->query('action', '');

        $logs = ActivityLog::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('user_name', 'like', "%{$search}%")
                        ->orWhere('subject_name', 'like', "%{$search}%")
                        ->orWhere('action', 'like', "%{$search}%");
                });
            })
            ->when($action !== '', fn ($query) => $query->where('action', $action))
            ->latest()
            ->paginate(25)
            ->appends(['search' => $search, 'action' => $action]);

        $actions = ActivityLog::query()->distinct()->orderBy('action')->pluck('action');

        return view('activity-logs.index', compact('logs', 'search', 'action', 'actions'));
    }
}
