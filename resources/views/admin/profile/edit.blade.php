@extends('layouts.admin')
@section('title', 'Edit Profil')
@section('page-title', 'Edit Profil')

@section('content')
<div class="max-w-xl mx-auto space-y-5">

    {{-- Info card --}}
    <div class="card p-6 flex items-center gap-4">
        <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-xl font-bold text-white flex-none shadow-md"
             style="background: linear-gradient(135deg, #7c6af6, #a78bfa)">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <div>
            <p class="font-bold text-surface-800 dark:text-surface-100 text-lg">{{ $user->name }}</p>
            <p class="text-sm text-surface-400 dark:text-surface-500">
                @{{ $user->username }} &bull;
                <span class="capitalize">{{ $user->role }}</span>
            </p>
        </div>
    </div>

    {{-- Form --}}
    <form method="POST" action="{{ route('admin.profile.update') }}" class="card p-6 space-y-5">
        @csrf
        @method('PUT')

        <h2 class="font-semibold text-surface-700 dark:text-surface-200 text-sm uppercase tracking-wide border-b border-surface-100 dark:border-surface-800 pb-3">
            Informasi Akun
        </h2>

        <div>
            <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">
                Nama Lengkap <span class="text-red-500">*</span>
            </label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}"
                   class="input-base @error('name') border-red-400 @enderror">
            @error('name')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">
                Username <span class="text-red-500">*</span>
            </label>
            <input type="text" name="username" value="{{ old('username', $user->username) }}"
                   class="input-base @error('username') border-red-400 @enderror">
            @error('username')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <h2 class="font-semibold text-surface-700 dark:text-surface-200 text-sm uppercase tracking-wide border-b border-surface-100 dark:border-surface-800 pb-3 pt-2">
            Ganti Password
            <span class="text-xs font-normal text-surface-400 normal-case tracking-normal">— kosongkan jika tidak ingin ganti</span>
        </h2>

        <div>
            <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">Password Lama</label>
            <input type="password" name="current_password"
                   class="input-base @error('current_password') border-red-400 @enderror"
                   placeholder="Masukkan password lama">
            @error('current_password')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-xs font-semibold text-surface-500 dark:text-surface-400 mb-1.5">Password Baru</label>
            <input type="password" name="new_password"
                   class="input-base @error('new_password') border-red-400 @enderror"
                   placeholder="Min. 6 karakter">
            @error('new_password')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit" class="btn-primary px-6">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Simpan Perubahan
            </button>
        </div>
    </form>

</div>
@endsection
