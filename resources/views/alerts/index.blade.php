<x-layout id="page-alerts">
    <div class="container py-5">
        <div class="d-flex align-items-center mb-4">
            <h1 class="h3 fw-bold mb-0">Meus alertas</h1>
            <a href="{{ route('alerts.create') }}" class="btn btn-dark ms-auto">Novo alerta</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Filtros</th>
                            <th>Status</th>
                            <th class="text-end">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($alerts as $alert)
                            <tr>
                                <td>{{ $alert->name }}</td>
                                <td><code>{{ json_encode($alert->filters, JSON_UNESCAPED_UNICODE) }}</code></td>
                                <td>{{ $alert->active ? 'Ativo' : 'Inativo' }}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('alerts.destroy', $alert) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Remover</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">Voce ainda nao criou alertas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">{{ $alerts->links() }}</div>
    </div>
</x-layout>
