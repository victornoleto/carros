@php
    $median = function ($values) {
        $values = collect($values)->filter(fn ($value) => is_numeric($value))->sort()->values();
        $count = $values->count();
        if ($count === 0) return 0;
        $middle = intdiv($count, 2);
        return $count % 2 ? $values[$middle] : (($values[$middle - 1] + $values[$middle]) / 2);
    };
    $medianPrice = max(1, $median($cars->pluck('price')));
    $medianKm = max(1, $median($cars->pluck('odometer')));
    $scored = $cars->map(function ($car) use ($medianPrice, $medianKm) {
        $age = max(0, now()->year - (int) $car->year);
        $fair = max($medianPrice * pow(0.94, $age) + (($medianKm - $car->odometer) * 0.08), $medianPrice * 0.42);
        $score = ($fair - $car->price) / max(1, $fair);
        $car->fair_price = $fair;
        $car->curve_score = $score;
        $car->curve_tone = $score >= 0.04 ? 'good' : ($score <= -0.04 ? 'warn' : 'neutral');
        return $car;
    })->sortByDesc('curve_score')->values();
    $good = $scored->where('curve_score', '>=', 0.04);
    $fairCount = $scored->filter(fn ($car) => abs($car->curve_score) < 0.04)->count();
    $highCount = $scored->where('curve_score', '<=', -0.04)->count();
    $topDeals = $good->take(6);
    $models = $scored->groupBy(fn ($car) => mb_strtoupper($car->brand).' '.mb_strtoupper($car->model))->take(4);
@endphp

<x-layout id="page-dashboard">
    <section class="page-head">
        <div>
            <div class="t-up">Mapa de oportunidades</div>
            <h1>Carros monitorados <span class="t-mute">· {{ number_format($scored->count(), 0, ',', '.') }} anúncios</span></h1>
            <div class="page-sub">
                {{ $good->count() }} anúncios abaixo da curva agora.
                <a href="{{ auth()->check() ? route('alerts.create', request()->query()) : route('login') }}" class="t-good fw-semibold text-decoration-none">Salvar alerta →</a>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a class="btn-curva btn-curva--ghost btn-curva--sm" href="{{ route('listings', request()->query()) }}">Ver tabela</a>
            <a class="btn-curva btn-curva--primary btn-curva--sm" href="{{ auth()->check() ? route('alerts.create', request()->query()) : route('login') }}">Salvar busca</a>
        </div>
    </section>

    <section class="kpi-strip">
        <div class="kpi"><div class="t-up">Anúncios</div><div class="num-lg mt-2">{{ number_format($totalCars ?? $scored->count(), 0, ',', '.') }}</div></div>
        <div class="kpi"><div class="t-up">Preço mediano</div><div class="num-lg mt-2">R$ {{ number_format($medianPrice / 1000, 1, ',', '.') }}k</div></div>
        <div class="kpi"><div class="t-up">Km mediano</div><div class="num-lg mt-2">{{ number_format($medianKm / 1000, 0, ',', '.') }}k</div></div>
        <div class="kpi"><div class="t-up">Abaixo da curva</div><div class="num-lg mt-2 t-good">{{ $good->count() }}</div></div>
        <div class="kpi"><div class="t-up">Atualizado</div><div class="mono mt-2" style="font-size:18px">agora</div></div>
    </section>

    <section class="active-strip">
        @if ($databaseUnavailable ?? false)
            <span class="badge badge--warn" style="white-space:normal">Banco indisponível. Confira as variáveis DB_* no .env e rode as migrations.</span>
        @endif
        <span class="t-up me-1">Ativos</span>
        @foreach ((array) request('models', []) as $model)
            <span class="chip chip--active">Modelo · {{ $model }} <span>×</span></span>
        @endforeach
        @if (request('year_min') || request('year_max'))<span class="chip chip--active">Ano · {{ request('year_min', 'min') }}–{{ request('year_max', 'max') }} <span>×</span></span>@endif
        @if (request('price_min') || request('price_max'))<span class="chip chip--active">Preço · {{ request('price_min', 'min') }}–{{ request('price_max', 'max') }}k <span>×</span></span>@endif
        @if (! request()->hasAny(['models', 'year_min', 'year_max', 'price_min', 'price_max']))
            <span class="chip chip--active">Todos os modelos</span>
            <span class="chip">abaixo da curva</span>
        @endif
        <span class="flex-grow-1"></span>
        <a class="btn-curva btn-curva--ghost btn-curva--sm" href="{{ route('dashboard') }}">Limpar tudo</a>
    </section>

    <section class="dashboard-grid">
        <aside class="side-panel">
            <x-filters buttonType="submit" />
        </aside>

        <div class="plot-area">
            <div class="card-curva scatter-wrap">
                <svg width="760" height="460" viewBox="0 0 760 460" role="img" aria-label="Gráfico de preço por quilometragem">
                    @php
                        $padL = 56; $padT = 24; $plotW = 680; $plotH = 392;
                        $maxKm = max(220000, $scored->max('odometer') ?: 220000);
                        $maxPrice = max(170000, $scored->max('price') ?: 170000);
                        $xScale = fn ($km) => $padL + min(1, max(0, $km / $maxKm)) * $plotW;
                        $yScale = fn ($price) => $padT + (1 - min(1, max(0, $price / $maxPrice))) * $plotH;
                        $curve = collect(range(0, 40))->map(function ($i) use ($xScale, $yScale, $maxKm, $medianPrice) {
                            $km = $maxKm * ($i / 40);
                            $price = max($medianPrice * 0.42, $medianPrice * exp(-$km / 200000));
                            return round($xScale($km), 1).' '.round($yScale($price), 1);
                        })->implode(' L ');
                    @endphp
                    <rect x="{{ $padL }}" y="{{ $padT }}" width="{{ $plotW }}" height="{{ $plotH }}" fill="var(--surface-2)" />
                    @foreach ([0, 50000, 100000, 150000, 200000] as $km)
                        <line x1="{{ $xScale($km) }}" x2="{{ $xScale($km) }}" y1="{{ $padT }}" y2="{{ $padT + $plotH }}" stroke="var(--line)" stroke-dasharray="2 4" />
                        <text x="{{ $xScale($km) }}" y="438" font-size="10" fill="var(--mute)" text-anchor="middle" font-family="var(--font-mono)">{{ $km / 1000 }}k km</text>
                    @endforeach
                    @foreach ([40000, 80000, 120000, 160000] as $price)
                        <line x1="{{ $padL }}" x2="{{ $padL + $plotW }}" y1="{{ $yScale($price) }}" y2="{{ $yScale($price) }}" stroke="var(--line)" stroke-dasharray="2 4" />
                        <text x="48" y="{{ $yScale($price) + 4 }}" font-size="10" fill="var(--mute)" text-anchor="end" font-family="var(--font-mono)">{{ $price / 1000 }}k</text>
                    @endforeach
                    <path d="M {{ $padL }} {{ $padT + $plotH }} L {{ $curve }} L {{ $padL + $plotW }} {{ $padT + $plotH }} Z" fill="rgba(26,108,77,0.04)" />
                    <path d="M {{ $curve }}" stroke="var(--mute)" stroke-width="1.3" fill="none" stroke-dasharray="4 3" />
                    <text x="470" y="120" font-size="10" fill="var(--mute)" font-family="var(--font-mono)">curva mediana</text>
                    @foreach ($scored->take(180) as $car)
                        @php
                            $color = $car->curve_tone === 'good' ? '#1a6c4d' : ($car->curve_tone === 'warn' ? '#b04421' : '#8b7d5c');
                            $radius = abs($car->curve_score) >= 0.10 ? 4.5 : 3.5;
                        @endphp
                        <a href="{{ route('provider.redirect', $car->id) }}" target="_blank">
                            <circle cx="{{ $xScale($car->odometer) }}" cy="{{ $yScale($car->price) }}" r="{{ $radius }}" fill="{{ $color }}" opacity="0.85">
                                <title>{{ mb_strtoupper($car->brand) }} {{ mb_strtoupper($car->model) }} · R$ {{ number_format($car->price, 0, ',', '.') }} · {{ number_format($car->odometer, 0, ',', '.') }} km</title>
                            </circle>
                        </a>
                    @endforeach
                    <g transform="translate(516, 34)">
                        <rect width="220" height="58" rx="6" fill="var(--surface)" stroke="var(--line)" />
                        <circle cx="14" cy="18" r="4" fill="#1a6c4d" /><text x="24" y="22" font-size="11" fill="var(--ink)">abaixo da curva</text><text x="200" y="22" font-size="11" fill="var(--mute)" font-family="var(--font-mono)" text-anchor="end">{{ $good->count() }}</text>
                        <circle cx="14" cy="36" r="4" fill="#8b7d5c" /><text x="24" y="40" font-size="11" fill="var(--ink)">na curva</text><text x="200" y="40" font-size="11" fill="var(--mute)" font-family="var(--font-mono)" text-anchor="end">{{ $fairCount }}</text>
                        <circle cx="14" cy="50" r="4" fill="#b04421" /><text x="24" y="54" font-size="11" fill="var(--ink)">acima da curva</text><text x="200" y="54" font-size="11" fill="var(--mute)" font-family="var(--font-mono)" text-anchor="end">{{ $highCount }}</text>
                    </g>
                </svg>
            </div>

            <div class="card-curva p-3">
                <div class="d-flex justify-content-between align-items-center mb-3"><div class="t-up">Agrupado por modelo</div><span class="mono t-mute" style="font-size:11px">mediana ± banda</span></div>
                <div class="model-grid">
                    @forelse ($models as $name => $items)
                        <div>
                            <div class="d-flex justify-content-between align-items-baseline mb-2"><strong style="font-size:13px">{{ $name }}</strong><span class="mono t-mute" style="font-size:11px">{{ $items->count() }} anúncios</span></div>
                            <div class="hist">@foreach (range(0,17) as $i)<i style="left: {{ ($i / 18) * 100 }}%; height: {{ 8 + (($i * 7 + strlen($name)) % 24) }}px; {{ abs($i - 9) < 2 ? 'background: var(--ink)' : '' }}"></i>@endforeach</div>
                            <div class="d-flex justify-content-between align-items-baseline mt-2"><span class="mono" style="font-size:12px">R$ {{ number_format($median($items->pluck('price')) / 1000, 1, ',', '.') }}k</span><span class="badge badge--good">{{ $items->where('curve_score', '>=', 0.04)->count() }} abaixo</span></div>
                        </div>
                    @empty
                        <div class="t-mute">Sem anúncios para os filtros atuais.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <aside class="deals-rail">
            <div class="d-flex justify-content-between align-items-center p-3 border-bottom" style="border-color: var(--line) !important"><strong style="font-size:13px">Abaixo da curva</strong><span class="badge badge--good">{{ $topDeals->count() }} novos</span></div>
            <div class="flex-grow-1">
                @forelse ($topDeals as $car)
                    <a class="deal-card d-block" href="{{ route('provider.redirect', $car->id) }}" target="_blank">
                        <div class="d-flex justify-content-between align-items-baseline mb-1"><strong style="font-size:13px">{{ mb_strtoupper($car->brand) }} {{ mb_strtoupper($car->model) }}</strong><span class="badge badge--good">↓ −{{ abs(round($car->curve_score * 100)) }}% curva</span></div>
                        <div class="t-mute mb-2" style="font-size:11px">{{ mb_strtoupper($car->version ?? '-') }}</div>
                        <div class="d-flex justify-content-between align-items-baseline"><span><span class="display t-mute" style="font-size:14px">R$</span> <span class="num-lg">{{ number_format($car->price, 0, ',', '.') }}</span></span><span class="mono t-mute" style="font-size:11px">{{ $car->year }} · {{ number_format($car->odometer / 1000, 0, ',', '.') }}k km<br>{{ $car->city }}/{{ $car->state }}</span></div>
                    </a>
                @empty
                    <div class="p-4 text-center t-mute" style="font-size:12px">Nenhum anúncio abaixo da curva nos filtros atuais.</div>
                @endforelse
            </div>
            <div class="p-3 border-top" style="border-color: var(--line) !important"><a class="btn-curva btn-curva--good w-100" href="{{ auth()->check() ? route('alerts.create', request()->query()) : route('login') }}">Salvar busca como alerta</a></div>
        </aside>
    </section>
</x-layout>
