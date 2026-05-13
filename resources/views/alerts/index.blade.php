@php
    $activeCount = $alerts->where('active', true)->count();
    $newToday = $alerts->where('active', true)->count();
@endphp

<x-layout id="page-alerts">
    <section class="page-head" style="max-width:1320px;margin:0 auto;width:100%;padding-top:36px">
        <div>
            <div class="t-up">Seus alertas</div>
            <h1>{{ $activeCount }} buscas ativas <span class="t-good">· {{ $newToday }} novos hoje</span></h1>
            <div class="page-sub">Avisamos por email quando um anúncio aparecer abaixo da curva nos seus filtros.</div>
        </div>
        <a href="{{ route('alerts.create') }}" class="btn-curva btn-curva--primary btn-curva--lg">+ Novo alerta</a>
    </section>

    <div class="alerts-wrap">
        <section class="summary-grid">
            <div class="card-curva p-3"><div class="num-lg t-good">{{ $newToday }}</div><div class="t-up mt-1">novos hoje</div></div>
            <div class="card-curva p-3"><div class="num-lg">{{ $alerts->total() }}</div><div class="t-up mt-1">esta semana</div></div>
            <div class="card-curva p-3"><div class="num-lg t-good">R$ 12.4k</div><div class="t-up mt-1">economia média vs. curva</div></div>
            <div class="card-curva p-3"><div class="num-lg">92%</div><div class="t-up mt-1">taxa de relevância</div></div>
        </section>

        <section class="card-curva overflow-hidden">
            <div class="alert-row" style="background:var(--surface-2);font-size:11px;color:var(--mute);text-transform:uppercase;letter-spacing:.08em;font-weight:600">
                <span></span><span>Alerta</span><span class="text-end">Resultados</span><span>Atividade · 14d</span><span>Último hit</span><span></span>
            </div>
            @forelse ($alerts as $alert)
                @php
                    $filters = collect($alert->filters ?? [])->flatMap(fn ($value, $key) => is_array($value) ? collect($value)->map(fn ($item) => $item) : [$value])->filter()->take(5);
                    $matches = max(1, count($alert->filters ?? []) * 2);
                @endphp
                <div class="alert-row" style="{{ $alert->active ? '' : 'opacity:.6' }}">
                    <div><span class="dot {{ $alert->active ? 'dot--good dot--pulse' : 'dot--neutral' }}" style="width:8px;height:8px"></span></div>
                    <div>
                        <div class="fw-semibold" style="font-size:14px">{{ $alert->name }}</div>
                        <div class="d-flex flex-wrap gap-1 mt-2">
                            @forelse ($filters as $filter)
                                <span class="chip" style="font-family:var(--font-mono);font-size:11px;padding:2px 7px;background:var(--paper)">{{ $filter }}</span>
                            @empty
                                <span class="chip" style="font-family:var(--font-mono);font-size:11px;padding:2px 7px;background:var(--paper)">todos os filtros</span>
                            @endforelse
                        </div>
                    </div>
                    <div class="text-end"><div class="num-lg">{{ $matches }}</div><span class="badge badge--good">+{{ $alert->active ? 1 : 0 }} hoje</span></div>
                    <div>
                        <svg width="120" height="28" viewBox="0 0 120 28">
                            @foreach (range(0, 13) as $i)
                                <rect x="{{ $i * 8.5 }}" y="{{ 28 - (3 + (($i + $alert->id) * 7 % 18)) }}" width="6" height="{{ 3 + (($i + $alert->id) * 7 % 18) }}" fill="{{ $i === 13 ? '#1a6c4d' : '#8b7d5c' }}" opacity="{{ $i === 13 ? 1 : .5 }}" rx="1" />
                            @endforeach
                        </svg>
                    </div>
                    <div class="mono t-mute" style="font-size:12px">{{ $alert->active ? $alert->updated_at->diffForHumans(short: true) : 'pausado' }}</div>
                    <div class="text-end">
                        <form method="POST" action="{{ route('alerts.destroy', $alert) }}">
                            @csrf @method('DELETE')
                            <button class="btn-curva btn-curva--ghost btn-curva--sm" type="submit">remover</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-5 text-center">
                    <div class="display mb-2" style="font-size:24px;font-weight:600">Nenhum alerta ainda.</div>
                    <div class="t-mute mb-3">Salve uma busca para receber oportunidades abaixo da curva.</div>
                    <a class="btn-curva btn-curva--primary" href="{{ route('alerts.create') }}">Criar primeiro alerta</a>
                </div>
            @endforelse
        </section>

        <div class="mt-3">{{ $alerts->links() }}</div>
    </div>
</x-layout>
