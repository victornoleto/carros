<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link href="{{ asset('assets/main.css') }}" rel="stylesheet">
</head>

<body class="bg-gray">

    <main class="p-5">

        <div id="filters">

            <form action="">

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
                                    <td><input type="number" name="price_min" class="form-control"></td>
                                    <td><input type="number" name="price_max" class="form-control" value="150"></td>
                                </tr>
                                <tr>
                                    <td>Quilometragem <small class="opacity-50">(Km x1000)</small></td>
                                    <td><input type="number" name="odometer_min" class="form-control"></td>
                                    <td><input type="number" name="odometer_max" class="form-control" value="120"></td>
                                </tr>
                                <tr>
                                    <td>Ano</td>
                                    <td><input type="number" name="year_min" class="form-control" value="2013"></td>
                                    <td><input type="number" name="year_max" class="form-control"></td>
                                </tr>
                            </tbody>
                        </table>

                    </div>

                    <div class="col-3">

                        <div class="form-group">
                            <label class="mb-2"><b>Estado</b></label>
                            <input type="text" name="state" class="form-control" value="GO" />
                        </div>

                    </div>

                </div>

                <button id="update-btn" type="button" class="btn btn-dark fw-bold">Atualizar</button>

            </form>

        </div>

        <div id="charts-row" class="row"></div>

    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js" integrity="sha512-3gJwYpMe3QewGELv8k/BX9vcqhryRdzRMxVfq6ngyWXwo03GFEzjsUm8Q7RZcHPHksttq7/GFoxjCVUjkjvPdw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js" integrity="sha384-fbbOQedDUMZZ5KreZpsbe1LCZPVmfTnH7ois6mU1QK+m14rQ1l2bGBq41eYeM/fS" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('assets/color-gradient.js') }}"></script>
    <script src="{{ asset('assets/tooltip.js') }}"></script>
    <script src="{{ asset('assets/main.js') }}"></script>

</body>
</html>