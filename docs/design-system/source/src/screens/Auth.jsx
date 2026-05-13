// Curva — Auth screens (Login + Cadastro)
// Split-pane: marketing left, form right. Cream + ink.

const CurvaAuth = (function () {
  const D = window.CARROS_DATA;
  const { useState } = React;

  // ── Marketing pane (left) ──────────────────────────────────────────
  function MarketingPane({ mode = 'login' }) {
    return (
      <div style={{
        flex: 1,
        background: 'var(--ink)',
        color: 'var(--paper)',
        padding: '48px 56px',
        display: 'flex',
        flexDirection: 'column',
        gap: 32,
        position: 'relative',
        overflow: 'hidden',
      }}>
        {/* mini scatter as bg */}
        <ScatterDecor />

        <div style={{ position: 'relative', zIndex: 1 }}>
          <window.CurvaLogo size={20} color="var(--paper)" />
        </div>

        <div style={{ flex: 1, display: 'flex', flexDirection: 'column', justifyContent: 'center', position: 'relative', zIndex: 1, maxWidth: 460 }}>
          <div style={{ fontFamily: 'var(--font-mono)', fontSize: 11, textTransform: 'uppercase', letterSpacing: '0.12em', color: 'rgba(243,240,232,0.55)', marginBottom: 18 }}>
            {mode === 'login' ? '— bem-vindo de volta' : '— acompanhe oportunidades'}
          </div>
          <h1 className="display" style={{
            fontSize: 52, fontWeight: 600, lineHeight: 1.05, letterSpacing: '-0.035em',
            margin: 0, color: 'var(--paper)',
          }}>
            Bons negócios ficam <span style={{ color: '#4ddb98' }}>abaixo da curva.</span>
          </h1>
          <p style={{ marginTop: 22, color: 'rgba(243,240,232,0.7)', fontSize: 15, lineHeight: 1.55, maxWidth: 420 }}>
            Curva agrega anúncios de OLX, Webmotors e iCarros, normaliza os dados e te mostra na hora quais
            saem do padrão de preço × km. Pesquisar é grátis. Salvar alertas precisa de conta.
          </p>

          <div style={{ display: 'flex', gap: 28, marginTop: 36 }}>
            <Stat n="12.487" l="anúncios indexados" />
            <Stat n="33" l="abaixo da curva hoje" tone="good" />
            <Stat n="4" l="fontes" />
          </div>
        </div>

        <div style={{ position: 'relative', zIndex: 1, fontFamily: 'var(--font-mono)', fontSize: 11, color: 'rgba(243,240,232,0.4)' }}>
          curva · v0.4 · usa-leste · sandbox
        </div>
      </div>
    );
  }

  function Stat({ n, l, tone }) {
    return (
      <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
        <span className="display" style={{
          fontSize: 28, fontWeight: 600, letterSpacing: '-0.025em',
          color: tone === 'good' ? '#4ddb98' : 'var(--paper)',
        }}>{n}</span>
        <span style={{ fontFamily: 'var(--font-mono)', fontSize: 10, textTransform: 'uppercase', letterSpacing: '0.08em', color: 'rgba(243,240,232,0.55)' }}>
          {l}
        </span>
      </div>
    );
  }

  function ScatterDecor() {
    // Background scatter visual; faintly visible.
    return (
      <svg style={{ position: 'absolute', inset: 0, opacity: 0.13 }} width="100%" height="100%" preserveAspectRatio="none" viewBox="0 0 600 800">
        <path d="M 60 700 Q 250 500, 540 100" stroke="rgba(243,240,232,0.6)" strokeWidth="1" fill="none" strokeDasharray="3 4" />
        {Array.from({ length: 60 }).map((_, i) => {
          const x = 40 + (i * 51 % 540);
          const y = 80 + (i * 137 % 680);
          // distance from curve
          const cy = 700 - ((x - 60) / 480) * 600;
          const r = 2 + (i % 4);
          const isGreen = y > cy + 40;
          return <circle key={i} cx={x} cy={y} r={r} fill={isGreen ? '#4ddb98' : 'rgba(243,240,232,0.55)'} />;
        })}
      </svg>
    );
  }

  // ── Login ──────────────────────────────────────────────────────────
  function Login({ onNav, onSubmit, width = 1440, hideTopBar = false }) {
    return (
      <div className="curva" style={{ width, minHeight: 900, display: 'flex' }}>
        <MarketingPane mode="login" />
        <div style={{
          width: 560, padding: '64px 64px', background: 'var(--surface)',
          display: 'flex', flexDirection: 'column', justifyContent: 'center', gap: 28,
        }}>
          <div>
            <div className="t-up" style={{ marginBottom: 12 }}>Entrar</div>
            <h2 className="display" style={{ fontSize: 32, fontWeight: 600, letterSpacing: '-0.025em', margin: 0 }}>
              Continue sua busca.
            </h2>
            <p style={{ marginTop: 8, fontSize: 14, color: 'var(--mute)', lineHeight: 1.5 }}>
              Pesquisar carros é grátis e sempre será. Sua conta serve para salvar buscas como alertas e
              receber novos anúncios abaixo da curva.
            </p>
          </div>

          <div style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
            <button className="btn btn--ghost btn--lg" style={{ justifyContent: 'center', width: '100%' }}>
              <GoogleG /> Continuar com Google
            </button>
          </div>

          <Divider label="ou com email" />

          <form onSubmit={(e) => { e.preventDefault(); onSubmit?.(); }} style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
            <div className="field">
              <label className="field__label">Email</label>
              <input className="input" placeholder="seu@email.com" defaultValue="guilherme@gmail.com" />
            </div>
            <div className="field">
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline' }}>
                <label className="field__label">Senha</label>
                <a href="#" style={{ fontSize: 12, color: 'var(--mute)', textDecoration: 'none' }}>esqueci</a>
              </div>
              <input className="input" type="password" defaultValue="abcdefghi" />
            </div>
            <label style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: 13, color: 'var(--mute)' }}>
              <input type="checkbox" defaultChecked style={{ accentColor: 'var(--ink)' }} />
              Lembrar deste dispositivo
            </label>
            <button type="submit" className="btn btn--primary btn--lg" style={{ justifyContent: 'center', width: '100%' }}>
              Entrar
            </button>
          </form>

          <div style={{ fontSize: 13, color: 'var(--mute)', textAlign: 'center' }}>
            Primeira vez por aqui?{' '}
            <a onClick={() => onNav?.('cadastro')} style={{ color: 'var(--ink)', fontWeight: 600, cursor: 'pointer' }}>Criar conta grátis</a>
          </div>
        </div>
      </div>
    );
  }

  function GoogleG() {
    return (
      <svg width="16" height="16" viewBox="0 0 18 18">
        <path fill="#4285F4" d="M16.51 8H8.98v3h4.3c-.18 1-.74 1.48-1.6 2.04v2.01h2.6a7.8 7.8 0 002.38-5.88c0-.57-.05-.66-.15-1.18z" />
        <path fill="#34A853" d="M8.98 17c2.16 0 3.97-.72 5.3-1.94l-2.6-2.04a4.8 4.8 0 01-7.18-2.54H1.83v2.07A8 8 0 008.98 17z" />
        <path fill="#FBBC05" d="M4.5 10.49a4.86 4.86 0 010-3.07V5.35H1.83a8 8 0 000 7.28L4.5 10.5z" />
        <path fill="#EA4335" d="M8.98 4.18c1.18 0 2.23.4 3.06 1.2l2.3-2.3A8 8 0 001.82 5.35l2.67 2.06A4.74 4.74 0 018.98 4.18z" />
      </svg>
    );
  }

  function Divider({ label }) {
    return (
      <div style={{ display: 'flex', alignItems: 'center', gap: 14, color: 'var(--mute-2)' }}>
        <div style={{ flex: 1, height: 1, background: 'var(--line)' }} />
        <span style={{ fontSize: 11, textTransform: 'uppercase', letterSpacing: '0.08em' }}>{label}</span>
        <div style={{ flex: 1, height: 1, background: 'var(--line)' }} />
      </div>
    );
  }

  // ── Cadastro ───────────────────────────────────────────────────────
  function Cadastro({ onNav, onSubmit, width = 1440, hideTopBar = false }) {
    return (
      <div className="curva" style={{ width, minHeight: 900, display: 'flex' }}>
        <MarketingPane mode="signup" />
        <div style={{
          width: 560, padding: '56px 64px', background: 'var(--surface)',
          display: 'flex', flexDirection: 'column', justifyContent: 'center', gap: 22,
        }}>
          <div>
            <div className="t-up" style={{ marginBottom: 12 }}>Criar conta</div>
            <h2 className="display" style={{ fontSize: 30, fontWeight: 600, letterSpacing: '-0.025em', margin: 0 }}>
              Em 30 segundos.
            </h2>
            <p style={{ marginTop: 6, fontSize: 13.5, color: 'var(--mute)', lineHeight: 1.5 }}>
              Crie alertas, salve buscas favoritas e receba avisos quando um anúncio aparecer abaixo da curva.
            </p>
          </div>

          <div style={{
            display: 'flex', flexDirection: 'column', gap: 0,
            background: 'var(--paper)', borderRadius: 10, border: '1px solid var(--line)', padding: 14,
          }}>
            <Benefit i="1" t="Alertas salvos" s="Avisos por email quando um anúncio abaixo da curva aparecer." />
            <Benefit i="2" t="Histórico de busca" s="Compare valores ao longo do tempo, veja se um modelo está caindo." />
            <Benefit i="3" t="Notas privadas" s="Marque anúncios para revisitar; veja se sumiram ou caíram de preço." />
          </div>

          <form onSubmit={(e) => { e.preventDefault(); onSubmit?.(); }} style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
            <div className="field">
              <label className="field__label">Seu nome</label>
              <input className="input" placeholder="Guilherme" />
            </div>
            <div className="field">
              <label className="field__label">Email</label>
              <input className="input" placeholder="seu@email.com" />
            </div>
            <div className="field">
              <label className="field__label">Senha</label>
              <input className="input" type="password" placeholder="mínimo 8 caracteres" />
            </div>
            <label style={{ display: 'flex', gap: 8, fontSize: 12, color: 'var(--mute)', lineHeight: 1.4, marginTop: 4 }}>
              <input type="checkbox" defaultChecked style={{ accentColor: 'var(--ink)', marginTop: 2 }} />
              <span>Concordo com os <a href="#" style={{ color: 'var(--ink)' }}>termos</a> e a <a href="#" style={{ color: 'var(--ink)' }}>política de privacidade</a>.</span>
            </label>
            <button type="submit" className="btn btn--primary btn--lg" style={{ justifyContent: 'center', width: '100%', marginTop: 4 }}>
              Criar minha conta
            </button>
          </form>

          <div style={{ fontSize: 13, color: 'var(--mute)', textAlign: 'center' }}>
            Já tem conta?{' '}
            <a onClick={() => onNav?.('login')} style={{ color: 'var(--ink)', fontWeight: 600, cursor: 'pointer' }}>Entrar</a>
          </div>
        </div>
      </div>
    );
  }

  function Benefit({ i, t, s }) {
    return (
      <div style={{
        display: 'flex', gap: 12, padding: '10px 4px',
        borderBottom: '1px solid var(--line)',
      }}>
        <div style={{
          width: 24, height: 24, borderRadius: 999, background: 'var(--ink)', color: 'var(--paper)',
          display: 'flex', alignItems: 'center', justifyContent: 'center',
          fontSize: 11, fontWeight: 600, fontFamily: 'var(--font-mono)', flex: '0 0 24px',
        }}>{i}</div>
        <div style={{ display: 'flex', flexDirection: 'column' }}>
          <span style={{ fontSize: 13, fontWeight: 600 }}>{t}</span>
          <span style={{ fontSize: 12, color: 'var(--mute)' }}>{s}</span>
        </div>
      </div>
    );
  }

  return { Login, Cadastro };
})();

window.CurvaLogin = CurvaAuth.Login;
window.CurvaCadastro = CurvaAuth.Cadastro;
