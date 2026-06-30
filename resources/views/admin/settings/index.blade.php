@extends('layouts.admin')
@section('title', 'Pengaturan')
@section('page-title', 'Pengaturan Sistem')

@section('content')
<div class="max-w-2xl" x-data="settingsPage()">

    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-5">
        @csrf
        @method('PUT')

        {{-- Informasi Sekolah --}}
        <div class="card p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-8 h-8 rounded-lg bg-primary-50 dark:bg-primary-950/40 flex items-center justify-center">
                    <svg class="w-4 h-4 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-surface-800 dark:text-surface-100">Informasi Aplikasi</h3>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">Nama Aplikasi</label>
                    <input type="text" name="app_name" value="{{ old('app_name', $settings['app_name']) }}"
                           class="input-base">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">Nama Sekolah</label>
                    <input type="text" name="sekolah_nama" value="{{ old('sekolah_nama', $settings['sekolah_nama']) }}"
                           class="input-base" placeholder="SMK Negeri 1 ...">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">Alamat Sekolah</label>
                    <textarea name="sekolah_alamat" rows="2" class="input-base resize-none"
                              placeholder="Jl. ...">{{ old('sekolah_alamat', $settings['sekolah_alamat']) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Gemini AI --}}
        <div class="card p-6">
            <div class="flex items-start justify-between mb-5">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/40 flex items-center justify-center">
                        <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m1.636-6.364l.707.707M6.343 17.657l-.707.707M17.657 17.657l.707-.707M12 21v-1"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-surface-800 dark:text-surface-100">Google Gemini API</h3>
                        <p class="text-xs text-surface-400 dark:text-surface-500 mt-0.5">Untuk pembuatan soal dan penilaian otomatis esai.</p>
                    </div>
                </div>
                <button type="button" @click="testGemini()" :disabled="testing"
                        class="btn-ghost text-xs disabled:opacity-50">
                    <svg x-show="!testing" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <svg x-show="testing" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                    <span x-show="!testing">Test Koneksi</span>
                    <span x-show="testing">Menguji...</span>
                </button>
            </div>

            <div x-show="testResult" class="mb-4 rounded-xl px-4 py-3 text-sm"
                 :class="testOk
                    ? 'bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400'
                    : 'bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400'">
                <span x-text="testResult"></span>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">API Key</label>
                    <input type="password" name="gemini_api_key"
                           class="input-base font-mono"
                           placeholder="Kosongkan untuk tidak mengubah key yang tersimpan"
                           autocomplete="new-password">
                    @if ($settings['gemini_api_key'])
                        <p class="text-xs text-surface-400 dark:text-surface-500 mt-1.5">
                            Key tersimpan: <code class="bg-surface-100 dark:bg-surface-800 px-1.5 py-0.5 rounded">{{ $settings['gemini_api_key'] }}</code>
                        </p>
                    @else
                        <p class="text-xs text-amber-500 mt-1.5 flex items-center gap-1.5">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            API key belum dikonfigurasi. Penilaian esai AI tidak akan berfungsi.
                        </p>
                    @endif
                </div>
                <div>
                    <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">Model Gemini</label>
                    <select name="gemini_model" class="input-base">
                        @foreach ($models as $value => $label)
                            <option value="{{ $value }}" {{ old('gemini_model', $settings['gemini_model']) === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-surface-400 dark:text-surface-500 mt-1.5">
                        Flash direkomendasikan untuk penilaian batch — lebih cepat dan hemat kuota.
                    </p>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 rounded-xl px-4 py-3 text-sm text-red-700 dark:text-red-400">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="flex justify-end">
            <button type="submit" class="btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Simpan Pengaturan
            </button>
        </div>
    </form>

    {{-- Info Sistem --}}
    <div class="card p-6 mt-5">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-8 h-8 rounded-lg bg-surface-100 dark:bg-surface-800 flex items-center justify-center">
                <svg class="w-4 h-4 text-surface-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/>
                </svg>
            </div>
            <h3 class="font-semibold text-surface-800 dark:text-surface-100">Informasi Sistem</h3>
        </div>
        <div class="grid grid-cols-2 gap-0 text-sm divide-y divide-surface-50 dark:divide-surface-800">
            @php
                $info = [
                    'Versi PHP'    => PHP_VERSION,
                    'Laravel'      => app()->version(),
                    'Timezone'     => config('app.timezone'),
                    'Environment'  => app()->environment(),
                    'Queue Driver' => config('queue.default'),
                    'Cache Driver' => config('cache.default'),
                ];
            @endphp
            @foreach ($info as $label => $val)
                <div class="flex items-center justify-between py-2.5">
                    <span class="text-surface-500 dark:text-surface-400">{{ $label }}</span>
                    <code class="text-xs bg-surface-100 dark:bg-surface-800 px-2 py-0.5 rounded font-mono text-surface-700 dark:text-surface-300">{{ $val }}</code>
                </div>
            @endforeach
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function settingsPage() {
    return {
        testing: false,
        testResult: '',
        testOk: false,

        async testGemini() {
            this.testing = true;
            this.testResult = '';
            const res = await fetch('{{ route('admin.settings.test-gemini') }}', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
            });
            const data = await res.json();
            this.testOk = data.ok;
            this.testResult = data.message;
            this.testing = false;
        },
    };
}
function csrf() { return document.querySelector('meta[name="csrf-token"]').content; }
</script>
@endpush
