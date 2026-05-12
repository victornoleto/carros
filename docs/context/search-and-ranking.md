# Busca E Comparacao

A busca publica filtra anuncios ativos e nao banidos. Os filtros principais sao:

- Ano minimo e maximo.
- Preco minimo e maximo, informado em milhares de reais na interface atual.
- Quilometragem minima e maxima, informada em milhares de km na interface atual.
- Estados.
- Cidades.
- Modelos.

## Dashboard

O dashboard agrupa carros por marca/modelo e plota pontos com:

- Eixo X: quilometragem em milhares de km.
- Eixo Y: preco em milhares de reais.
- Raio fixo por ponto.

Para reduzir ruido, a query seleciona anuncios equivalentes por versao/ano/preco arredondado e prioriza menor quilometragem e ano mais novo.

## Tabela

A tabela ordena por:

- Ano descrescente.
- Preco crescente.
- Quilometragem crescente.

Esse criterio favorece anuncios mais novos, baratos e menos rodados.
