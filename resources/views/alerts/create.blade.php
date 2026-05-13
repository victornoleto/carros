@php
    $flatFilters = collect($filters)->flatMap(fn ($value, $key) => is_array($value) ? collect($value)->map(fn ($item) => [$key, $item]) : [[$key, $value]])->filter(fn ($pair) => filled($pair[1]));
    $defaultName = $flatFilters->firstWhere(0, 'models')[1] ?? 'Busca abaixo da curva';
@endphp

<x-layout id="page-alert-create">
    <section style="max-width:1180px;margin:0 auto;width:100%;padding:36px 28px 0">
        <div class="d-flex align-items-center gap-2 t-mute mb-2" style="font-size:12px"><a class="text-decoration-none" href="{{ route('alerts.index') }}">Meus alertas</a><span>·</span><strong style="color:var(--ink)">Novo alerta</strong></div>
        <h1 class="display m-0" style="font-size:34px;font-weight:600;letter-spacing:-.03em">Confirmar e nomear</h1>
        <div class="page-sub">Confira os filtros da sua busca atual. Você sempre pode editar depois.</div>
    </section>

    <form method="POST" action="{{ route('alerts.store') }}" class="alert-create-grid">
        @csrf
        <section class="card-curva p-4 d-flex flex-column gap-4">
            @if ($errors->any())<div class="badge badge--warn justify-content-start" style="white-space:normal">{{ $errors->first() }}</div>@endif
            <div class="field">
                <label class="field__label">Nome do alerta</label>
                <input type="text" name="name" value="{{ old('name', $defaultName) }}" class="input" required style="font-size:16px">
                <div class="t-mute" style="font-size:11px">Aparece no email e na lista. Pode ser qualquer coisa.</div>
            </div>

            <div>
                <div class="t-up mb-3">Filtros da busca</div>
                <div class="card-curva overflow-hidden">
                    @forelse ($flatFilters as [$key, $value])
                        <div class="filter-row"><span class="t-up">{{ str_replace('_', ' ', $key) }}</span><span class="mono">{{ $value }}</span><a class="t-mute text-end text-decoration-none" href="{{ route('dashboard', $filters) }}">editar</a></div>
                    @empty
                        <div class="filter-row"><span class="t-up">Busca</span><span class="mono">Todos os anúncios ativos</span><a class="t-mute text-end text-decoration-none" href="{{ route('dashboard') }}">editar</a></div>
                    @endforelse
                    <div class="filter-row"><span class="t-up">Posição na curva</span><span class="mono"><span class="dot dot--good me-2"></span>≥ 10% abaixo</span><a class="t-mute text-end text-decoration-none" href="{{ route('dashboard', $filters) }}">editar</a></div>
                </div>
                <a class="btn-curva btn-curva--ghost btn-curva--sm mt-3" href="{{ route('dashboard', $filters) }}">← Voltar e ajustar filtros</a>
            </div>

            <div>
                <div class="t-up mb-3">Frequência</div>
                <div class="d-flex gap-2">
                    @foreach ([['Tempo real', 'avisa em até 5 min'], ['Diário', 'resumo às 8h'], ['Semanal', 'segunda · 8h']] as [$title, $sub])
                        <label class="card-curva p-3 flex-fill" style="cursor:pointer;background:{{ $loop->first ? 'var(--paper)' : 'var(--surface)' }};border-color:{{ $loop->first ? 'var(--ink)' : 'var(--line)' }}">
                            <input type="radio" name="frequency" value="{{ strtolower(str_replace(' ', '-', $title)) }}" class="d-none" {{ $loop->first ? 'checked' : '' }}>
                            <span class="fw-semibold" style="font-size:13px">{{ $title }}</span><br><span class="t-mute" style="font-size:11px">{{ $sub }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            @foreach ($filters as $key => $value)
                @if (is_array($value))
                    @foreach ($value as $item)<input type="hidden" name="filters[{{ $key }}][]" value="{{ $item }}">@endforeach
                @else
                    <input type="hidden" name="filters[{{ $key }}]" value="{{ $value }}">
                @endif
            @endforeach
            <input type="hidden" name="filters[min_score]" value="0.10">
        </section>

        <aside class="d-flex flex-column gap-3">
            <div class="card-curva p-4" style="background:var(--ink);color:var(--paper)">
                <div class="mono mb-3" style="font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:rgba(243,240,232,.55)">pré-visualização</div>
                <div class="display" style="font-size:22px;font-weight:600;line-height:1.15">{{ old('name', $defaultName) }}</div>
                <p style="font-size:13px;color:rgba(243,240,232,.7);line-height:1.5;margin-top:14px">Vamos avisar em <strong style="color:var(--paper)">tempo real</strong> quando um anúncio compatível aparecer <strong style="color:#4ddb98">≥10% abaixo da curva</strong>.</p>
                <div class="row g-3 mt-2 pt-3" style="border-top:1px solid rgba(243,240,232,.12)">
                    <div class="col-6"><div class="display" style="font-size:22px;font-weight:600;color:#4ddb98">9</div><div class="mono" style="font-size:10px;color:rgba(243,240,232,.55);text-transform:uppercase">hits agora</div></div>
                    <div class="col-6"><div class="display" style="font-size:22px;font-weight:600">3/sem</div><div class="mono" style="font-size:10px;color:rgba(243,240,232,.55);text-transform:uppercase">cadência típica</div></div>
                    <div class="col-6"><div class="display" style="font-size:22px;font-weight:600;color:#4ddb98">R$ 14k</div><div class="mono" style="font-size:10px;color:rgba(243,240,232,.55);text-transform:uppercase">economia média</div></div>
                    <div class="col-6"><div class="display" style="font-size:22px;font-weight:600;color:#4ddb98">-12%</div><div class="mono" style="font-size:10px;color:rgba(243,240,232,.55);text-transform:uppercase">vs. curva</div></div>
                </div>
            </div>
            <div class="card-curva p-3"><div class="t-up mb-2">Top match agora</div><div class="fw-semibold">Honda Civic Touring 1.5 Turbo</div><div class="t-mute mb-3" style="font-size:11px">2021 · 48.300 km · Campinas/SP · Webmotors</div><div class="d-flex justify-content-between align-items-baseline"><span><span class="display t-mute">R$</span> <span class="num-lg">104.900</span></span><span class="badge badge--good">↓ −14% curva</span></div></div>
            <div class="d-flex gap-2"><a class="btn-curva btn-curva--ghost flex-fill" href="{{ route('alerts.index') }}">Cancelar</a><button class="btn-curva btn-curva--good btn-curva--lg flex-grow-1" type="submit">Criar alerta</button></div>
        </aside>
    </form>
</x-layout>
