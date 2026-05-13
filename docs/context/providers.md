# Providers

Os providers representam sites externos de anuncios. Cada provider possui uma rotina de sincronizacao e uma rotina de processamento dos dados brutos.

## Providers Configurados

- OLX: ativo.
- Webmotors: ativo.
- iCarros: codigo existente, atualmente desativado no sync geral.
- UsadosBR: codigo existente, atualmente desativado no sync geral.

## Contrato De Dados

Cada anuncio processado deve produzir:

- `brand`
- `model`
- `version`
- `year`
- `year_model`
- `price`
- `odometer`
- `state`
- `city`
- `provider`
- `provider_id`
- `provider_updated_at`
- `provider_url`

## Regras Importantes

- `provider` e `provider_id` identificam unicamente um anuncio.
- Dados textuais de busca sao normalizados para lowercase.
- URL do provider nao deve ser normalizada, pois pode ser case-sensitive.
- TLS fica ativo por padrao nas requisicoes HTTP.
- OLX pode exigir o `Protected Fetch Proxy` local quando o request direto retornar `403`; veja `docs/context/protected-fetch-proxy.md`.
