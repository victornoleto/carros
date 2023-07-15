<x-layout id="page-table">

    <x-page-with-filters>

        <div class="table-responsive fixed-header">

            <table class="table sortable">
                <thead>
                    <tr>
                        <th>Marca/Modelo</th>
                        <th>Ano</th>
                        <th>Preço (R$)</th>
                        <th>Quilometragem (Km)</th>
                        <th>Cidade</th>
                        <th>Últ. atual.</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cars as $car)
                        <tr>
                            <td>{{ mb_strtoupper($car->brand) }} {{ mb_strtoupper($car->model) }} <small class="opacity-50">{{ mb_strtoupper($car->version ?? '-') }}</small></td>
                            <td>{{ $car->year }}</td>
                            <td data-sort="{{ $car->price }}">{{ number_format($car->price, 2, ',', '.') }}</td>
                            <td data-sort="{{ $car->odometer }}">{{ number_format($car->odometer, 0, ',', '.') }}</td>
                            <td>{{ $car->city }}/{{ $car->state }}</td>
                            <td data-sort="{{ $car->provider_updated_at->toDateTimeString() }}">{{ $car->provider_updated_at->format('d M, H:m') }} <small class="opacity-50">{{ $car->provider }}</small></td>
                            <td class="no-sort">
                                <a href="{{ route('provider.redirect', $car->id) }}" target="_blank">Abrir link</a>
                                {{-- <a href="{{ route('car.ban', $car->id) }}" target="_blank">Banir</a> --}}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    
        <script src="{{ asset('assets/js/sortable.js') }}"></script>

    </x-page-with-filters>

</x-layout>
