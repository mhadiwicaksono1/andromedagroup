<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\Activity;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $selectedProjectId = $request->query('project_id');

        $projects = Project::withCount(['tasks', 'tasks as completed_tasks_count' => function ($query) {
            $query->where('status', 'done');
        }])->get();

        $tasksQuery = Task::with('project')->orderBy('order_index', 'asc')->orderBy('created_at', 'desc');

        if ($selectedProjectId) {
            $tasksQuery->where('project_id', $selectedProjectId);
        }

        $allTasks = $tasksQuery->get();

        $columns = [
            'todo' => $allTasks->where('status', 'todo')->values(),
            'in_progress' => $allTasks->where('status', 'in_progress')->values(),
            'review' => $allTasks->where('status', 'review')->values(),
            'done' => $allTasks->where('status', 'done')->values(),
        ];

        $totalTasks = Task::count();
        $doneTasks = Task::where('status', 'done')->count();
        $inProgressTasks = Task::where('status', 'in_progress')->count();
        $urgentTasks = Task::where('priority', 'urgent')->where('status', '!=', 'done')->count();
        $completionRate = $totalTasks > 0 ? (int) round(($doneTasks / $totalTasks) * 100) : 0;

        $stats = [
            'total' => $totalTasks,
            'done' => $doneTasks,
            'in_progress' => $inProgressTasks,
            'urgent' => $urgentTasks,
            'completion_rate' => $completionRate,
        ];

        $recentActivities = Activity::with(['task', 'project'])
            ->latest()
            ->take(8)
            ->get();

        return view('dashboard', compact(
            'projects',
            'columns',
            'stats',
            'recentActivities',
            'selectedProjectId'
        ));
    }

    public function stats()
    {
        $total = Task::count();
        $byStatus = [
            'todo' => Task::where('status', 'todo')->count(),
            'in_progress' => Task::where('status', 'in_progress')->count(),
            'review' => Task::where('status', 'review')->count(),
            'done' => Task::where('status', 'done')->count(),
        ];

        $byPriority = [
            'urgent' => Task::where('priority', 'urgent')->count(),
            'high' => Task::where('priority', 'high')->count(),
            'medium' => Task::where('priority', 'medium')->count(),
            'low' => Task::where('priority', 'low')->count(),
        ];

        $projectsProgress = Project::withCount('tasks')
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'color' => $p->color,
                    'total' => $p->tasks_count,
                    'completion' => $p->completion_rate,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => [
                'total' => $total,
                'by_status' => $byStatus,
                'by_priority' => $byPriority,
                'projects' => $projectsProgress,
            ],
        ]);
    }
}
