@props([
    'buttonType' => 'button'
])

<form id="filters" action="">

    <div class="row">

        <div class="col-6">

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Filtro</th>
                        <th>Valor min.</th>
                        <th>Valor max.</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Preço <small class="opacity-50">(R$ x1000)</small></td>
                        <td><input type="number" name="price_min" class="form-control" value="{{ Request::get('price_min') }}"></td>
                        <td><input type="number" name="price_max" class="form-control" value="{{ Request::get('price_max', 150) }}"></td>
                    </tr>
                    <tr>
                        <td>Quilometragem <small class="opacity-50">(Km x1000)</small></td>
                        <td><input type="number" name="odometer_min" class="form-control" value="{{ Request::get('odometer_min') }}"></td>
                        <td><input type="number" name="odometer_max" class="form-control" value="{{ Request::get('odometer_max', 120) }}"></td>
                    </tr>
                    <tr>
                        <td>Ano</td>
                        <td><input type="number" name="year_min" class="form-control" value="{{ Request::get('year_min', 2013) }}"></td>
                        <td><input type="number" name="year_max" class="form-control" value="{{ Request::get('year_max') }}"></td>
                    </tr>
                </tbody>
            </table>

        </div>

        <div class="col-6">

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

    <button id="update-btn" type="{{ $buttonType }}" class="btn btn-dark fw-bold mb-3">Atualizar</button>

</form>