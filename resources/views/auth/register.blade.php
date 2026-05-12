<x-layout id="page-register">
    <div class="container py-5" style="max-width: 520px;">
        <h1 class="h3 fw-bold mb-4">Criar conta</h1>

        <form method="POST" action="{{ route('register') }}" class="card card-body shadow-sm">
            @csrf

            @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <div class="mb-3">
                <label class="form-label">Nome</label>
                <input type="text" name="name" value="{{ old('name') }}" class="form-control" required autofocus>
            </div>

            <div class="mb-3">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Senha</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Confirmar senha</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-dark fw-bold">Criar conta</button>
        </form>

        <p class="mt-3 mb-0">Ja tem conta? <a href="{{ route('login') }}">Entrar</a></p>
    </div>
</x-layout>
