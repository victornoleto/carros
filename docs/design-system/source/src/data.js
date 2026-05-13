// Mock car data + helpers for Curva
// All prices in BRL, km in thousands.

window.CARROS_DATA = (function () {
  // models: realistic Brazilian used-car market
  const MODELS = [
    { make: 'Mitsubishi', model: 'Lancer', version: 'GT 2.0 16V',           ref: 78500 },
    { make: 'Mitsubishi', model: 'Lancer', version: 'HL-T 2.0',             ref: 72000 },
    { make: 'Honda',      model: 'Civic',  version: 'EXR 2.0 Flex',         ref: 89000 },
    { make: 'Honda',      model: 'Civic',  version: 'Touring 1.5 Turbo',    ref: 124000 },
    { make: 'Toyota',     model: 'Corolla',version: 'XEi 2.0 Flex',         ref: 115000 },
    { make: 'Toyota',     model: 'Corolla',version: 'Altis 1.8 Hybrid',     ref: 158000 },
    { make: 'Volkswagen', model: 'Golf',   version: 'GTI 2.0 TSI',          ref: 142000 },
    { make: 'Volkswagen', model: 'Jetta',  version: 'GLI 350 TSI',          ref: 168000 },
    { make: 'Hyundai',    model: 'HB20',   version: 'Comfort Plus 1.6',     ref: 62000 },
    { make: 'Fiat',       model: 'Pulse',  version: 'Impetus 1.0 Turbo',    ref: 96000 },
    { make: 'Chevrolet',  model: 'Cruze',  version: 'LT 1.4 Turbo',         ref: 89000 },
    { make: 'Nissan',     model: 'Sentra', version: 'SL 2.0 CVT',           ref: 84000 },
  ];

  const CITIES = [
    { city: 'São Paulo', uf: 'SP' },
    { city: 'Campinas', uf: 'SP' },
    { city: 'Santos', uf: 'SP' },
    { city: 'Rio de Janeiro', uf: 'RJ' },
    { city: 'Niterói', uf: 'RJ' },
    { city: 'Belo Horizonte', uf: 'MG' },
    { city: 'Curitiba', uf: 'PR' },
    { city: 'Porto Alegre', uf: 'RS' },
    { city: 'Florianópolis', uf: 'SC' },
    { city: 'Brasília', uf: 'DF' },
  ];

  const PROVIDERS = ['OLX', 'Webmotors', 'iCarros', 'MercadoLivre'];

  // seeded random
  let seed = 42;
  const rand = () => { seed = (seed * 9301 + 49297) % 233280; return seed / 233280; };
  const pick = (a) => a[Math.floor(rand() * a.length)];

  // Price model: ref price, depreciate by age (~9% per year, declining)
  // and by km (~R$0.30/km), with noise. Some listings are deliberately
  // below curve (good deals) or above (overpriced).
  function estimateFair(refPrice, year, km) {
    const age = 2026 - year;
    const ageFactor = Math.pow(0.94, age);
    const kmPenalty = Math.min(km * 0.12, refPrice * 0.28);
    return Math.max(refPrice * ageFactor - kmPenalty, refPrice * 0.42);
  }

  const listings = [];
  let id = 1000;
  for (const m of MODELS) {
    // 14-20 listings per model — enough density for the scatter to read
    const count = 14 + Math.floor(rand() * 7);
    for (let i = 0; i < count; i++) {
      const year = 2014 + Math.floor(rand() * 11); // 2014..2024
      const km = Math.round((30 + rand() * 180) * 1000); // 30k..210k
      const fair = estimateFair(m.ref, year, km);
      // bias: 25% great deals, 15% bad, rest fair-ish
      const r = rand();
      let priceMul;
      if (r < 0.18)      priceMul = 0.74 + rand() * 0.10; // great deal
      else if (r < 0.30) priceMul = 0.86 + rand() * 0.06; // good
      else if (r < 0.78) priceMul = 0.94 + rand() * 0.10; // fair
      else if (r < 0.92) priceMul = 1.06 + rand() * 0.08; // a bit high
      else               priceMul = 1.18 + rand() * 0.12; // overpriced
      const price = Math.round(fair * priceMul / 100) * 100;
      const score = (fair - price) / fair; // positive = below curve = good
      const loc = pick(CITIES);
      const provider = pick(PROVIDERS);
      const daysAgo = Math.floor(rand() * 21);
      const photos = Math.floor(8 + rand() * 22);
      listings.push({
        id: 'L' + (id++),
        make: m.make,
        model: m.model,
        version: m.version,
        year,
        km,
        price,
        fair: Math.round(fair),
        score,
        city: loc.city,
        uf: loc.uf,
        provider,
        daysAgo,
        photos,
        url: '#',
        seller: rand() < 0.65 ? 'Particular' : 'Loja',
      });
    }
  }

  // sort by score desc default
  listings.sort((a, b) => b.score - a.score);

  return {
    MODELS,
    CITIES,
    PROVIDERS,
    listings,
    fmtBRL(n) {
      return 'R$ ' + n.toLocaleString('pt-BR', { maximumFractionDigits: 0 });
    },
    fmtBRLshort(n) {
      if (n >= 1000) return 'R$ ' + (n / 1000).toLocaleString('pt-BR', { maximumFractionDigits: 1 }) + 'k';
      return 'R$ ' + n;
    },
    fmtKM(km) {
      return km.toLocaleString('pt-BR') + ' km';
    },
    fmtKMshort(km) {
      return Math.round(km / 1000) + 'k km';
    },
    scoreBucket(s) {
      // s is signed: positive = good
      if (s >= 0.12) return 'great';
      if (s >= 0.04) return 'good';
      if (s >= -0.04) return 'fair';
      if (s >= -0.12) return 'high';
      return 'bad';
    },
    scoreLabel(s) {
      const pct = Math.round(Math.abs(s) * 100);
      if (s >= 0.04) return `−${pct}% curva`;
      if (s <= -0.04) return `+${pct}% curva`;
      return `na curva`;
    },
    scoreTone(s) {
      if (s >= 0.04) return 'good';
      if (s <= -0.04) return 'warn';
      return 'neutral';
    },
  };
})();
