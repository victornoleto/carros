// Curva — Dashboard (scatter map of opportunities)
// Renders the "Mapa de oportunidades": preço × km with regression curve,
// outliers below curve glow green. Filters + KPIs + top-deals rail.

const CurvaDashboard = (function () {
  const D = window.CARROS_DATA;
  const { useState, useMemo, useRef, useEffect } = React;

  // ── Stats helpers ────────────────────────────────────────────────
  function median(arr) {
    const s = [...arr].sort((a, b) => a - b);
    const m = Math.floor(s.length / 2);
    return s.length % 2 ? s[m] : (s[m - 1] + s[m]) / 2;
  }

  // ── Filter Sidebar ───────────────────────────────────────────────
  function Sidebar({ filters, setFilters, compact }) {
    const F = filters;
    const set = (k, v) => setFilters({ ...F, [k]: v });
    return (
      <aside style={{
        width: compact ? '100%' : 280,
        padding: '24px 24px',
        background: 'var(--surface)',
        borderRight: compact ? 'none' : '1px solid var(--line)',
        display: 'flex', flexDirection: 'column', gap: 22,
      }}>
        <div>
          <div className="t-up" style={{ marginBottom: 10 }}>Filtros</div>
          <div style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: 12, color: 'var(--mute)' }}>
            <span className="mono">{F._count.toLocaleString('pt-BR')}</span>
            <span>de</span>
            <span className="mono">{F._total.toLocaleString('pt-BR')}</span>
            <span>anúncios</span>
          </div>
        </div>

        <FilterGroup title="Modelo">
          <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6 }}>
            {['Civic', 'Lancer', 'Corolla', 'Golf', 'Jetta', 'Cruze', 'Sentra'].map(m => (
              <Chip key={m} active={F.model === m} onClick={() => set('model', F.model === m ? null : m)}>
                {m}
              </Chip>
            ))}
          </div>
        </FilterGroup>

        <FilterGroup title="Versão" subtitle="Civic">
          <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
            {['EXR 2.0 Flex', 'Touring 1.5 Turbo', 'EX 1.8', 'LX 1.8'].map((v, i) => (
              <label key={v} style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: 13, cursor: 'pointer' }}>
                <input type="checkbox" defaultChecked={i < 2} style={{ accentColor: 'var(--ink)' }} />
                <span>{v}</span>
                <span className="mono" style={{ marginLeft: 'auto', color: 'var(--mute)', fontSize: 11 }}>{[28, 21, 14, 19][i]}</span>
              </label>
            ))}
          </div>
        </FilterGroup>

        <FilterGroup title="Ano">
          <RangeBar min={2014} max={2024} v0={F.yearMin} v1={F.yearMax}
            onChange={(a, b) => setFilters({ ...F, yearMin: a, yearMax: b })}
            format={(n) => n} />
        </FilterGroup>

        <FilterGroup title="Preço">
          <RangeBar min={40000} max={180000} step={5000} v0={F.priceMin} v1={F.priceMax}
            onChange={(a, b) => setFilters({ ...F, priceMin: a, priceMax: b })}
            format={D.fmtBRLshort} />
        </FilterGroup>

        <FilterGroup title="Quilometragem">
          <RangeBar min={0} max={220000} step={5000} v0={F.kmMin} v1={F.kmMax}
            onChange={(a, b) => setFilters({ ...F, kmMin: a, kmMax: b })}
            format={D.fmtKMshort} />
        </FilterGroup>

        <FilterGroup title="Estado">
          <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6 }}>
            {['SP', 'RJ', 'MG', 'PR', 'RS', 'SC', 'DF'].map(s => (
              <Chip key={s} active={F.ufs.includes(s)}
                onClick={() => set('ufs', F.ufs.includes(s) ? F.ufs.filter(x => x !== s) : [...F.ufs, s])}>
                {s}
              </Chip>
            ))}
          </div>
        </FilterGroup>

        <FilterGroup title="Provider">
          <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6 }}>
            {D.PROVIDERS.map(p => (
              <Chip key={p} active={F.providers.includes(p)}
                onClick={() => set('providers', F.providers.includes(p) ? F.providers.filter(x => x !== p) : [...F.providers, p])}>
                {p}
              </Chip>
            ))}
          </div>
        </FilterGroup>

        <FilterGroup title="Apenas">
          <label style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: 13, cursor: 'pointer' }}>
            <input type="checkbox" defaultChecked style={{ accentColor: 'var(--good)' }} />
            <span>Abaixo da curva</span>
            <span className="badge badge--good" style={{ marginLeft: 'auto' }}>{F._good ?? 0}</span>
          </label>
          <label style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: 13, cursor: 'pointer' }}>
            <input type="checkbox" style={{ accentColor: 'var(--ink)' }} />
            <span>Apenas particular</span>
          </label>
          <label style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: 13, cursor: 'pointer' }}>
            <input type="checkbox" style={{ accentColor: 'var(--ink)' }} />
            <span>Novos hoje</span>
          </label>
        </FilterGroup>
      </aside>
    );
  }

  function FilterGroup({ title, subtitle, children }) {
    return (
      <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
        <div style={{ display: 'flex', alignItems: 'baseline', justifyContent: 'space-between' }}>
          <div style={{ fontSize: 12, fontWeight: 600, color: 'var(--ink)' }}>{title}</div>
          {subtitle && <div style={{ fontSize: 10, color: 'var(--mute-2)', textTransform: 'uppercase', letterSpacing: '0.06em' }}>{subtitle}</div>}
        </div>
        {children}
      </div>
    );
  }

  function Chip({ active, children, onClick }) {
    return (
      <button onClick={onClick}
        className={`chip ${active ? 'chip--active' : ''}`}
        style={{ fontSize: 12 }}>
        {children}
      </button>
    );
  }

  function RangeBar({ min, max, step = 1, v0, v1, onChange, format }) {
    // dual-handle range visual (non-interactive bar with knobs at v0,v1)
    const span = max - min;
    const left = ((v0 - min) / span) * 100;
    const right = ((v1 - min) / span) * 100;
    return (
      <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', fontFamily: 'var(--font-mono)', fontSize: 12 }}>
          <span>{format(v0)}</span>
          <span style={{ color: 'var(--mute-2)' }}>—</span>
          <span>{format(v1)}</span>
        </div>
        <div style={{ position: 'relative', height: 22 }}>
          <div style={{ position: 'absolute', top: 10, left: 0, right: 0, height: 2, background: 'var(--line)' }} />
          <div style={{ position: 'absolute', top: 10, left: left + '%', width: (right - left) + '%', height: 2, background: 'var(--ink)' }} />
          <Knob pos={left} />
          <Knob pos={right} />
        </div>
      </div>
    );
  }
  function Knob({ pos }) {
    return (
      <div style={{
        position: 'absolute', top: 5, left: `calc(${pos}% - 6px)`,
        width: 12, height: 12, borderRadius: 999, background: 'var(--surface)',
        border: '2px solid var(--ink)', boxShadow: '0 1px 4px rgba(0,0,0,0.15)',
      }} />
    );
  }

  // ── Scatter plot ─────────────────────────────────────────────────
  function ScatterPlot({ listings, highlight = [], onHover, hovered, width = 760, height = 460, counts }) {
    // X: km (0–220k), Y: price (25k–170k)
    const xMin = 0, xMax = 220000;
    const yMin = 25000, yMax = 170000;
    const pad = { l: 56, r: 24, t: 24, b: 44 };
    const w = width - pad.l - pad.r;
    const h = height - pad.t - pad.b;

    const xs = (x) => pad.l + ((x - xMin) / (xMax - xMin)) * w;
    const ys = (y) => pad.t + (1 - (y - yMin) / (yMax - yMin)) * h;

    // build regression curve (visual): exp decay from top-left to bottom-right
    const curvePts = [];
    for (let i = 0; i <= 40; i++) {
      const km = xMin + (xMax - xMin) * (i / 40);
      // approx: price = 145000 * exp(-km / 180000)
      const price = 145000 * Math.exp(-km / 200000);
      curvePts.push([xs(km), ys(price)]);
    }
    const curveD = 'M ' + curvePts.map(p => p.join(' ')).join(' L ');

    // band (±10%) around the curve
    const upperPts = curvePts.map(([x, y]) => [x, y - 22]);
    const lowerPts = curvePts.map(([x, y]) => [x, y + 22]);
    const bandD = 'M ' + upperPts.map(p => p.join(' ')).join(' L ') +
      ' L ' + [...lowerPts].reverse().map(p => p.join(' ')).join(' L ') + ' Z';

    return (
      <div style={{ position: 'relative' }}>
        <svg width={width} height={height} style={{ display: 'block' }}>
          {/* surface bg */}
          <rect x={pad.l} y={pad.t} width={w} height={h} fill="var(--surface-2)" />
          {/* horizontal grid */}
          {[40, 60, 80, 100, 120, 140, 160].map(yk => (
            <g key={'h' + yk}>
              <line x1={pad.l} x2={pad.l + w} y1={ys(yk * 1000)} y2={ys(yk * 1000)} stroke="var(--line)" strokeDasharray="2 4" />
              <text x={pad.l - 8} y={ys(yk * 1000) + 4} fontSize="10" fill="var(--mute)" textAnchor="end" fontFamily="var(--font-mono)">
                {yk}k
              </text>
            </g>
          ))}
          {/* vertical grid */}
          {[0, 50, 100, 150, 200].map(xk => (
            <g key={'v' + xk}>
              <line x1={xs(xk * 1000)} x2={xs(xk * 1000)} y1={pad.t} y2={pad.t + h} stroke="var(--line)" strokeDasharray="2 4" />
              <text x={xs(xk * 1000)} y={pad.t + h + 18} fontSize="10" fill="var(--mute)" textAnchor="middle" fontFamily="var(--font-mono)">
                {xk}k km
              </text>
            </g>
          ))}

          {/* axis labels */}
          <text x={pad.l} y={pad.t - 8} fontSize="10" fill="var(--mute)"
            fontFamily="var(--font-mono)">
            preço
          </text>
          <text x={pad.l + w} y={pad.t + h + 36} fontSize="10" fill="var(--mute)"
            fontFamily="var(--font-mono)" textAnchor="end">
            quilometragem
          </text>

          {/* "abaixo da curva" zone shading */}
          <path d={`M ${pad.l} ${ys(40000)} L ${curvePts.map(p => p.join(' ')).join(' L ')} L ${pad.l + w} ${ys(40000)} Z`}
            fill="rgba(26,108,77,0.04)" />

          {/* regression band */}
          <path d={bandD} fill="rgba(20,17,13,0.05)" />
          {/* regression curve */}
          <path d={curveD} stroke="var(--mute)" strokeWidth="1.3" fill="none" strokeDasharray="4 3" />
          <text x={pad.l + w * 0.62} y={ys(145000 * Math.exp(-(xMax * 0.62) / 200000)) - 8}
            fontSize="10" fill="var(--mute)" fontFamily="var(--font-mono)">
            curva mediana
          </text>

          {/* dots */}
          {listings.map(l => {
            const tone = D.scoreTone(l.score);
            const isHighlight = highlight.includes(l.id) || hovered === l.id;
            const color = tone === 'good' ? '#1a6c4d' : tone === 'warn' ? '#b04421' : '#8b7d5c';
            const r = isHighlight ? 6 : Math.abs(l.score) >= 0.10 ? 4.5 : 3.5;
            const x = xs(l.km), y = ys(l.price);
            if (x < pad.l || x > pad.l + w || y < pad.t || y > pad.t + h) return null;
            return (
              <g key={l.id} onMouseEnter={() => onHover?.(l.id)} onMouseLeave={() => onHover?.(null)}
                style={{ cursor: 'pointer' }}>
                {isHighlight && (
                  <circle cx={x} cy={y} r={r + 5} fill={color} opacity="0.18" />
                )}
                <circle cx={x} cy={y} r={r} fill={color}
                  stroke={isHighlight ? '#fff' : 'none'} strokeWidth={isHighlight ? 1.5 : 0}
                  opacity={isHighlight ? 1 : 0.85} />
              </g>
            );
          })}

          {/* legend, top-right */}
          <g transform={`translate(${pad.l + w - 220}, ${pad.t + 10})`}>
            <rect x="0" y="0" width="220" height="58" rx="6" fill="var(--surface)" stroke="var(--line)" />
            <circle cx="14" cy="18" r="4" fill="#1a6c4d" />
            <text x="24" y="22" fontSize="11" fill="var(--ink)" fontFamily="var(--font-body)">abaixo da curva</text>
            <text x="200" y="22" fontSize="11" fill="var(--mute)" fontFamily="var(--font-mono)" textAnchor="end">{counts?.good ?? 0}</text>
            <circle cx="14" cy="36" r="4" fill="#8b7d5c" />
            <text x="24" y="40" fontSize="11" fill="var(--ink)" fontFamily="var(--font-body)">na curva</text>
            <text x="200" y="40" fontSize="11" fill="var(--mute)" fontFamily="var(--font-mono)" textAnchor="end">{counts?.fair ?? 0}</text>
            <circle cx="14" cy="50" r="4" fill="#b04421" />
            <text x="24" y="54" fontSize="11" fill="var(--ink)" fontFamily="var(--font-body)">acima da curva</text>
            <text x="200" y="54" fontSize="11" fill="var(--mute)" fontFamily="var(--font-mono)" textAnchor="end">{counts?.high ?? 0}</text>
          </g>
        </svg>

        {/* hover/highlight callout */}
        {hovered && (() => {
          const l = listings.find(x => x.id === hovered);
          if (!l) return null;
          const x = xs(l.km), y = ys(l.price);
          const flipX = x > width - 240;
          const flipY = y > height - 170;
          return (
            <div style={{
              position: 'absolute',
              left: flipX ? x - 234 : x + 12,
              top: flipY ? y - 160 : y - 12,
              width: 220,
              background: 'var(--surface)',
              border: '1px solid var(--ink)',
              borderRadius: 8,
              padding: 12,
              boxShadow: '0 8px 24px rgba(0,0,0,0.12)',
              pointerEvents: 'none',
              zIndex: 5,
            }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', marginBottom: 6 }}>
                <div style={{ fontSize: 12, fontWeight: 600 }}>{l.make} {l.model}</div>
                <window.CurvaScoreBadge score={l.score} />
              </div>
              <div style={{ fontSize: 11, color: 'var(--mute)', marginBottom: 8 }}>{l.version} · {l.year}</div>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline' }}>
                <window.CurvaPrice value={l.price} size="lg" />
                <span className="mono" style={{ fontSize: 12, color: 'var(--mute)' }}>{D.fmtKMshort(l.km)}</span>
              </div>
              <div style={{ marginTop: 8, paddingTop: 8, borderTop: '1px solid var(--line)', fontSize: 11, color: 'var(--mute)', display: 'flex', justifyContent: 'space-between' }}>
                <span>{l.city}/{l.uf}</span>
                <span className="mono">{l.provider}</span>
              </div>
            </div>
          );
        })()}
      </div>
    );
  }

  // ── KPI strip ────────────────────────────────────────────────────
  function KPIs({ listings }) {
    const prices = listings.map(l => l.price);
    const kms = listings.map(l => l.km);
    const goodCount = listings.filter(l => l.score >= 0.04).length;
    const items = [
      { label: 'Anúncios', value: listings.length.toLocaleString('pt-BR'), accent: false },
      { label: 'Preço mediano', value: D.fmtBRLshort(median(prices)), accent: false },
      { label: 'Km mediano', value: D.fmtKMshort(median(kms)), accent: false },
      { label: 'Abaixo da curva', value: goodCount, accent: 'good' },
      { label: 'Atualizado', value: 'há 12 min', sub: true, accent: false },
    ];
    return (
      <div style={{ display: 'flex', gap: 0, borderTop: '1px solid var(--line)', borderBottom: '1px solid var(--line)' }}>
        {items.map((it, i) => (
          <div key={i} style={{
            flex: 1, padding: '18px 24px',
            borderLeft: i > 0 ? '1px solid var(--line)' : 'none',
            display: 'flex', flexDirection: 'column', gap: 6,
          }}>
            <div className="t-up">{it.label}</div>
            <div className={it.sub ? 'mono' : 'num-lg'}
              style={{
                fontSize: it.sub ? 18 : 28,
                color: it.accent === 'good' ? 'var(--good)' : 'var(--ink)',
                fontFamily: it.sub ? 'var(--font-mono)' : 'var(--font-display)',
                fontWeight: it.sub ? 500 : 600,
              }}>
              {it.value}
            </div>
          </div>
        ))}
      </div>
    );
  }

  // ── Top-deals rail (right of scatter) ───────────────────────────
  function DealsRail({ listings, onHover, hovered }) {
    const top = listings.filter(l => l.score >= 0.04).slice(0, 6);
    return (
      <div style={{ width: 300, display: 'flex', flexDirection: 'column' }}>
        <div style={{ padding: '14px 16px', borderBottom: '1px solid var(--line)', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
          <div style={{ fontSize: 13, fontWeight: 600 }}>Abaixo da curva</div>
          <span className="badge badge--good">{top.length} {top.length === 1 ? 'novo' : 'novos'}</span>
        </div>
        <div style={{ flex: 1, overflow: 'auto' }}>
          {top.length === 0 && (
            <div style={{ padding: 24, fontSize: 12, color: 'var(--mute)', textAlign: 'center', lineHeight: 1.6 }}>
              Nenhum anúncio abaixo da curva nos filtros atuais. Tente relaxar preço ou km.
            </div>
          )}
          {top.map(l => (
            <div key={l.id}
              onMouseEnter={() => onHover?.(l.id)}
              onMouseLeave={() => onHover?.(null)}
              style={{
                padding: '14px 16px', borderBottom: '1px solid var(--line)',
                background: hovered === l.id ? 'var(--surface-2)' : 'transparent',
                cursor: 'pointer', transition: 'background .12s',
              }}>
              <div style={{ display: 'flex', alignItems: 'baseline', justifyContent: 'space-between', marginBottom: 4 }}>
                <div style={{ fontSize: 13, fontWeight: 600 }}>{l.make} {l.model}</div>
                <window.CurvaScoreBadge score={l.score} />
              </div>
              <div style={{ fontSize: 11, color: 'var(--mute)', marginBottom: 8 }}>
                {l.version}
              </div>
              <div style={{ display: 'flex', alignItems: 'baseline', justifyContent: 'space-between' }}>
                <window.CurvaPrice value={l.price} size="lg" />
                <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'flex-end' }}>
                  <span className="mono" style={{ fontSize: 11, color: 'var(--mute)' }}>{l.year} · {D.fmtKMshort(l.km)}</span>
                  <span className="mono" style={{ fontSize: 10, color: 'var(--mute-2)' }}>{l.city}/{l.uf}</span>
                </div>
              </div>
            </div>
          ))}
        </div>
        <div style={{ padding: 14, borderTop: '1px solid var(--line)' }}>
          <button className="btn btn--good" style={{ width: '100%' }}>
            Salvar busca como alerta
          </button>
        </div>
      </div>
    );
  }

  // ── Active filter strip (between KPIs and chart) ────────────────
  function ActiveFilters({ filters, setFilters }) {
    const chips = [];
    if (filters.model) chips.push({ key: 'model', label: `Modelo · ${filters.model}` });
    chips.push({ key: 'year', label: `Ano · ${filters.yearMin}–${filters.yearMax}` });
    chips.push({ key: 'price', label: `Preço · ${D.fmtBRLshort(filters.priceMin)}–${D.fmtBRLshort(filters.priceMax)}` });
    chips.push({ key: 'km', label: `Km · até ${D.fmtKMshort(filters.kmMax)}` });
    if (filters.ufs.length) chips.push({ key: 'ufs', label: `UF · ${filters.ufs.join(', ')}` });
    return (
      <div style={{
        display: 'flex', alignItems: 'center', gap: 8,
        padding: '12px 28px', background: 'var(--paper)',
        borderBottom: '1px solid var(--line)',
        flexWrap: 'wrap',
      }}>
        <span className="t-up" style={{ marginRight: 4 }}>Ativos</span>
        {chips.map(c => (
          <span key={c.key} className="chip chip--active" style={{ fontSize: 12 }}>
            {c.label}
            <span className="chip__x" style={{ marginLeft: 4 }}>×</span>
          </span>
        ))}
        <span style={{ flex: 1 }} />
        <button className="btn btn--ghost btn--sm">Limpar tudo</button>
      </div>
    );
  }

  // ── Main composition ─────────────────────────────────────────────
  function Dashboard({ loggedIn = false, onNav, embedded = false, width = 1440, hideTopBar = false }) {
    const [filters, setFilters] = useState({
      model: 'Civic',
      yearMin: 2014, yearMax: 2024,
      priceMin: 30000, priceMax: 180000,
      kmMin: 0, kmMax: 220000,
      ufs: ['SP', 'RJ', 'MG'],
      providers: ['OLX', 'Webmotors', 'iCarros', 'MercadoLivre'],
    });
    const [hovered, setHovered] = useState(null);

    const allListings = D.listings;
    const filtered = useMemo(() => {
      return allListings.filter(l => {
        if (filters.model && l.model !== filters.model) return false;
        if (l.year < filters.yearMin || l.year > filters.yearMax) return false;
        if (l.price < filters.priceMin || l.price > filters.priceMax) return false;
        if (l.km > filters.kmMax) return false;
        return true;
      });
    }, [filters]);

    const goodCount = filtered.filter(l => l.score >= 0.04).length;
    const fairCount = filtered.filter(l => l.score < 0.04 && l.score > -0.04).length;
    const highCount = filtered.filter(l => l.score <= -0.04).length;

    // pre-select 1 nice highlighted dot
    const featured = filtered.find(l => l.score >= 0.14) || filtered[0];

    return (
      <div className="curva" style={{ width, minHeight: embedded ? 'auto' : 900, display: 'flex', flexDirection: 'column' }}>
        {!hideTopBar && <window.CurvaTopBar active="dashboard" onNav={onNav} loggedIn={loggedIn} />}

        {/* Header strip */}
        <div style={{
          display: 'flex', alignItems: 'flex-end', justifyContent: 'space-between',
          padding: '28px 28px 20px', gap: 20,
        }}>
          <div>
            <div className="t-up" style={{ marginBottom: 8 }}>Mapa de oportunidades</div>
            <h1 className="display" style={{
              fontSize: 38, fontWeight: 600, letterSpacing: '-0.03em', margin: 0,
            }}>
              Honda Civic <span style={{ color: 'var(--mute-2)' }}>· {filtered.length} anúncios</span>
            </h1>
            <div style={{ fontSize: 13, color: 'var(--mute)', marginTop: 6 }}>
              {goodCount} anúncios abaixo da curva agora. <a href="#" style={{ color: 'var(--good)', fontWeight: 600, textDecoration: 'none' }} onClick={(e) => { e.preventDefault(); onNav?.('criarAlerta'); }}>Salvar alerta →</a>
            </div>
          </div>
          <div style={{ display: 'flex', gap: 8 }}>
            <button className="btn btn--ghost btn--sm">Exportar</button>
            <button className="btn btn--ghost btn--sm">Comparar</button>
            <button className="btn btn--primary btn--sm">Salvar busca</button>
          </div>
        </div>

        <KPIs listings={filtered} />
        <ActiveFilters filters={filters} setFilters={setFilters} />

        <div style={{ display: 'flex', flex: 1, minHeight: 540 }}>
          <Sidebar filters={{ ...filters, _count: filtered.length, _total: allListings.length, _good: goodCount }} setFilters={setFilters} />
          <div style={{ flex: 1, padding: '24px 24px 24px 24px', background: 'var(--paper)', display: 'flex', flexDirection: 'column', gap: 18 }}>
            <div style={{ background: 'var(--surface)', borderRadius: 10, border: '1px solid var(--line)', padding: '12px 12px 10px' }}>
              <ScatterPlot
                listings={filtered}
                hovered={hovered || featured?.id}
                onHover={setHovered}
                width={width === 1440 ? 760 : Math.max(600, width - 280 - 300 - 80)}
                height={460}
                counts={{ good: goodCount, fair: fairCount, high: highCount }}
              />
            </div>

            {/* Group by model strip */}
            <div style={{ background: 'var(--surface)', borderRadius: 10, border: '1px solid var(--line)', padding: '14px 16px' }}>
              <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 12 }}>
                <div className="t-up">Agrupado por modelo</div>
                <span style={{ fontSize: 11, color: 'var(--mute)' }} className="mono">mediana ± banda</span>
              </div>
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr 1fr', gap: 16 }}>
                {['Civic', 'Lancer', 'Corolla', 'Golf'].map((m, i) => (
                  <ModelBar key={m} model={m} count={[137, 88, 124, 96][i]} median={[112, 76, 118, 138][i]} good={[33, 19, 21, 12][i]} />
                ))}
              </div>
            </div>
          </div>
          <div style={{ borderLeft: '1px solid var(--line)', background: 'var(--surface)' }}>
            <DealsRail listings={filtered} hovered={hovered} onHover={setHovered} />
          </div>
        </div>
      </div>
    );
  }

  function ModelBar({ model, count, median, good }) {
    return (
      <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline' }}>
          <div style={{ fontSize: 13, fontWeight: 600 }}>{model}</div>
          <span className="mono" style={{ fontSize: 11, color: 'var(--mute)' }}>{count} anúncios</span>
        </div>
        <div style={{ position: 'relative', height: 36, background: 'var(--surface-2)', borderRadius: 4, overflow: 'hidden' }}>
          {/* Histogram bars */}
          {Array.from({ length: 18 }).map((_, i) => {
            const h = 12 + Math.sin(i * 0.7 + model.length) * 10 + Math.cos(i * 0.4) * 8 + 14;
            const isMedian = Math.abs(i - 9) < 1.2;
            return (
              <div key={i} style={{
                position: 'absolute', bottom: 0, left: (i / 18) * 100 + '%',
                width: 'calc(100% / 18 - 1px)', height: Math.max(4, Math.min(32, h)),
                background: isMedian ? 'var(--ink)' : 'var(--line-2)',
                borderRadius: '2px 2px 0 0',
              }} />
            );
          })}
        </div>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline' }}>
          <span style={{ fontSize: 12 }}>
            <span className="mono" style={{ fontWeight: 600 }}>R$ {median}k</span>
            <span style={{ color: 'var(--mute)', fontSize: 11 }}> · mediana</span>
          </span>
          <span className="badge badge--good">{good} abaixo</span>
        </div>
      </div>
    );
  }

  return Dashboard;
})();

window.CurvaDashboard = CurvaDashboard;
