<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:todo,in_progress,review,done',
            'due_date' => 'nullable|date',
        ]);

        $maxOrder = Task::where('status', $validated['status'])->max('order_index') ?? 0;
        $validated['order_index'] = $maxOrder + 1;

        $task = Task::create($validated);

        Activity::create([
            'task_id' => $task->id,
            'project_id' => $task->project_id,
            'type' => 'created',
            'description' => 'Tugas baru dibuat: "' . $task->title . '"',
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Tugas berhasil dibuat!',
                'data' => $task->load('project'),
            ]);
        }

        return redirect()->back()->with('success', 'Tugas baru berhasil ditambahkan!');
    }

    public function show(Task $task)
    {
        $task->load(['project', 'activities' => function ($q) {
            $q->latest()->take(10);
        }]);

        return response()->json([
            'status' => 'success',
            'data' => $task,
        ]);
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'project_id' => 'sometimes|exists:projects,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'status' => 'sometimes|in:todo,in_progress,review,done',
            'due_date' => 'nullable|date',
        ]);

        $oldStatus = $task->status;
        $task->update($validated);

        if (isset($validated['status']) && $validated['status'] !== $oldStatus) {
            Activity::create([
                'task_id' => $task->id,
                'project_id' => $task->project_id,
                'type' => 'status_changed',
                'description' => 'Status tugas diubah dari ' . strtoupper($oldStatus) . ' ke ' . strtoupper($task->status),
            ]);
        } else {
            Activity::create([
                'task_id' => $task->id,
                'project_id' => $task->project_id,
                'type' => 'updated',
                'description' => 'Informasi tugas "' . $task->title . '" diperbarui.',
            ]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Tugas berhasil diperbarui!',
                'data' => $task->load('project'),
            ]);
        }

        return redirect()->back()->with('success', 'Tugas berhasil diperbarui!');
    }

    public function updateStatus(Request $request, Task $task)
    {
        $validated = $request->validate([
            'status' => 'required|in:todo,in_progress,review,done',
            'order_index' => 'nullable|integer',
        ]);

        $oldStatus = $task->status;
        $newStatus = $validated['status'];

        $task->status = $newStatus;
        if (isset($validated['order_index'])) {
            $task->order_index = $validated['order_index'];
        }
        $task->save();

        if ($oldStatus !== $newStatus) {
            $statusLabels = [
                'todo' => 'To Do',
                'in_progress' => 'In Progress',
                'review' => 'Review',
                'done' => 'Done',
            ];
            $oldLabel = $statusLabels[$oldStatus] ?? $oldStatus;
            $newLabel = $statusLabels[$newStatus] ?? $newStatus;

            Activity::create([
                'task_id' => $task->id,
                'project_id' => $task->project_id,
                'type' => 'status_changed',
                'description' => 'Tugas dipindahkan dari [' . $oldLabel . '] ke [' . $newLabel . ']',
            ]);
        }

        $counts = [
            'todo' => Task::where('status', 'todo')->count(),
            'in_progress' => Task::where('status', 'in_progress')->count(),
            'review' => Task::where('status', 'review')->count(),
            'done' => Task::where('status', 'done')->count(),
            'total' => Task::count(),
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'Status berhasil diubah ke ' . ucfirst($newStatus),
            'data' => [
                'task' => $task->load('project'),
                'counts' => $counts,
            ],
        ]);
    }

    public function destroy(Request $request, Task $task)
    {
        $title = $task->title;
        $projectId = $task->project_id;

        Activity::create([
            'project_id' => $projectId,
            'type' => 'deleted',
            'description' => 'Tugas "' . $title . '" telah dihapus.',
        ]);

        $task->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Tugas "' . $title . '" berhasil dihapus!',
            ]);
        }

        return redirect()->back()->with('success', 'Tugas berhasil dihapus!');
    }
}
