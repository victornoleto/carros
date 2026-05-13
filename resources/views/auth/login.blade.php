<x-layout id="page-login">
    <section class="auth-shell">
        <aside class="auth-hero">
            <svg class="scatter-decor" width="100%" height="100%" preserveAspectRatio="none" viewBox="0 0 600 800">
                <path d="M 60 700 Q 250 500, 540 100" stroke="rgba(243,240,232,0.6)" stroke-width="1" fill="none" stroke-dasharray="3 4" />
                @foreach (range(0, 60) as $i)
                    @php $x = 40 + ($i * 51 % 540); $y = 80 + ($i * 137 % 680); $green = $y > (700 - (($x - 60) / 480) * 600) + 40; @endphp
                    <circle cx="{{ $x }}" cy="{{ $y }}" r="{{ 2 + ($i % 4) }}" fill="{{ $green ? '#4ddb98' : 'rgba(243,240,232,0.55)' }}" />
                @endforeach
            </svg>
            <div style="position:relative; z-index:1" class="curva-logo"><span style="color:var(--paper)">curva</span></div>
            <h1>Bons negócios ficam <span style="color:#4ddb98">abaixo da curva.</span></h1>
            <p style="position:relative; z-index:1">Curva agrega anúncios, normaliza os dados e mostra quais saem do padrão de preço × km. Pesquisar é grátis. Salvar alertas precisa de conta.</p>
            <div class="d-flex gap-4" style="position:relative; z-index:1">
                <div><div class="display" style="font-size:28px;font-weight:600">12.487</div><div class="mono" style="font-size:10px;color:rgba(243,240,232,.55);text-transform:uppercase;letter-spacing:.08em">anúncios indexados</div></div>
                <div><div class="display" style="font-size:28px;font-weight:600;color:#4ddb98">33</div><div class="mono" style="font-size:10px;color:rgba(243,240,232,.55);text-transform:uppercase;letter-spacing:.08em">abaixo da curva hoje</div></div>
                <div><div class="display" style="font-size:28px;font-weight:600">4</div><div class="mono" style="font-size:10px;color:rgba(243,240,232,.55);text-transform:uppercase;letter-spacing:.08em">fontes</div></div>
            </div>
        </aside>

        <div class="auth-form">
            <div>
                <div class="t-up mb-2">Entrar</div>
                <h2 class="display m-0" style="font-size:32px;font-weight:600">Continue sua busca.</h2>
                <p class="page-sub">Sua conta serve para salvar buscas como alertas e receber novos anúncios abaixo da curva.</p>
            </div>

            <button class="btn-curva btn-curva--ghost btn-curva--lg w-100" type="button">Continuar com Google</button>
            <div class="divider">ou com email</div>

            <form method="POST" action="{{ route('login') }}" class="d-flex flex-column gap-3">
                @csrf
                @if ($errors->any())<div class="badge badge--warn justify-content-start" style="white-space:normal">{{ $errors->first() }}</div>@endif
                <div class="field"><label class="field__label">Email</label><input type="email" name="email" value="{{ old('email') }}" class="input" required autofocus placeholder="seu@email.com"></div>
                <div class="field"><div class="d-flex justify-content-between"><label class="field__label">Senha</label><a class="t-mute text-decoration-none" style="font-size:12px" href="#">esqueci</a></div><input type="password" name="password" class="input" required></div>
                <label class="d-flex align-items-center gap-2 t-mute" style="font-size:13px"><input type="checkbox" name="remember" value="1" style="accent-color:var(--ink)"> Lembrar deste dispositivo</label>
                <button type="submit" class="btn-curva btn-curva--primary btn-curva--lg w-100">Entrar</button>
            </form>

            <div class="text-center t-mute" style="font-size:13px">Primeira vez por aqui? <a class="fw-semibold text-decoration-none" href="{{ route('register') }}">Criar conta grátis</a></div>
        </div>
    </section>
</x-layout>
