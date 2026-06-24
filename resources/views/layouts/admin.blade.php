<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel Admin') — PiCBT</title>

    {{-- PWA --}}
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#7c6af6">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="PiCBT">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/icons/favicon-32.png">
    <link rel="icon" type="image/svg+xml" href="/icons/icon.svg">

    {{-- Prevent dark-mode flash: apply theme before paint --}}
    <script>
        (function(){
            var t = localStorage.getItem('theme');
            if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches))
                document.documentElement.classList.add('dark');
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')

    <style>
        /* Sidebar gradient background */
        .sidebar-bg {
            background: linear-gradient(180deg, #1a1535 0%, #0f0d25 100%);
        }
        .dark .sidebar-bg {
            background: linear-gradient(180deg, #111128 0%, #0a0a1c 100%);
        }
    </style>
</head>
<body class="h-full bg-surface-50 dark:bg-surface-950 text-surface-800 dark:text-surface-100 font-sans antialiased transition-colors duration-200"
      x-data="{ ...darkMode(), sidebarOpen: false }" x-init="init()">

{{-- ── Progress bar ── --}}
<div id="nprogress-bar"></div>

{{-- ── Page loader ── --}}
<div id="page-loader">
    <div class="flex flex-col items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-primary-600 flex items-center justify-center shadow-soft-md">
            <svg class="w-5 h-5 text-white animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
        </div>
        <span class="text-xs text-surface-400 font-medium tracking-wide">Memuat...</span>
    </div>
</div>

<div class="flex h-full">

    {{-- ════════════════════════════════════════
         SIDEBAR
    ════════════════════════════════════════ --}}
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="sidebar-bg fixed inset-y-0 left-0 z-40 w-64 flex flex-col
                  transition-transform duration-200 ease-soft lg:static lg:flex
                  border-r border-white/5">

        {{-- Logo --}}
        <div class="flex items-center gap-3 px-5 py-5 border-b border-white/8">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center shadow-soft-md flex-none"
                 style="background: linear-gradient(135deg,#7c6af6 0%,#a78bfa 100%)">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <span class="font-bold text-white text-base tracking-tight">PiCBT</span>
                <p class="text-[10px] text-white/40 leading-none mt-0.5">Admin Panel</p>
            </div>
        </div>

        {{-- Navigation --}}
        @php
            $isMasterActive = request()->routeIs('admin.students.*') || request()->routeIs('admin.subjects.*');
            $icons = [
                'dashboard' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
                'master'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>',
                'students'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>',
                'subjects'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>',
                'bank'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/>',
                'exam'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>',
                'results'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
                'settings'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
            ];
        @endphp
        <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto scrollbar-thin">

            {{-- Dashboard --}}
            @php $active = request()->routeIs('admin.dashboard'); @endphp
            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all duration-150 group relative
                      {{ $active ? 'nav-item-active' : 'text-white/50 hover:text-white hover:bg-white/6' }}">
                @if($active)<span class="absolute left-0 top-1/2 -translate-y-1/2 w-0.5 h-5 bg-primary-400 rounded-r"></span>@endif
                <svg class="flex-none {{ $active ? 'text-primary-400' : 'text-white/40 group-hover:text-white/70' }}"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" style="width:18px;height:18px">
                    {!! $icons['dashboard'] !!}
                </svg>
                <span>Dashboard</span>
            </a>

            {{-- Master Data (collapsible group) --}}
            <div x-data="{ open: {{ $isMasterActive ? 'true' : 'false' }} }">
                <button @click="open = !open"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all duration-150 group relative
                               {{ $isMasterActive ? 'nav-item-active' : 'text-white/50 hover:text-white hover:bg-white/6' }}">
                    @if($isMasterActive)<span class="absolute left-0 top-1/2 -translate-y-1/2 w-0.5 h-5 bg-primary-400 rounded-r"></span>@endif
                    <svg class="flex-none {{ $isMasterActive ? 'text-primary-400' : 'text-white/40 group-hover:text-white/70' }}"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" style="width:18px;height:18px">
                        {!! $icons['master'] !!}
                    </svg>
                    <span class="flex-1 text-left">Master Data</span>
                    <svg :class="open ? 'rotate-180' : ''" class="transition-transform duration-150 text-white/30" style="width:14px;height:14px"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="open" x-collapse class="mt-0.5 ml-4 pl-3 border-l border-white/10 space-y-0.5">
                    @php $activeSt = request()->routeIs('admin.students.*'); @endphp
                    <a href="{{ route('admin.students.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm transition-all duration-150 group relative
                              {{ $activeSt ? 'nav-item-active' : 'text-white/45 hover:text-white hover:bg-white/6' }}">
                        @if($activeSt)<span class="absolute left-0 top-1/2 -translate-y-1/2 w-0.5 h-4 bg-primary-400 rounded-r"></span>@endif
                        <svg class="flex-none {{ $activeSt ? 'text-primary-400' : 'text-white/35 group-hover:text-white/60' }}"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" style="width:16px;height:16px">
                            {!! $icons['students'] !!}
                        </svg>
                        <span>Siswa</span>
                    </a>

                    @php $activeSub = request()->routeIs('admin.subjects.*'); @endphp
                    <a href="{{ route('admin.subjects.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm transition-all duration-150 group relative
                              {{ $activeSub ? 'nav-item-active' : 'text-white/45 hover:text-white hover:bg-white/6' }}">
                        @if($activeSub)<span class="absolute left-0 top-1/2 -translate-y-1/2 w-0.5 h-4 bg-primary-400 rounded-r"></span>@endif
                        <svg class="flex-none {{ $activeSub ? 'text-primary-400' : 'text-white/35 group-hover:text-white/60' }}"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" style="width:16px;height:16px">
                            {!! $icons['subjects'] !!}
                        </svg>
                        <span>Mata Pelajaran</span>
                    </a>
                </div>
            </div>

            {{-- Menu lainnya --}}
            @php
                $navRest = [
                    ['route' => 'admin.banks.index',    'label' => 'Bank Soal',   'icon' => 'bank'],
                    ['route' => 'admin.exams.index',    'label' => 'Ujian',       'icon' => 'exam'],
                    ['route' => 'admin.results.index',  'label' => 'Rekap Nilai', 'icon' => 'results'],
                    ['route' => 'admin.settings.index', 'label' => 'Pengaturan',  'icon' => 'settings'],
                ];
            @endphp
            @foreach ($navRest as $item)
                @php $active = request()->routeIs($item['route']); @endphp
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all duration-150 group relative
                          {{ $active ? 'nav-item-active' : 'text-white/50 hover:text-white hover:bg-white/6' }}">
                    @if($active)<span class="absolute left-0 top-1/2 -translate-y-1/2 w-0.5 h-5 bg-primary-400 rounded-r"></span>@endif
                    <svg class="flex-none {{ $active ? 'text-primary-400' : 'text-white/40 group-hover:text-white/70' }}"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" style="width:18px;height:18px">
                        {!! $icons[$item['icon']] !!}
                    </svg>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach

        </nav>

        {{-- Divider --}}
        <div class="mx-4 border-t border-white/8"></div>

        {{-- User info --}}
        <div class="px-3 py-4">
            <div class="flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-white/5 transition-colors group">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold text-white flex-none"
                     style="background: linear-gradient(135deg, #7c6af6, #a78bfa)">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white/85 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-white/35 capitalize">{{ auth()->user()->role }}</p>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}" data-spa-ignore>
                    @csrf
                    <button type="submit"
                            class="text-white/30 hover:text-red-400 transition-colors p-1 rounded-lg hover:bg-red-500/10"
                            title="Logout">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Sidebar overlay (mobile) --}}
    <div x-show="sidebarOpen" @click="sidebarOpen = false"
         x-transition:enter="transition-opacity duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-30 bg-black/60 backdrop-blur-sm lg:hidden" x-cloak></div>

    {{-- ════════════════════════════════════════
         MAIN CONTENT
    ════════════════════════════════════════ --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        {{-- Topbar --}}
        <header class="flex-none bg-white/80 dark:bg-surface-900/80 backdrop-blur-md border-b border-surface-100 dark:border-surface-800
                        px-4 py-3 flex items-center gap-4 sticky top-0 z-20">
            {{-- Mobile menu button --}}
            <button @click="sidebarOpen = !sidebarOpen"
                    class="lg:hidden p-2 rounded-xl text-surface-500 hover:text-surface-800 dark:hover:text-surface-100 hover:bg-surface-100 dark:hover:bg-surface-800 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- Page title --}}
            <h1 id="spa-title" class="flex-1 font-semibold text-surface-800 dark:text-surface-100 text-base">
                @yield('page-title', 'Dashboard')
            </h1>

            {{-- Right side --}}
            <div class="flex items-center gap-2">
                {{-- Date --}}
                <div class="hidden sm:block text-xs text-surface-400 dark:text-surface-500 mr-2">
                    {{ now()->isoFormat('dddd, D MMMM Y') }}
                </div>

                {{-- Dark / Light mode toggle --}}
                <button @click="toggle()"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl border transition-all duration-150
                               border-surface-200 dark:border-surface-700
                               bg-surface-50 hover:bg-surface-100 dark:bg-surface-800 dark:hover:bg-surface-700
                               text-surface-600 dark:text-surface-300 text-xs font-medium">
                    {{-- Sun (selalu render, tampil/sembunyi via CSS class) --}}
                    <svg :class="dark ? 'block' : 'hidden'" style="width:15px;height:15px;flex-shrink:0"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    {{-- Moon --}}
                    <svg :class="dark ? 'hidden' : 'block'" style="width:15px;height:15px;flex-shrink:0"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    <span x-text="dark ? 'Terang' : 'Gelap'"></span>
                </button>
            </div>
        </header>

        {{-- Flash messages (swapped by SPA on navigation) --}}
        <div id="spa-flash" class="flex-none">
        @if (session('success') || session('error') || session('info'))
        <div class="px-6 pt-4 space-y-2">
            @if (session('success'))
                <div x-data="{ show: true }" x-show="show"
                     x-init="setTimeout(() => show = false, 4500)"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="flex items-center gap-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800
                            text-emerald-800 dark:text-emerald-300 rounded-xl px-4 py-3 text-sm shadow-soft" x-cloak>
                    <svg class="w-4 h-4 flex-none text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span class="flex-1">{{ session('success') }}</span>
                    <button @click="show = false" class="text-emerald-500 hover:text-emerald-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            @endif
            @if (session('error'))
                <div x-data="{ show: true }" x-show="show"
                     x-init="setTimeout(() => show = false, 5500)"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="flex items-center gap-3 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800
                            text-red-800 dark:text-red-300 rounded-xl px-4 py-3 text-sm shadow-soft" x-cloak>
                    <svg class="w-4 h-4 flex-none text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="flex-1">{{ session('error') }}</span>
                    <button @click="show = false" class="text-red-500 hover:text-red-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            @endif
            @if (session('info'))
                <div x-data="{ show: true }" x-show="show"
                     x-init="setTimeout(() => show = false, 4500)"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="flex items-center gap-3 bg-primary-50 dark:bg-primary-950/40 border border-primary-200 dark:border-primary-800
                            text-primary-800 dark:text-primary-300 rounded-xl px-4 py-3 text-sm shadow-soft" x-cloak>
                    <svg class="w-4 h-4 flex-none text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="flex-1">{{ session('info') }}</span>
                    <button @click="show = false" class="text-primary-500 hover:text-primary-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            @endif
        </div>
        @endif
        </div>

        {{-- Content --}}
        <main id="spa-main" class="flex-1 overflow-y-auto px-6 py-5 scrollbar-thin">
            <div id="spa-content" class="page-enter max-w-screen-xl mx-auto">
                @yield('content')
            </div>
        </main>
    </div>
</div>

@stack('modals')
<div id="spa-scripts" style="display:none">@stack('scripts')</div>

<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    });
}
</script>
</body>
</html>
