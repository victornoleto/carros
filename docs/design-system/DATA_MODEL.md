# Curva — Data Model

Esquema sugerido para Laravel. Adapte conforme suas regras de normalização e providers.

## Tabelas principais

### `listings` (anúncios normalizados)

```php
Schema::create('listings', function (Blueprint $t) {
    $t->id();
    $t->string('external_id');           // id do provider
    $t->string('provider');              // 'olx' | 'webmotors' | 'icarros' | 'mercadolivre'
    $t->string('url');                   // url original
    $t->string('make', 50);              // 'Honda'
    $t->string('model', 80);             // 'Civic'
    $t->string('version', 120);          // 'Touring 1.5 Turbo'
    $t->smallInteger('year');            // 2021
    $t->integer('km');                   // 48300
    $t->integer('price');                // 104900 (em reais inteiros)
    $t->integer('fair_price')->nullable(); // preço justo calculado
    $t->float('score', 6, 3)->nullable(); // (fair - price) / fair — positivo = abaixo da curva
    $t->string('city', 80);
    $t->string('uf', 2);
    $t->enum('seller_type', ['particular', 'loja'])->default('particular');
    $t->smallInteger('photos_count')->default(0);
    $t->timestamp('listed_at')->nullable(); // quando foi publicado no provider
    $t->timestamp('last_seen_at');        // última vez que vimos o anúncio ativo
    $t->boolean('active')->default(true);
    $t->jsonb('raw')->nullable();         // payload original do provider
    $t->timestamps();

    $t->unique(['provider', 'external_id']);
    $t->index(['make', 'model', 'version']);
    $t->index(['active', 'score']);
    $t->index(['uf', 'city']);
});
```

### `users`

Padrão do Laravel. Campos extras se quiser:
- `phone` (pra alertas via WhatsApp)
- `notification_prefs` (JSONB: `{ email: true, web_push: true, whatsapp: false }`)

### `alerts` (buscas salvas)

```php
Schema::create('alerts', function (Blueprint $t) {
    $t->id();
    $t->foreignId('user_id')->constrained()->cascadeOnDelete();
    $t->string('name', 120);
    $t->jsonb('filters');                 // ver shape abaixo
    $t->enum('frequency', ['instant', 'daily', 'weekly'])->default('instant');
    $t->jsonb('channels')->default('["email"]'); // ['email', 'web_push', 'whatsapp']
    $t->enum('status', ['active', 'paused'])->default('active');
    $t->timestamp('last_hit_at')->nullable();
    $t->timestamps();

    $t->index(['user_id', 'status']);
});
```

**Shape do JSON `filters`**:

```json
{
  "make": "Honda",
  "model": "Civic",
  "versions": ["Touring 1.5 Turbo", "EX 1.8"],
  "year_min": 2019,
  "year_max": 2024,
  "price_min": 90000,
  "price_max": 145000,
  "km_max": 70000,
  "ufs": ["SP", "RJ", "MG"],
  "providers": ["olx", "webmotors"],
  "seller_type": "particular",
  "min_score": 0.10
}
```

### `alert_hits` (matches já notificados — pra não notificar duas vezes)

```php
Schema::create('alert_hits', function (Blueprint $t) {
    $t->id();
    $t->foreignId('alert_id')->constrained()->cascadeOnDelete();
    $t->foreignId('listing_id')->constrained()->cascadeOnDelete();
    $t->timestamp('notified_at');
    $t->timestamps();
    $t->unique(['alert_id', 'listing_id']);
});
```

## Algoritmo de scoring (centro do produto)

```php
class CurveScorer
{
    /**
     * Estima preço justo dado modelo, ano e km.
     * Retorna um inteiro em reais.
     */
    public function estimateFair(int $refPrice, int $year, int $km): int
    {
        $age = now()->year - $year;
        $ageFactor = pow(0.94, $age);
        $kmPenalty = min($km * 0.12, $refPrice * 0.28);
        $fair = $refPrice * $ageFactor - $kmPenalty;
        return (int) max($fair, $refPrice * 0.42);
    }

    /**
     * Score: positivo = abaixo da curva (oportunidade).
     * Negativo = acima da curva (caro).
     */
    public function score(int $price, int $fair): float
    {
        return ($fair - $price) / $fair;
    }

    public function bucket(float $score): string
    {
        return match (true) {
            $score >= 0.12 => 'great',
            $score >= 0.04 => 'good',
            $score >= -0.04 => 'fair',
            $score >= -0.12 => 'high',
            default => 'bad',
        };
    }
}
```

**Onde rodar**: num Job de fila acionado após cada normalização de anúncio. O `refPrice` vem de uma tabela `model_references` (preço FIPE médio do modelo/versão), atualizada periodicamente.

**Atenção**: o algoritmo acima é uma referência analítica simples. Pra produção, considere uma regressão real (ex: `sklearn` ou um modelo no Python rodando como microserviço) treinada com seus dados normalizados. Mas a fórmula acima já dá um resultado utilizável e explicável.

## Endpoints sugeridos

```
GET  /api/listings                       Lista paginada com filtros
GET  /api/listings/scatter               Dados condensados pro scatter (id, km, price, score)
GET  /api/listings/{id}                  Detalhe
GET  /api/listings/aggregations          Mediana, contagens, etc por modelo

GET  /api/alerts                         Lista do user
POST /api/alerts                         Cria
PATCH /api/alerts/{id}                   Edita
DELETE /api/alerts/{id}                  Remove
POST /api/alerts/{id}/pause              Pausa
POST /api/alerts/{id}/resume             Resume
GET  /api/alerts/{id}/preview            Conta + top matches antes de salvar

POST /api/auth/login
POST /api/auth/register
POST /api/auth/google                    OAuth
```

**Performance**: o scatter da Dashboard pode ter milhares de pontos. Sirva apenas `{id, km, price, score}` (4 ints) — payload mínimo. Toda info adicional só no hover via outro endpoint ou já pré-carregada paginada na rail lateral.

## Jobs / Schedulers

- **`ScrapeOlxJob`** / **`ScrapeWebmotorsJob`** / etc — cada 5–15 min, dependendo do provider.
- **`NormalizeListingJob`** — extrai marca/modelo/versão/preço/km a partir do raw scraping; chama `CurveScorer`.
- **`MatchAlertsJob`** — após inserção em `listings`, verifica todos os alertas ativos contra a inserção; se match, cria `alert_hits` e dispara notificação.
- **`SendDailyDigestJob`** — schedule diário 8h, agrega hits do dia para alertas `daily`.
