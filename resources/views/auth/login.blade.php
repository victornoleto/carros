<x-layout id="page-login">
    <div class="container py-5" style="max-width: 480px;">
        <h1 class="h3 fw-bold mb-4">Entrar</h1>

        <form method="POST" action="{{ route('login') }}" class="card card-body shadow-sm">
            @csrf

            @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <div class="mb-3">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
            </div>

            <div class="mb-3">
                <label class="form-label">Senha</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="form-check mb-3">
                <input type="checkbox" name="remember" value="1" class="form-check-input" id="remember">
                <label class="form-check-label" for="remember">Manter conectado</label>
            </div>

            <button type="submit" class="btn btn-dark fw-bold">Entrar</button>
        </form>

        <p class="mt-3 mb-0">Ainda nao tem conta? <a href="{{ route('register') }}">Criar conta</a></p>
    </div>
</x-layout>
