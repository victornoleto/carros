# Curva — Handoff para Laravel

Pacote de entrega da identidade visual + mockups das 6 telas. Use isso como referência para implementar em Laravel + Blade.

## Stack recomendada

- **Laravel 11+** com Blade
- **Tailwind CSS** (config inclusa em `tokens.tailwind.config.js`) OU CSS vanilla (`tokens.css`)
- **Alpine.js** para interatividade leve (filtros, dropdowns, toggles), OU **Livewire** se preferir Laravel-native
- **Inertia + Vue/React** apenas se for SPA — não é obrigatório
- Para o scatter plot do Dashboard: **D3.js** (mais flexível) ou **uPlot** (mais leve). O exemplo em `source/src/screens/Dashboard.jsx` mostra o algoritmo de regressão e scoring.

## Estrutura deste pacote

```
curva-handoff/
├── README.md                ← você está aqui
├── DESIGN_SYSTEM.md         ← tokens, tipografia, cores, componentes
├── DATA_MODEL.md            ← esquema do banco + endpoints sugeridos
├── SCREENS.md               ← anatomia + comportamento de cada tela
├── tokens.css               ← variáveis CSS prontas pra Laravel
├── tokens.tailwind.config.js← config Tailwind correspondente
├── blade-snippets.md        ← exemplos práticos em Blade
└── source/                  ← código-fonte React do protótipo (referência)
    ├── index.html           ← canvas com TODAS as telas
    ├── prototype.html       ← protótipo navegável
    ├── brand.css            ← CSS completo (use partes em Laravel)
    ├── src/data.js          ← mock data + algoritmo de scoring
    ├── src/components.jsx   ← Logo, ScoreBadge, SparkCurve, Price
    └── src/screens/         ← cada tela em JSX
```

## Como rodar o protótipo localmente

O protótipo é HTML puro com React via CDN — não precisa de build.

```bash
cd source
# qualquer servidor estático:
php -S localhost:8000
# ou
python3 -m http.server 8000
```

Abra `http://localhost:8000/index.html` para o canvas com todas as telas, ou `prototype.html` para o protótipo navegável com tweaks (tema, cor, viewport).

## A marca em 1 minuto

- **Nome**: Curva
- **Tagline**: *"Anúncios abaixo da curva."*
- **Metáfora central**: a curva de regressão preço × km. Anúncios *abaixo* dela são oportunidades. Verde só é usado pra sinalizar isso — nunca decorativo.
- **Tom**: editorial, analítico, denso porém calmo. Hairlines de 1px, paper warm, tipografia carregando a hierarquia.

## Próximos passos sugeridos

1. Ler `DESIGN_SYSTEM.md` e copiar `tokens.css` (ou `tokens.tailwind.config.js`) pro projeto.
2. Ler `DATA_MODEL.md` e criar as migrations + models.
3. Implementar o **scoring de curva** (algoritmo em `source/src/data.js` — função `estimateFair` + cálculo de `score`). Esse cálculo deve ser feito server-side a cada normalização de anúncio (job em fila ao indexar OLX/Webmotors/etc).
4. Implementar as telas seguindo `SCREENS.md`, na ordem: Dashboard → Tabela → Login/Cadastro → Alertas.
5. O scatter plot do Dashboard é o componente mais complexo — pode usar D3 ou um wrapper Vue/Alpine; o JSX de referência mostra os passos: regressão exponencial, dots coloridos por score, banda mediana, callout de hover.
