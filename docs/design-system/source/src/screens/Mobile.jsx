// Curva — Mobile screens (390 wide). All set to fit inside ios_frame.
// One self-contained file. Each component takes no props except onNav.

const CurvaMobile = (function () {
  const D = window.CARROS_DATA;
  const { useState } = React;

  // ── Shared mobile chrome ──────────────────────────────────────────
  function MobileHeader({ title, sub, back, action, dark }) {
    return (
      <div style={{
        display: 'flex', alignItems: 'center', gap: 12,
        padding: '14px 18px 14px', background: dark ? 'var(--ink)' : 'var(--surface)',
        borderBottom: dark ? '1px solid rgba(243,240,232,0.08)' : '1px solid var(--line)',
        color: dark ? 'var(--paper)' : 'var(--ink)',
      }}>
        {back && (
          <button onClick={back} style={{
            border: 0, background: 'transparent', cursor: 'pointer',
            color: 'inherit', fontSize: 20, padding: 0, lineHeight: 1,
          }}>←</button>
        )}
        <div style={{ flex: 1, minWidth: 0 }}>
          <div style={{ fontFamily: 'var(--font-display)', fontSize: 17, fontWeight: 600, letterSpacing: '-0.02em' }}>
            {title}
          </div>
          {sub && <div style={{ fontSize: 11, color: dark ? 'rgba(243,240,232,0.55)' : 'var(--mute)', marginTop: 2 }}>{sub}</div>}
        </div>
        {action}
      </div>
    );
  }

  function MobileTabBar({ active, onNav }) {
    const items = [
      { id: 'dashboard', label: 'Mapa', icon: <IconMap /> },
      { id: 'tabela',    label: 'Anúncios', icon: <IconList /> },
      { id: 'alertas',   label: 'Alertas', icon: <IconBell /> },
      { id: 'perfil',    label: 'Conta', icon: <IconUser /> },
    ];
    return (
      <div style={{
        position: 'absolute', bottom: 0, left: 0, right: 0,
        display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)',
        background: 'var(--surface)', borderTop: '1px solid var(--line)',
        paddingBottom: 20,
      }}>
        {items.map(it => (
          <button key={it.id} onClick={() => onNav?.(it.id)}
            style={{
              border: 0, background: 'transparent', cursor: 'pointer',
              padding: '10px 4px 8px', display: 'flex', flexDirection: 'column',
              alignItems: 'center', gap: 4,
              color: active === it.id ? 'var(--ink)' : 'var(--mute-2)',
              fontFamily: 'var(--font-body)',
            }}>
            {it.icon}
            <span style={{ fontSize: 10, fontWeight: active === it.id ? 600 : 500 }}>{it.label}</span>
          </button>
        ))}
      </div>
    );
  }
  function IconMap() { return <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M2 16 L7 14 L13 16 L18 13 L18 5 L13 8 L7 6 L2 8 Z" stroke="currentColor" strokeWidth="1.4" strokeLinejoin="round"/><path d="M7 6 L7 14 M13 8 L13 16" stroke="currentColor" strokeWidth="1.4"/></svg>; }
  function IconList() { return <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M5 6h11M5 10h11M5 14h11" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round"/><circle cx="3" cy="6" r="1" fill="currentColor"/><circle cx="3" cy="10" r="1" fill="currentColor"/><circle cx="3" cy="14" r="1" fill="currentColor"/></svg>; }
  function IconBell() { return <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M5 14 L5 9 a5 5 0 0 1 10 0 L15 14 L17 16 L3 16 Z" stroke="currentColor" strokeWidth="1.4" strokeLinejoin="round"/><circle cx="10" cy="3.5" r="1" fill="currentColor"/></svg>; }
  function IconUser() { return <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="7" r="3" stroke="currentColor" strokeWidth="1.4"/><path d="M4 17 a6 6 0 0 1 12 0" stroke="currentColor" strokeWidth="1.4" strokeLinecap="round"/></svg>; }

  // ── Mobile: Dashboard ─────────────────────────────────────────────
  function Dashboard({ onNav }) {
    const top = D.listings.filter(l => l.score >= 0.04).slice(0, 4);
    return (
      <div className="curva" style={{ width: 390, height: 780, display: 'flex', flexDirection: 'column', background: 'var(--paper)', position: 'relative' }}>
        <MobileHeader
          title="Mapa de oportunidades"
          sub="Honda Civic · 137 anúncios"
          action={<window.CurvaLogo size={14} />}
        />
        {/* search/filter pill */}
        <div style={{ padding: '12px 16px', display: 'flex', gap: 8, overflowX: 'auto', background: 'var(--surface)', borderBottom: '1px solid var(--line)' }}>
          {['Civic', '2020+', '≤ 80k km', 'SP·RJ', 'abaixo da curva'].map((c, i) => (
            <span key={c} className={`chip ${i < 4 ? 'chip--active' : ''}`} style={{ fontSize: 11, whiteSpace: 'nowrap' }}>{c}</span>
          ))}
        </div>

        <div style={{ flex: 1, overflowY: 'auto', paddingBottom: 80 }}>
          {/* mini scatter */}
          <div style={{ padding: '16px', background: 'var(--surface)', borderBottom: '1px solid var(--line)' }}>
            <div style={{ display: 'flex', alignItems: 'baseline', justifyContent: 'space-between', marginBottom: 10 }}>
              <div className="t-up">Preço × km</div>
              <span className="badge badge--good">33 abaixo</span>
            </div>
            <MiniScatter />
            <div style={{ marginTop: 10, display: 'flex', justifyContent: 'space-between', fontSize: 11, color: 'var(--mute)' }}>
              <span className="mono">R$ 60k – R$ 160k</span>
              <span className="mono">0 – 200k km</span>
            </div>
          </div>

          {/* KPI row */}
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', background: 'var(--surface)', borderBottom: '1px solid var(--line)' }}>
            {[
              { l: 'Mediana', v: 'R$ 112k' },
              { l: 'Km mediano', v: '74k' },
              { l: 'Novos', v: '+12', tone: 'good' },
            ].map((k, i) => (
              <div key={i} style={{ padding: '12px 14px', borderLeft: i > 0 ? '1px solid var(--line)' : 'none' }}>
                <div className="t-up" style={{ fontSize: 9, marginBottom: 4 }}>{k.l}</div>
                <div className="display" style={{ fontSize: 18, fontWeight: 600, letterSpacing: '-0.02em', color: k.tone === 'good' ? 'var(--good)' : 'var(--ink)' }}>{k.v}</div>
              </div>
            ))}
          </div>

          {/* deals list */}
          <div style={{ padding: '14px 16px 6px' }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', marginBottom: 10 }}>
              <div style={{ fontSize: 14, fontWeight: 600 }}>Abaixo da curva</div>
              <a style={{ fontSize: 12, color: 'var(--mute)' }}>Ver todos →</a>
            </div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
              {top.map(l => <MobileListingCard key={l.id} listing={l} />)}
            </div>
          </div>

          <div style={{ padding: '12px 16px 0' }}>
            <button className="btn btn--good" style={{ width: '100%', justifyContent: 'center' }} onClick={() => onNav?.('criarAlerta')}>
              Salvar busca como alerta
            </button>
          </div>
        </div>

        <MobileTabBar active="dashboard" onNav={onNav} />
      </div>
    );
  }

  function MiniScatter() {
    const w = 358, h = 160, pad = { l: 28, r: 8, t: 8, b: 20 };
    const iw = w - pad.l - pad.r, ih = h - pad.t - pad.b;
    const pts = D.listings.filter(l => l.model === 'Civic').slice(0, 40);
    const xs = (km) => pad.l + (km / 210000) * iw;
    const ys = (price) => pad.t + (1 - (price - 30000) / 140000) * ih;
    const curve = [];
    for (let i = 0; i <= 20; i++) {
      const km = (210000) * (i / 20);
      curve.push([xs(km), ys(145000 * Math.exp(-km / 200000))]);
    }
    return (
      <svg width={w} height={h} viewBox={`0 0 ${w} ${h}`}>
        <rect x={pad.l} y={pad.t} width={iw} height={ih} fill="var(--surface-2)" rx="4" />
        {[60, 100, 140].map(p => (
          <line key={p} x1={pad.l} x2={pad.l + iw} y1={ys(p * 1000)} y2={ys(p * 1000)} stroke="var(--line)" strokeDasharray="2 4" />
        ))}
        <path d={'M ' + curve.map(p => p.join(' ')).join(' L ')} stroke="var(--mute)" strokeWidth="1.2" fill="none" strokeDasharray="3 3" />
        {pts.map((l, i) => {
          const tone = D.scoreTone(l.score);
          const c = tone === 'good' ? '#1a6c4d' : tone === 'warn' ? '#b04421' : '#8b7d5c';
          return <circle key={i} cx={xs(l.km)} cy={ys(l.price)} r={Math.abs(l.score) >= 0.1 ? 3.5 : 2.8} fill={c} opacity="0.85" />;
        })}
        {/* axis */}
        {[60, 100, 140].map(p => (
          <text key={'t' + p} x={pad.l - 4} y={ys(p * 1000) + 3} textAnchor="end" fontSize="9" fill="var(--mute)" fontFamily="var(--font-mono)">{p}k</text>
        ))}
      </svg>
    );
  }

  function MobileListingCard({ listing: l }) {
    return (
      <div style={{ background: 'var(--surface)', borderRadius: 10, border: '1px solid var(--line)', padding: 12, display: 'flex', gap: 12 }}>
        <window.CurvaPhotoStub width={64} height={64} />
        <div style={{ flex: 1, minWidth: 0, display: 'flex', flexDirection: 'column', gap: 4 }}>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', gap: 8 }}>
            <span style={{ fontSize: 13, fontWeight: 600, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{l.make} {l.model}</span>
            <window.CurvaScoreBadge score={l.score} />
          </div>
          <span style={{ fontSize: 11, color: 'var(--mute)' }}>{l.version}</span>
          <div style={{ display: 'flex', alignItems: 'baseline', justifyContent: 'space-between', marginTop: 4 }}>
            <window.CurvaPrice value={l.price} size="md" />
            <span className="mono" style={{ fontSize: 11, color: 'var(--mute)' }}>{l.year} · {D.fmtKMshort(l.km)}</span>
          </div>
        </div>
      </div>
    );
  }

  // ── Mobile: Tabela ────────────────────────────────────────────────
  function Tabela({ onNav }) {
    return (
      <div className="curva" style={{ width: 390, height: 780, display: 'flex', flexDirection: 'column', background: 'var(--paper)', position: 'relative' }}>
        <MobileHeader
          title="Anúncios"
          sub={`${D.listings.length.toLocaleString('pt-BR')} resultados`}
          action={
            <button className="btn btn--ghost btn--sm" style={{ padding: '6px 10px' }}>filtros</button>
          }
        />

        <div style={{ padding: '10px 16px 10px', background: 'var(--surface)', borderBottom: '1px solid var(--line)' }}>
          <div style={{
            display: 'flex', alignItems: 'center', gap: 8,
            background: 'var(--paper)', padding: '8px 12px', borderRadius: 8,
            border: '1px solid var(--line)',
          }}>
            <svg width="14" height="14" viewBox="0 0 14 14"><circle cx="6" cy="6" r="4" stroke="var(--mute)" fill="none" strokeWidth="1.4"/><line x1="9" y1="9" x2="12" y2="12" stroke="var(--mute)" strokeWidth="1.4" strokeLinecap="round"/></svg>
            <input placeholder="Civic Touring" style={{ flex: 1, border: 0, background: 'transparent', outline: 'none', fontSize: 13, fontFamily: 'var(--font-body)' }} />
            <span className="mono" style={{ fontSize: 10, color: 'var(--mute)' }}>↓ curva</span>
          </div>
        </div>

        <div style={{ flex: 1, overflowY: 'auto', paddingBottom: 80 }}>
          {D.listings.slice(0, 14).map(l => (
            <div key={l.id} style={{
              padding: '14px 16px', borderBottom: '1px solid var(--line)',
              background: 'var(--surface)', display: 'flex', gap: 12,
            }}>
              <window.CurvaPhotoStub width={68} height={68} />
              <div style={{ flex: 1, minWidth: 0 }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', gap: 8 }}>
                  <span style={{ fontSize: 13, fontWeight: 600 }}>{l.make} {l.model}</span>
                  <window.CurvaScoreBadge score={l.score} />
                </div>
                <div style={{ fontSize: 11, color: 'var(--mute)', marginTop: 2 }}>{l.version}</div>
                <div style={{ display: 'flex', alignItems: 'baseline', justifyContent: 'space-between', marginTop: 6 }}>
                  <window.CurvaPrice value={l.price} size="md" />
                  <span className="mono" style={{ fontSize: 10, color: 'var(--mute-2)' }}>{l.year} · {D.fmtKMshort(l.km)}</span>
                </div>
                <div style={{ display: 'flex', justifyContent: 'space-between', marginTop: 4, fontSize: 10, color: 'var(--mute-2)' }}>
                  <span>{l.city}/{l.uf} · {l.provider}</span>
                  <span>{l.daysAgo === 0 ? 'hoje' : `há ${l.daysAgo}d`}</span>
                </div>
              </div>
            </div>
          ))}
        </div>

        <MobileTabBar active="tabela" onNav={onNav} />
      </div>
    );
  }

  // ── Mobile: Login ─────────────────────────────────────────────────
  function Login({ onNav, onSubmit }) {
    return (
      <div className="curva" style={{ width: 390, height: 780, background: 'var(--ink)', color: 'var(--paper)', display: 'flex', flexDirection: 'column', position: 'relative', overflow: 'hidden' }}>
        <div style={{ position: 'absolute', inset: 0, opacity: 0.18 }}>
          <svg width="100%" height="100%" preserveAspectRatio="none" viewBox="0 0 400 800">
            <path d="M 30 720 Q 200 500, 370 80" stroke="rgba(243,240,232,0.7)" strokeWidth="1.2" fill="none" strokeDasharray="3 3" />
            {Array.from({ length: 36 }).map((_, i) => {
              const x = 30 + (i * 47 % 340);
              const y = 90 + (i * 139 % 600);
              const cy = 720 - ((x - 30) / 340) * 640;
              const isGreen = y > cy + 30;
              return <circle key={i} cx={x} cy={y} r={i % 5 === 0 ? 4 : 2.5} fill={isGreen ? '#4ddb98' : 'rgba(243,240,232,0.6)'} />;
            })}
          </svg>
        </div>
        <div style={{ padding: '60px 28px 30px', position: 'relative' }}>
          <window.CurvaLogo size={20} color="var(--paper)" />
        </div>
        <div style={{ flex: 1, padding: '0 28px', position: 'relative' }}>
          <div style={{ fontFamily: 'var(--font-mono)', fontSize: 10, textTransform: 'uppercase', letterSpacing: '0.12em', color: 'rgba(243,240,232,0.55)', marginBottom: 12 }}>
            — bem-vindo
          </div>
          <h1 className="display" style={{ fontSize: 34, fontWeight: 600, lineHeight: 1.05, letterSpacing: '-0.03em', margin: 0 }}>
            Bons negócios ficam <span style={{ color: '#4ddb98' }}>abaixo da curva.</span>
          </h1>
        </div>
        <div style={{
          background: 'var(--surface)', color: 'var(--ink)',
          padding: '24px 24px 32px', borderRadius: '22px 22px 0 0',
          position: 'relative', zIndex: 1,
        }}>
          <form onSubmit={(e) => { e.preventDefault(); onSubmit?.(); }} style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
            <button type="button" className="btn btn--ghost btn--lg" style={{ justifyContent: 'center', width: '100%' }}>
              <svg width="14" height="14" viewBox="0 0 18 18"><path fill="#4285F4" d="M16.5 8H9v3h4.3c-.2 1-.8 1.5-1.7 2v2h2.6A7.8 7.8 0 0016.5 9c0-.6-.05-.7-.15-1z"/><path fill="#34A853" d="M9 17c2.2 0 4-.7 5.3-1.9l-2.6-2A4.8 4.8 0 014.5 10.5L1.8 12.6A8 8 0 009 17z"/><path fill="#FBBC05" d="M4.5 10.5a4.86 4.86 0 010-3L1.8 5.3a8 8 0 000 7.3L4.5 10.5z"/><path fill="#EA4335" d="M9 4.2c1.2 0 2.2.4 3 1.2l2.3-2.3A8 8 0 001.8 5.3l2.7 2.2A4.74 4.74 0 019 4.2z"/></svg>
              Continuar com Google
            </button>
            <div style={{ display: 'flex', alignItems: 'center', gap: 12, color: 'var(--mute-2)', margin: '4px 0' }}>
              <div style={{ flex: 1, height: 1, background: 'var(--line)' }} />
              <span style={{ fontSize: 10, textTransform: 'uppercase', letterSpacing: '0.08em' }}>ou</span>
              <div style={{ flex: 1, height: 1, background: 'var(--line)' }} />
            </div>
            <input className="input" placeholder="seu@email.com" defaultValue="guilherme@gmail.com" />
            <input className="input" type="password" defaultValue="abcdefghi" />
            <button type="submit" className="btn btn--primary btn--lg" style={{ justifyContent: 'center', width: '100%' }}>Entrar</button>
            <div style={{ fontSize: 12, color: 'var(--mute)', textAlign: 'center', marginTop: 4 }}>
              Pesquisar é grátis. <a onClick={() => onNav?.('cadastro')} style={{ color: 'var(--ink)', fontWeight: 600 }}>Criar conta →</a>
            </div>
          </form>
        </div>
      </div>
    );
  }

  // ── Mobile: Cadastro (compact) ────────────────────────────────────
  function Cadastro({ onNav, onSubmit }) {
    return (
      <div className="curva" style={{ width: 390, height: 780, background: 'var(--surface)', display: 'flex', flexDirection: 'column' }}>
        <MobileHeader title="Criar conta" back={() => onNav?.('login')} />
        <div style={{ flex: 1, overflowY: 'auto', padding: '20px 24px' }}>
          <div className="t-up" style={{ marginBottom: 8 }}>Em 30 segundos</div>
          <h1 className="display" style={{ fontSize: 26, fontWeight: 600, letterSpacing: '-0.025em', margin: 0 }}>
            Salve buscas. Receba alertas.
          </h1>
          <p style={{ fontSize: 13, color: 'var(--mute)', marginTop: 8 }}>Pesquisar segue grátis. Conta é só para salvar.</p>

          <div style={{ display: 'flex', flexDirection: 'column', gap: 0, background: 'var(--paper)', borderRadius: 10, padding: '4px 14px', border: '1px solid var(--line)', margin: '18px 0 20px' }}>
            {[
              ['Alertas salvos', 'Avisos por email e push.'],
              ['Histórico', 'Compare valores ao longo do tempo.'],
              ['Notas privadas', 'Marque anúncios para revisitar.'],
            ].map(([t, s], i) => (
              <div key={t} style={{ display: 'flex', gap: 10, padding: '10px 0', borderBottom: i < 2 ? '1px solid var(--line)' : 'none' }}>
                <div style={{ width: 22, height: 22, borderRadius: 999, background: 'var(--ink)', color: 'var(--paper)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 11, fontFamily: 'var(--font-mono)' }}>{i + 1}</div>
                <div>
                  <div style={{ fontSize: 13, fontWeight: 600 }}>{t}</div>
                  <div style={{ fontSize: 12, color: 'var(--mute)' }}>{s}</div>
                </div>
              </div>
            ))}
          </div>

          <form onSubmit={(e) => { e.preventDefault(); onSubmit?.(); }} style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
            <input className="input" placeholder="Seu nome" />
            <input className="input" placeholder="Email" />
            <input className="input" type="password" placeholder="Senha (mín. 8)" />
            <button type="submit" className="btn btn--primary btn--lg" style={{ justifyContent: 'center', width: '100%' }}>Criar conta</button>
          </form>
        </div>
      </div>
    );
  }

  // ── Mobile: Meus alertas ──────────────────────────────────────────
  function MeusAlertas({ onNav }) {
    return (
      <div className="curva" style={{ width: 390, height: 780, background: 'var(--paper)', display: 'flex', flexDirection: 'column', position: 'relative' }}>
        <MobileHeader
          title="Alertas"
          sub="5 ativos · 5 novos hoje"
          action={<button className="btn btn--primary btn--sm" onClick={() => onNav?.('criarAlerta')}>+ Novo</button>}
        />
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(2, 1fr)', gap: 8, padding: '14px 16px 6px', background: 'var(--surface)', borderBottom: '1px solid var(--line)' }}>
          <SumTile n="5" l="novos hoje" tone="good" />
          <SumTile n="R$ 12.4k" l="economia média" tone="good" />
        </div>
        <div style={{ flex: 1, overflowY: 'auto', paddingBottom: 80 }}>
          {[
            { name: 'Lancer GT abaixo de R$ 70k', filters: 'GT 2.0 · ≤R$70k · SP·RJ', matches: 4, newToday: 1, active: true },
            { name: 'Civic Touring particular',  filters: 'Touring 1.5T · 2019+ · particular', matches: 9, newToday: 3, active: true },
            { name: 'Golf GTI abaixo da curva',  filters: 'GTI 2.0 TSI · ≥10% abaixo', matches: 2, newToday: 0, active: true },
            { name: 'Corolla XEi até 80k km',    filters: 'XEi 2.0 · 2018-22 · ≤80k km', matches: 6, newToday: 0, active: false },
            { name: 'Jetta GLI 350',             filters: 'GLI 350 TSI · 2019+ · ≤R$170k', matches: 3, newToday: 1, active: true },
          ].map((a, i) => (
            <div key={i} style={{ padding: '14px 16px', borderBottom: '1px solid var(--line)', background: 'var(--surface)', opacity: a.active ? 1 : 0.55 }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 6 }}>
                <span className={`dot ${a.active && a.newToday > 0 ? 'dot--good dot--pulse' : a.active ? 'dot--good' : 'dot--neutral'}`} style={{ width: 8, height: 8 }} />
                <span style={{ fontSize: 14, fontWeight: 600, flex: 1 }}>{a.name}</span>
                <span className="display" style={{ fontSize: 18, fontWeight: 600 }}>{a.matches}</span>
              </div>
              <div style={{ fontSize: 11, color: 'var(--mute)', fontFamily: 'var(--font-mono)', marginBottom: 8 }}>{a.filters}</div>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                {a.newToday > 0 ? <span className="badge badge--good">+{a.newToday} hoje</span> : <span style={{ fontSize: 11, color: 'var(--mute-2)' }}>{a.active ? 'sem novos' : 'pausado'}</span>}
                <span style={{ fontSize: 11, color: 'var(--mute)' }}>editar →</span>
              </div>
            </div>
          ))}
        </div>
        <MobileTabBar active="alertas" onNav={onNav} />
      </div>
    );
  }

  function SumTile({ n, l, tone }) {
    return (
      <div style={{ background: 'var(--surface)', borderRadius: 8, border: '1px solid var(--line)', padding: '10px 12px' }}>
        <div className="display" style={{ fontSize: 20, fontWeight: 600, letterSpacing: '-0.02em', color: tone === 'good' ? 'var(--good)' : 'var(--ink)' }}>{n}</div>
        <div className="t-up" style={{ fontSize: 9, marginTop: 2 }}>{l}</div>
      </div>
    );
  }

  // ── Mobile: Criar alerta ──────────────────────────────────────────
  function CriarAlerta({ onNav, onSubmit }) {
    const [name, setName] = useState('Civic Touring abaixo da curva');
    return (
      <div className="curva" style={{ width: 390, height: 780, background: 'var(--surface)', display: 'flex', flexDirection: 'column' }}>
        <MobileHeader title="Novo alerta" sub="Confira e nomeie" back={() => onNav?.('alertas')} />
        <div style={{ flex: 1, overflowY: 'auto', padding: '16px 18px 24px' }}>
          <div className="field">
            <label className="field__label">Nome</label>
            <input className="input" value={name} onChange={e => setName(e.target.value)} />
          </div>

          <div style={{ marginTop: 20 }}>
            <div className="t-up" style={{ marginBottom: 10 }}>Filtros</div>
            <div style={{ background: 'var(--paper)', border: '1px solid var(--line)', borderRadius: 10, overflow: 'hidden' }}>
              {[
                ['Modelo', 'Honda Civic'],
                ['Versão', 'Touring 1.5 Turbo'],
                ['Ano', '2019 — 2024'],
                ['Preço', 'R$ 90k — R$ 145k'],
                ['Km', 'até 70k'],
                ['Local', 'SP · RJ · MG'],
                ['Curva', '≥ 10% abaixo', true],
              ].map(([l, v, tone], i, arr) => (
                <div key={l} style={{ display: 'flex', alignItems: 'center', padding: '10px 12px', borderBottom: i < arr.length - 1 ? '1px solid var(--line)' : 'none' }}>
                  <span className="t-up" style={{ fontSize: 10, width: 70 }}>{l}</span>
                  <span style={{ flex: 1, fontFamily: 'var(--font-mono)', fontSize: 12 }}>
                    {tone && <span className="dot dot--good" style={{ marginRight: 6 }} />}{v}
                  </span>
                  <span style={{ fontSize: 11, color: 'var(--mute)' }}>editar</span>
                </div>
              ))}
            </div>
          </div>

          <div style={{ marginTop: 20 }}>
            <div className="t-up" style={{ marginBottom: 10 }}>Frequência</div>
            <div style={{ display: 'flex', gap: 6 }}>
              {[['Real-time', true], ['Diário'], ['Semanal']].map(([l, on]) => (
                <button key={l} style={{
                  flex: 1, padding: '12px 8px', borderRadius: 8, fontSize: 12, fontWeight: 600,
                  background: on ? 'var(--paper)' : 'var(--surface)',
                  border: '1px solid ' + (on ? 'var(--ink)' : 'var(--line)'),
                  fontFamily: 'var(--font-body)',
                }}>{l}</button>
              ))}
            </div>
          </div>

          <div style={{ marginTop: 20, background: 'var(--ink)', color: 'var(--paper)', padding: 16, borderRadius: 10 }}>
            <div style={{ fontSize: 10, textTransform: 'uppercase', letterSpacing: '0.08em', color: 'rgba(243,240,232,0.55)', fontFamily: 'var(--font-mono)', marginBottom: 8 }}>
              prévia
            </div>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline' }}>
              <span className="display" style={{ fontSize: 26, fontWeight: 600 }}>9</span>
              <span style={{ fontSize: 11, color: 'rgba(243,240,232,0.6)' }} className="mono">hits agora</span>
            </div>
            <div style={{ marginTop: 8, fontSize: 12, color: 'rgba(243,240,232,0.7)', lineHeight: 1.5 }}>
              Aviso em tempo real quando aparecer Civic Touring 2019+ ≤ 70k km em SP/RJ/MG
              <strong style={{ color: '#4ddb98' }}> ≥10% abaixo da curva</strong>.
            </div>
          </div>
        </div>

        <div style={{ padding: '12px 16px 24px', borderTop: '1px solid var(--line)', background: 'var(--surface)', display: 'flex', gap: 8 }}>
          <button className="btn btn--ghost" style={{ flex: 1, justifyContent: 'center' }} onClick={() => onNav?.('alertas')}>Cancelar</button>
          <button className="btn btn--good btn--lg" style={{ flex: 2, justifyContent: 'center' }} onClick={() => onSubmit?.()}>Criar alerta</button>
        </div>
      </div>
    );
  }

  return { Dashboard, Tabela, Login, Cadastro, MeusAlertas, CriarAlerta };
})();

Object.assign(window, {
  CurvaMobileDashboard:  CurvaMobile.Dashboard,
  CurvaMobileTabela:     CurvaMobile.Tabela,
  CurvaMobileLogin:      CurvaMobile.Login,
  CurvaMobileCadastro:   CurvaMobile.Cadastro,
  CurvaMobileAlertas:    CurvaMobile.MeusAlertas,
  CurvaMobileCriarAlerta: CurvaMobile.CriarAlerta,
});
