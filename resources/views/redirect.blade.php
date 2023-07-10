<!DOCTYPE html>
<html lang="en">
    
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link href="{{ asset('assets/main.css') }}" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js" integrity="sha512-3gJwYpMe3QewGELv8k/BX9vcqhryRdzRMxVfq6ngyWXwo03GFEzjsUm8Q7RZcHPHksttq7/GFoxjCVUjkjvPdw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js" integrity="sha384-fbbOQedDUMZZ5KreZpsbe1LCZPVmfTnH7ois6mU1QK+m14rQ1l2bGBq41eYeM/fS" crossorigin="anonymous"></script>
</head>

<body class="bg-gray">

    <main class="p-5">

        <table class="table table-striped">
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
                        <td>{{ $car->olx_updated_at->format('d M, H:m') }}</td>
                        <td>
                            <a href="{{ $car->url }}" target="_blank">Abrir link</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </main>

</body>