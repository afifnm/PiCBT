<!DOCTYPE html>
<html lang="id" class="h-full bg-gradient-to-br from-blue-900 to-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Siswa — PiCBT</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-4">

<div class="w-full max-w-sm">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-14 h-14 bg-blue-500 rounded-2xl mb-4 shadow-lg">
            <span class="text-white font-bold text-xl">CBT</span>
        </div>
        <h1 class="text-2xl font-bold text-white">PiCBT</h1>
        <p class="text-blue-300 text-sm mt-1">Portal Ujian Siswa</p>
    </div>

    <div class="bg-white rounded-2xl shadow-2xl p-8">
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 mb-5 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('student.login.post') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">NIS (Nomor Induk Siswa)</label>
                    <input type="text" name="nis" value="{{ old('nis') }}" autofocus
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-blue-400 transition font-mono tracking-wider"
                           placeholder="2025001">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Password</label>
                    <input type="password" name="password"
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-blue-400 transition"
                           placeholder="••••••••">
                    <p class="text-xs text-slate-400 mt-1">Password default: NIS Anda</p>
                </div>
            </div>

            <button type="submit"
                    class="mt-6 w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition shadow-sm">
                Masuk ke Ujian
            </button>
        </form>

        <div class="mt-5 text-center">
            <a href="{{ route('admin.login') }}"
               class="text-xs text-slate-400 hover:text-blue-600 transition">
                Login sebagai Admin/Guru →
            </a>
        </div>
    </div>

    <p class="text-center text-xs text-blue-400 mt-5">
        Hubungi guru/admin jika mengalami masalah login.
    </p>
</div>

</body>
</html>
