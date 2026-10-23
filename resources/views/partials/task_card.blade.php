@php
    $priorityConfig = [
        'urgent' => [
            'class' => 'bg-rose-500/15 text-rose-300 border-rose-500/30',
            'dot' => 'bg-rose-500 pulse-urgent',
            'label' => 'Urgent'
        ],
        'high' => [
            'class' => 'bg-amber-500/15 text-amber-300 border-amber-500/30',
            'dot' => 'bg-amber-400',
            'label' => 'High'
        ],
        'medium' => [
            'class' => 'bg-sky-500/15 text-sky-300 border-sky-500/30',
            'dot' => 'bg-sky-400',
            'label' => 'Medium'
        ],
        'low' => [
            'class' => 'bg-slate-500/15 text-slate-300 border-slate-500/30',
            'dot' => 'bg-slate-400',
            'label' => 'Low'
        ]
    ];
    $pCfg = $priorityConfig[$task->priority] ?? $priorityConfig['medium'];
@endphp

<div class="task-card group glass-panel p-4 rounded-xl border border-slate-800/80 hover:border-indigo-500/50 hover:shadow-lg hover:shadow-indigo-500/10 cursor-grab active:cursor-grabbing transition-all duration-200"
     draggable="true"
     data-task-id="{{ $task->id }}"
     data-status="{{ $task->status }}"
     data-priority="{{ $task->priority }}">

    <!-- Header: Project & Priority Badge -->
    <div class="flex items-center justify-between gap-2 mb-2.5">
        <div class="flex items-center gap-1.5 min-w-0">
            <span class="w-2 h-2 rounded-full flex-shrink-0" style="background-color: {{ $task->project->color ?? '#6366f1' }}"></span>
            <span class="task-project text-[11px] font-semibold text-slate-400 truncate max-w-[120px]">
                {{ $task->project->name ?? 'Proyek' }}
            </span>
        </div>

        <span class="px-2 py-0.5 text-[10px] font-semibold tracking-wide uppercase rounded-md border flex items-center gap-1 {{ $pCfg['class'] }}">
            <span class="w-1.5 h-1.5 rounded-full {{ $pCfg['dot'] }}"></span>
            <span>{{ $pCfg['label'] }}</span>
        </span>
    </div>

    <!-- Task Title -->
    <h3 class="task-title text-sm font-semibold text-slate-100 group-hover:text-indigo-300 transition-colors line-clamp-2 leading-snug mb-1.5">
        {{ $task->title }}
    </h3>

    <!-- Task Description Preview (if any) -->
    @if($task->description)
        <p class="task-desc text-xs text-slate-400 line-clamp-2 leading-relaxed mb-3">
            {{ $task->description }}
        </p>
    @endif

    <!-- Footer: Due Date & Action Buttons -->
    <div class="flex items-center justify-between pt-2 mt-2 border-t border-slate-800/60 text-xs">
        <!-- Due Date -->
        <div class="flex items-center gap-1 text-[11px] text-slate-400">
            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <span>
                {{ $task->due_date ? $task->due_date->format('d M') : 'Tanpa tenggat' }}
            </span>
        </div>

        <!-- Quick Actions (Does not trigger card view) -->
        <div class="flex items-center gap-1 opacity-80 group-hover:opacity-100 transition-opacity">
            <!-- Quick Toggle Status (Done / Todo) -->
            <button type="button" 
                    title="{{ $task->status === 'done' ? 'Tandai belum selesai' : 'Tandai selesai' }}" 
                    class="action-btn btn-toggle-complete p-1 rounded-md hover:bg-slate-800 text-slate-400 hover:text-emerald-400 transition-colors">
                <svg class="w-4 h-4 {{ $task->status === 'done' ? 'text-emerald-400' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 13l4 4L19 7"></path>
                </svg>
            </button>

            <!-- Delete Button -->
            <button type="button" 
                    title="Hapus tugas" 
                    class="action-btn btn-delete-task p-1 rounded-md hover:bg-slate-800 text-slate-400 hover:text-rose-400 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </button>
        </div>
    </div>
</div>
