@extends('layouts.admin')
@section('title', 'Master User')
@section('page-title', 'Master User')

@section('content')
<div x-data="userPage()" x-init="init()">

    {{-- Toolbar --}}
    <div class="flex flex-wrap gap-3 mb-5">
        <div class="flex-1 min-w-48">
            <input type="text" x-model="search" @input.debounce.400ms="fetchUsers()"
                   placeholder="Cari nama atau username..." class="input-base">
        </div>
        <select x-model="filterRole" @change="fetchUsers()" class="input-base !w-auto">
            <option value="">Semua Role</option>
            <option value="admin">Admin</option>
            <option value="guru">Guru</option>
        </select>
        <button @click="openCreate()" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah User
        </button>
    </div>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <table class="table-base">
            <thead>
                <tr>
                    <th class="w-10">No</th>
                    <th>Nama</th>
                    <th>Username</th>
                    <th class="text-center">Role</th>
                    <th class="text-center">Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(u, i) in users" :key="u.id">
                    <tr>
                        <td class="text-surface-600 dark:text-surface-500 text-center text-xs tabular-nums" x-text="(meta.from || 1) + i"></td>
                        <td>
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold text-white flex-none"
                                     style="background: linear-gradient(135deg, #7c6af6, #a78bfa)"
                                     x-text="u.name.charAt(0).toUpperCase()"></div>
                                <span class="font-medium" x-text="u.name"></span>
                            </div>
                        </td>
                        <td class="font-mono text-surface-500 dark:text-surface-400 text-sm" x-text="u.username"></td>
                        <td class="text-center">
                            <span class="badge"
                                  :class="u.role === 'admin'
                                    ? 'bg-violet-50 text-violet-700 dark:bg-violet-950/50 dark:text-violet-400'
                                    : 'badge-blue'"
                                  x-text="u.role === 'admin' ? 'Admin' : 'Guru'"></span>
                        </td>
                        <td class="text-center">
                            <span class="badge"
                                  :class="u.is_active ? 'badge-green' : 'badge-red'"
                                  x-text="u.is_active ? 'Aktif' : 'Nonaktif'"></span>
                        </td>
                        <td>
                            <div class="flex items-center gap-1.5 justify-end">
                                <button @click="openEdit(u)"
                                        class="text-xs px-3 py-1.5 rounded-lg border border-surface-200 dark:border-surface-700
                                               hover:bg-surface-50 dark:hover:bg-surface-800 transition-colors text-surface-600 dark:text-surface-300">
                                    Edit
                                </button>
                                <button @click="openReset(u)"
                                        class="text-xs px-3 py-1.5 rounded-lg border border-amber-200 dark:border-amber-900
                                               text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/30 transition-colors">
                                    Reset PW
                                </button>
                                <button @click="confirmDelete(u)"
                                        :disabled="u.id === {{ auth()->id() }}"
                                        class="text-xs px-3 py-1.5 rounded-lg border border-red-200 dark:border-red-900
                                               text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors
                                               disabled:opacity-30 disabled:cursor-not-allowed">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>
                <tr x-show="users.length === 0 && !loading">
                    <td colspan="6" class="py-12 text-center">
                        <svg class="w-8 h-8 text-surface-200 dark:text-surface-700 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <p class="text-sm text-surface-400">Tidak ada data user.</p>
                    </td>
                </tr>
                <tr x-show="loading">
                    <td colspan="6" class="py-8 text-center">
                        <div class="flex items-center justify-center gap-2 text-surface-400">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                            </svg>
                            <span class="text-sm">Memuat data...</span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        {{-- Pagination --}}
        <div class="px-5 py-3 border-t border-surface-100 dark:border-surface-800
                    flex items-center justify-between text-sm text-surface-400 dark:text-surface-500"
             x-show="meta.last_page > 1">
            <span x-text="`Menampilkan ${meta.from}–${meta.to} dari ${meta.total} user`"></span>
            <div class="flex gap-1">
                <button @click="goPage(meta.current_page - 1)" :disabled="meta.current_page === 1"
                        class="px-3 py-1 rounded-lg border border-surface-200 dark:border-surface-700
                               disabled:opacity-40 hover:bg-surface-50 dark:hover:bg-surface-800 transition-colors">←</button>
                <button @click="goPage(meta.current_page + 1)" :disabled="meta.current_page === meta.last_page"
                        class="px-3 py-1 rounded-lg border border-surface-200 dark:border-surface-700
                               disabled:opacity-40 hover:bg-surface-50 dark:hover:bg-surface-800 transition-colors">→</button>
            </div>
        </div>
    </div>

    {{-- MODAL: Create / Edit --}}
    <template x-teleport="#modal-root">
    <div x-show="showModal" x-cloak @click.self="closeModal()" class="modal-overlay">
        <div @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="modal-panel max-w-lg">
            <div class="modal-header">
                <h3 x-text="modalTitle"></h3>
                <button @click="closeModal()" class="modal-close">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form @submit.prevent="submitForm()" class="modal-body space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input type="text" x-model="form.name" class="input-base" placeholder="Budi Santoso">
                        <p x-show="errors.name" x-text="errors.name" class="text-xs text-red-500 mt-1"></p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">
                            Username <span class="text-red-500">*</span>
                        </label>
                        <input type="text" x-model="form.username" class="input-base" placeholder="budi.santoso">
                        <p x-show="errors.username" x-text="errors.username" class="text-xs text-red-500 mt-1"></p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">
                            Role <span class="text-red-500">*</span>
                        </label>
                        <select x-model="form.role" class="input-base">
                            <option value="guru">Guru</option>
                            <option value="admin">Admin</option>
                        </select>
                        <p x-show="errors.role" x-text="errors.role" class="text-xs text-red-500 mt-1"></p>
                    </div>
                </div>

                <div x-show="!editMode">
                    <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" x-model="form.password" class="input-base" placeholder="Min. 6 karakter">
                    <p x-show="errors.password" x-text="errors.password" class="text-xs text-red-500 mt-1"></p>
                </div>

                <div class="flex items-center gap-3">
                    <label class="flex items-center gap-2 cursor-pointer select-none text-sm text-surface-700 dark:text-surface-200">
                        <input type="checkbox" x-model="form.is_active" class="accent-primary-600 rounded w-4 h-4">
                        Akun Aktif
                    </label>
                </div>

                <div x-show="formError"
                     class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800
                            rounded-xl px-4 py-3 text-sm text-red-700 dark:text-red-400"
                     x-text="formError"></div>
                <div class="flex gap-3 pt-2">
                    <button type="button" @click="closeModal()" class="btn-ghost flex-1 justify-center">Batal</button>
                    <button type="submit" :disabled="submitting" class="btn-primary flex-1 justify-center disabled:opacity-50">
                        <span x-show="!submitting" x-text="editMode ? 'Simpan Perubahan' : 'Tambah User'"></span>
                        <span x-show="submitting" class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                            </svg>
                            Menyimpan...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    </template>

    {{-- MODAL: Reset Password --}}
    <template x-teleport="#modal-root">
    <div x-show="showReset" x-cloak @click.self="showReset = false" class="modal-overlay">
        <div @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="modal-panel max-w-sm">
            <div class="modal-header">
                <h3>Reset Password</h3>
                <button @click="showReset = false" class="modal-close">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form @submit.prevent="submitReset()" class="modal-body space-y-4">
                <p class="text-sm text-surface-500 dark:text-surface-400">
                    Reset password untuk <strong class="text-surface-800 dark:text-surface-100" x-text="resetTarget?.name"></strong>
                </p>
                <div>
                    <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">
                        Password Baru <span class="text-red-500">*</span>
                    </label>
                    <input type="password" x-model="resetPassword" class="input-base" placeholder="Min. 6 karakter">
                    <p x-show="resetError" x-text="resetError" class="text-xs text-red-500 mt-1"></p>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" @click="showReset = false" class="btn-ghost flex-1 justify-center">Batal</button>
                    <button type="submit" :disabled="resetting"
                            class="flex-1 inline-flex items-center justify-center px-4 py-2
                                   bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-xl transition disabled:opacity-50">
                        <span x-show="!resetting">Reset Password</span>
                        <span x-show="resetting">Mereset...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    </template>

    {{-- MODAL: Konfirmasi hapus --}}
    <template x-teleport="#modal-root">
    <div x-show="showDelete" x-cloak @click.self="showDelete = false" class="modal-overlay">
        <div @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="modal-panel max-w-sm p-6 text-center">
            <div class="w-12 h-12 rounded-2xl bg-red-50 dark:bg-red-950/40 flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>
            <h3 class="font-semibold text-surface-800 dark:text-surface-100 mb-2">Hapus User?</h3>
            <p class="text-sm text-surface-500 dark:text-surface-400 mb-6">
                <strong x-text="deleteTarget?.name"></strong> akan dihapus permanen.
            </p>
            <div x-show="deleteError"
                 class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800
                        rounded-xl px-4 py-3 text-sm text-red-700 dark:text-red-400 mb-4"
                 x-text="deleteError"></div>
            <div class="flex gap-3">
                <button @click="showDelete = false" class="btn-ghost flex-1 justify-center">Batal</button>
                <button @click="doDelete()"
                        class="flex-1 inline-flex items-center justify-center px-4 py-2
                               bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl transition-all">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>
    </template>

</div>
@endsection

@push('scripts')
<script>
function userPage() {
    return {
        users: [], meta: {}, loading: true,
        search: '', filterRole: '', currentPage: 1,

        showModal: false, modalTitle: '', editMode: false, editId: null,
        form: { name: '', username: '', role: 'guru', password: '', is_active: true },
        errors: {}, formError: '', submitting: false,

        showReset: false, resetTarget: null, resetPassword: '', resetError: '', resetting: false,

        showDelete: false, deleteTarget: null, deleteError: '',

        init() { this.fetchUsers(); },

        async fetchUsers(page = 1) {
            this.loading = true;
            this.currentPage = page;
            const p = new URLSearchParams({ page, search: this.search, role: this.filterRole });
            const res = await fetch(`{{ route('admin.users.json') }}?${p}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
            });
            const data = await res.json();
            this.users = data.data;
            this.meta  = data.meta;
            this.loading = false;
        },

        goPage(page) {
            if (page >= 1 && page <= this.meta.last_page) this.fetchUsers(page);
        },

        openCreate() {
            this.editMode = false; this.editId = null;
            this.form = { name: '', username: '', role: 'guru', password: '', is_active: true };
            this.errors = {}; this.formError = '';
            this.modalTitle = 'Tambah User'; this.showModal = true;
        },

        openEdit(u) {
            this.editMode = true; this.editId = u.id;
            this.form = { name: u.name, username: u.username, role: u.role, password: '', is_active: u.is_active };
            this.errors = {}; this.formError = '';
            this.modalTitle = 'Edit User'; this.showModal = true;
        },

        closeModal() { this.showModal = false; },

        async submitForm() {
            this.submitting = true; this.errors = {}; this.formError = '';
            const url    = this.editMode ? `{{ url('admin/users') }}/${this.editId}` : `{{ route('admin.users.store') }}`;
            const method = this.editMode ? 'PUT' : 'POST';
            const res = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
                body: JSON.stringify(this.form),
            });
            const data = await res.json();
            if (res.ok)                  { this.closeModal(); this.fetchUsers(this.currentPage); }
            else if (res.status === 422)  { this.errors = data.errors ?? {}; this.formError = data.message ?? ''; }
            else                         { this.formError = data.message ?? 'Terjadi kesalahan.'; }
            this.submitting = false;
        },

        openReset(u) {
            this.resetTarget = u; this.resetPassword = ''; this.resetError = '';
            this.showReset = true;
        },

        async submitReset() {
            if (!this.resetPassword || this.resetPassword.length < 6) {
                this.resetError = 'Password minimal 6 karakter.'; return;
            }
            this.resetting = true; this.resetError = '';
            const res = await fetch(`{{ url('admin/users') }}/${this.resetTarget.id}/reset-password`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
                body: JSON.stringify({ password: this.resetPassword }),
            });
            if (res.ok) { this.showReset = false; }
            else { const d = await res.json(); this.resetError = d.message ?? 'Gagal reset password.'; }
            this.resetting = false;
        },

        confirmDelete(u) { this.deleteTarget = u; this.deleteError = ''; this.showDelete = true; },

        async doDelete() {
            const res = await fetch(`{{ url('admin/users') }}/${this.deleteTarget.id}`, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
            });
            if (res.ok) { this.showDelete = false; this.fetchUsers(this.currentPage); }
            else { const d = await res.json(); this.deleteError = d.message ?? 'Gagal menghapus.'; }
        },
    };
}
function csrf() { return document.querySelector('meta[name="csrf-token"]').content; }
</script>
@endpush
