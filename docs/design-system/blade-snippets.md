# Curva — Blade snippets

Trechos prontos pra colar em views Blade. Assumindo `tokens.css` importado e Alpine.js disponível.

## TopBar

```blade
{{-- resources/views/components/curva/topbar.blade.php --}}
@props(['active' => 'dashboard', 'user' => null])

<header class="flex items-center gap-8 px-7 py-3.5 bg-[var(--surface)] border-b border-[var(--line)] sticky top-0 z-10">
    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
        <x-curva.logo size="18" />
    </a>

    <nav class="flex gap-1">
        @foreach (['dashboard' => 'Mapa', 'tabela' => 'Anúncios', 'alertas' => 'Alertas'] as $key => $label)
            <a href="{{ route($key) }}"
               class="px-3 py-2 rounded-md text-sm
                      {{ $active === $key
                         ? 'bg-[var(--paper)] text-[var(--ink)] font-semibold'
                         : 'text-[var(--mute)] font-medium hover:bg-[var(--surface-2)]' }}">
                {{ $label }}
            </a>
        @endforeach
    </nav>

    <div class="flex-1"></div>

    <div class="flex items-center gap-1.5 text-xs text-[var(--mute)]">
        <span class="dot dot--good dot--pulse"></span>
        <span class="mono">{{ number_format($total ?? 0, 0, ',', '.') }} anúncios · atualizado há {{ $updatedAgo }}</span>
    </div>

    @if ($user)
        <span class="text-sm text-[var(--mute)]">{{ $user->email }}</span>
        <div class="w-[30px] h-[30px] rounded-full bg-[var(--ink)] text-[var(--paper)] flex items-center justify-center text-xs font-semibold">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
    @else
        <a href="{{ route('login') }}" class="btn btn--ghost btn--sm">Entrar</a>
        <a href="{{ route('register') }}" class="btn btn--primary btn--sm">Criar conta</a>
    @endif
</header>
```

## Listing row (tabela)

```blade
{{-- resources/views/components/curva/listing-row.blade.php --}}
@props(['listing'])

@php
    $tone = match (true) {
        $listing->score >= 0.04  => 'good',
        $listing->score <= -0.04 => 'warn',
        default                  => 'neutral',
    };
    $pct = abs(round($listing->score * 100));
    $arrow = $listing->score >= 0.04 ? '↓' : ($listing->score <= -0.04 ? '↑' : '·');
    $label = abs($listing->score) >= 0.04
        ? ($listing->score >= 0 ? "−{$pct}% curva" : "+{$pct}% curva")
        : 'na curva';
@endphp

<tr>
    <td>
        <img src="{{ $listing->photo_url ?? asset('img/listing-placeholder.png') }}"
             alt="" class="w-14 h-[42px] object-cover rounded-[6px] border border-black/5">
    </td>
    <td>
        <div class="flex flex-col gap-0.5">
            <span class="font-semibold text-sm">{{ $listing->make }} {{ $listing->model }}</span>
            <span class="text-xs text-[var(--mute)]">{{ $listing->version }}</span>
        </div>
    </td>
    <td class="text-right">
        <span class="mono text-sm">{{ $listing->year }}</span>
    </td>
    <td class="text-right">
        <div class="flex flex-col items-end gap-0.5">
            <span class="inline-flex items-baseline gap-1">
                <span class="display text-sm text-[var(--mute)] font-medium">R$</span>
                <span class="num-md font-semibold">{{ number_format($listing->price, 0, ',', '.') }}</span>
            </span>
            <span class="mono text-[10px] text-[var(--mute-2)]">
                justo R$ {{ number_format($listing->fair_price / 1000, 1, ',', '.') }}k
            </span>
        </div>
    </td>
    <td class="text-right">
        <span class="mono text-sm">{{ number_format($listing->km, 0, ',', '.') }}</span>
    </td>
    <td class="text-right">
        <div class="inline-flex items-center gap-1.5">
            {{-- sparkcurve aqui se quiser; ver source/src/components.jsx --}}
            <span class="badge badge--{{ $tone }}">{{ $arrow }} {{ $label }}</span>
        </div>
    </td>
    <td>
        <span class="text-sm">{{ $listing->city }}</span>
        <span class="text-xs text-[var(--mute)] ml-1">· {{ $listing->uf }}</span>
    </td>
    <td>
        <span class="mono text-xs text-[var(--mute)]">{{ ucfirst($listing->provider) }}</span>
    </td>
    <td>
        <span class="text-xs text-[var(--mute)]">
            {{ $listing->last_seen_at->diffForHumans(short: true) }}
        </span>
    </td>
    <td class="text-right">
        <a href="{{ $listing->url }}" target="_blank" rel="noopener" class="btn btn--ghost btn--sm">
            abrir ↗
        </a>
    </td>
</tr>
```

## Alpine — filtros do dashboard

```blade
<div x-data="filters({
    model: 'Civic',
    yearMin: 2014, yearMax: 2024,
    priceMin: 30000, priceMax: 180000,
    kmMax: 220000,
    ufs: ['SP', 'RJ', 'MG'],
})" x-init="$watch('$data', () => fetch())">
    {{-- chips, sliders, etc --}}
</div>

<script>
function filters(initial) {
    return {
        ...initial,
        async fetch() {
            const params = new URLSearchParams(this);
            const res = await fetch(`/api/listings?${params}`);
            const data = await res.json();
            // dispatch event para o scatter + tabela atualizarem
            window.dispatchEvent(new CustomEvent('curva:filters', { detail: data }));
        },
    };
}
</script>
```

## Score helper (PHP)

```php
// app/Support/CurvaScore.php
namespace App\Support;

class CurvaScore
{
    public static function tone(float $score): string
    {
        return match (true) {
            $score >= 0.04  => 'good',
            $score <= -0.04 => 'warn',
            default         => 'neutral',
        };
    }

    public static function label(float $score): string
    {
        $pct = abs(round($score * 100));
        return match (true) {
            $score >= 0.04  => "−{$pct}% curva",
            $score <= -0.04 => "+{$pct}% curva",
            default         => 'na curva',
        };
    }

    public static function arrow(float $score): string
    {
        return $score >= 0.04 ? '↓' : ($score <= -0.04 ? '↑' : '·');
    }
}
```

Uso no Blade:

```blade
@use(App\Support\CurvaScore)

<span class="badge badge--{{ CurvaScore::tone($listing->score) }}">
    {{ CurvaScore::arrow($listing->score) }} {{ CurvaScore::label($listing->score) }}
</span>
```

## Scatter plot — sugestão de implementação

Recomendo **D3.js** (já tem no Tailwind/Vite). O algoritmo a portar está em `source/src/screens/Dashboard.jsx` na função `ScatterPlot`. Pseudocódigo:

```js
// resources/js/scatter.js
import * as d3 from 'd3';

export function renderScatter(el, { listings, counts }) {
    const w = 760, h = 460;
    const pad = { l: 56, r: 24, t: 24, b: 44 };
    const xScale = d3.scaleLinear().domain([0, 220000]).range([pad.l, w - pad.r]);
    const yScale = d3.scaleLinear().domain([25000, 170000]).range([h - pad.b, pad.t]);

    // regression curve: 145000 * exp(-km / 200000)
    const curveFn = km => 145000 * Math.exp(-km / 200000);
    const curveData = d3.range(0, 220001, 5000).map(km => [km, curveFn(km)]);

    const svg = d3.select(el).append('svg').attr('width', w).attr('height', h);

    // grid, axes, curve, band, dots, legend, hover...
    // (ver Dashboard.jsx para detalhes — mesma estrutura)
}
```

Liga no Alpine:

```js
// quando filters dispara curva:filters
window.addEventListener('curva:filters', (e) => {
    document.querySelector('#scatter').innerHTML = '';
    renderScatter('#scatter', e.detail);
});
```
