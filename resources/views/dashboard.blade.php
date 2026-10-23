@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Top Summary Banner & Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Metric 1: Total Tasks -->
        <div class="glass-panel p-5 rounded-2xl relative overflow-hidden group hover:border-indigo-500/40 transition-all duration-300">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Tugas</span>
                <span class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span id="stat-total-tasks" class="text-3xl font-extrabold text-white font-heading">{{ $stats['total'] }}</span>
                <span class="text-xs text-slate-400">tugas aktif</span>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-indigo-600"></div>
        </div>

        <!-- Metric 2: In Progress -->
        <div class="glass-panel p-5 rounded-2xl relative overflow-hidden group hover:border-cyan-500/40 transition-all duration-300">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Sedang Dikerjakan</span>
                <span class="w-8 h-8 rounded-lg bg-cyan-500/10 text-cyan-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-cyan-400 font-heading">{{ $stats['in_progress'] }}</span>
                <span class="text-xs text-slate-400">dalam proses</span>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-cyan-500 to-blue-600"></div>
        </div>

        <!-- Metric 3: Urgent Tasks -->
        <div class="glass-panel p-5 rounded-2xl relative overflow-hidden group hover:border-rose-500/40 transition-all duration-300">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Prioritas Mendesak</span>
                <span class="w-8 h-8 rounded-lg bg-rose-500/10 text-rose-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-rose-400 font-heading flex items-center gap-2">
                    {{ $stats['urgent'] }}
                    @if($stats['urgent'] > 0)
                        <span class="inline-block w-2.5 h-2.5 rounded-full bg-rose-500 pulse-urgent"></span>
                    @endif
                </span>
                <span class="text-xs text-slate-400">perlu perhatian segera</span>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-rose-500 to-red-600"></div>
        </div>

        <!-- Metric 4: Completion Rate -->
        <div class="glass-panel p-5 rounded-2xl relative overflow-hidden group hover:border-emerald-500/40 transition-all duration-300">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Penyelesaian</span>
                <span class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span id="stat-completion-rate" class="text-3xl font-extrabold text-emerald-400 font-heading">{{ $stats['completion_rate'] }}%</span>
                <span class="text-xs text-slate-400">selesai ({{ $stats['done'] }}/{{ $stats['total'] }})</span>
            </div>
            <div class="w-full h-1.5 bg-slate-800 rounded-full mt-3 overflow-hidden">
                <div id="stat-completion-bar" class="h-full bg-emerald-500 rounded-full transition-all duration-500" style="width: {{ $stats['completion_rate'] }}%"></div>
            </div>
        </div>
    </div>

    <!-- Filter & Project Navigation Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 glass-panel p-3.5 rounded-2xl">
        <!-- Project Tabs -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 scrollbar-none">
            <a href="{{ route('dashboard.index') }}" 
               class="px-3.5 py-1.5 text-xs font-semibold rounded-xl whitespace-nowrap transition-all {{ empty($selectedProjectId) ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'bg-slate-800/80 text-slate-400 hover:text-white hover:bg-slate-700/80' }}">
                Semua Proyek ({{ $projects->sum('tasks_count') }})
            </a>
            @foreach($projects as $proj)
                <a href="{{ route('dashboard.index', ['project_id' => $proj->id]) }}" 
                   class="px-3.5 py-1.5 text-xs font-semibold rounded-xl whitespace-nowrap flex items-center gap-2 transition-all {{ $selectedProjectId == $proj->id ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'bg-slate-800/80 text-slate-400 hover:text-white hover:bg-slate-700/80' }}">
                    <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $proj->color }}"></span>
                    <span>{{ $proj->name }}</span>
                    <span class="px-1.5 py-0.2 rounded-md bg-black/20 text-[10px]">{{ $proj->tasks_count }}</span>
                </a>
            @endforeach
        </div>

        <!-- Priority Filter Chips -->
        <div class="flex items-center gap-1.5 border-t md:border-t-0 pt-2 md:pt-0 border-slate-800">
            <span class="text-xs text-slate-400 mr-1 hidden sm:inline">Prioritas:</span>
            <button data-priority="all" class="priority-filter-chip px-2.5 py-1 text-xs font-medium rounded-lg bg-indigo-600 text-white transition-all">
                Semua
            </button>
            <button data-priority="urgent" class="priority-filter-chip px-2.5 py-1 text-xs font-medium rounded-lg bg-slate-800/80 text-slate-400 hover:text-rose-400 transition-all flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Urgent
            </button>
            <button data-priority="high" class="priority-filter-chip px-2.5 py-1 text-xs font-medium rounded-lg bg-slate-800/80 text-slate-400 hover:text-amber-400 transition-all flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> High
            </button>
            <button data-priority="medium" class="priority-filter-chip px-2.5 py-1 text-xs font-medium rounded-lg bg-slate-800/80 text-slate-400 hover:text-sky-400 transition-all flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span> Medium
            </button>
            <button data-priority="low" class="priority-filter-chip px-2.5 py-1 text-xs font-medium rounded-lg bg-slate-800/80 text-slate-400 hover:text-slate-300 transition-all flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Low
            </button>
        </div>
    </div>

    <!-- Interactive Drag & Drop Kanban Columns Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-start">
        <!-- 1. Column: TO DO -->
        <div class="glass-panel rounded-2xl p-4 flex flex-col min-h-[580px] border-t-4 border-t-slate-500/80">
            <div class="flex items-center justify-between mb-3 px-1">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span>
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-200">To Do</h2>
                </div>
                <span id="count-todo" class="px-2 py-0.5 text-xs font-bold rounded-full bg-slate-800 text-slate-300 border border-slate-700/60">
                    {{ count($columns['todo']) }}
                </span>
            </div>

            <!-- Dropzone -->
            <div class="kanban-dropzone flex-1 space-y-3 p-1 rounded-xl transition-all duration-200 min-h-[480px]" data-status="todo">
                @forelse($columns['todo'] as $task)
                    @include('partials.task_card', ['task' => $task])
                @empty
                    <div class="column-empty-state py-12 flex flex-col items-center justify-center text-center text-slate-500 border border-dashed border-slate-800 rounded-xl">
                        <svg class="w-8 h-8 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <span class="text-xs">Tarik tugas ke sini</span>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- 2. Column: IN PROGRESS -->
        <div class="glass-panel rounded-2xl p-4 flex flex-col min-h-[580px] border-t-4 border-t-indigo-500">
            <div class="flex items-center justify-between mb-3 px-1">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 animate-pulse"></span>
                    <h2 class="text-sm font-bold uppercase tracking-wider text-indigo-300">In Progress</h2>
                </div>
                <span id="count-in_progress" class="px-2 py-0.5 text-xs font-bold rounded-full bg-indigo-950/60 text-indigo-400 border border-indigo-500/30">
                    {{ count($columns['in_progress']) }}
                </span>
            </div>

            <!-- Dropzone -->
            <div class="kanban-dropzone flex-1 space-y-3 p-1 rounded-xl transition-all duration-200 min-h-[480px]" data-status="in_progress">
                @forelse($columns['in_progress'] as $task)
                    @include('partials.task_card', ['task' => $task])
                @empty
                    <div class="column-empty-state py-12 flex flex-col items-center justify-center text-center text-slate-500 border border-dashed border-slate-800 rounded-xl">
                        <svg class="w-8 h-8 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <span class="text-xs">Tarik tugas ke sini</span>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- 3. Column: REVIEW -->
        <div class="glass-panel rounded-2xl p-4 flex flex-col min-h-[580px] border-t-4 border-t-amber-500">
            <div class="flex items-center justify-between mb-3 px-1">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                    <h2 class="text-sm font-bold uppercase tracking-wider text-amber-300">Review</h2>
                </div>
                <span id="count-review" class="px-2 py-0.5 text-xs font-bold rounded-full bg-amber-950/60 text-amber-400 border border-amber-500/30">
                    {{ count($columns['review']) }}
                </span>
            </div>

            <!-- Dropzone -->
            <div class="kanban-dropzone flex-1 space-y-3 p-1 rounded-xl transition-all duration-200 min-h-[480px]" data-status="review">
                @forelse($columns['review'] as $task)
                    @include('partials.task_card', ['task' => $task])
                @empty
                    <div class="column-empty-state py-12 flex flex-col items-center justify-center text-center text-slate-500 border border-dashed border-slate-800 rounded-xl">
                        <svg class="w-8 h-8 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <span class="text-xs">Tarik tugas ke sini</span>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- 4. Column: COMPLETED -->
        <div class="glass-panel rounded-2xl p-4 flex flex-col min-h-[580px] border-t-4 border-t-emerald-500">
            <div class="flex items-center justify-between mb-3 px-1">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                    <h2 class="text-sm font-bold uppercase tracking-wider text-emerald-300">Completed</h2>
                </div>
                <span id="count-done" class="px-2 py-0.5 text-xs font-bold rounded-full bg-emerald-950/60 text-emerald-400 border border-emerald-500/30">
                    {{ count($columns['done']) }}
                </span>
            </div>

            <!-- Dropzone -->
            <div class="kanban-dropzone flex-1 space-y-3 p-1 rounded-xl transition-all duration-200 min-h-[480px]" data-status="done">
                @forelse($columns['done'] as $task)
                    @include('partials.task_card', ['task' => $task])
                @empty
                    <div class="column-empty-state py-12 flex flex-col items-center justify-center text-center text-slate-500 border border-dashed border-slate-800 rounded-xl">
                        <svg class="w-8 h-8 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-xs">Tarik tugas ke sini</span>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Activity History Section -->
    <div class="glass-panel rounded-2xl p-5 border border-slate-800">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-300">Riwayat Aktivitas Terkini</h3>
            </div>
            <span class="text-xs text-slate-500">{{ count($recentActivities) }} aktivitas tercatat</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
            @forelse($recentActivities as $act)
                <div class="bg-slate-900/60 p-3 rounded-xl border border-slate-800/80 flex items-start gap-2.5">
                    <div class="w-2 h-2 rounded-full bg-indigo-400 mt-1.5 flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-slate-300 font-medium truncate" title="{{ $act->description }}">
                            {{ $act->description }}
                        </p>
                        <span class="text-[10px] text-slate-500 mt-0.5 block">
                            {{ $act->created_at->diffForHumans() }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-4 text-center text-xs text-slate-500">
                    Belum ada riwayat aktivitas yang tercatat.
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- ==========================================
     MODALS SECTION
=========================================== -->

<!-- Modal: New Task -->
<div id="modal-new-task" class="modal-container fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl relative animate-in fade-in zoom-in-95 duration-200">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="text-lg font-bold text-white font-heading flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                Buat Tugas Baru
            </h3>
            <button id="btn-close-task-modal" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form id="form-new-task" class="mt-5 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Judul Tugas *</label>
                <input type="text" name="title" id="task-title-input" required placeholder="Contoh: Desain landing page responsif"
                       class="w-full px-3.5 py-2.5 text-sm bg-slate-800/80 border border-slate-700 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Pilih Proyek *</label>
                    <select name="project_id" required class="w-full px-3.5 py-2.5 text-sm bg-slate-800/80 border border-slate-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" {{ $selectedProjectId == $p->id ? 'selected' : '' }}>
                                {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Prioritas *</label>
                    <select name="priority" required class="w-full px-3.5 py-2.5 text-sm bg-slate-800/80 border border-slate-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="medium" selected>Medium</option>
                        <option value="urgent">Urgent</option>
                        <option value="high">High</option>
                        <option value="low">Low</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Status Awal</label>
                    <select name="status" class="w-full px-3.5 py-2.5 text-sm bg-slate-800/80 border border-slate-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="todo" selected>To Do</option>
                        <option value="in_progress">In Progress</option>
                        <option value="review">Review</option>
                        <option value="done">Completed</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Tenggat Waktu</label>
                    <input type="date" name="due_date" 
                           class="w-full px-3.5 py-2 text-sm bg-slate-800/80 border border-slate-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Rincian / Catatan</label>
                <textarea name="description" rows="3" placeholder="Tambahkan deskripsi atau spesifikasi tugas..."
                          class="w-full px-3.5 py-2.5 text-sm bg-slate-800/80 border border-slate-700 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>

            <div class="pt-3 flex justify-end gap-3">
                <button type="button" onclick="window.kanban.closeModal(document.getElementById('modal-new-task'))"
                        class="px-4 py-2.5 text-sm font-semibold rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors">
                    Batal
                </button>
                <button type="submit" 
                        class="px-5 py-2.5 text-sm font-semibold rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/30 transition-all">
                    Simpan Tugas
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: New Project -->
<div id="modal-new-project" class="modal-container fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl relative">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="text-lg font-bold text-white font-heading flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-cyan-400"></span>
                Buat Proyek Baru
            </h3>
            <button id="btn-close-project-modal" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form id="form-new-project" class="mt-5 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Nama Proyek *</label>
                <input type="text" name="name" id="project-name-input" required placeholder="Contoh: Mobile Banking App"
                       class="w-full px-3.5 py-2.5 text-sm bg-slate-800/80 border border-slate-700 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Deskripsi Singkat</label>
                <textarea name="description" rows="2" placeholder="Tujuan atau cakupan proyek..."
                          class="w-full px-3.5 py-2 text-sm bg-slate-800/80 border border-slate-700 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Warna Identitas Proyek</label>
                <div class="flex items-center gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="color" value="#6366f1" checked class="sr-only peer">
                        <span class="w-7 h-7 rounded-full bg-indigo-500 inline-block peer-checked:ring-4 peer-checked:ring-indigo-500/40 peer-checked:scale-110 transition-all"></span>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="color" value="#10b981" class="sr-only peer">
                        <span class="w-7 h-7 rounded-full bg-emerald-500 inline-block peer-checked:ring-4 peer-checked:ring-emerald-500/40 peer-checked:scale-110 transition-all"></span>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="color" value="#f59e0b" class="sr-only peer">
                        <span class="w-7 h-7 rounded-full bg-amber-500 inline-block peer-checked:ring-4 peer-checked:ring-amber-500/40 peer-checked:scale-110 transition-all"></span>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="color" value="#ec4899" class="sr-only peer">
                        <span class="w-7 h-7 rounded-full bg-pink-500 inline-block peer-checked:ring-4 peer-checked:ring-pink-500/40 peer-checked:scale-110 transition-all"></span>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="color" value="#06b6d4" class="sr-only peer">
                        <span class="w-7 h-7 rounded-full bg-cyan-500 inline-block peer-checked:ring-4 peer-checked:ring-cyan-500/40 peer-checked:scale-110 transition-all"></span>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="color" value="#8b5cf6" class="sr-only peer">
                        <span class="w-7 h-7 rounded-full bg-purple-500 inline-block peer-checked:ring-4 peer-checked:ring-purple-500/40 peer-checked:scale-110 transition-all"></span>
                    </label>
                </div>
            </div>

            <div class="pt-3 flex justify-end gap-3">
                <button type="button" onclick="window.kanban.closeModal(document.getElementById('modal-new-project'))"
                        class="px-4 py-2.5 text-sm font-semibold rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors">
                    Batal
                </button>
                <button type="submit" 
                        class="px-5 py-2.5 text-sm font-semibold rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white shadow-lg shadow-cyan-600/30 transition-all">
                    Buat Proyek
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: View Task Details -->
<div id="modal-view-task" class="modal-container fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl relative">
        <button onclick="window.kanban.closeModal(document.getElementById('modal-view-task'))" class="absolute top-5 right-5 text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <div id="view-task-content"></div>
    </div>
</div>

<!-- Modal: Analytics / Metrics Drawer -->
<div id="modal-analytics" class="modal-container fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl relative">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="text-lg font-bold text-white font-heading flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                Ringkasan Kinerja &amp; Statistik
            </h3>
            <button id="btn-close-analytics" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div id="analytics-content" class="mt-4"></div>
    </div>
</div>
@endsection
