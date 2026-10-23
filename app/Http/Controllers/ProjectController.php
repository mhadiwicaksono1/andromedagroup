<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|string|max:30',
            'icon' => 'nullable|string|max:30',
        ]);

        $project = Project::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'] ?? '#6366f1',
            'icon' => $validated['icon'] ?? 'folder',
            'status' => 'active',
        ]);

        Activity::create([
            'project_id' => $project->id,
            'type' => 'created',
            'description' => 'Proyek baru "' . $project->name . '" telah dibuat.',
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Proyek berhasil ditambahkan!',
                'data' => $project,
            ]);
        }

        return redirect()->route('dashboard.index', ['project_id' => $project->id])
            ->with('success', 'Proyek baru berhasil dibuat!');
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|string|max:30',
            'icon' => 'nullable|string|max:30',
            'status' => 'nullable|string|in:active,completed,archived',
        ]);

        $project->update($validated);

        Activity::create([
            'project_id' => $project->id,
            'type' => 'updated',
            'description' => 'Data proyek "' . $project->name . '" diperbarui.',
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Proyek berhasil diperbarui!',
                'data' => $project,
            ]);
        }

        return redirect()->back()->with('success', 'Proyek berhasil diperbarui!');
    }

    public function destroy(Request $request, Project $project)
    {
        $name = $project->name;
        $project->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Proyek "' . $name . '" berhasil dihapus!',
            ]);
        }

        return redirect()->route('dashboard.index')->with('success', 'Proyek berhasil dihapus!');
    }
}
