@php
    $scoreFor = function ($car) {
        $age = max(0, now()->year - (int) $car->year);
        $fair = max(((float) $car->price) * 1.08 * pow(0.96, $age) - ($car->odometer * 0.02), ((float) $car->price) * 0.72);
        $score = ($fair - $car->price) / max(1, $fair);
        return [$fair, $score, $score >= 0.04 ? 'good' : ($score <= -0.04 ? 'warn' : 'neutral')];
    };
@endphp

<x-layout id="page-table">
    <section class="page-head">
        <div>
            <div class="t-up">Anúncios agregados</div>
            <h1>{{ number_format($cars->total(), 0, ',', '.') }} oportunidades <span class="t-mute">de múltiplas fontes</span></h1>
            <div class="page-sub">Ordenado por atualização e preço. Use a coluna <strong>vs. curva</strong> como sinal primário.</div>
        </div>
        <form class="d-flex gap-2 align-items-center" action="{{ route('listings') }}">
            <input class="input" name="q" value="{{ request('q') }}" placeholder="Buscar modelo ou versão" style="width:260px">
            <button class="btn-curva btn-curva--ghost btn-curva--sm">Filtros</button>
            <a class="btn-curva btn-curva--primary btn-curva--sm" href="{{ auth()->check() ? route('alerts.create', request()->query()) : route('login') }}">Salvar busca</a>
        </form>
    </section>

    <section class="active-strip" style="border-bottom: 0">
        @if ($databaseUnavailable ?? false)
            <span class="badge badge--warn" style="white-space:normal">Banco indisponível. Confira as variáveis DB_* no .env e rode as migrations.</span>
        @endif
        <span class="t-up me-1">Atalhos</span>
        @foreach (['Abaixo da curva', '≤ 80k km', '2020+', 'Particular', 'SP capital', 'Civic Touring', 'Recém-listados'] as $index => $chip)
            <span class="chip {{ $index < 2 ? 'chip--active' : '' }}">{{ $chip }}</span>
        @endforeach
    </section>

    <section class="px-4 pb-4 desktop-table">
        <div class="card-curva overflow-hidden">
            <table class="tbl">
                <colgroup>
                    <col style="width:80px"><col><col style="width:80px"><col style="width:140px"><col style="width:120px"><col style="width:160px"><col style="width:150px"><col style="width:120px"><col style="width:120px"><col style="width:90px">
                </colgroup>
                <thead>
                    <tr>
                        <th>foto</th>
                        <th>Marca · Modelo · Versão</th>
                        <th class="text-end">Ano</th>
                        <th class="text-end">Preço</th>
                        <th class="text-end">Km</th>
                        <th class="text-end">vs. curva</th>
                        <th>Cidade · UF</th>
                        <th>Provider</th>
                        <th>Atualizado</th>
                        <th class="text-end"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cars as $car)
                        @php
                            [$fair, $score, $tone] = $scoreFor($car);
                            $arrow = $score >= 0.04 ? '↓' : ($score <= -0.04 ? '↑' : '·');
                            $label = abs($score) >= 0.04 ? (($score >= 0.04 ? '−' : '+').abs(round($score * 100)).'% curva') : 'na curva';
                            $color = $tone === 'good' ? '#1a6c4d' : ($tone === 'warn' ? '#b04421' : '#8b7d5c');
                        @endphp
                        <tr>
                            <td><div class="photo-stub"></div></td>
                            <td><div class="fw-semibold" style="font-size:14px">{{ mb_strtoupper($car->brand) }} {{ mb_strtoupper($car->model) }}</div><div class="t-mute" style="font-size:12px">{{ mb_strtoupper($car->version ?? '-') }}</div></td>
                            <td class="text-end"><span class="mono">{{ $car->year }}</span></td>
                            <td class="text-end"><div><span class="display t-mute" style="font-size:12px">R$</span> <span class="num-md">{{ number_format($car->price, 0, ',', '.') }}</span></div><div class="mono t-mute" style="font-size:10px">justo R$ {{ number_format($fair / 1000, 1, ',', '.') }}k</div></td>
                            <td class="text-end"><span class="mono">{{ number_format($car->odometer, 0, ',', '.') }}</span></td>
                            <td class="text-end">
                                <div class="d-inline-flex align-items-center gap-2">
                                    <svg width="48" height="20" viewBox="0 0 48 20" aria-hidden="true"><path d="M 2 4 Q 20 9, 46 17" stroke="rgba(20,17,13,0.18)" stroke-width="1.25" fill="none"/><circle cx="30" cy="{{ max(4, min(16, 10 + (-$score * 24))) }}" r="2.6" fill="{{ $color }}"/></svg>
                                    <span class="badge badge--{{ $tone }}">{{ $arrow }} {{ $label }}</span>
                                </div>
                            </td>
                            <td>{{ $car->city }} <span class="t-mute" style="font-size:11px">· {{ $car->state }}</span></td>
                            <td><span class="mono t-mute" style="font-size:12px">{{ $car->provider }}</span></td>
                            <td><span class="t-mute" style="font-size:12px">{{ $car->provider_updated_at->diffForHumans(short: true) }}</span></td>
                            <td class="text-end"><a class="btn-curva btn-curva--ghost btn-curva--sm" href="{{ route('provider.redirect', $car->id) }}" target="_blank">abrir ↗</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center t-mute py-5">Nenhum anúncio encontrado para os filtros atuais.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="d-flex align-items-center justify-content-between p-3" style="background: var(--surface-2); border-top: 1px solid var(--line)">
                <span class="mono t-mute" style="font-size:12px">{{ $cars->firstItem() ?? 0 }}–{{ $cars->lastItem() ?? 0 }} de {{ number_format($cars->total(), 0, ',', '.') }}</span>
                <div>{{ $cars->links() }}</div>
            </div>
        </div>
    </section>
</x-layout>
