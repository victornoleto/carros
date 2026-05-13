# Protected Fetch Proxy

O projeto inclui um servico Python local em `services/protected_fetch_proxy` para buscar paginas protegidas por anti-bot usando `curl_cffi` com impersonation de Chrome e, quando necessario, proxy residencial upstream.

## Quando Usar

Use este servico quando o provider receber `403` com `curl`, Guzzle ou clients HTTP comuns.

Casos atuais:

- OLX: `curl_cffi` sem upstream ja conseguiu retornar HTML com cards.
- Webmotors: `curl_cffi` sem upstream continua recebendo PerimeterX `403`; use proxy residencial.

## Arquitetura

- Laravel continua responsavel pelo pipeline de sync/processamento.
- O proxy Python faz somente o fetch HTTP da pagina externa.
- O proxy retorna o HTML para o Laravel.
- O parser do provider extrai os cards do HTML.

O proxy nao deve processar anuncio, gravar dados ou conhecer regras de negocio.

## Variaveis Do Proxy

Arquivo local:

```bash
services/protected_fetch_proxy/.env
```

Variaveis:

```env
PROTECTED_FETCH_PROXY_TOKEN=change-me-local-token
PROTECTED_FETCH_ALLOWED_HOSTS=www.olx.com.br,olx.com.br,www.webmotors.com.br,webmotors.com.br
PROTECTED_FETCH_MIN_INTERVAL_SECONDS=3.0
PROTECTED_FETCH_MAX_BODY_BYTES=8388608
PROTECTED_FETCH_MAX_HEADER_VALUE_LENGTH=4096
PROTECTED_FETCH_UPSTREAM_PROXY_URL=
PROTECTED_FETCH_UPSTREAM_PROXY_URLS=
PROTECTED_FETCH_UPSTREAM_PROXY_RETRY_STATUSES=403,429
PROTECTED_FETCH_UPSTREAM_PROXY_RETRY_SLEEP_SECONDS=2.0
```

Para Webmotors, configure pelo menos um proxy residencial:

```env
PROTECTED_FETCH_UPSTREAM_PROXY_URL=http://usuario:senha@host:porta
```

Para rotacao manual entre endpoints:

```env
PROTECTED_FETCH_UPSTREAM_PROXY_URLS=http://u:s@host1:porta,http://u:s@host2:porta
```

Quando `PROTECTED_FETCH_UPSTREAM_PROXY_URLS` tem mais de um endpoint, cada request comeca pelo proximo proxy da lista. Se o upstream retornar um status em `PROTECTED_FETCH_UPSTREAM_PROXY_RETRY_STATUSES`, o servico aguarda `PROTECTED_FETCH_UPSTREAM_PROXY_RETRY_SLEEP_SECONDS` e tenta o proximo endpoint.

## Instalar

```bash
make proxy-install
cp services/protected_fetch_proxy/config.example.env services/protected_fetch_proxy/.env
```

Depois, altere o token no `.env` do proxy.

## Rodar Localmente

```bash
make proxy
```

Healthcheck:

```bash
make proxy-health
```

## Rodar Com Systemd

Crie `/etc/systemd/system/carros-protected-fetch-proxy.service`:

```ini
[Unit]
Description=Carros Protected Fetch Proxy
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/var/www/github/personal/carros
EnvironmentFile=/var/www/github/personal/carros/services/protected_fetch_proxy/.env
ExecStart=/var/www/github/personal/carros/.venv/bin/python -m uvicorn services.protected_fetch_proxy.app:app --host 127.0.0.1 --port 8788
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

Ative:

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now carros-protected-fetch-proxy
sudo systemctl status carros-protected-fetch-proxy
```

Logs:

```bash
journalctl -u carros-protected-fetch-proxy -f
```

## Integracao Com Laravel

Quando o provider OLX for integrado ao proxy, use variaveis no `.env` da aplicacao Laravel:

```env
CAR_SCRAPING_PROXY_URL=http://127.0.0.1:8788/fetch
CAR_SCRAPING_PROXY_TOKEN=<mesmo-token-do-proxy>
CAR_SCRAPING_PROXY_PROVIDERS=webmotors
CAR_SCRAPING_PAGE_SLEEP_SECONDS=3
```

Fluxo atual no `CarSyncService`:

- Providers listados em `CAR_SCRAPING_PROXY_PROVIDERS` usam o proxy diretamente.
- Providers nao listados tentam requisicao direta primeiro.
- Ao receber `403` em requisicao direta, chama `CAR_SCRAPING_PROXY_URL` via `POST`.
- Enviar o token em `x-proxy-token`.
- Enviar `url`, `headers` e `timeout_seconds` no JSON.
- Usa `body` da resposta como entrada do parser atual.
- Se o upstream do proxy tambem retornar `403`, a sincronizacao falha com erro explicito.

## Operacao

- Mantenha o servico bindado em `127.0.0.1`.
- Nunca exponha este proxy publicamente.
- Mantenha a allowlist restrita a `www.olx.com.br,olx.com.br` enquanto ele for usado apenas para OLX.
- Use intervalo minimo entre requests para reduzir bloqueios.
- Monitore respostas upstream `403`, `429` e tamanho do HTML retornado.
- Para Webmotors, comece com `PROTECTED_FETCH_MIN_INTERVAL_SECONDS=3.0` ou maior.
- No Laravel, use `CAR_SCRAPING_PAGE_SLEEP_SECONDS=3` ou maior para reduzir bursts entre paginas.

## Limites

`curl_cffi` melhora o fingerprint TLS/HTTP, mas nao resolve todos os desafios anti-bot. A Webmotors atualmente retorna desafio PerimeterX no IP local; sem proxy residencial, o fallback continua recebendo `403`.
