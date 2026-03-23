<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Task Tracker</title>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen" x-data="taskApp()" x-init="loadTasks()">

<div class="max-w-4xl mx-auto py-10 px-4">

    <h1 class="text-3xl font-bold text-gray-800 mb-8">Task Tracker</h1>

    <!-- Error banner -->
    <div x-show="error" x-transition class="mb-4 bg-red-100 border border-red-300 text-red-700 rounded px-4 py-3 text-sm" x-text="error"></div>

    <!-- Filters -->
    <div class="flex flex-wrap gap-3 mb-6">
        <select x-model="filterStatus" @change="loadTasks()" class="border border-gray-300 rounded px-3 py-2 text-sm bg-white">
            <option value="">Vsi statusi</option>
            <option value="todo">Todo</option>
            <option value="in_progress">In Progress</option>
            <option value="done">Done</option>
        </select>
        <select x-model="filterPriority" @change="loadTasks()" class="border border-gray-300 rounded px-3 py-2 text-sm bg-white">
            <option value="">Vse prioritete</option>
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
        </select>
        <button @click="openCreate()" class="ml-auto bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded">
            + Nov task
        </button>
    </div>

    <!-- Task list -->
    <div class="space-y-3">
        <template x-if="tasks.length === 0">
            <p class="text-gray-500 text-sm text-center py-10">Ni taskov.</p>
        </template>
        <template x-for="task in tasks" :key="task.id">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 flex items-start gap-4">
                <!-- Priority dot -->
                <span class="mt-1 w-3 h-3 rounded-full flex-shrink-0"
                    :class="{
                        'bg-red-500': task.priority === 'high',
                        'bg-yellow-400': task.priority === 'medium',
                        'bg-green-400': task.priority === 'low'
                    }">
                </span>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-semibold text-gray-800 truncate" x-text="task.title"></span>
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                            :class="{
                                'bg-gray-100 text-gray-600': task.status === 'todo',
                                'bg-blue-100 text-blue-700': task.status === 'in_progress',
                                'bg-green-100 text-green-700': task.status === 'done'
                            }"
                            x-text="statusLabel(task.status)">
                        </span>
                    </div>
                    <p x-show="task.description" class="text-sm text-gray-500 mt-1 truncate" x-text="task.description"></p>
                    <p x-show="task.due_date" class="text-xs text-gray-400 mt-1">Rok: <span x-text="task.due_date"></span></p>
                </div>

                <div class="flex gap-2 flex-shrink-0">
                    <button @click="openEdit(task)" class="text-sm text-blue-600 hover:underline">Uredi</button>
                    <button @click="deleteTask(task.id)" class="text-sm text-red-500 hover:underline">Izbriši</button>
                </div>
            </div>
        </template>
    </div>
</div>

<!-- Modal -->
<div x-show="showModal" x-transition.opacity class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
    <div @click.outside="showModal = false" class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4" x-text="editingTask ? 'Uredi task' : 'Nov task'"></h2>

        <div class="space-y-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Naslov *</label>
                <input x-model="form.title" type="text" maxlength="255"
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :class="{'border-red-400': formErrors.title}"
                    placeholder="Naslov taska">
                <p x-show="formErrors.title" class="text-red-500 text-xs mt-1" x-text="formErrors.title?.[0]"></p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Opis</label>
                <textarea x-model="form.description" rows="2"
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Neobvezen opis"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select x-model="form.status" class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                        :class="{'border-red-400': formErrors.status}">
                        <option value="todo">Todo</option>
                        <option value="in_progress">In Progress</option>
                        <option value="done">Done</option>
                    </select>
                    <p x-show="formErrors.status" class="text-red-500 text-xs mt-1" x-text="formErrors.status?.[0]"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Prioriteta</label>
                    <select x-model="form.priority" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Rok (due date)</label>
                <input x-model="form.due_date" type="date"
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :class="{'border-red-400': formErrors.due_date}">
                <p x-show="formErrors.due_date" class="text-red-500 text-xs mt-1" x-text="formErrors.due_date?.[0]"></p>
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-6">
            <button @click="showModal = false" class="text-sm text-gray-600 hover:underline px-3 py-2">Prekliči</button>
            <button @click="saveTask()" :disabled="saving"
                class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-sm font-medium px-5 py-2 rounded">
                <span x-text="saving ? 'Shranjujem...' : 'Shrani'"></span>
            </button>
        </div>
    </div>
</div>

<script>
function taskApp() {
    return {
        tasks: [],
        filterStatus: '',
        filterPriority: '',
        showModal: false,
        editingTask: null,
        saving: false,
        error: '',
        form: { title: '', description: '', status: 'todo', priority: 'medium', due_date: '' },
        formErrors: {},

        async loadTasks() {
            this.error = '';
            const params = new URLSearchParams();
            if (this.filterStatus)   params.set('status',   this.filterStatus);
            if (this.filterPriority) params.set('priority', this.filterPriority);
            try {
                const res = await fetch(`/api/tasks?${params}`, { headers: { Accept: 'application/json' } });
                this.tasks = await res.json();
            } catch {
                this.error = 'Napaka pri nalaganju taskov.';
            }
        },

        openCreate() {
            this.editingTask = null;
            this.form = { title: '', description: '', status: 'todo', priority: 'medium', due_date: '' };
            this.formErrors = {};
            this.showModal = true;
        },

        openEdit(task) {
            this.editingTask = task;
            this.form = {
                title:       task.title,
                description: task.description ?? '',
                status:      task.status,
                priority:    task.priority,
                due_date:    task.due_date ?? '',
            };
            this.formErrors = {};
            this.showModal = true;
        },

        async saveTask() {
            this.saving = true;
            this.formErrors = {};
            this.error = '';

            const url    = this.editingTask ? `/api/tasks/${this.editingTask.id}` : '/api/tasks';
            const method = this.editingTask ? 'PUT' : 'POST';
            const body   = { ...this.form };
            if (!body.due_date) delete body.due_date;
            if (!body.description) delete body.description;

            try {
                const res = await fetch(url, {
                    method,
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                    body: JSON.stringify(body),
                });
                const data = await res.json();

                if (res.status === 422) {
                    this.formErrors = data.errors ?? {};
                } else if (res.ok) {
                    this.showModal = false;
                    await this.loadTasks();
                } else {
                    this.error = data.message ?? 'Prišlo je do napake.';
                }
            } catch {
                this.error = 'Napaka pri shranjevanju.';
            } finally {
                this.saving = false;
            }
        },

        async deleteTask(id) {
            if (!confirm('Izbriši task?')) return;
            this.error = '';
            try {
                const res = await fetch(`/api/tasks/${id}`, {
                    method: 'DELETE',
                    headers: { Accept: 'application/json' },
                });
                if (res.ok) {
                    await this.loadTasks();
                } else {
                    this.error = 'Napaka pri brisanju.';
                }
            } catch {
                this.error = 'Napaka pri brisanju.';
            }
        },

        statusLabel(status) {
            return { todo: 'Todo', in_progress: 'In Progress', done: 'Done' }[status] ?? status;
        },
    };
}
</script>
</body>
</html>
