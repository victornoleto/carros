@props([
    'buttonType' => 'submit'
])

@php
    $selectedModels = (array) request('models', []);
    $selectedStates = (array) request('states', []);
    $selectedCities = (array) request('cities', []);
    $yearMin = (int) request('year_min', 2014);
    $yearMax = (int) request('year_max', 2024);
    $priceMin = (int) request('price_min', 30);
    $priceMax = (int) request('price_max', 600);
    $odometerMin = (int) request('odometer_min', 0);
    $odometerMax = (int) request('odometer_max', 220);
@endphp

<form id="filters" class="filters-panel" action="">
    <div class="filters-panel__scroll">
        <div>
            <div class="t-up mb-2">Filtros</div>
            <div class="filters-count"><span class="mono">{{ number_format($count, 0, ',', '.') }}</span> de <span class="mono">{{ number_format($total, 0, ',', '.') }}</span> anúncios</div>
        </div>

        <div class="filter-block">
            <div class="filter-block__head"><span>Modelo</span></div>
            <div class="filter-chips">
                @foreach (collect($models)->take(8) as $model)
                    <label class="filter-chip {{ in_array($model['text'], $selectedModels) ? 'is-active' : '' }}">
                        <input type="checkbox" name="models[]" value="{{ $model['text'] }}" @checked(in_array($model['text'], $selectedModels))>
                        {{ $model['name'] }}
                    </label>
                @endforeach
            </div>
        </div>

        <div class="filter-block">
            <div class="filter-block__head"><span>Ano</span></div>
            <div class="filter-range" data-range-filter>
                <div class="filter-range__labels mono"><span data-range-min-label>{{ $yearMin }}</span><span>—</span><span data-range-max-label>{{ $yearMax }}</span></div>
                <div class="filter-range__track">
                    <input type="range" name="year_min" min="2014" max="2024" step="1" value="{{ $yearMin }}" data-range-min>
                    <input type="range" name="year_max" min="2014" max="2024" step="1" value="{{ $yearMax }}" data-range-max>
                </div>
            </div>
        </div>

        <div class="filter-block">
            <div class="filter-block__head"><span>Preço</span></div>
            <div class="filter-range" data-range-filter data-prefix="R$ " data-suffix="k">
                <div class="filter-range__labels mono"><span data-range-min-label>R$ {{ $priceMin }}k</span><span>—</span><span data-range-max-label>R$ {{ $priceMax }}k</span></div>
                <div class="filter-range__track">
                    <input type="range" name="price_min" min="30" max="600" step="5" value="{{ $priceMin }}" data-range-min>
                    <input type="range" name="price_max" min="30" max="600" step="5" value="{{ $priceMax }}" data-range-max>
                </div>
            </div>
        </div>

        <div class="filter-block">
            <div class="filter-block__head"><span>Quilometragem</span></div>
            <div class="filter-range" data-range-filter data-suffix="k km">
                <div class="filter-range__labels mono"><span data-range-min-label>{{ $odometerMin }}k km</span><span>—</span><span data-range-max-label>{{ $odometerMax }}k km</span></div>
                <div class="filter-range__track">
                    <input type="range" name="odometer_min" min="0" max="220" step="5" value="{{ $odometerMin }}" data-range-min>
                    <input type="range" name="odometer_max" min="0" max="220" step="5" value="{{ $odometerMax }}" data-range-max>
                </div>
            </div>
        </div>

        <div class="filter-block">
            <div class="filter-block__head"><span>Estado</span></div>
            <div class="filter-chips filter-chips--small">
                @foreach ($states as $state)
                    <label class="filter-chip {{ in_array($state, $selectedStates) ? 'is-active' : '' }}">
                        <input type="checkbox" name="states[]" value="{{ $state }}" @checked(in_array($state, $selectedStates))>
                        {{ $state }}
                    </label>
                @endforeach
            </div>
        </div>

        <div class="filter-block">
            <div class="filter-block__head"><span>Cidades</span><span>{{ count($selectedCities) ? count($selectedCities).' selecionadas' : '' }}</span></div>
            <div class="filter-options">
                @foreach (collect($cities)->take(18) as $city)
                    <label class="filter-check">
                        <input type="checkbox" name="cities[]" value="{{ $city['text'] }}" @checked(in_array($city['text'], $selectedCities))>
                        <span>{{ $city['name'] }}</span>
                        <span class="mono">{{ $city['state'] }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>

    <div class="filters-panel__footer">
        <button id="clear-filters-button" type="button" class="btn-curva btn-curva--ghost w-100">Limpar</button>
        @auth
            <a href="{{ route('alerts.create', request()->query()) }}" class="btn-curva btn-curva--ghost w-100">Criar alerta</a>
        @else
            <a href="{{ route('login') }}" class="btn-curva btn-curva--ghost w-100">Entrar para criar alerta</a>
        @endauth
        <button id="filters-button" type="{{ $buttonType }}" class="btn-curva btn-curva--primary w-100">Atualizar</button>
    </div>
</form>
