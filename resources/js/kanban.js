/**
 * Andromeda Flow — Modern Kanban & Task Management JavaScript Engine
 */

class KanbanApp {
    constructor() {
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        this.draggedCard = null;
        this.placeholder = null;
        this.sourceColumn = null;
        this.activeFilter = 'all';
        this.searchQuery = '';

        this.init();
    }

    init() {
        this.initTheme();
        this.initDragAndDrop();
        this.initSearch();
        this.initPriorityFilter();
        this.initModals();
        this.initKeyboardShortcuts();
        this.initQuickStatusToggles();
        this.initAnalyticsModal();
    }

    /* -------------------------------------------------------------
       1. THEME CONTROLLER
    -------------------------------------------------------------- */
    initTheme() {
        const toggleBtn = document.getElementById('theme-toggle');
        const iconDark = document.getElementById('theme-icon-dark');
        const iconLight = document.getElementById('theme-icon-light');

        const savedTheme = localStorage.getItem('andromeda-theme') || 'dark';
        this.applyTheme(savedTheme);

        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                const current = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
                const next = current === 'dark' ? 'light' : 'dark';
                this.applyTheme(next);
            });
        }
    }

    applyTheme(theme) {
        const iconDark = document.getElementById('theme-icon-dark');
        const iconLight = document.getElementById('theme-icon-light');

        if (theme === 'light') {
            document.documentElement.classList.remove('dark');
            document.documentElement.classList.add('light');
            iconDark?.classList.remove('hidden');
            iconLight?.classList.add('hidden');
        } else {
            document.documentElement.classList.remove('light');
            document.documentElement.classList.add('dark');
            iconDark?.classList.add('hidden');
            iconLight?.classList.remove('hidden');
        }
        localStorage.setItem('andromeda-theme', theme);
    }

    /* -------------------------------------------------------------
       2. DRAG AND DROP ENGINE (HTML5 Native Drag & Drop API)
    -------------------------------------------------------------- */
    initDragAndDrop() {
        // Create reusable placeholder
        this.placeholder = document.createElement('div');
        this.placeholder.className = 'drag-placeholder';

        // Bind existing cards
        this.bindCardEvents();

        // Bind dropzones
        const dropzones = document.querySelectorAll('.kanban-dropzone');
        dropzones.forEach(zone => {
            zone.addEventListener('dragover', (e) => this.onDragOver(e, zone));
            zone.addEventListener('dragenter', (e) => this.onDragEnter(e, zone));
            zone.addEventListener('dragleave', (e) => this.onDragLeave(e, zone));
            zone.addEventListener('drop', (e) => this.onDrop(e, zone));
        });
    }

    bindCardEvents(container = document) {
        const cards = container.querySelectorAll('.task-card');
        cards.forEach(card => {
            if (card.dataset.bound) return;
            card.dataset.bound = 'true';

            card.addEventListener('dragstart', (e) => this.onDragStart(e, card));
            card.addEventListener('dragend', (e) => this.onDragEnd(e, card));

            // Card click to inspect/view
            card.addEventListener('click', (e) => {
                // If clicked on quick buttons (edit, delete, toggle), ignore card view
                if (e.target.closest('.action-btn')) return;
                const taskId = card.dataset.taskId;
                if (taskId) this.openViewModal(taskId);
            });
        });
    }

    onDragStart(e, card) {
        this.draggedCard = card;
        this.sourceColumn = card.closest('.kanban-dropzone');

        card.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', card.dataset.taskId);

        // Slight timeout so card isn't instantly hidden while drag image generates
        setTimeout(() => {
            if (this.draggedCard) {
                this.draggedCard.style.display = 'none';
                if (this.draggedCard.parentNode) {
                    this.draggedCard.parentNode.insertBefore(this.placeholder, this.draggedCard);
                }
            }
        }, 0);
    }

    onDragEnd(e, card) {
        card.classList.remove('dragging');
        card.style.display = 'block';

        if (this.placeholder && this.placeholder.parentNode) {
            this.placeholder.parentNode.replaceChild(card, this.placeholder);
        }

        document.querySelectorAll('.kanban-dropzone').forEach(zone => {
            zone.classList.remove('drag-over');
        });

        this.draggedCard = null;
        this.sourceColumn = null;
    }

    onDragEnter(e, zone) {
        e.preventDefault();
        zone.classList.add('drag-over');
    }

    onDragLeave(e, zone) {
        // Only remove if leaving the dropzone itself, not a child
        if (!zone.contains(e.relatedTarget)) {
            zone.classList.remove('drag-over');
        }
    }

    onDragOver(e, zone) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';

        const afterElement = this.getDragAfterElement(zone, e.clientY);
        if (afterElement == null) {
            zone.appendChild(this.placeholder);
        } else {
            zone.insertBefore(this.placeholder, afterElement);
        }
    }

    getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('.task-card:not(.dragging)')];

        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    async onDrop(e, zone) {
        e.preventDefault();
        zone.classList.remove('drag-over');

        const taskId = e.dataTransfer.getData('text/plain') || this.draggedCard?.dataset.taskId;
        if (!taskId || !this.draggedCard) return;

        const newStatus = zone.dataset.status;
        const oldStatus = this.draggedCard.dataset.status;

        // Place card into drop position
        if (this.placeholder && this.placeholder.parentNode) {
            this.placeholder.parentNode.replaceChild(this.draggedCard, this.placeholder);
        }
        this.draggedCard.style.display = 'block';
        this.draggedCard.dataset.status = newStatus;

        // Update column badge counters in DOM
        this.updateColumnCounters();

        // Trigger confetti celebration if moved to "done"
        if (newStatus === 'done' && oldStatus !== 'done') {
            this.triggerConfetti();
        }

        // Call backend API to sync state
        try {
            const response = await fetch(`/tasks/${taskId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ status: newStatus })
            });

            const result = await response.json();
            if (result.status === 'success') {
                const statusLabels = {
                    'todo': 'To Do',
                    'in_progress': 'In Progress',
                    'review': 'Review',
                    'done': 'Done'
                };
                this.showToast(`Status tugas dipindahkan ke "${statusLabels[newStatus] || newStatus}"`, 'success');
            } else {
                throw new Error(result.message || 'Gagal mengubah status');
            }
        } catch (err) {
            console.error('Drag drop sync error:', err);
            this.showToast('Gagal menyinkronkan status dengan server.', 'error');
            // Revert back if failed
            if (this.sourceColumn) {
                this.sourceColumn.appendChild(this.draggedCard);
                this.draggedCard.dataset.status = oldStatus;
                this.updateColumnCounters();
            }
        }
    }

    updateColumnCounters() {
        const statuses = ['todo', 'in_progress', 'review', 'done'];
        let total = 0;
        let doneCount = 0;

        statuses.forEach(status => {
            const zone = document.querySelector(`.kanban-dropzone[data-status="${status}"]`);
            if (zone) {
                const count = zone.querySelectorAll('.task-card').length;
                const counterBadge = document.getElementById(`count-${status}`);
                if (counterBadge) counterBadge.textContent = count;
                total += count;
                if (status === 'done') doneCount = count;

                // Toggle empty state placeholder
                const emptyNotice = zone.querySelector('.column-empty-state');
                if (emptyNotice) {
                    emptyNotice.style.display = count === 0 ? 'flex' : 'none';
                }
            }
        });

        // Update global header stats
        const statTotal = document.getElementById('stat-total-tasks');
        const statRate = document.getElementById('stat-completion-rate');
        const statRateBar = document.getElementById('stat-completion-bar');

        if (statTotal) statTotal.textContent = total;
        if (statRate && total > 0) {
            const percentage = Math.round((doneCount / total) * 100);
            statRate.textContent = `${percentage}%`;
            if (statRateBar) statRateBar.style.width = `${percentage}%`;
        }
    }

    /* -------------------------------------------------------------
       3. QUICK STATUS TOGGLE (Checkmark)
    -------------------------------------------------------------- */
    initQuickStatusToggles() {
        document.addEventListener('click', async (e) => {
            const toggleBtn = e.target.closest('.btn-toggle-complete');
            if (!toggleBtn) return;

            e.stopPropagation();
            const card = toggleBtn.closest('.task-card');
            if (!card) return;

            const taskId = card.dataset.taskId;
            const currentStatus = card.dataset.status;
            const newStatus = currentStatus === 'done' ? 'todo' : 'done';

            const targetZone = document.querySelector(`.kanban-dropzone[data-status="${newStatus}"]`);
            if (!targetZone) return;

            // Animate movement
            card.style.opacity = '0.5';
            targetZone.prepend(card);
            card.dataset.status = newStatus;
            this.updateColumnCounters();

            if (newStatus === 'done') {
                this.triggerConfetti();
                this.showToast('Tugas ditandai selesai! 🎉', 'success');
            } else {
                this.showToast('Tugas dipindahkan kembali ke To Do', 'info');
            }

            try {
                const res = await fetch(`/tasks/${taskId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ status: newStatus })
                });
                const data = await res.json();
                card.style.opacity = '1';
            } catch (err) {
                console.error('Toggle error:', err);
                this.showToast('Gagal memperbarui status tugas.', 'error');
            }
        });
    }

    /* -------------------------------------------------------------
       4. INSTANT SEARCH & FILTER
    -------------------------------------------------------------- */
    initSearch() {
        const searchInput = document.getElementById('global-search');
        const mobileSearch = document.getElementById('mobile-search');
        const clearBtn = document.getElementById('clear-search');

        const handleInput = (val) => {
            this.searchQuery = val.trim().toLowerCase();
            if (clearBtn) {
                clearBtn.classList.toggle('hidden', this.searchQuery === '');
            }
            this.applyFilters();
        };

        searchInput?.addEventListener('input', (e) => handleInput(e.target.value));
        mobileSearch?.addEventListener('input', (e) => handleInput(e.target.value));

        clearBtn?.addEventListener('click', () => {
            if (searchInput) searchInput.value = '';
            if (mobileSearch) mobileSearch.value = '';
            handleInput('');
            searchInput?.focus();
        });
    }

    initPriorityFilter() {
        const filterBtns = document.querySelectorAll('.priority-filter-chip');
        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                filterBtns.forEach(b => {
                    b.classList.remove('bg-indigo-600', 'text-white', 'shadow-indigo-500/20');
                    b.classList.add('bg-slate-800/80', 'text-slate-400');
                });

                btn.classList.add('bg-indigo-600', 'text-white', 'shadow-indigo-500/20');
                btn.classList.remove('bg-slate-800/80', 'text-slate-400');

                this.activeFilter = btn.dataset.priority || 'all';
                this.applyFilters();
            });
        });
    }

    applyFilters() {
        const cards = document.querySelectorAll('.task-card');
        cards.forEach(card => {
            const title = (card.querySelector('.task-title')?.textContent || '').toLowerCase();
            const desc = (card.querySelector('.task-desc')?.textContent || '').toLowerCase();
            const project = (card.querySelector('.task-project')?.textContent || '').toLowerCase();
            const priority = (card.dataset.priority || '').toLowerCase();

            const matchesSearch = this.searchQuery === '' ||
                title.includes(this.searchQuery) ||
                desc.includes(this.searchQuery) ||
                project.includes(this.searchQuery);

            const matchesPriority = this.activeFilter === 'all' || priority === this.activeFilter;

            if (matchesSearch && matchesPriority) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });

        this.updateColumnCounters();
    }

    /* -------------------------------------------------------------
       5. MODAL WORKFLOWS (CRUD)
    -------------------------------------------------------------- */
    initModals() {
        // New Task Modal Triggers
        const openTaskModalBtn = document.getElementById('btn-open-new-task-modal');
        const taskModal = document.getElementById('modal-new-task');
        const closeTaskModalBtn = document.getElementById('btn-close-task-modal');
        const formNewTask = document.getElementById('form-new-task');

        openTaskModalBtn?.addEventListener('click', () => {
            this.openModal(taskModal);
            document.getElementById('task-title-input')?.focus();
        });

        closeTaskModalBtn?.addEventListener('click', () => this.closeModal(taskModal));

        // Submit New Task via AJAX
        formNewTask?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = formNewTask.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="animate-spin inline-block mr-2">⟳</span> Menyimpan...';

            const formData = new FormData(formNewTask);
            const payload = Object.fromEntries(formData.entries());

            try {
                const response = await fetch('/tasks', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload)
                });

                const res = await response.json();
                if (res.status === 'success') {
                    this.showToast('Tugas baru berhasil ditambahkan! 🚀', 'success');
                    this.closeModal(taskModal);
                    formNewTask.reset();
                    // Reload page or append task smoothly
                    window.location.reload();
                } else {
                    throw new Error(res.message || 'Gagal menyimpan tugas.');
                }
            } catch (err) {
                console.error('Create task error:', err);
                this.showToast(err.message || 'Terjadi kesalahan saat menyimpan tugas.', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span>Simpan Tugas</span>';
            }
        });

        // New Project Modal Triggers
        const openProjectModalBtn = document.getElementById('btn-open-new-project-modal');
        const projectModal = document.getElementById('modal-new-project');
        const closeProjectModalBtn = document.getElementById('btn-close-project-modal');
        const formNewProject = document.getElementById('form-new-project');

        openProjectModalBtn?.addEventListener('click', () => {
            this.openModal(projectModal);
            document.getElementById('project-name-input')?.focus();
        });

        closeProjectModalBtn?.addEventListener('click', () => this.closeModal(projectModal));

        formNewProject?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = formNewProject.querySelector('button[type="submit"]');
            submitBtn.disabled = true;

            const formData = new FormData(formNewProject);
            const payload = Object.fromEntries(formData.entries());

            try {
                const res = await fetch('/projects', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.status === 'success') {
                    this.showToast('Proyek baru berhasil dibuat!', 'success');
                    window.location.reload();
                }
            } catch (err) {
                console.error(err);
                this.showToast('Gagal membuat proyek baru.', 'error');
            } finally {
                submitBtn.disabled = false;
            }
        });

        // Delete Task Delegate
        document.addEventListener('click', async (e) => {
            const delBtn = e.target.closest('.btn-delete-task');
            if (!delBtn) return;

            e.stopPropagation();
            const card = delBtn.closest('.task-card');
            const taskId = card?.dataset.taskId;
            const taskTitle = card?.querySelector('.task-title')?.textContent || 'Tugas';

            if (!confirm(`Apakah Anda yakin ingin menghapus "${taskTitle}"?`)) return;

            try {
                const res = await fetch(`/tasks/${taskId}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await res.json();
                if (data.status === 'success') {
                    card.style.transform = 'scale(0.8)';
                    card.style.opacity = '0';
                    setTimeout(() => {
                        card.remove();
                        this.updateColumnCounters();
                    }, 250);
                    this.showToast('Tugas berhasil dihapus.', 'info');
                }
            } catch (err) {
                console.error('Delete error:', err);
                this.showToast('Gagal menghapus tugas.', 'error');
            }
        });

        // Close modal on backdrop click or Escape
        document.querySelectorAll('.modal-container').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) this.closeModal(modal);
            });
        });
    }

    openModal(modal) {
        if (!modal) return;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    closeModal(modal) {
        if (!modal) return;
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    async openViewModal(taskId) {
        const modal = document.getElementById('modal-view-task');
        if (!modal) return;

        this.openModal(modal);

        const container = document.getElementById('view-task-content');
        container.innerHTML = `
            <div class="py-12 flex flex-col items-center justify-center text-slate-400">
                <div class="w-8 h-8 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin mb-3"></div>
                <p class="text-sm">Memuat rincian tugas...</p>
            </div>
        `;

        try {
            const res = await fetch(`/tasks/${taskId}`, {
                headers: { 'Accept': 'application/json' }
            });
            const { data } = await res.json();

            const priorityColors = {
                urgent: 'bg-rose-500/10 text-rose-400 border-rose-500/30',
                high: 'bg-amber-500/10 text-amber-400 border-amber-500/30',
                medium: 'bg-sky-500/10 text-sky-400 border-sky-500/30',
                low: 'bg-slate-500/10 text-slate-400 border-slate-500/30'
            };

            const statusBadges = {
                todo: 'To Do',
                in_progress: 'In Progress',
                review: 'Review',
                done: 'Completed'
            };

            let activitiesHtml = '';
            if (data.activities && data.activities.length > 0) {
                activitiesHtml = data.activities.map(act => `
                    <div class="flex items-start gap-3 text-xs border-l-2 border-slate-700 pl-3 py-1">
                        <div class="w-2 h-2 rounded-full bg-indigo-400 -ml-[17px] mt-1"></div>
                        <div class="flex-1">
                            <p class="text-slate-200 font-medium">${act.description}</p>
                            <span class="text-[10px] text-slate-500">${new Date(act.created_at).toLocaleString('id-ID')}</span>
                        </div>
                    </div>
                `).join('');
            } else {
                activitiesHtml = '<p class="text-xs text-slate-500 italic">Belum ada riwayat aktivitas.</p>';
            }

            container.innerHTML = `
                <div class="space-y-5">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800 pb-4">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-lg border ${priorityColors[data.priority] || priorityColors.medium}">
                                ${data.priority.toUpperCase()}
                            </span>
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                ${statusBadges[data.status] || data.status}
                            </span>
                        </div>
                        <span class="text-xs text-slate-400 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Tenggat: ${data.due_date ? new Date(data.due_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : 'Tidak ada tenggat'}
                        </span>
                    </div>

                    <div>
                        <div class="text-xs text-indigo-400 font-semibold mb-1 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full" style="background-color: ${data.project?.color || '#6366f1'}"></span>
                            ${data.project?.name || 'Tanpa Proyek'}
                        </div>
                        <h2 class="text-xl font-bold text-slate-100 font-heading">${data.title}</h2>
                    </div>

                    <div class="bg-slate-900/60 rounded-xl p-4 border border-slate-800">
                        <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-2">Deskripsi Tugas</label>
                        <p class="text-sm text-slate-300 leading-relaxed whitespace-pre-wrap">${data.description || 'Tidak ada rincian tambahan untuk tugas ini.'}</p>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-3">Linimasa Aktivitas</label>
                        <div class="space-y-3 max-h-48 overflow-y-auto pr-1">
                            ${activitiesHtml}
                        </div>
                    </div>
                </div>
            `;
        } catch (err) {
            container.innerHTML = `
                <div class="p-6 text-center text-rose-400 text-sm">
                    Gagal memuat rincian tugas. Silakan coba kembali.
                </div>
            `;
        }
    }

    /* -------------------------------------------------------------
       6. ANALYTICS DRAWER & STATS
    -------------------------------------------------------------- */
    initAnalyticsModal() {
        const btnOpen = document.getElementById('btn-open-analytics');
        const modal = document.getElementById('modal-analytics');
        const btnClose = document.getElementById('btn-close-analytics');

        btnOpen?.addEventListener('click', async () => {
            this.openModal(modal);
            const content = document.getElementById('analytics-content');
            content.innerHTML = `
                <div class="py-12 flex flex-col items-center justify-center text-slate-400">
                    <div class="w-8 h-8 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin mb-3"></div>
                    <p class="text-sm">Menghitung metrik performa...</p>
                </div>
            `;

            try {
                const res = await fetch('/api/stats');
                const { data } = await res.json();

                const total = data.total || 1;
                const donePercent = Math.round(((data.by_status.done || 0) / total) * 100);

                let projectsHtml = data.projects.map(p => `
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-medium text-slate-200 flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full" style="background-color: ${p.color}"></span>
                                ${p.name}
                            </span>
                            <span class="text-slate-400">${p.total} tugas (${p.completion}%)</span>
                        </div>
                        <div class="w-full h-2 rounded-full bg-slate-800 overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500" style="width: ${p.completion}%; background-color: ${p.color}"></div>
                        </div>
                    </div>
                `).join('');

                content.innerHTML = `
                    <div class="space-y-6">
                        <!-- Progress Gauge -->
                        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 flex items-center justify-between">
                            <div>
                                <span class="text-xs uppercase tracking-wider text-slate-400 font-semibold block">Tingkat Penyelesaian</span>
                                <h3 class="text-3xl font-extrabold text-white mt-1">${donePercent}%</h3>
                                <p class="text-xs text-slate-400 mt-0.5">${data.by_status.done} dari ${data.total} tugas selesai</p>
                            </div>
                            <div class="relative w-20 h-20">
                                <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                                    <path class="text-slate-800" stroke-width="3.5" stroke="currentColor" fill="none"
                                          d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                    <path class="text-indigo-500 transition-all duration-1000 ease-out" stroke-dasharray="${donePercent}, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none"
                                          d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center text-xs font-bold text-indigo-400">
                                    ${donePercent}%
                                </div>
                            </div>
                        </div>

                        <!-- Status Distribution -->
                        <div>
                            <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">Distribusi Status</h4>
                            <div class="grid grid-cols-2 gap-2.5">
                                <div class="bg-slate-900/40 border border-slate-800/80 rounded-xl p-3">
                                    <span class="text-xs text-slate-400">To Do</span>
                                    <p class="text-lg font-bold text-slate-200 mt-0.5">${data.by_status.todo}</p>
                                </div>
                                <div class="bg-indigo-950/20 border border-indigo-500/20 rounded-xl p-3">
                                    <span class="text-xs text-indigo-400">In Progress</span>
                                    <p class="text-lg font-bold text-indigo-300 mt-0.5">${data.by_status.in_progress}</p>
                                </div>
                                <div class="bg-amber-950/20 border border-amber-500/20 rounded-xl p-3">
                                    <span class="text-xs text-amber-400">Review</span>
                                    <p class="text-lg font-bold text-amber-300 mt-0.5">${data.by_status.review}</p>
                                </div>
                                <div class="bg-emerald-950/20 border border-emerald-500/20 rounded-xl p-3">
                                    <span class="text-xs text-emerald-400">Completed</span>
                                    <p class="text-lg font-bold text-emerald-300 mt-0.5">${data.by_status.done}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Project Progress -->
                        <div>
                            <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">Kemajuan Berdasarkan Proyek</h4>
                            <div class="space-y-3">
                                ${projectsHtml}
                            </div>
                        </div>
                    </div>
                `;
            } catch (err) {
                content.innerHTML = '<p class="text-sm text-rose-400 text-center py-6">Gagal memuat statistik.</p>';
            }
        });

        btnClose?.addEventListener('click', () => this.closeModal(modal));
    }

    /* -------------------------------------------------------------
       7. KEYBOARD SHORTCUTS
    -------------------------------------------------------------- */
    initKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Hotkey '/' to focus search
            if (e.key === '/' && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
                e.preventDefault();
                const search = document.getElementById('global-search');
                search?.focus();
            }

            // 'Escape' to close all open modals
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-container:not(.hidden)').forEach(modal => {
                    this.closeModal(modal);
                });
            }
        });
    }

    /* -------------------------------------------------------------
       8. TOAST NOTIFICATIONS
    -------------------------------------------------------------- */
    showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = 'pointer-events-auto transform transition-all duration-300 ease-out translate-y-3 opacity-0';

        const bgTypes = {
            success: 'bg-slate-900 border-emerald-500/40 text-emerald-300',
            error: 'bg-slate-900 border-rose-500/40 text-rose-300',
            info: 'bg-slate-900 border-indigo-500/40 text-indigo-300'
        };

        const iconTypes = {
            success: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>',
            error: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>',
            info: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>'
        };

        toast.innerHTML = `
            <div class="flex items-center gap-3 p-3.5 rounded-xl border shadow-2xl backdrop-blur-xl ${bgTypes[type] || bgTypes.success}">
                <div class="flex-shrink-0 w-5 h-5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        ${iconTypes[type] || iconTypes.success}
                    </svg>
                </div>
                <div class="text-xs font-medium text-slate-200 flex-1 leading-snug">
                    ${message}
                </div>
                <button class="text-slate-400 hover:text-slate-200" onclick="this.closest('.transform').remove()">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `;

        container.appendChild(toast);

        // Slide in
        setTimeout(() => {
            toast.classList.remove('translate-y-3', 'opacity-0');
        }, 10);

        // Auto remove after 3.8s
        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-y-2');
            setTimeout(() => toast.remove(), 300);
        }, 3800);
    }

    /* -------------------------------------------------------------
       9. LIGHTWEIGHT CANVAS CONFETTI
    -------------------------------------------------------------- */
    triggerConfetti() {
        const canvas = document.getElementById('confetti-canvas');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        const particles = [];
        const colors = ['#6366f1', '#a855f7', '#ec4899', '#3b82f6', '#10b981', '#f59e0b'];

        for (let i = 0; i < 90; i++) {
            particles.push({
                x: canvas.width / 2 + (Math.random() - 0.5) * 350,
                y: canvas.height / 3 + (Math.random() - 0.5) * 100,
                radius: Math.random() * 5 + 3,
                color: colors[Math.floor(Math.random() * colors.length)],
                vx: (Math.random() - 0.5) * 10,
                vy: Math.random() * -8 - 4,
                gravity: 0.28,
                rotation: Math.random() * 360,
                rotSpeed: (Math.random() - 0.5) * 10,
                opacity: 1
            });
        }

        let animationFrame;
        const render = () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            let active = false;

            particles.forEach(p => {
                p.x += p.vx;
                p.y += p.vy;
                p.vy += p.gravity;
                p.rotation += p.rotSpeed;
                p.opacity -= 0.012;

                if (p.opacity > 0) {
                    active = true;
                    ctx.save();
                    ctx.translate(p.x, p.y);
                    ctx.rotate((p.rotation * Math.PI) / 180);
                    ctx.fillStyle = p.color;
                    ctx.globalAlpha = Math.max(p.opacity, 0);
                    ctx.fillRect(-p.radius, -p.radius, p.radius * 2, p.radius * 1.6);
                    ctx.restore();
                }
            });

            if (active) {
                animationFrame = requestAnimationFrame(render);
            } else {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                cancelAnimationFrame(animationFrame);
            }
        };

        render();
    }
}

// Auto instantiate on DOM load only if kanban board exists
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('kanban-board')) {
        window.kanban = new KanbanApp();
    }
});
