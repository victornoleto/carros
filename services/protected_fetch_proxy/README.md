# Protected Fetch Proxy

Servico FastAPI local para buscar paginas allowlisted usando um client HTTP com fingerprint mais proximo de navegador.

Uso principal neste projeto: buscar paginas da OLX quando `curl`/Guzzle recebem `403` por bloqueio anti-bot.

## Seguranca

- Aceita somente URLs `https`.
- Aceita somente hosts em `PROTECTED_FETCH_ALLOWED_HOSTS`.
- Exige token local via header `x-proxy-token`.
- Filtra headers enviados pelo caller.
- Limita tamanho da resposta.
- Respeita intervalo minimo entre requests via `PROTECTED_FETCH_MIN_INTERVAL_SECONDS`.
- Pode encaminhar requests por proxy upstream residencial.
- Deve rodar apenas em `127.0.0.1`.

## Setup Local

Na raiz do projeto:

```bash
make proxy-install
cp services/protected_fetch_proxy/config.example.env services/protected_fetch_proxy/.env
```

Edite `services/protected_fetch_proxy/.env` e defina um valor forte para `PROTECTED_FETCH_PROXY_TOKEN`.

Para Webmotors, configure tambem um proxy residencial upstream:

```env
PROTECTED_FETCH_UPSTREAM_PROXY_URL=http://usuario:senha@host:porta
PROTECTED_FETCH_MIN_INTERVAL_SECONDS=3.0
```

Para rotacao entre varios endpoints:

```env
PROTECTED_FETCH_UPSTREAM_PROXY_URLS=http://u:s@host1:porta,http://u:s@host2:porta
PROTECTED_FETCH_UPSTREAM_PROXY_RETRY_STATUSES=403,429
PROTECTED_FETCH_UPSTREAM_PROXY_RETRY_SLEEP_SECONDS=2.0
```

Com varios endpoints, cada request comeca pelo proximo proxy da lista. Em status `403` ou `429`, o servico aguarda e tenta o proximo endpoint disponivel.

## Rodar

```bash
make proxy
```

Equivalente manual:

```bash
.venv/bin/python -m uvicorn services.protected_fetch_proxy.app:app --host 127.0.0.1 --port 8788
```

## Healthcheck

```bash
make proxy-health
```

Ou:

```bash
curl http://127.0.0.1:8788/health
```

## Teste De Fetch

```bash
curl -X POST http://127.0.0.1:8788/fetch \
  -H "content-type: application/json" \
  -H "x-proxy-token: <token>" \
  -d '{
    "url": "https://www.olx.com.br/autos-e-pecas/carros-vans-e-utilitarios/mitsubishi/lancer?o=3",
    "headers": {
      "accept": "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
      "accept-language": "pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7",
      "user-agent": "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36"
    },
    "timeout_seconds": 60
  }'
```

Resposta esperada:

- `status_code`: status retornado pela OLX.
- `content_type`: tipo de conteudo upstream.
- `final_url`: URL final apos redirects.
- `body`: HTML ou JSON retornado.
- `body_is_json`: `false` para paginas HTML da OLX.

Se `status_code` continuar `403` na Webmotors, o proxy residencial usado tambem esta bloqueado ou nao tem reputacao suficiente. Troque o endpoint/IP ou aumente o intervalo entre requests.
