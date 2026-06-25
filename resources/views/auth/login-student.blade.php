<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Siswa — PiCBT</title>
    <link rel="shortcut icon" href="/logo.webp" type="image/webp">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @keyframes aurora-drift-a {
            0%,100% { transform: translate(0,0) scale(1); }
            33%     { transform: translate(6vw,8vh) scale(1.1); }
            66%     { transform: translate(-4vw,4vh) scale(.95); }
        }
        @keyframes aurora-drift-b {
            0%,100% { transform: translate(0,0) scale(1); }
            40%     { transform: translate(-8vw,-6vh) scale(1.12); }
            70%     { transform: translate(5vw,3vh) scale(.9); }
        }
        @keyframes aurora-drift-c {
            0%,100% { transform: translate(-50%,-50%) scale(1); }
            50%     { transform: translate(-50%,-50%) scale(1.2); }
        }
        @keyframes orb-float {
            0%,100% { transform: translateY(0) translateX(0); }
            40%     { transform: translateY(-40px) translateX(20px); }
            70%     { transform: translateY(-15px) translateX(-10px); }
        }
        @keyframes star-twinkle {
            0%,100% { opacity:.15; transform:scale(1); }
            50%     { opacity:.9;  transform:scale(1.5); }
        }
        @keyframes fade-in-up {
            from { opacity:0; transform:translateY(40px) scale(.97); }
            to   { opacity:1; transform:translateY(0)    scale(1); }
        }
        @property --angle {
            syntax: '<angle>';
            initial-value: 0deg;
            inherits: false;
        }
        @keyframes border-spin { to { --angle: 360deg; } }
        @keyframes input-border-spin { to { --angle: 360deg; } }
        @keyframes icon-pulse {
            0%,100% { box-shadow: 0 0 0 0 rgba(99,102,241,.5); }
            50%     { box-shadow: 0 0 0 14px rgba(99,102,241,.0); }
        }
        @keyframes particle-float {
            0%   { transform:translateY(0) rotate(0deg);   opacity:0; }
            10%  { opacity:.7; }
            90%  { opacity:.3; }
            100% { transform:translateY(-110px) rotate(360deg); opacity:0; }
        }
        @keyframes btn-gradient {
            0%,100% { background-position:0% 50%; }
            50%     { background-position:100% 50%; }
        }
        @keyframes label-up {
            from { opacity:0; transform:translateY(8px); }
            to   { opacity:1; transform:translateY(0); }
        }
        @keyframes grid-pulse {
            0%,100% { opacity:.035; }
            50%     { opacity:.07; }
        }

        body {
            background: #0b0f1a;
            font-family: 'Inter', sans-serif;
        }
        .aurora-a { animation: aurora-drift-a 18s ease-in-out infinite; }
        .aurora-b { animation: aurora-drift-b 22s ease-in-out infinite; }
        .aurora-c { animation: aurora-drift-c 26s ease-in-out infinite; }
        .orb-a    { animation: orb-float 14s ease-in-out infinite; }
        .orb-b    { animation: orb-float 17s ease-in-out 3s infinite; }
        .hero-grid {
            background-image: radial-gradient(circle, rgba(255,255,255,.038) 1px, transparent 1px);
            background-size: 28px 28px;
            animation: grid-pulse 5s ease-in-out infinite;
        }
        .card-border {
            border-radius: 1.75rem;
            padding: 1.5px;
            background: conic-gradient(from var(--angle),
                rgba(99,102,241,.9), rgba(139,92,246,.9), rgba(236,72,153,.5),
                rgba(99,102,241,.2), rgba(99,102,241,.9));
            animation: border-spin 2.5s linear infinite;
            transition: filter .4s ease;
        }
        .card-border:hover { filter: drop-shadow(0 0 18px rgba(99,102,241,.6)); }
        .card-inner {
            border-radius: calc(1.75rem - 1.5px);
            background: rgba(13,17,32,.88);
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
            border: none;
            padding: 2.5rem;
            box-shadow:
                0 30px 70px -12px rgba(0,0,0,.55),
                0 0 0 1px rgba(99,102,241,.12),
                inset 0 1px 0 rgba(255,255,255,.06);
        }
        .icon-badge { animation: icon-pulse 2.6s ease-in-out infinite; }
        .input-wrap { position: relative; border-radius: .75rem; }
        .input-wrap::before {
            content: '';
            position: absolute;
            inset: -1.5px;
            border-radius: .85rem;
            background: conic-gradient(from var(--angle),
                rgba(99,102,241,.95), rgba(139,92,246,.95), rgba(99,102,241,.95));
            animation: input-border-spin 1.4s linear infinite;
            opacity: 0;
            transition: opacity .2s ease;
            z-index: 0;
        }
        .input-wrap:focus-within::before { opacity: 1; }
        .input-wrap input { position: relative; z-index: 1; }
        .input-field {
            width: 100%;
            padding: .75rem 1rem .75rem 2.75rem;
            border-radius: .75rem;
            border: 1px solid rgba(99,102,241,.18);
            background: rgba(255,255,255,.04);
            color: #e2e8f0;
            font-size: .875rem;
            font-family: inherit;
            outline: none;
            transition: background .25s, border-color .25s;
        }
        .input-field::placeholder { color: rgba(148,163,184,.45); }
        .input-field:focus {
            background: rgba(99,102,241,.07);
            border-color: transparent;
        }
        .btn-primary {
            background: linear-gradient(90deg, #4f46e5, #7c3aed, #9333ea, #7c3aed, #4f46e5);
            background-size: 300% 100%;
            animation: btn-gradient 3s ease infinite;
            border: none;
            transition: transform .25s ease, box-shadow .25s ease;
        }
        .btn-primary:hover  { transform: translateY(-3px); box-shadow: 0 14px 32px -4px rgba(99,102,241,.55); }
        .btn-primary:active { transform: scale(.96); box-shadow: none; }
        .field-label {
            animation: label-up .45s ease forwards;
            opacity: 0;
            display: block;
            font-size: .7rem;
            font-weight: 600;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: rgba(148,163,184,.7);
            margin-bottom: .5rem;
        }
        .field-label-1 { animation-delay: .55s; }
        .field-label-2 { animation-delay: .7s; }
        .card-animate {
            animation: fade-in-up .8s cubic-bezier(.16,1,.3,1) forwards;
        }
        .particle {
            position: absolute;
            width: 5px; height: 5px;
            border-radius: 50%;
            pointer-events: none;
            animation: particle-float linear infinite;
        }
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus {
            -webkit-box-shadow: 0 0 0 1000px rgba(13,17,32,.95) inset !important;
            -webkit-text-fill-color: #e2e8f0 !important;
            caret-color: #e2e8f0;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 overflow-hidden relative">

    {{-- Background --}}
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
        <div class="aurora-a absolute" style="width:80vw;height:80vw;max-width:900px;max-height:900px;top:-20%;left:-20%;border-radius:50%;background:radial-gradient(circle,rgba(37,99,235,.18) 0%,transparent 65%)"></div>
        <div class="aurora-b absolute" style="width:70vw;height:70vw;max-width:800px;max-height:800px;bottom:-15%;right:-15%;border-radius:50%;background:radial-gradient(circle,rgba(99,102,241,.16) 0%,transparent 65%)"></div>
        <div class="aurora-c absolute" style="width:50vw;height:50vw;max-width:600px;max-height:600px;top:35%;left:50%;transform:translate(-50%,-50%);border-radius:50%;background:radial-gradient(circle,rgba(139,92,246,.1) 0%,transparent 65%)"></div>
        <div class="orb-a absolute" style="width:320px;height:320px;top:10%;right:20%;border-radius:50%;background:radial-gradient(circle,rgba(99,102,241,.12) 0%,transparent 60%);filter:blur(2px)"></div>
        <div class="orb-b absolute" style="width:240px;height:240px;bottom:25%;left:15%;border-radius:50%;background:radial-gradient(circle,rgba(37,99,235,.1) 0%,transparent 60%);filter:blur(2px)"></div>
        <div class="hero-grid absolute inset-0"></div>
        <div id="stars-layer" class="absolute inset-0"></div>
    </div>

    {{-- Particles --}}
    <div class="particle" style="bottom:8%;left:18%;background:rgba(99,102,241,.7);animation-duration:4.3s;animation-delay:0s"></div>
    <div class="particle" style="bottom:14%;left:32%;background:rgba(139,92,246,.7);animation-duration:5.1s;animation-delay:1.4s"></div>
    <div class="particle" style="bottom:6%;left:52%;background:rgba(56,189,248,.6);animation-duration:3.9s;animation-delay:.8s"></div>
    <div class="particle" style="bottom:11%;left:68%;background:rgba(167,139,250,.7);animation-duration:4.7s;animation-delay:2.2s"></div>
    <div class="particle" style="bottom:18%;left:82%;background:rgba(236,72,153,.5);animation-duration:5.4s;animation-delay:.3s"></div>

    {{-- Card --}}
    <div class="card-animate relative z-10 w-full max-w-md">
        <div class="card-border">
            <div class="card-inner">

                {{-- Header --}}
                <div class="text-center mb-8">
                    <div class="icon-badge inline-flex items-center justify-center w-14 h-14 rounded-2xl mb-4"
                         style="background:linear-gradient(135deg,#4f46e5,#7c3aed);box-shadow:0 8px 24px -4px rgba(99,102,241,.5)">
                        <img src="/logo.webp" alt="PiCBT" class="w-8 h-8 rounded-xl object-contain">
                    </div>
                    <h1 class="text-2xl font-bold tracking-tight" style="color:#f1f5f9">Portal Ujian Siswa</h1>
                    <p class="text-sm mt-1" style="color:rgba(148,163,184,.6)">Masuk untuk mengikuti ujian</p>
                </div>

                {{-- Error --}}
                @if ($errors->any())
                    <div class="mb-5 px-4 py-3 rounded-xl text-sm" style="background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.3);color:#fca5a5">
                        {{ $errors->first() }}
                    </div>
                @endif

                {{-- Form --}}
                <form method="POST" action="{{ route('student.login.post') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="nis" class="field-label field-label-1">NIS (Nomor Induk Siswa)</label>
                        <div class="input-wrap">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none z-10">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:rgba(99,102,241,.7)">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                          d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2"/>
                                </svg>
                            </span>
                            <input id="nis" type="text" name="nis" value="{{ old('nis') }}"
                                   placeholder="2025001" required autofocus autocomplete="username"
                                   class="input-field" style="font-variant-numeric:tabular-nums;letter-spacing:.05em">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="field-label field-label-2" style="margin-bottom:.5rem">Password</label>
                        <div class="input-wrap">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none z-10">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:rgba(99,102,241,.7)">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </span>
                            <input id="password" type="password" name="password"
                                   placeholder="••••••••••" required autocomplete="current-password"
                                   class="input-field">
                        </div>
                        <p class="text-xs mt-1.5" style="color:rgba(148,163,184,.4)">Password default: NIS Anda</p>
                    </div>

                    <button type="submit"
                            class="btn-primary group relative w-full py-3.5 rounded-xl font-semibold text-sm text-white
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-950">
                        <span class="flex items-center justify-center gap-2">
                            Masuk ke Ujian
                            <svg class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </span>
                    </button>
                </form>

                {{-- Switch --}}
                <p class="text-center text-xs mt-6">
                    <a href="{{ route('admin.login') }}"
                       class="inline-flex items-center gap-1.5 font-medium transition-colors duration-200"
                       style="color:rgba(148,163,184,.5)"
                       onmouseover="this.style.color='rgba(99,102,241,.9)'"
                       onmouseout="this.style.color='rgba(148,163,184,.5)'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Login sebagai Admin/Guru
                    </a>
                </p>

                <p class="text-center text-xs mt-3" style="color:rgba(148,163,184,.35)">
                    Hubungi guru/admin jika mengalami masalah login.
                </p>

            </div>
        </div>
    </div>

    <script>
    (function () {
        const sl = document.getElementById('stars-layer');
        if (!sl) return;
        Array.from({ length: 90 }, () => {
            const s = document.createElement('span');
            const sz = Math.random() * 2 + .8;
            s.style.cssText = `position:absolute;border-radius:50%;background:#fff;
                width:${sz}px;height:${sz}px;
                top:${Math.random()*100}%;left:${Math.random()*100}%;
                animation:star-twinkle ${2 + Math.random()*4}s ease-in-out ${Math.random()*5}s infinite;`;
            sl.appendChild(s);
        });
    })();
    </script>

</body>
</html>
