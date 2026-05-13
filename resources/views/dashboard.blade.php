@php
    $median = function ($values) {
        $values = collect($values)->filter(fn ($value) => is_numeric($value))->sort()->values();
        $count = $values->count();
        if ($count === 0) return 0;
        $middle = intdiv($count, 2);
        return $count % 2 ? $values[$middle] : (($values[$middle - 1] + $values[$middle]) / 2);
    };
    $percentile = function ($values, float $percentile) {
        $values = collect($values)->filter(fn ($value) => is_numeric($value))->sort()->values();
        $count = $values->count();
        if ($count === 0) return 0;
        $index = ($count - 1) * $percentile;
        $lower = (int) floor($index);
        $upper = (int) ceil($index);
        if ($lower === $upper) return $values[$lower];
        return $values[$lower] + (($values[$upper] - $values[$lower]) * ($index - $lower));
    };
    $medianPrice = max(1, $median($cars->pluck('price')));
    $medianKm = max(1, $median($cars->pluck('odometer')));
    $curveBand = 0.10;
    $curvePrice = fn ($km) => max(25000, 145000 * exp(-min(300000, max(0, (float) $km)) / 200000));
    $scored = $cars->map(function ($car) use ($curvePrice, $curveBand) {
        $fair = $curvePrice($car->odometer);
        $score = ($fair - $car->price) / max(1, $fair);
        $car->fair_price = $fair;
        $car->curve_score = $score;
        $car->curve_tone = $score >= $curveBand ? 'good' : ($score <= -$curveBand ? 'warn' : 'neutral');
        return $car;
    })->sortByDesc('curve_score')->values();
    $good = $scored->where('curve_score', '>=', $curveBand);
    $fairCount = $scored->filter(fn ($car) => abs($car->curve_score) < $curveBand)->count();
    $highCount = $scored->where('curve_score', '<=', -$curveBand)->count();
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
            <x-filters buttonType="submit" :count="$scored->count()" :total="$totalCars ?? $scored->count()" />
        </aside>

        <div class="plot-area">
            <div class="card-curva scatter-wrap">
                <div class="scatter-chart">
                <svg class="scatter-svg" width="760" height="460" viewBox="0 0 760 460" role="img" aria-label="Gráfico de preço por quilometragem">
                    @php
                        $padL = 56; $padR = 24; $padT = 24; $padB = 44;
                        $plotW = 760 - $padL - $padR;
                        $plotH = 460 - $padT - $padB;
                        $roundUp = fn ($value, $step) => (int) ceil(max(1, (float) $value) / $step) * $step;
                        $actualMaxKm = (float) ($scored->max('odometer') ?: 0);
                        $actualMaxPrice = (float) ($scored->max('price') ?: 0);
                        $robustMaxKm = max($medianKm * 2.5, $percentile($scored->pluck('odometer'), 0.95) * 1.25, 220000);
                        $robustMaxPrice = max($medianPrice * 5, $percentile($scored->pluck('price'), 0.95) * 1.25, 170000);
                        $xMin = 0;
                        $xMax = $roundUp(min(max($actualMaxKm, 220000), $robustMaxKm), 50000);
                        $yMin = 25000;
                        $yMax = $roundUp(min(max($actualMaxPrice, 170000), $robustMaxPrice), 50000);
                        $xHasOverflow = $actualMaxKm > $xMax;
                        $yHasOverflow = $actualMaxPrice > $yMax;
                        $xTicks = collect(range(0, (int) ($xMax / 50000)))->map(fn ($i) => $i * 50000);
                        $yTicks = collect(range(50000, $yMax, 50000));
                        $xScale = fn ($km) => $padL + ((min($xMax, max($xMin, (float) $km)) - $xMin) / ($xMax - $xMin)) * $plotW;
                        $yScale = fn ($price) => $padT + (1 - ((min($yMax, max($yMin, (float) $price)) - $yMin) / ($yMax - $yMin))) * $plotH;
                        $curvePoints = collect(range(0, 40))->map(function ($i) use ($xScale, $yScale, $xMin, $xMax, $curvePrice) {
                            $km = $xMin + (($xMax - $xMin) * ($i / 40));
                            $price = $curvePrice($km);
                            return round($xScale($km), 1).' '.round($yScale($price), 1);
                        });
                        $curve = $curvePoints->implode(' L ');
                        $upperBand = $curvePoints->map(function ($point) {
                            [$x, $y] = explode(' ', $point);
                            return $x.' '.round(((float) $y) - 22, 1);
                        });
                        $lowerBand = $curvePoints->map(function ($point) {
                            [$x, $y] = explode(' ', $point);
                            return $x.' '.round(((float) $y) + 22, 1);
                        });
                        $band = 'M '.$upperBand->implode(' L ').' L '.$lowerBand->reverse()->implode(' L ').' Z';
                    @endphp
                    <rect x="{{ $padL }}" y="{{ $padT }}" width="{{ $plotW }}" height="{{ $plotH }}" fill="var(--surface-2)" />
                    @foreach ($xTicks as $km)
                        <line x1="{{ $xScale($km) }}" x2="{{ $xScale($km) }}" y1="{{ $padT }}" y2="{{ $padT + $plotH }}" stroke="var(--line)" stroke-dasharray="2 4" />
                        <text x="{{ $xScale($km) }}" y="{{ $padT + $plotH + 18 }}" font-size="10" fill="var(--mute)" text-anchor="middle" font-family="var(--font-mono)">{{ number_format($km / 1000, 0, ',', '.') }}k{{ $xHasOverflow && $loop->last ? '+' : '' }} km</text>
                    @endforeach
                    @foreach ($yTicks as $price)
                        <line x1="{{ $padL }}" x2="{{ $padL + $plotW }}" y1="{{ $yScale($price) }}" y2="{{ $yScale($price) }}" stroke="var(--line)" stroke-dasharray="2 4" />
                        <text x="48" y="{{ $yScale($price) + 4 }}" font-size="10" fill="var(--mute)" text-anchor="end" font-family="var(--font-mono)">{{ number_format($price / 1000, 0, ',', '.') }}k{{ $yHasOverflow && $loop->last ? '+' : '' }}</text>
                    @endforeach
                    <text x="{{ $padL }}" y="{{ $padT - 8 }}" font-size="10" fill="var(--mute)" font-family="var(--font-mono)">preço</text>
                    <text x="{{ $padL + $plotW }}" y="{{ $padT + $plotH + 36 }}" font-size="10" fill="var(--mute)" text-anchor="end" font-family="var(--font-mono)">quilometragem</text>
                    <path d="M {{ $padL }} {{ $yScale(40000) }} L {{ $curve }} L {{ $padL + $plotW }} {{ $yScale(40000) }} Z" fill="rgba(26,108,77,0.04)" />
                    <path d="{{ $band }}" fill="rgba(20,17,13,0.05)" />
                    <path d="M {{ $curve }}" stroke="var(--mute)" stroke-width="1.3" fill="none" stroke-dasharray="4 3" />
                    <text x="{{ $padL + ($plotW * 0.62) }}" y="{{ $yScale($curvePrice($xMax * 0.62)) - 8 }}" font-size="10" fill="var(--mute)" font-family="var(--font-mono)">curva mediana</text>
                    @foreach ($scored as $car)
                        @php
                            $color = $car->curve_tone === 'good' ? '#1a6c4d' : ($car->curve_tone === 'warn' ? '#b04421' : '#8b7d5c');
                            $radius = abs($car->curve_score) >= 0.10 ? 4.5 : 3.5;
                            $x = $xScale($car->odometer);
                            $y = $yScale($car->price);
                            $scoreLabel = $car->curve_score >= $curveBand ? '↓ -'.abs(round($car->curve_score * 100)).'% curva' : ($car->curve_score <= -$curveBand ? '↑ +'.abs(round($car->curve_score * 100)).'% curva' : 'na curva');
                        @endphp
                        @if ($x >= $padL && $x <= $padL + $plotW && $y >= $padT && $y <= $padT + $plotH)
                            <a href="{{ route('provider.redirect', $car->id) }}" target="_blank">
                                <g class="scatter-point"
                                    data-x="{{ $x }}"
                                    data-y="{{ $y }}"
                                    data-title="{{ e(mb_strtoupper($car->brand).' '.mb_strtoupper($car->model)) }}"
                                    data-badge="{{ e($scoreLabel) }}"
                                    data-subtitle="{{ e(mb_strtoupper($car->version ?? '-').' · '.$car->year) }}"
                                    data-price="{{ e(number_format($car->price, 0, ',', '.')) }}"
                                    data-km="{{ e(number_format($car->odometer / 1000, 0, ',', '.').'k km') }}"
                                    data-location="{{ e($car->city.'/'.$car->state) }}"
                                    data-provider="{{ e($car->provider ?? '') }}">
                                    <circle cx="{{ $x }}" cy="{{ $y }}" r="{{ $radius }}" fill="{{ $color }}" opacity="0.85">
                                        <title>{{ mb_strtoupper($car->brand) }} {{ mb_strtoupper($car->model) }} · R$ {{ number_format($car->price, 0, ',', '.') }} · {{ number_format($car->odometer, 0, ',', '.') }} km</title>
                                    </circle>
                                    <circle cx="{{ $x }}" cy="{{ $y }}" r="{{ $radius + 6 }}" fill="{{ $color }}" opacity="0" />
                                </g>
                            </a>
                        @endif
                    @endforeach
                </svg>
                <div class="scatter-hotspots" aria-hidden="true">
                    @foreach ($scored as $car)
                        @php
                            $x = $xScale($car->odometer);
                            $y = $yScale($car->price);
                            $scoreLabel = $car->curve_score >= $curveBand ? '↓ -'.abs(round($car->curve_score * 100)).'% curva' : ($car->curve_score <= -$curveBand ? '↑ +'.abs(round($car->curve_score * 100)).'% curva' : 'na curva');
                            $badgeTone = $car->curve_tone === 'warn' ? 'warn' : ($car->curve_tone === 'neutral' ? 'neutral' : 'good');
                            $left = ($x / 760) * 100;
                            $top = ($y / 460) * 100;
                            $flipX = $x > 520;
                            $flipY = $y > 300;
                        @endphp
                        @if ($x >= $padL && $x <= $padL + $plotW && $y >= $padT && $y <= $padT + $plotH)
                            <a class="scatter-hotspot {{ $flipX ? 'scatter-hotspot--flip-x' : '' }} {{ $flipY ? 'scatter-hotspot--flip-y' : '' }}" style="left: {{ $left }}%; top: {{ $top }}%;" href="{{ route('provider.redirect', $car->id) }}" target="_blank" tabindex="-1">
                                <div class="scatter-tooltip">
                                    <div class="scatter-tooltip__head">
                                        <strong>{{ mb_strtoupper($car->brand) }} {{ mb_strtoupper($car->model) }}</strong>
                                        <span class="badge badge--{{ $badgeTone }} mono">{{ $scoreLabel }}</span>
                                    </div>
                                    <div class="scatter-tooltip__subtitle">{{ mb_strtoupper($car->version ?? '-') }} · {{ $car->year }}</div>
                                    <div class="scatter-tooltip__main"><span>R$</span><strong>{{ number_format($car->price, 0, ',', '.') }}</strong><span class="mono">{{ number_format($car->odometer / 1000, 0, ',', '.') }}k km</span></div>
                                    <div class="scatter-tooltip__foot"><span>{{ $car->city }}/{{ $car->state }}</span><span class="mono">{{ $car->provider ?? '' }}</span></div>
                                </div>
                            </a>
                        @endif
                    @endforeach
                </div>
                <div class="scatter-legend" aria-hidden="true">
                    <div><span class="dot dot--good"></span><span>abaixo da curva</span><span class="mono">{{ $good->count() }}</span></div>
                    <div><span class="dot dot--neutral"></span><span>na curva</span><span class="mono">{{ $fairCount }}</span></div>
                    <div><span class="dot dot--warn"></span><span>acima da curva</span><span class="mono">{{ $highCount }}</span></div>
                </div>
                </div>
            </div>

            <div class="card-curva p-3">
                <div class="d-flex justify-content-between align-items-center mb-3"><div class="t-up">Agrupado por modelo</div><span class="mono t-mute" style="font-size:11px">mediana ± banda</span></div>
                <div class="model-grid">
                    @forelse ($models as $name => $items)
                        <div>
                            <div class="d-flex justify-content-between align-items-baseline mb-2"><strong style="font-size:13px">{{ $name }}</strong><span class="mono t-mute" style="font-size:11px">{{ $items->count() }} anúncios</span></div>
                            <div class="hist">@foreach (range(0,17) as $i)<i style="left: {{ ($i / 18) * 100 }}%; height: {{ 8 + (($i * 7 + strlen($name)) % 24) }}px; {{ abs($i - 9) < 2 ? 'background: var(--ink)' : '' }}"></i>@endforeach</div>
                            <div class="d-flex justify-content-between align-items-baseline mt-2"><span class="mono" style="font-size:12px">R$ {{ number_format($median($items->pluck('price')) / 1000, 1, ',', '.') }}k</span><span class="badge badge--good">{{ $items->where('curve_score', '>=', $curveBand)->count() }} abaixo</span></div>
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
