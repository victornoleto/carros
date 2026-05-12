# Carros E Precos

## Cars

A tabela `cars` armazena o estado atual conhecido de cada anuncio.

Campos relevantes:

- Dados do veiculo: marca, modelo, versao, ano, ano-modelo, preco e quilometragem.
- Localizacao: estado e cidade.
- Origem: provider, provider_id e provider_url.
- Controle: ativo e banido.

## Car Prices

A tabela `car_prices` registra mudancas de preco.

Quando `price` muda em um carro existente, o sistema salva:

- Preco novo.
- Preco antigo.
- Diferenca.
- Data de atualizacao no provider.
- Data de registro local.

## Regra De Idempotencia

Um mesmo anuncio deve ser identificado por `(provider, provider_id)`. Esse par deve permanecer unico para evitar duplicidade causada por jobs concorrentes.
