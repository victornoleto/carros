<x-layout>

    <x-page-with-filters>

        <div class="table-responsive fixed-header">

            <table class="table">
                <thead>
                    <tr>
                        <th>Marca</th>
                        <th>Modelo</th>
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
                            <td>{{ mb_strtoupper($car->brand) }}</td>
                            <td>{{ mb_strtoupper($car->version ?? $car->model) }}</td>
                            <td>{{ $car->year }}</td>
                            <td>{{ number_format($car->price, 2, ',', '.') }}</td>
                            <td>{{ number_format($car->odometer, 0, ',', '.') }}</td>
                            <td>{{ $car->city }}/{{ $car->state }}</td>
                            <td>{{ $car->provider_updated_at->format('d M, H:m') }} <small class="opacity-50">{{ $car->provider }}</small></td>
                            <td>
                                <a href="{{ route('provider.redirect', $car->id) }}" target="_blank">Abrir link</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    
        <script src="{{ asset('assets/js/select2.js') }}"></script>

    </x-page-with-filters>

</x-layout>
