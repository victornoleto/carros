# Curva — Anatomia das telas

Cada tela com: estrutura, comportamento, query de dados, observações de UX. Para HTML/CSS de referência, abra `source/index.html` (canvas) ou `source/prototype.html` (clicável).

---

## 1. Dashboard público — `/` (ou `/mapa`)

**Tela mais importante.** É o "mapa de oportunidades" — onde o usuário rapidamente percebe o que vale a pena olhar.

### Estrutura (desktop)

```
┌─────────────────────────────────────────────────────────────┐
│  TopBar: logo · Mapa | Anúncios | Alertas · "X anúncios"   │
├─────────────────────────────────────────────────────────────┤
│  H1: "Honda Civic · N anúncios"     [Salvar busca]         │
│  Sub: "M anúncios abaixo da curva agora. Salvar alerta →"  │
├─────────────────────────────────────────────────────────────┤
│  KPI strip: Anúncios | Preço med | Km med | Abaixo | Atu.  │
├─────────────────────────────────────────────────────────────┤
│  Active filters: [Civic ×] [2014-2024 ×] ...               │
├───────────┬─────────────────────────────────┬───────────────┤
│           │                                 │               │
│  Filters  │    Scatter plot Preço × Km     │  Deals rail   │
│  (280px)  │    (regressão + dots)          │  (300px)      │
│           │                                 │  + CTA salvar │
│           ├─────────────────────────────────┤               │
│           │  "Agrupado por modelo" (4 cols) │               │
└───────────┴─────────────────────────────────┴───────────────┘
```

### Scatter plot — o componente-coração

- **Eixo X**: km (0 a 220.000)
- **Eixo Y**: preço (R$ 25k a R$ 170k)
- **Curva mediana**: regressão exponencial `145000 * exp(-km/200000)` — dashed line, cinza
- **Banda mediana**: ±~22px ao redor da curva, fundo cinza claro
- **Zona "abaixo da curva"**: fundo verde super-claro abaixo da curva
- **Dots**: cor por `scoreTone`. Raio maior pra outliers (`|score| >= 0.10`). Outlines em branco no dot highlighted
- **Hover**: callout com marca/modelo/versão/ano/preço/km/cidade/provider
- **Legend** no canto superior direito do plot: contagens por bucket

### Filtros (sidebar)

- **Modelo** (chips, único)
- **Versão** (checkboxes, múltiplo, filtradas pelo modelo)
- **Ano** (slider duplo)
- **Preço** (slider duplo, R$ 30k–R$ 180k)
- **Km** (slider duplo, 0–220k)
- **Estado** (chips, múltiplo)
- **Provider** (chips, múltiplo)
- **Toggles**: "Abaixo da curva", "Apenas particular", "Novos hoje"

Todos os filtros são serializados na URL (querystring) pra deep-linking e share.

### Deals rail (direita)

Lista de até 6 anúncios com `score >= 0.04`, ordenados por score desc. Cada card:
- Marca/modelo + score badge
- Versão
- Preço grande + (ano · km) + cidade

Footer: botão verde "Salvar busca como alerta" — leva pra `/alertas/novo` com os filtros atuais.

### Dados necessários

```
GET /api/listings?model=Civic&year_min=2014&...&with_scatter=1
→ {
  total: 204,
  filtered: 34,
  median_price: 54900,
  median_km: 133000,
  buckets: { good: 12, fair: 13, high: 9 },
  scatter: [{ id, km, price, score }, ...],  // payload mínimo, milhares possível
  top_deals: [{ id, make, model, version, year, km, price, score, city, uf, provider }, ...]
}
```

### UX notes

- O hover do scatter destaca o dot E o card na rail (sincronizado)
- Click no dot abre o anúncio em nova aba
- Click no card da rail também destaca o dot
- Sem login, o CTA "Salvar busca" envia pra /login com filtros guardados em session

---

## 2. Tabela pública — `/anuncios`

Tabela densa, sortable, paginada. Default ordenado por `score desc` (abaixo da curva primeiro).

### Colunas (na ordem)

| Col | Conteúdo |
|---|---|
| foto | placeholder 56×42 (use uma foto real quando houver) |
| Marca · Modelo · Versão | duas linhas: bold + mute |
| Ano | mono, right-align |
| Preço | display 14px + "justo R$ Xk" mute abaixo |
| Km | mono, right-align |
| vs. curva | sparkcurve glyph + badge (`↓ −14% curva`) |
| Cidade · UF | inline |
| Provider | mono, mute |
| Atualizado | "hoje" / "ontem" / "há Xd" |
| ação | botão ghost `abrir ↗` |

### Quick filters (chips acima da tabela)

`Abaixo da curva`, `≤ 80k km`, `2020+`, `Particular`, `SP capital`, `Civic Touring`, `Recém-listados`.

Estes são presets — clicar aplica vários filtros de uma vez. Mostrar quais filtros foram aplicados na strip de "ativos" (se houver) ou simplesmente atualizar a tabela.

### Footer

- "1–14 de N" à esquerda
- Paginação à direita (← 1 2 3 4 5 … N →)

### Dados

```
GET /api/listings?sort=score&dir=desc&page=1&per_page=14&q=civic
```

---

## 3. Login — `/login`

Split-pane: esquerda escura com marketing, direita branca com form.

### Esquerda (dark hero, ~50%)

- Logo no topo
- Eyebrow mono: "— bem-vindo de volta"
- H1 grande (52px): **"Bons negócios ficam abaixo da curva."** (com "abaixo da curva." em verde)
- Parágrafo curto explicando o que é o produto
- 3 stats em linha: "12.487 anúncios indexados", "33 abaixo da curva hoje", "4 fontes"
- Fundo: scatter decorativo (faint, opacity ~13%)

### Direita (form, ~560px)

- "Entrar" (label small caps)
- H2: "Continue sua busca."
- Sub: lembre que pesquisar é grátis, conta só pra alertas
- Botão Google (ghost large)
- Divisor "ou com email"
- Email + Senha + Lembrar
- Botão primário "Entrar"
- "Primeira vez? Criar conta grátis"

### Comportamento

- Submit chama `/api/auth/login`
- Em sucesso, redireciona pra `intended()` ou `/alertas`
- "Esqueci": dropdown ou modal pra reset por email

---

## 4. Cadastro — `/registro`

Mesma estrutura do Login (split-pane), com:

### Direita

- "Criar conta" + "Em 30 segundos."
- Caixa de benefícios (3 itens numerados):
  1. **Alertas salvos** — Avisos por email quando um anúncio abaixo da curva aparecer
  2. **Histórico de busca** — Compare valores ao longo do tempo
  3. **Notas privadas** — Marque anúncios pra revisitar
- Form: Nome + Email + Senha
- Aceite de termos + privacidade
- Botão "Criar minha conta"

### Comportamento

- Validação inline: email único, senha mín. 8 caracteres
- Em sucesso, loga automaticamente e vai pra `/alertas`

---

## 5. Meus alertas — `/alertas`

Dashboard pessoal de buscas salvas.

### Header

- "Seus alertas"
- H1: "N buscas ativas · M novos hoje" (M em verde)
- Botão primário: "+ Novo alerta"

### Summary tiles (4 cards)

- Novos hoje (verde)
- Esta semana
- Economia média vs. curva
- Taxa de relevância (% de hits que viraram leads)

### Lista de alertas

Tabela densa, uma linha por alerta:

| Status | Alerta | Resultados | Atividade · 14d | Último hit | ações |
|---|---|---|---|---|---|
| dot pulse (verde se novo, neutro se pausado) | Nome em bold + chips dos filtros (mono, pill) | Número grande + "+N hoje" badge | Sparkline de 14 barras (atividade diária) | "há 28min" / "há 2d" / "pausado" | menu ··· |

### Dados

```
GET /api/alerts → [
  {
    id, name, filters, status,
    matches_count, new_today, last_hit_at,
    activity_14d: [0, 2, 1, 0, 3, ...],  // contagem por dia
  }, ...
]
```

---

## 6. Criar alerta — `/alertas/novo`

Fluxo curto: confirmar filtros da busca atual, dar um nome, escolher frequência/canais.

### Layout 2-col

**Esquerda (form, ~1fr)**:
- Breadcrumb: "Meus alertas · Novo alerta"
- H1: "Confirmar e nomear"
- Sub: "Confira os filtros da sua busca atual. Você sempre pode editar depois."
- Input grande: Nome do alerta
- Lista de filtros (read-only, com "editar" inline em cada linha)
- Botão "← Voltar e ajustar filtros" (volta pra Dashboard com filtros pré-carregados)
- Frequência: 3 cards radio (Tempo real / Diário / Semanal)
- Canais: 3 toggles (Email / Web push / WhatsApp)

**Direita (preview, 420px)**:
- Card preto com:
  - "pré-visualização" (mono small caps)
  - Nome do alerta em display
  - Frase explicando o que será notificado, com "≥10% abaixo da curva" destacado em verde
  - Grid 2×2 de stats: hits agora · cadência típica · economia média · vs. curva mediana
- Card branco: "Top match agora" — mostra o melhor anúncio que casaria com o alerta
- Botões: Cancelar (ghost) + Criar alerta (verde, primário)

### Comportamento

- Em submit, POST `/api/alerts` com filters da sessão + nome + frequência + canais
- Redireciona pra `/alertas` com toast "Alerta criado · você será notificado em <canal>"

---

## Mobile

Todas as 6 telas têm uma versão mobile (390px wide) em `source/src/screens/Mobile.jsx`. Padrões:

- **Header**: 14px vertical / 18px horizontal, com back arrow opcional + action à direita
- **TabBar fixa** no rodapé (Mapa | Anúncios | Alertas | Conta), 4 colunas com ícones SVG
- **Cards de anúncio**: foto 64×64 + info ao lado, score badge no canto superior direito
- **Scroll**: scrollbars escondidas (`scrollbar-width: none` no contexto mobile)
- **Login mobile**: hero escuro com scatter decor, panel branco curvado por baixo (`border-radius: 22px 22px 0 0`)
