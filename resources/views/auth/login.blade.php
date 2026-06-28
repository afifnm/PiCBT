<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — {{ $appName }}</title>
    <link rel="shortcut icon" href="/logo.webp" type="image/webp">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @keyframes aurora-drift-a {
            0%,100% { transform:translate(0,0) scale(1); }
            33%     { transform:translate(6vw,8vh) scale(1.12); }
            66%     { transform:translate(-4vw,4vh) scale(.95); }
        }
        @keyframes aurora-drift-b {
            0%,100% { transform:translate(0,0) scale(1); }
            40%     { transform:translate(-8vw,-6vh) scale(1.14); }
            70%     { transform:translate(5vw,3vh) scale(.9); }
        }
        @keyframes aurora-drift-c {
            0%,100% { transform:translate(-50%,-50%) scale(1); }
            50%     { transform:translate(-50%,-50%) scale(1.25); }
        }
        @keyframes orb-float {
            0%,100% { transform:translateY(0) translateX(0); }
            40%     { transform:translateY(-40px) translateX(20px); }
            70%     { transform:translateY(-15px) translateX(-10px); }
        }
        @keyframes star-twinkle {
            0%,100% { opacity:.12; transform:scale(1); }
            50%     { opacity:.95; transform:scale(1.6); }
        }
        @keyframes fade-in-up {
            from { opacity:0; transform:translateY(36px) scale(.97); }
            to   { opacity:1; transform:translateY(0) scale(1); }
        }
        @property --angle {
            syntax:'<angle>';
            initial-value:0deg;
            inherits:false;
        }
        @keyframes border-spin     { to { --angle:360deg; } }
        @keyframes input-border-spin { to { --angle:360deg; } }
        @keyframes icon-pulse {
            0%,100% { box-shadow:0 0 0 0 rgba(99,102,241,.55); }
            50%     { box-shadow:0 0 0 16px rgba(99,102,241,0); }
        }
        @keyframes particle-float {
            0%   { transform:translateY(0) rotate(0deg); opacity:0; }
            10%  { opacity:.75; }
            90%  { opacity:.25; }
            100% { transform:translateY(-120px) rotate(360deg); opacity:0; }
        }
        @keyframes btn-gradient {
            0%,100% { background-position:0% 50%; }
            50%     { background-position:100% 50%; }
        }
        @keyframes grid-pulse {
            0%,100% { opacity:.03; }
            50%     { opacity:.065; }
        }
        @keyframes slide-tab {
            from { opacity:0; transform:translateY(12px); }
            to   { opacity:1; transform:translateY(0); }
        }

        * { box-sizing:border-box; }
        body {
            background:#080c18;
            font-family:'Inter',sans-serif;
            min-height:100dvh;
        }

        /* ── Background ── */
        .aurora-a { animation:aurora-drift-a 18s ease-in-out infinite; }
        .aurora-b { animation:aurora-drift-b 22s ease-in-out infinite; }
        .aurora-c { animation:aurora-drift-c 26s ease-in-out infinite; }
        .orb-a    { animation:orb-float 14s ease-in-out infinite; }
        .orb-b    { animation:orb-float 17s ease-in-out 3s infinite; }
        .hero-grid {
            background-image:radial-gradient(circle,rgba(255,255,255,.036) 1px,transparent 1px);
            background-size:28px 28px;
            animation:grid-pulse 5s ease-in-out infinite;
        }

        /* ── Card ── */
        .card-border {
            border-radius:1.85rem;
            padding:1.5px;
            background:conic-gradient(from var(--angle),
                rgba(99,102,241,.9), rgba(139,92,246,.9), rgba(236,72,153,.45),
                rgba(99,102,241,.15), rgba(99,102,241,.9));
            animation:border-spin 2.8s linear infinite;
            transition:filter .4s ease;
        }
        .card-border:hover { filter:drop-shadow(0 0 22px rgba(99,102,241,.5)); }
        .card-inner {
            border-radius:calc(1.85rem - 1.5px);
            background:rgba(10,14,28,.9);
            backdrop-filter:blur(32px);
            -webkit-backdrop-filter:blur(32px);
            padding:2rem 2rem 1.75rem;
        }
        @media (min-width:400px) {
            .card-inner { padding:2.5rem 2.5rem 2.25rem; }
        }
        .card-animate { animation:fade-in-up .85s cubic-bezier(.16,1,.3,1) forwards; }

        /* ── Tab toggle ── */
        .tab-track {
            display:flex;
            background:rgba(255,255,255,.04);
            border:1px solid rgba(255,255,255,.07);
            border-radius:.85rem;
            padding:3px;
            gap:3px;
        }
        .tab-btn {
            flex:1;
            display:flex;
            align-items:center;
            justify-content:center;
            gap:.45rem;
            padding:.6rem .5rem;
            border-radius:.65rem;
            font-size:.8rem;
            font-weight:600;
            letter-spacing:.02em;
            cursor:pointer;
            border:none;
            outline:none;
            transition:background .22s ease, color .22s ease, box-shadow .22s ease;
            color:rgba(148,163,184,.55);
            background:transparent;
            -webkit-tap-highlight-color:transparent;
        }
        .tab-btn.active {
            background:linear-gradient(135deg,rgba(79,70,229,.85),rgba(124,58,237,.85));
            color:#f1f5f9;
            box-shadow:0 4px 14px -2px rgba(99,102,241,.45), inset 0 1px 0 rgba(255,255,255,.12);
        }
        .tab-btn svg { flex-shrink:0; }

        /* ── Form panels ── */
        .tab-panel { animation:slide-tab .3s ease forwards; }

        /* ── Inputs ── */
        .input-wrap { position:relative; border-radius:.75rem; }
        .input-wrap::before {
            content:'';
            position:absolute;
            inset:-1.5px;
            border-radius:.88rem;
            background:conic-gradient(from var(--angle),
                rgba(99,102,241,.95),rgba(139,92,246,.95),rgba(99,102,241,.95));
            animation:input-border-spin 1.4s linear infinite;
            opacity:0;
            transition:opacity .2s ease;
            z-index:0;
        }
        .input-wrap:focus-within::before { opacity:1; }
        .input-wrap input { position:relative; z-index:1; }
        .input-field {
            width:100%;
            padding:.8rem 2.85rem .8rem 2.85rem;
            border-radius:.75rem;
            border:1px solid rgba(99,102,241,.16);
            background:rgba(255,255,255,.038);
            color:#e2e8f0;
            font-size:.9rem;
            font-family:inherit;
            outline:none;
            transition:background .25s, border-color .25s;
        }
        /* field tanpa toggle-pw (username/NIS) — hapus padding kanan ekstra */
        .input-field.no-action { padding-right:1rem; }
        .input-field::placeholder { color:rgba(148,163,184,.38); }
        .input-field:focus {
            background:rgba(99,102,241,.07);
            border-color:transparent;
        }
        .input-icon {
            position:absolute;
            top:50%; left:0;
            transform:translateY(-50%);
            width:44px;
            display:flex; align-items:center; justify-content:center;
            pointer-events:none;
            z-index:10;
            color:rgba(139,92,246,.8);
        }
        .input-icon svg { display:block; width:18px; height:18px; flex-shrink:0; }
        .input-action {
            position:absolute;
            top:50%; right:0;
            transform:translateY(-50%);
            width:44px;
            display:flex; align-items:center; justify-content:center;
            z-index:10;
        }
        .toggle-pw {
            background:none; border:none; cursor:pointer;
            color:rgba(148,163,184,.4);
            padding:5px;
            border-radius:.35rem;
            transition:color .18s, background .18s;
            display:flex; align-items:center; justify-content:center;
            line-height:0;
        }
        .toggle-pw:hover { color:rgba(167,139,250,.9); background:rgba(139,92,246,.12); }
        .toggle-pw svg { display:block; width:18px; height:18px; }

        /* ── Button ── */
        @keyframes lock-open {
            0%   { transform:translateX(0) rotate(0deg); }
            30%  { transform:translateX(-3px) rotate(-15deg); }
            60%  { transform:translateX(2px) rotate(8deg); }
            100% { transform:translateX(0) rotate(0deg); }
        }
        @keyframes arrow-slide {
            0%,40% { transform:translateX(0); opacity:1; }
            55%    { transform:translateX(6px); opacity:0; }
            56%    { transform:translateX(-6px); opacity:0; }
            75%    { transform:translateX(0); opacity:1; }
            100%   { transform:translateX(0); opacity:1; }
        }
        .btn-submit {
            width:100%;
            padding:.875rem 1.25rem;
            border-radius:.85rem;
            border:none;
            font-family:inherit;
            font-size:.9rem;
            font-weight:700;
            letter-spacing:.03em;
            color:#fff;
            cursor:pointer;
            background:linear-gradient(90deg,#4f46e5,#7c3aed,#9333ea,#7c3aed,#4f46e5);
            background-size:300% 100%;
            animation:btn-gradient 3.5s ease infinite;
            transition:transform .22s ease, box-shadow .22s ease;
            display:flex; align-items:center; justify-content:center; gap:.55rem;
            overflow:hidden;
            position:relative;
        }
        .btn-submit::before {
            content:'';
            position:absolute;
            inset:0;
            background:linear-gradient(90deg,rgba(255,255,255,.0),rgba(255,255,255,.1),rgba(255,255,255,.0));
            transform:translateX(-100%);
            transition:transform 0s;
        }
        .btn-submit:hover::before {
            transform:translateX(100%);
            transition:transform .5s ease;
        }
        .btn-submit:hover  { transform:translateY(-2px); box-shadow:0 12px 30px -6px rgba(99,102,241,.65); }
        .btn-submit:active { transform:scale(.97); box-shadow:none; }
        .btn-submit .btn-lock { transition:transform .35s cubic-bezier(.34,1.56,.64,1); }
        .btn-submit:hover .btn-lock { animation:lock-open .5s ease forwards; }
        .btn-submit .btn-arrow { transition:transform .3s ease, opacity .3s ease; }
        .btn-submit:hover .btn-arrow { animation:arrow-slide .7s ease forwards; }

        /* ── Label ── */
        .field-label {
            display:block;
            font-size:.68rem;
            font-weight:700;
            letter-spacing:.11em;
            text-transform:uppercase;
            color:rgba(148,163,184,.6);
            margin-bottom:.45rem;
        }

        /* ── Checkbox ── */
        .dark-check {
            width:1rem; height:1rem;
            border-radius:.25rem;
            border:1px solid rgba(99,102,241,.35);
            background:rgba(255,255,255,.04);
            cursor:pointer;
            accent-color:#6366f1;
            flex-shrink:0;
        }

        /* ── Error ── */
        .error-box {
            padding:.75rem 1rem;
            border-radius:.75rem;
            font-size:.82rem;
            background:rgba(248,113,113,.08);
            border:1px solid rgba(248,113,113,.28);
            color:#fca5a5;
            display:flex; align-items:flex-start; gap:.5rem;
        }

        /* ── Particles ── */
        .particle {
            position:absolute;
            width:5px; height:5px;
            border-radius:50%;
            pointer-events:none;
            animation:particle-float linear infinite;
        }

        /* ── Autofill fix ── */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus {
            -webkit-box-shadow:0 0 0 1000px rgba(10,14,28,.97) inset !important;
            -webkit-text-fill-color:#e2e8f0 !important;
            caret-color:#e2e8f0;
        }

        /* ── Logo badge ── */
        .icon-badge { animation:icon-pulse 2.8s ease-in-out infinite; }

        /* ── Divider ── */
        .divider {
            display:flex; align-items:center; gap:.75rem;
            margin:.25rem 0;
        }
        .divider::before, .divider::after {
            content:'';
            flex:1;
            height:1px;
            background:rgba(255,255,255,.06);
        }

        /* ── Help text ── */
        .help-text {
            font-size:.72rem;
            color:rgba(148,163,184,.35);
            text-align:center;
            line-height:1.5;
        }
    </style>
</head>
<body class="flex items-center justify-center p-4 overflow-hidden relative" style="min-height:100dvh"
      x-data="loginPortal()" x-init="init()">

    {{-- ── Background ── --}}
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
        <div class="aurora-a absolute" style="width:85vw;height:85vw;max-width:950px;max-height:950px;top:-22%;left:-18%;border-radius:50%;background:radial-gradient(circle,rgba(37,99,235,.17) 0%,transparent 65%)"></div>
        <div class="aurora-b absolute" style="width:75vw;height:75vw;max-width:850px;max-height:850px;bottom:-18%;right:-18%;border-radius:50%;background:radial-gradient(circle,rgba(99,102,241,.14) 0%,transparent 65%)"></div>
        <div class="aurora-c absolute" style="width:55vw;height:55vw;max-width:640px;max-height:640px;top:38%;left:50%;transform:translate(-50%,-50%);border-radius:50%;background:radial-gradient(circle,rgba(139,92,246,.09) 0%,transparent 65%)"></div>
        <div class="orb-a absolute" style="width:300px;height:300px;top:8%;right:18%;border-radius:50%;background:radial-gradient(circle,rgba(99,102,241,.1) 0%,transparent 60%);filter:blur(3px)"></div>
        <div class="orb-b absolute" style="width:220px;height:220px;bottom:22%;left:12%;border-radius:50%;background:radial-gradient(circle,rgba(37,99,235,.09) 0%,transparent 60%);filter:blur(3px)"></div>
        <div class="hero-grid absolute inset-0"></div>
        <div id="stars-layer" class="absolute inset-0"></div>
    </div>

    {{-- ── Particles ── --}}
    <div class="particle" style="bottom:8%;left:18%;background:rgba(99,102,241,.7);animation-duration:4.3s;animation-delay:0s"></div>
    <div class="particle" style="bottom:14%;left:33%;background:rgba(139,92,246,.7);animation-duration:5.1s;animation-delay:1.4s"></div>
    <div class="particle" style="bottom:6%;left:53%;background:rgba(56,189,248,.6);animation-duration:3.9s;animation-delay:.8s"></div>
    <div class="particle" style="bottom:11%;left:69%;background:rgba(167,139,250,.7);animation-duration:4.7s;animation-delay:2.2s"></div>
    <div class="particle" style="bottom:18%;left:83%;background:rgba(236,72,153,.5);animation-duration:5.4s;animation-delay:.3s"></div>

    {{-- ── Card ── --}}
    <div class="card-animate relative z-10 w-full" style="max-width:420px">
        <div class="card-border">
            <div class="card-inner">

                {{-- Logo + Title --}}
                <div class="text-center mb-6">
                    <div class="icon-badge inline-flex items-center justify-center w-14 h-14 rounded-2xl mb-3"
                         style="background:linear-gradient(135deg,#4f46e5,#7c3aed);box-shadow:0 8px 26px -4px rgba(99,102,241,.55)">
                        <img src="/logo.webp" alt="{{ $appName }}" class="w-8 h-8 rounded-xl object-contain">
                    </div>
                    <h1 class="text-xl font-bold tracking-tight" style="color:#f1f5f9;letter-spacing:-.01em">
                        Portal Login {{ $appName }}
                    </h1>
                    <p class="text-xs mt-1" style="color:rgba(148,163,184,.5)">
                        Sistem Computer Based Test — SMK
                    </p>
                </div>

                {{-- ── Tab Switch ── --}}
                <div class="tab-track mb-5">
                    <button type="button" class="tab-btn" :class="{ active: tab === 'siswa' }" @click="switchTab('siswa')">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 14a7 7 0 00-7 7h14a7 7 0 00-7-7zM12 11a4 4 0 100-8 4 4 0 000 8z"/>
                        </svg>
                        Siswa
                    </button>
                    <button type="button" class="tab-btn" :class="{ active: tab === 'admin' }" @click="switchTab('admin')">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Admin / Guru
                    </button>
                </div>

                {{-- ── Error ── --}}
                @if ($errors->any())
                    <div class="error-box mb-4">
                        <svg class="w-4 h-4 flex-shrink-0 mt-px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                {{-- ════════════════ PANEL SISWA ════════════════ --}}
                <div x-show="tab === 'siswa'" x-transition:enter="slide-tab" class="tab-panel">
                    <form method="POST" action="{{ route('student.login.post') }}" class="space-y-4">
                        @csrf

                        <div>
                            <label for="nis" class="field-label">NIS (Nomor Induk Siswa)</label>
                            <div class="input-wrap">
                                <span class="input-icon">
                                    {{-- ID card icon --}}
                                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <rect x="3" y="5" width="18" height="14" rx="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <circle cx="8.5" cy="11" r="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.5 17c0-1.657 1.343-2.5 3-2.5s3 .843 3 2.5"/>
                                        <path stroke-linecap="round" d="M14 9h4M14 13h3"/>
                                    </svg>
                                </span>
                                <input id="nis" type="text" name="nis"
                                       value="{{ old('nis') }}"
                                       placeholder="Masukkan NIS kamu" required
                                       autocomplete="username"
                                       class="input-field no-action"
                                       style="font-variant-numeric:tabular-nums;letter-spacing:.04em"
                                       x-ref="nisInput">
                            </div>
                        </div>

                        <div>
                            <label for="pw_siswa" class="field-label">Password</label>
                            <div class="input-wrap">
                                <span class="input-icon">
                                    {{-- Lock icon --}}
                                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <rect x="5" y="11" width="14" height="10" rx="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 018 0v4"/>
                                    </svg>
                                </span>
                                <input id="pw_siswa" :type="showPwSiswa ? 'text' : 'password'"
                                       name="password"
                                       placeholder="••••••••••" required
                                       autocomplete="current-password"
                                       class="input-field">
                                <span class="input-action">
                                    <button type="button" class="toggle-pw" @click="showPwSiswa = !showPwSiswa"
                                            :aria-label="showPwSiswa ? 'Sembunyikan' : 'Tampilkan'">
                                        <svg x-show="!showPwSiswa" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            <circle cx="12" cy="12" r="3" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <svg x-show="showPwSiswa" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" style="display:none">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.477 10.477A3 3 0 0013.523 13.523M7.05 7.05A7.003 7.003 0 002.458 12C3.732 16.057 7.523 19 12 19c1.9 0 3.664-.595 5.12-1.61M9.9 4.24A9.12 9.12 0 0112 4c4.478 0 8.268 2.943 9.542 7a9.996 9.996 0 01-1.93 3.592"/>
                                        </svg>
                                    </button>
                                </span>
                            </div>
                        </div>

                        <button type="submit" class="btn-submit" style="margin-top:1.25rem">
                            {{-- Lock icon: animates open on hover --}}
                            <svg class="btn-lock" style="width:17px;height:17px;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M8 11V7a4 4 0 118 0v4M5 11h14a2 2 0 012 2v7a2 2 0 01-2 2H5a2 2 0 01-2-2v-7a2 2 0 012-2z"/>
                            </svg>
                            Masuk ke Ujian
                            {{-- Arrow: slides right on hover --}}
                            <svg class="btn-arrow" style="width:16px;height:16px;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </button>
                    </form>

                    <p class="help-text mt-4">
                        Hubungi guru atau admin jika mengalami masalah login.
                    </p>
                </div>

                {{-- ════════════════ PANEL ADMIN/GURU ════════════════ --}}
                <div x-show="tab === 'admin'" class="tab-panel" style="display:none">
                    <form method="POST" action="{{ route('admin.login.post') }}" class="space-y-4">
                        @csrf

                        <div>
                            <label for="username" class="field-label">Username</label>
                            <div class="input-wrap">
                                <span class="input-icon">
                                    {{-- Person icon --}}
                                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <circle cx="12" cy="7" r="4" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 21c0-3.866 3.134-7 7-7s7 3.134 7 7"/>
                                    </svg>
                                </span>
                                <input id="username" type="text" name="username"
                                       value="{{ old('username') }}"
                                       placeholder="Masukkan username" required
                                       autocomplete="username"
                                       class="input-field no-action"
                                       x-ref="usernameInput">
                            </div>
                        </div>

                        <div>
                            <label for="pw_admin" class="field-label">Password</label>
                            <div class="input-wrap">
                                <span class="input-icon">
                                    {{-- Lock icon --}}
                                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <rect x="5" y="11" width="14" height="10" rx="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 018 0v4"/>
                                    </svg>
                                </span>
                                <input id="pw_admin" :type="showPwAdmin ? 'text' : 'password'"
                                       name="password"
                                       placeholder="••••••••••" required
                                       autocomplete="current-password"
                                       class="input-field">
                                <span class="input-action">
                                    <button type="button" class="toggle-pw" @click="showPwAdmin = !showPwAdmin"
                                            :aria-label="showPwAdmin ? 'Sembunyikan' : 'Tampilkan'">
                                        <svg x-show="!showPwAdmin" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            <circle cx="12" cy="12" r="3" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <svg x-show="showPwAdmin" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" style="display:none">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.477 10.477A3 3 0 0013.523 13.523M7.05 7.05A7.003 7.003 0 002.458 12C3.732 16.057 7.523 19 12 19c1.9 0 3.664-.595 5.12-1.61M9.9 4.24A9.12 9.12 0 0112 4c4.478 0 8.268 2.943 9.542 7a9.996 9.996 0 01-1.93 3.592"/>
                                        </svg>
                                    </button>
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2.5">
                            <input id="remember_me" type="checkbox" name="remember" class="dark-check">
                            <label for="remember_me" class="text-sm cursor-pointer select-none"
                                   style="color:rgba(148,163,184,.6)">
                                Ingat saya
                            </label>
                        </div>

                        <button type="submit" class="btn-submit" style="margin-top:.5rem">
                            <svg class="btn-lock" style="width:17px;height:17px;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M8 11V7a4 4 0 118 0v4M5 11h14a2 2 0 012 2v7a2 2 0 01-2 2H5a2 2 0 01-2-2v-7a2 2 0 012-2z"/>
                            </svg>
                            Masuk ke Panel
                            <svg class="btn-arrow" style="width:16px;height:16px;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </button>
                    </form>
                </div>

                {{-- ── Footer ── --}}
                <div class="divider mt-5">
                    <span class="text-xs" style="color:rgba(148,163,184,.25)">{{ $appName }}</span>
                </div>
                <p class="help-text mt-2">
                    &copy; {{ date('Y') }} SMK — Sistem CBT Online
                </p>

            </div>
        </div>
    </div>

    <script>
    function loginPortal() {
        return {
            tab: '{{ old("nis") ? "siswa" : (old("username") ? "admin" : ($errors->any() && !old("username") ? "siswa" : "siswa")) }}',
            showPwSiswa: false,
            showPwAdmin: false,

            init() {
                /* Stars */
                const sl = document.getElementById('stars-layer');
                if (sl) {
                    Array.from({ length: 80 }, () => {
                        const s = document.createElement('span');
                        const sz = Math.random() * 2 + .7;
                        s.style.cssText = `position:absolute;border-radius:50%;background:#fff;
                            width:${sz}px;height:${sz}px;
                            top:${Math.random()*100}%;left:${Math.random()*100}%;
                            animation:star-twinkle ${2+Math.random()*4}s ease-in-out ${Math.random()*5}s infinite;`;
                        sl.appendChild(s);
                    });
                }

                /* Auto-detect which tab to show based on old input */
                @if(old('username'))
                    this.tab = 'admin';
                @elseif(old('nis'))
                    this.tab = 'siswa';
                @endif
            },

            switchTab(t) {
                this.tab = t;
                this.$nextTick(() => {
                    if (t === 'siswa' && this.$refs.nisInput) this.$refs.nisInput.focus();
                    if (t === 'admin' && this.$refs.usernameInput) this.$refs.usernameInput.focus();
                });
            }
        };
    }
    </script>

</body>
</html>
