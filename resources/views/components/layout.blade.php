@props([
    'id' => ''
])

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Curva</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="curva-shell">

    <nav class="curva-topbar">
        <a class="curva-logo" href="{{ route('dashboard') }}" aria-label="Curva">
            <svg width="28" height="28" viewBox="0 0 28 28" fill="none" aria-hidden="true">
                <rect x="0.75" y="0.75" width="26.5" height="26.5" rx="6" stroke="currentColor" stroke-opacity="0.18" />
                <path d="M3 21 C 9 18, 14 12, 25 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" fill="none" />
                <circle cx="6" cy="22" r="1.2" fill="currentColor" opacity="0.35" />
                <circle cx="11" cy="16" r="1.2" fill="currentColor" opacity="0.35" />
                <circle cx="17" cy="11" r="1.2" fill="currentColor" opacity="0.35" />
                <circle cx="22" cy="7" r="1.2" fill="currentColor" opacity="0.35" />
                <circle cx="9" cy="24" r="2" fill="#1a6c4d" />
            </svg>
            <span>curva</span>
        </a>

        <div class="curva-nav">
            <a class="{{ request()->routeIs('dashboard') ? 'is-active' : '' }}" href="{{ route('dashboard') }}">Mapa</a>
            <a class="{{ request()->routeIs('table', 'listings') ? 'is-active' : '' }}" href="{{ route('listings') }}">Anúncios</a>
            @auth
                <a class="{{ request()->routeIs('alerts.*') ? 'is-active' : '' }}" href="{{ route('alerts.index') }}">Alertas</a>
            @else
                <a href="{{ route('login') }}">Alertas</a>
            @endauth
        </div>

        <div class="curva-topbar__meta">
            <span class="dot dot--good dot--pulse"></span>
            <span class="mono">anúncios · atualizado agora</span>
        </div>

        @auth
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button class="btn-curva btn-curva--ghost btn-curva--sm" type="submit">Sair</button>
            </form>
            <div class="curva-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
        @else
            <a class="btn-curva btn-curva--ghost btn-curva--sm" href="{{ route('login') }}">Entrar</a>
            <a class="btn-curva btn-curva--primary btn-curva--sm" href="{{ route('register') }}">Criar conta</a>
        @endauth
    </nav>

    <main id="{{ $id }}" class="curva-main">

       {{ $slot }}

    </main>

</body>
</html>
