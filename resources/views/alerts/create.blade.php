<x-layout id="page-alert-create">
    <div class="container py-5" style="max-width: 720px;">
        <h1 class="h3 fw-bold mb-4">Criar alerta</h1>

        <form method="POST" action="{{ route('alerts.store') }}" class="card card-body shadow-sm">
            @csrf

            @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <div class="mb-3">
                <label class="form-label">Nome do alerta</label>
                <input type="text" name="name" value="{{ old('name', 'Busca de carros') }}" class="form-control" required>
            </div>

            <p class="text-muted">Os filtros atuais serao salvos como criterios do alerta.</p>

            @foreach ($filters as $key => $value)
                @if (is_array($value))
                    @foreach ($value as $item)
                        <input type="hidden" name="filters[{{ $key }}][]" value="{{ $item }}">
                    @endforeach
                @else
                    <input type="hidden" name="filters[{{ $key }}]" value="{{ $value }}">
                @endif
            @endforeach

            <pre class="bg-light border rounded p-3"><code>{{ json_encode($filters, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>

            <button type="submit" class="btn btn-dark fw-bold">Salvar alerta</button>
        </form>
    </div>
</x-layout>
