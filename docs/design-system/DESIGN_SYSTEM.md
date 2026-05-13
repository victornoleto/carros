# Curva — Design System

## 1. Marca

**Logo**: wordmark "curva" em Space Grotesk 600, sempre minúsculo. Glyph é uma mini curva de regressão com um ponto verde (outlier). Disponível em SVG: ver `source/src/components.jsx` (função `Logo`).

**Tagline**: *Anúncios abaixo da curva.*

**Princípios** (use isso pra resolver dúvidas de design):
1. **Número antes do adjetivo** — mostre `R$ 89.900` grande, antes de rótulos.
2. **A curva é a régua** — todo preço se compara à mediana de preço × km.
3. **Densidade calma** — hairlines 1px, paper warm, sem sombras pesadas.
4. **Verde é raro** — só pra anúncios abaixo da curva. Nunca decorativo.
5. **Mono para dados** — km, ano, % curva sempre em JetBrains Mono.

## 2. Cores (tokens)

| Token | Hex | Uso |
|---|---|---|
| `--paper` | `#f3f0e8` | fundo principal (light) |
| `--paper-2` | `#ece7db` | variação |
| `--surface` | `#ffffff` | cards, superfícies |
| `--surface-2` | `#faf8f3` | superfícies secundárias |
| `--ink` | `#14110d` | texto principal, ações primárias |
| `--ink-2` | `#2a251e` | hover de ink |
| `--mute` | `#6e6557` | texto secundário |
| `--mute-2` | `#97907f` | texto terciário |
| `--line` | `#e3ddcd` | hairlines, bordas |
| `--line-2` | `#d3cab5` | bordas mais fortes |
| `--good` | `#1a6c4d` | **abaixo da curva** — único uso |
| `--good-soft` | `#d9ebe1` | fundo do badge `good` |
| `--good-ink` | `#0e3e2c` | texto do badge `good` |
| `--warn` | `#b04421` | acima da curva (alerta de preço alto) |
| `--warn-soft` | `#f1dbcf` | fundo do badge `warn` |
| `--neutral` | `#8b7d5c` | na curva |
| `--neutral-soft` | `#ece4d0` | fundo do badge `neutral` |

**Dark mode** (opcional, ver `prototype.html` para a inversão completa):

```css
[data-mode="dark"] {
  --paper: #14110d; --paper-2: #1e1a14;
  --surface: #1c1812; --surface-2: #221e17;
  --ink: #f3f0e8; --ink-2: #e6e1d3;
  --mute: #a89c84; --mute-2: #7a6f5a;
  --line: #2b2620; --line-2: #3a342a;
}
```

## 3. Tipografia

Três famílias do Google Fonts. Importe em `<head>`:

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
```

| Família | Token | Uso |
|---|---|---|
| **Space Grotesk** 600 | `--font-display` | títulos, números grandes, preços (`R$ 89.900`) — sempre com `letter-spacing: -0.025em` |
| **Inter** 400/600 | `--font-body` | corpo de texto, botões, labels |
| **JetBrains Mono** 400/500 | `--font-mono` | km, ano, % curva, qualquer dado técnico — com `font-feature-settings: 'tnum'` |

Escalas recomendadas (use as classes utilitárias em `tokens.css`):

- **H1 (page title)**: 34–38px / 600 / `-0.03em` / display
- **H2 (section)**: 22–26px / 600 / `-0.025em` / display
- **Body**: 14–15px / 400 / inter
- **Label small caps**: 11px / 600 / `tracking 0.08em` / `uppercase` / inter — classe `.t-up`
- **Mono inline**: 12–13px / 500 / mono
- **Preço grande**: 28–32px / 600 / display — com R$ menor (14–18px / 500) ao lado

## 4. Espaço & raio

- **Hairline**: `1px solid var(--line)`
- **Raio**: 4 (badges/chips), 6 (botões/input pequeno), 10 (cards), 14 (cards grandes), 22 (panels)
- **Padding-padrão**: cards `18–28px`, linhas de tabela `12–14px vertical / 14–16px horizontal`, headers de página `28px`.

## 5. Componentes

### Botão (`.btn`)

```html
<button class="btn btn--primary">Salvar busca</button>
<button class="btn btn--good">Confirmar</button>
<button class="btn btn--ghost">Filtros</button>
<button class="btn btn--primary btn--sm">Small</button>
```

CSS completo em `tokens.css`. Variantes: `--primary` (ink/paper), `--good` (verde, só pra ações de "abaixo da curva"), `--ghost` (transparente com borda).

### Badge de curva (`.badge`)

O componente mais característico. Sempre acompanhado de uma seta:
- ↓ verde para `score >= 0.04` → `−14% curva`
- ↑ vermelho para `score <= -0.04` → `+8% curva`
- · neutro para `|score| < 0.04` → `na curva`

```html
<span class="badge badge--good">↓ −14% curva</span>
<span class="badge badge--warn">↑ +8% curva</span>
<span class="badge badge--neutral">· na curva</span>
```

Lógica de tone (server-side ou JS): ver `scoreTone(score)` em `source/src/data.js`.

### Sparkcurve

Glyph SVG inline (54×22) mostrando a curva mediana com um dot colorido na posição do anúncio. Use em listagens densas pra dar contexto visual sem precisar abrir o scatter completo.

Implementação: ver `SparkCurve` em `source/src/components.jsx`. É um SVG estático parametrizado pelo score.

### Chip de filtro (`.chip`)

```html
<span class="chip">2020+</span>
<span class="chip chip--active">Civic</span>
<span class="chip chip--active">≤ 80k km <span class="chip__x">×</span></span>
```

### Card (`.card`)

```html
<div class="card">
  <!-- conteúdo -->
</div>
```

`background: var(--surface); border: 1px solid var(--line); border-radius: 10px`.

### Tabela (`.tbl`)

Veja `tokens.css` — usa `.tbl th` em uppercase 11px, `.tbl td` em 13px, hover sutil na linha. Ver `source/src/screens/Tabela.jsx` para anatomia completa com sorting + paginação.

### Input (`.input`)

```html
<div class="field">
  <label class="field__label">Email</label>
  <input class="input" type="email" placeholder="seu@email.com" />
</div>
```

Focus: borda escura + ring sutil. Classe `.input.mono` pra dados técnicos.

### Dot indicator

```html
<span class="dot dot--good"></span>   <!-- abaixo da curva -->
<span class="dot dot--good dot--pulse"></span>  <!-- alerta ativo -->
<span class="dot dot--warn"></span>   <!-- acima -->
<span class="dot dot--neutral"></span><!-- na curva -->
```

## 6. Iconografia

O design usa pouquíssimos ícones — quando necessário, são em SVG inline com `stroke-width: 1.4` e `stroke="currentColor"`. Exemplos em `source/src/screens/Mobile.jsx` (IconMap, IconList, IconBell, IconUser).

Para ícones gerais, recomendo **Lucide** (https://lucide.dev/) com weight thin/regular para combinar com a estética hairline.

## 7. Logo (SVG)

```html
<span style="display:inline-flex; align-items:center; gap:8px;">
  <svg width="28" height="28" viewBox="0 0 28 28" fill="none">
    <rect x="0.75" y="0.75" width="26.5" height="26.5" rx="6" stroke="currentColor" stroke-opacity="0.18"/>
    <path d="M3 21 C 9 18, 14 12, 25 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" fill="none"/>
    <circle cx="6" cy="22" r="1.2" fill="currentColor" opacity="0.35"/>
    <circle cx="11" cy="16" r="1.2" fill="currentColor" opacity="0.35"/>
    <circle cx="17" cy="11" r="1.2" fill="currentColor" opacity="0.35"/>
    <circle cx="22" cy="7" r="1.2" fill="currentColor" opacity="0.35"/>
    <circle cx="9" cy="24" r="2" fill="#1a6c4d"/>
  </svg>
  <span style="font-family:'Space Grotesk',sans-serif; font-weight:600; letter-spacing:-0.025em; font-size:20px;">curva</span>
</span>
```

O ponto verde fica **sempre verde** (#1a6c4d), mesmo em dark mode — é o outlier que dá identidade à marca.
