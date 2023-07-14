@props([
    'buttonType' => 'button'
])

<form id="filters" class="p-4 d-flex flex-column overflow-hidden" action="">

    <div class="position-relative flex-grow-1">

        <div class="position-absolute top-0 left-0 w-100 h-100 overflow-x-hidden overflow-y-auto">

            <div class="form-group mb-3">
        
                <label for="">Ano</label>
        
                <div class="row">
        
                    <div class="col-6">
                        <td><input type="number" name="year_min" class="form-control" value="{{ Request::get('year_min', 2013) }}"></td>
                    </div>
        
                    <div class="col-6">
                        <td><input type="number" name="year_max" class="form-control" value="{{ Request::get('year_max') }}"></td>
                    </div>
        
                </div>
        
            </div>
        
            <div class="form-group mb-3">
        
                <label for="">Preço <small class="opacity-50">(R$ x1000)</small></label>
        
                <div class="row">
                    <div class="col-6">
                        <input type="number" name="price_min" class="form-control" value="{{ Request::get('price_min') }}"></td>
                    </div>
                    <div class="col-6">
                        <input type="number" name="price_max" class="form-control" value="{{ Request::get('price_max', 100) }}"></td>
                    </div>
                </div>
        
            </div>
        
            <div class="form-group mb-3">
        
                <label for="">Quilometragem <small class="opacity-50">(R$ x1000)</small></label>
        
                <div class="row">
                    <div class="col-6">
                        <input type="number" name="odometer_min" class="form-control" value="{{ Request::get('odometer_min') }}">
                    </div>
                    <div class="col-6">
                        <input type="number" name="odometer_max" class="form-control" value="{{ Request::get('odometer_max', 120) }}">
                    </div>
                </div>
        
            </div>
        
            <div class="form-group mb-3">

                <label for="">Estado</label>

                <select name="states[]" id="" class="form-select select2" multiple>

                    @foreach ($states as $state)
                        <option value="{{ $state }}" {{ in_array($state, Request::get('states') ?? []) ? 'selected' : '' }}>{{ $state }}</option>
                    @endforeach

                </select>

            </div>
        
            <div class="form-group mb-3">

                <label for="">Cidades</label>

                <select name="cities[]" class="form-select select2" multiple>

                    @foreach ($cities as $city)
                        <option value="{{ $city['text'] }}" {{ in_array($city['name'], Request::get('cities') ?? []) ? 'selected' : '' }}>{{ $city['text'] }}</option>
                    @endforeach

                </select>

            </div>
        
            <div class="form-group">

                <label for="">Modelos</label>

                <select name="models[]" class="form-select select2" multiple>

                    @foreach ($models as $model)
                        <option value="{{ $model['text'] }}" {{ in_array($model['text'], (Request::get('models') ?? [])) ? 'selected' : '' }}>{{ $model['text'] }}</option>
                    @endforeach

                </select>
                
            </div>

        </div>
        
    </div>
    
    <div class="footer d-flex flex-column gap-2">

        <button id="clear-filters-button" type="button" class="btn btn-light w-100 fw-bold">Limpar</button>

        <button id="filters-button" type="{{ $buttonType }}" class="btn btn-dark w-100 fw-bold">Atualizar</button>
    </div>

</form>