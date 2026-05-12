# Pipeline De Sincronizacao

A sincronizacao e feita por comandos Artisan e jobs de fila.

## Fluxo

1. `cars:sync {brand} {model} {provider?}` seleciona providers.
2. Um job de sync busca uma pagina de resultados no provider.
3. O sync extrai anuncios brutos da pagina.
4. Para cada anuncio bruto, um job de processamento e enviado.
5. O job de processamento normaliza, valida e salva o anuncio.
6. Quando um preco muda, um registro e salvo em `car_prices`.

## Recursao

Syncs recursivos buscam paginas seguintes enquanto houver resultados e o limite configurado nao for atingido.

## Configuracao

As configuracoes ficam em `config/car_scraping.php`:

- Estado filtrado.
- Timeout HTTP.
- Verificacao TLS.
- Numero maximo de paginas.
