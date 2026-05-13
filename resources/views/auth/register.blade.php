<x-layout id="page-register">
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
            <p style="position:relative; z-index:1">Crie alertas, salve buscas favoritas e receba avisos quando um anúncio aparecer fora do padrão de preço × km.</p>
        </aside>

        <div class="auth-form">
            <div>
                <div class="t-up mb-2">Criar conta</div>
                <h2 class="display m-0" style="font-size:30px;font-weight:600">Em 30 segundos.</h2>
                <p class="page-sub">Pesquisar segue grátis. Conta é só para salvar e acompanhar oportunidades.</p>
            </div>

            <div class="card-curva p-3" style="background:var(--paper)">
                @foreach ([['Alertas salvos', 'Avisos por email quando um anúncio abaixo da curva aparecer.'], ['Histórico de busca', 'Compare valores ao longo do tempo.'], ['Notas privadas', 'Marque anúncios para revisitar.']] as [$title, $text])
                    <div class="d-flex gap-3 py-2 border-bottom" style="border-color:var(--line) !important">
                        <span class="curva-avatar" style="width:24px;height:24px;font-size:11px">{{ $loop->iteration }}</span>
                        <div><div class="fw-semibold" style="font-size:13px">{{ $title }}</div><div class="t-mute" style="font-size:12px">{{ $text }}</div></div>
                    </div>
                @endforeach
            </div>

            <form method="POST" action="{{ route('register') }}" class="d-flex flex-column gap-3">
                @csrf
                @if ($errors->any())<div class="badge badge--warn justify-content-start" style="white-space:normal">{{ $errors->first() }}</div>@endif
                <div class="field"><label class="field__label">Seu nome</label><input type="text" name="name" value="{{ old('name') }}" class="input" required autofocus placeholder="Guilherme"></div>
                <div class="field"><label class="field__label">Email</label><input type="email" name="email" value="{{ old('email') }}" class="input" required placeholder="seu@email.com"></div>
                <div class="field"><label class="field__label">Senha</label><input type="password" name="password" class="input" required placeholder="mínimo 8 caracteres"></div>
                <div class="field"><label class="field__label">Confirmar senha</label><input type="password" name="password_confirmation" class="input" required></div>
                <label class="d-flex gap-2 t-mute" style="font-size:12px;line-height:1.4"><input type="checkbox" required style="accent-color:var(--ink);margin-top:2px"> <span>Concordo com os termos e a política de privacidade.</span></label>
                <button type="submit" class="btn-curva btn-curva--primary btn-curva--lg w-100">Criar minha conta</button>
            </form>

            <div class="text-center t-mute" style="font-size:13px">Já tem conta? <a class="fw-semibold text-decoration-none" href="{{ route('login') }}">Entrar</a></div>
        </div>
    </section>
</x-layout>
