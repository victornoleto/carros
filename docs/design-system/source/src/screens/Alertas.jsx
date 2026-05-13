// Curva — Alertas (Meus alertas + Criar alerta)

const CurvaAlertas = (function () {
  const D = window.CARROS_DATA;
  const { useState } = React;

  // ── Mock saved alerts ──────────────────────────────────────────────
  const ALERTS = [
    {
      id: 'a1',
      name: 'Lancer GT abaixo de R$ 70k',
      model: 'Mitsubishi Lancer',
      filters: ['GT 2.0', '2014–2018', '≤ R$ 70.000', '≤ 120k km', 'SP · RJ'],
      status: 'active',
      matches: 4,
      newToday: 1,
      lastHit: 'há 3h',
      pulse: 8,
    },
    {
      id: 'a2',
      name: 'Civic Touring particular',
      model: 'Honda Civic',
      filters: ['Touring 1.5 Turbo', '2019+', '≤ R$ 130.000', '≤ 60k km', 'Apenas particular'],
      status: 'active',
      matches: 9,
      newToday: 3,
      lastHit: 'há 28min',
      pulse: 12,
    },
    {
      id: 'a3',
      name: 'Golf GTI abaixo da curva',
      model: 'Volkswagen Golf',
      filters: ['GTI 2.0 TSI', '2018+', 'Abaixo da curva (≥10%)', 'Sudeste'],
      status: 'active',
      matches: 2,
      newToday: 0,
      lastHit: 'há 2d',
      pulse: 4,
    },
    {
      id: 'a4',
      name: 'Corolla XEi até 80k km',
      model: 'Toyota Corolla',
      filters: ['XEi 2.0', '2018–2022', '≤ 80k km', 'SP'],
      status: 'paused',
      matches: 6,
      newToday: 0,
      lastHit: 'há 12d',
      pulse: 0,
    },
    {
      id: 'a5',
      name: 'Jetta GLI 350',
      model: 'Volkswagen Jetta',
      filters: ['GLI 350 TSI', '2019+', '≤ R$ 170.000', '≤ 60k km'],
      status: 'active',
      matches: 3,
      newToday: 1,
      lastHit: 'há 1d',
      pulse: 6,
    },
  ];

  // Tiny pulse strip showing notifications over last 14 days
  function PulseStrip({ value }) {
    return (
      <svg width="120" height="28" viewBox="0 0 120 28">
        {Array.from({ length: 14 }).map((_, i) => {
          const seed = (value + 1) * (i + 3);
          const h = value === 0 ? 1 : 2 + ((seed * 7) % 18);
          const today = i === 13;
          return (
            <rect key={i} x={i * 8.5} y={28 - h} width={6} height={h}
              fill={today && value > 0 ? '#1a6c4d' : value === 0 ? '#d3cab5' : '#8b7d5c'}
              opacity={today ? 1 : 0.5}
              rx="1" />
          );
        })}
      </svg>
    );
  }

  // ── Meus alertas ────────────────────────────────────────────────────
  function MeusAlertas({ onNav, width = 1440, hideTopBar = false }) {
    return (
      <div className="curva" style={{ width, minHeight: 900, display: 'flex', flexDirection: 'column' }}>
        {!hideTopBar && <window.CurvaTopBar active="alertas" onNav={onNav} loggedIn={true} />}

        {/* header */}
        <div style={{
          display: 'flex', alignItems: 'flex-end', justifyContent: 'space-between',
          padding: '36px 28px 24px', gap: 20, maxWidth: 1320, margin: '0 auto', width: '100%',
        }}>
          <div>
            <div className="t-up" style={{ marginBottom: 8 }}>Seus alertas</div>
            <h1 className="display" style={{ fontSize: 36, fontWeight: 600, letterSpacing: '-0.03em', margin: 0 }}>
              5 buscas ativas <span style={{ color: 'var(--good)' }}>· 5 novos hoje</span>
            </h1>
            <div style={{ fontSize: 13, color: 'var(--mute)', marginTop: 6 }}>
              Avisamos por email quando um anúncio aparecer abaixo da curva nos seus filtros.
            </div>
          </div>
          <button className="btn btn--primary btn--lg" onClick={() => onNav?.('criarAlerta')}>+ Novo alerta</button>
        </div>

        {/* Inbox summary */}
        <div style={{ maxWidth: 1320, margin: '0 auto', width: '100%', padding: '0 28px 16px', display: 'flex', gap: 12 }}>
          <SummaryTile n="5" l="novos hoje" tone="good" />
          <SummaryTile n="23" l="esta semana" />
          <SummaryTile n="R$ 12.4k" l="economia média vs. curva" tone="good" />
          <SummaryTile n="92%" l="taxa de relevância" />
        </div>

        {/* Alerts list */}
        <div style={{ maxWidth: 1320, margin: '0 auto', width: '100%', padding: '0 28px 32px' }}>
          <div style={{ background: 'var(--surface)', border: '1px solid var(--line)', borderRadius: 10, overflow: 'hidden' }}>
            <div style={{ display: 'grid', gridTemplateColumns: '40px 1fr 130px 150px 110px 60px', padding: '12px 18px', background: 'var(--surface-2)', borderBottom: '1px solid var(--line)', fontSize: 11, color: 'var(--mute)', textTransform: 'uppercase', letterSpacing: '0.08em', fontWeight: 600 }}>
              <span></span>
              <span>Alerta</span>
              <span style={{ textAlign: 'right' }}>Resultados</span>
              <span>Atividade · 14d</span>
              <span>Último hit</span>
              <span></span>
            </div>
            {ALERTS.map(a => (
              <AlertRow key={a.id} alert={a} />
            ))}
          </div>

          <div style={{ marginTop: 24, padding: '20px 24px', background: 'var(--paper-2)', borderRadius: 10, border: '1px dashed var(--line-2)', display: 'flex', alignItems: 'center', gap: 16 }}>
            <div style={{ flex: 1 }}>
              <div style={{ fontSize: 13, fontWeight: 600, marginBottom: 4 }}>Dica · Alertas combinados</div>
              <div style={{ fontSize: 12, color: 'var(--mute)' }}>
                Você pode criar um alerta abrangente (ex: "Lancer ou Civic em SP") e refinar os resultados depois — Curva ranqueia tudo pela posição na curva.
              </div>
            </div>
            <button className="btn btn--ghost btn--sm">Saber mais</button>
          </div>
        </div>
      </div>
    );
  }

  function SummaryTile({ n, l, tone }) {
    return (
      <div style={{ flex: 1, background: 'var(--surface)', border: '1px solid var(--line)', borderRadius: 10, padding: '14px 18px' }}>
        <div className="display" style={{ fontSize: 26, fontWeight: 600, letterSpacing: '-0.025em', color: tone === 'good' ? 'var(--good)' : 'var(--ink)' }}>{n}</div>
        <div className="t-up" style={{ marginTop: 4 }}>{l}</div>
      </div>
    );
  }

  function AlertRow({ alert: a }) {
    const isPaused = a.status === 'paused';
    return (
      <div style={{
        display: 'grid', gridTemplateColumns: '40px 1fr 130px 150px 110px 60px',
        padding: '16px 18px', borderBottom: '1px solid var(--line)',
        alignItems: 'center', gap: 12,
        opacity: isPaused ? 0.6 : 1,
        transition: 'background .12s',
      }}
        className="alert-row"
      >
        <div>
          {isPaused ? (
            <span className="dot dot--neutral" style={{ width: 8, height: 8 }} />
          ) : a.newToday > 0 ? (
            <span className="dot dot--good dot--pulse" style={{ width: 8, height: 8, background: 'var(--good)' }} />
          ) : (
            <span className="dot dot--good" style={{ width: 8, height: 8 }} />
          )}
        </div>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 6, minWidth: 0 }}>
          <div style={{ fontSize: 14, fontWeight: 600 }}>{a.name}</div>
          <div style={{ display: 'flex', flexWrap: 'wrap', gap: 4 }}>
            {a.filters.map(f => (
              <span key={f} style={{
                fontSize: 11, padding: '2px 7px', background: 'var(--paper)',
                color: 'var(--mute)', borderRadius: 999, border: '1px solid var(--line)',
                fontFamily: 'var(--font-mono)',
              }}>{f}</span>
            ))}
          </div>
        </div>
        <div style={{ textAlign: 'right' }}>
          <div className="display" style={{ fontSize: 22, fontWeight: 600, letterSpacing: '-0.02em' }}>{a.matches}</div>
          {a.newToday > 0 ? (
            <span className="badge badge--good">+{a.newToday} hoje</span>
          ) : (
            <span style={{ fontSize: 11, color: 'var(--mute-2)' }}>sem novos</span>
          )}
        </div>
        <div>
          <PulseStrip value={a.pulse} />
        </div>
        <div style={{ fontSize: 12, color: 'var(--mute)' }} className="mono">
          {isPaused ? 'pausado' : a.lastHit}
        </div>
        <div style={{ display: 'flex', gap: 4, justifyContent: 'flex-end' }}>
          <button title="Editar" className="btn btn--ghost btn--sm" style={{ padding: '5px 8px', fontSize: 11 }}>·····</button>
        </div>
      </div>
    );
  }

  // ── Criar alerta ─────────────────────────────────────────────────────
  function CriarAlerta({ onNav, onSubmit, width = 1440, hideTopBar = false }) {
    const [name, setName] = useState('Civic Touring particular abaixo da curva');
    const [freq, setFreq] = useState('instant');
    return (
      <div className="curva" style={{ width, minHeight: 900, display: 'flex', flexDirection: 'column' }}>
        {!hideTopBar && <window.CurvaTopBar active="alertas" onNav={onNav} loggedIn={true} />}

        {/* breadcrumb header */}
        <div style={{ maxWidth: 1180, margin: '0 auto', width: '100%', padding: '36px 28px 0' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: 12, color: 'var(--mute)', marginBottom: 12 }}>
            <a onClick={() => onNav?.('alertas')} style={{ color: 'var(--mute)', cursor: 'pointer' }}>Meus alertas</a>
            <span>·</span>
            <span style={{ color: 'var(--ink)', fontWeight: 600 }}>Novo alerta</span>
          </div>
          <h1 className="display" style={{ fontSize: 34, fontWeight: 600, letterSpacing: '-0.03em', margin: 0 }}>
            Confirmar e nomear
          </h1>
          <div style={{ fontSize: 13, color: 'var(--mute)', marginTop: 6 }}>
            Confira os filtros da sua busca atual. Você sempre pode editar depois.
          </div>
        </div>

        <div style={{ maxWidth: 1180, margin: '0 auto', width: '100%', padding: '28px', display: 'grid', gridTemplateColumns: '1fr 420px', gap: 28, flex: 1 }}>
          {/* left: form */}
          <div style={{ background: 'var(--surface)', border: '1px solid var(--line)', borderRadius: 10, padding: 28, display: 'flex', flexDirection: 'column', gap: 24 }}>
            <div className="field">
              <label className="field__label">Nome do alerta</label>
              <input className="input" value={name} onChange={e => setName(e.target.value)} style={{ fontSize: 16 }} />
              <div style={{ fontSize: 11, color: 'var(--mute)' }}>Aparece no email e na lista. Pode ser qualquer coisa.</div>
            </div>

            <div>
              <div className="t-up" style={{ marginBottom: 12 }}>Filtros da busca</div>
              <div style={{ display: 'flex', flexDirection: 'column', gap: 0, border: '1px solid var(--line)', borderRadius: 8, overflow: 'hidden' }}>
                <FilterRow label="Modelo" value="Honda Civic" />
                <FilterRow label="Versão" value="Touring 1.5 Turbo" />
                <FilterRow label="Ano" value="2019 — 2024" />
                <FilterRow label="Preço" value="R$ 90.000 — R$ 145.000" />
                <FilterRow label="Quilometragem" value="até 70.000 km" />
                <FilterRow label="Localização" value="SP · RJ · MG" />
                <FilterRow label="Provider" value="Todos" muted />
                <FilterRow label="Vendedor" value="Apenas particular" />
                <FilterRow label="Posição na curva" value="≥ 10% abaixo" tone="good" />
              </div>
              <button className="btn btn--ghost btn--sm" style={{ marginTop: 12 }} onClick={() => onNav?.('dashboard')}>
                ← Voltar e ajustar filtros
              </button>
            </div>

            <div>
              <div className="t-up" style={{ marginBottom: 12 }}>Frequência</div>
              <div style={{ display: 'flex', gap: 8 }}>
                {[
                  { k: 'instant', l: 'Tempo real', s: 'avisa em até 5 min' },
                  { k: 'daily', l: 'Diário', s: 'resumo às 8h' },
                  { k: 'weekly', l: 'Semanal', s: 'segunda · 8h' },
                ].map(o => (
                  <button key={o.k} onClick={() => setFreq(o.k)}
                    style={{
                      flex: 1, padding: '14px 16px', textAlign: 'left',
                      background: freq === o.k ? 'var(--paper)' : 'var(--surface)',
                      border: '1px solid ' + (freq === o.k ? 'var(--ink)' : 'var(--line)'),
                      borderRadius: 8, cursor: 'pointer',
                      display: 'flex', flexDirection: 'column', gap: 4,
                      fontFamily: 'var(--font-body)',
                    }}>
                    <span style={{ fontSize: 13, fontWeight: 600 }}>{o.l}</span>
                    <span style={{ fontSize: 11, color: 'var(--mute)' }}>{o.s}</span>
                  </button>
                ))}
              </div>
            </div>

            <div>
              <div className="t-up" style={{ marginBottom: 12 }}>Canais</div>
              <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
                <ToggleRow label="Email · guilherme@gmail.com" defaultOn />
                <ToggleRow label="Web push (este navegador)" defaultOn />
                <ToggleRow label="WhatsApp · +55 (11) ●●●●● 4421" />
              </div>
            </div>
          </div>

          {/* right: preview */}
          <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
            <div style={{ background: 'var(--ink)', color: 'var(--paper)', borderRadius: 10, padding: 22 }}>
              <div style={{ fontSize: 11, textTransform: 'uppercase', letterSpacing: '0.08em', color: 'rgba(243,240,232,0.55)', fontFamily: 'var(--font-mono)', marginBottom: 12 }}>
                pré-visualização
              </div>
              <div className="display" style={{ fontSize: 22, fontWeight: 600, letterSpacing: '-0.02em', lineHeight: 1.15 }}>
                {name || 'Sem nome'}
              </div>
              <div style={{ marginTop: 14, fontSize: 13, color: 'rgba(243,240,232,0.7)', lineHeight: 1.5 }}>
                Vamos te avisar em <strong style={{ color: 'var(--paper)' }}>tempo real</strong> quando um Civic Touring 1.5 Turbo particular,
                2019+, ≤ 70k km, em SP/RJ/MG aparecer ao menos <strong style={{ color: '#4ddb98' }}>10% abaixo da curva</strong>.
              </div>
              <div style={{ marginTop: 18, paddingTop: 14, borderTop: '1px solid rgba(243,240,232,0.12)', display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 14 }}>
                <Mini n="9" l="hits agora" tone="good" />
                <Mini n="3/sem" l="cadência típica" />
                <Mini n="R$ 14k" l="economia média" tone="good" />
                <Mini n="-12%" l="vs. curva mediana" tone="good" />
              </div>
            </div>

            <div style={{ background: 'var(--surface)', border: '1px solid var(--line)', borderRadius: 10, padding: 18 }}>
              <div className="t-up" style={{ marginBottom: 10 }}>Top match agora</div>
              <div style={{ fontSize: 14, fontWeight: 600, marginBottom: 4 }}>Honda Civic Touring 1.5 Turbo</div>
              <div style={{ fontSize: 11, color: 'var(--mute)', marginBottom: 12 }}>2021 · 48.300 km · Campinas/SP · Webmotors</div>
              <div style={{ display: 'flex', alignItems: 'baseline', justifyContent: 'space-between' }}>
                <window.CurvaPrice value={104900} size="lg" />
                <window.CurvaScoreBadge score={0.14} />
              </div>
            </div>

            <div style={{ display: 'flex', gap: 8 }}>
              <button className="btn btn--ghost" style={{ flex: 1, justifyContent: 'center' }} onClick={() => onNav?.('alertas')}>
                Cancelar
              </button>
              <button className="btn btn--good btn--lg" style={{ flex: 2, justifyContent: 'center' }} onClick={() => onSubmit?.()}>
                Criar alerta
              </button>
            </div>
          </div>
        </div>
      </div>
    );
  }

  function FilterRow({ label, value, muted, tone }) {
    return (
      <div style={{ display: 'grid', gridTemplateColumns: '180px 1fr 80px', padding: '12px 16px', borderBottom: '1px solid var(--line)', alignItems: 'center', fontSize: 13 }}>
        <span className="t-up">{label}</span>
        <span style={{
          color: muted ? 'var(--mute-2)' : 'var(--ink)',
          fontFamily: 'var(--font-mono)', fontSize: 13,
        }}>
          {tone === 'good' && <span className="dot dot--good" style={{ marginRight: 6 }} />}
          {value}
        </span>
        <a href="#" style={{ textAlign: 'right', fontSize: 11, color: 'var(--mute)', textDecoration: 'none' }}>editar</a>
      </div>
    );
  }

  function ToggleRow({ label, defaultOn }) {
    const [on, setOn] = useState(defaultOn ?? false);
    return (
      <div onClick={() => setOn(!on)} style={{
        display: 'flex', alignItems: 'center', gap: 12,
        padding: '10px 14px', background: 'var(--surface-2)', borderRadius: 8,
        border: '1px solid var(--line)', cursor: 'pointer',
      }}>
        <div style={{
          width: 34, height: 20, borderRadius: 999,
          background: on ? 'var(--good)' : 'var(--line-2)',
          position: 'relative', transition: 'background .15s',
        }}>
          <div style={{
            position: 'absolute', top: 2, left: on ? 16 : 2,
            width: 16, height: 16, borderRadius: 999, background: '#fff',
            boxShadow: '0 1px 3px rgba(0,0,0,.2)', transition: 'left .15s',
          }} />
        </div>
        <span style={{ fontSize: 13 }}>{label}</span>
      </div>
    );
  }

  function Mini({ n, l, tone }) {
    return (
      <div>
        <div className="display" style={{ fontSize: 22, fontWeight: 600, letterSpacing: '-0.02em', color: tone === 'good' ? '#4ddb98' : 'var(--paper)' }}>{n}</div>
        <div style={{ fontFamily: 'var(--font-mono)', fontSize: 10, textTransform: 'uppercase', letterSpacing: '0.08em', color: 'rgba(243,240,232,0.55)', marginTop: 2 }}>{l}</div>
      </div>
    );
  }

  return { MeusAlertas, CriarAlerta };
})();

window.CurvaMeusAlertas = CurvaAlertas.MeusAlertas;
window.CurvaCriarAlerta = CurvaAlertas.CriarAlerta;
