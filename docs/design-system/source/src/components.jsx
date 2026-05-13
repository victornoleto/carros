// Curva — shared UI components
// Available globally via window.Curva (since babel scripts have separate scope)

const { useState, useEffect, useMemo, useRef, useCallback } = React;
const D = window.CARROS_DATA;

// ─── Logo ────────────────────────────────────────────────────────────
function Logo({ size = 20, color = 'currentColor' }) {
  // glyph: stylized regression curve with a green outlier dot
  const s = size;
  return (
    <span className="curva-mark" style={{ fontSize: s, lineHeight: 1 }}>
      <span className="curva-mark__glyph" style={{ width: s * 1.4, height: s * 1.4 }}>
        <svg width={s * 1.4} height={s * 1.4} viewBox="0 0 28 28" fill="none" aria-hidden="true">
          <rect x="0.75" y="0.75" width="26.5" height="26.5" rx="6" stroke={color} strokeOpacity="0.18" />
          <path d="M3 21 C 9 18, 14 12, 25 5" stroke={color} strokeWidth="1.6" strokeLinecap="round" fill="none" />
          <circle cx="6"  cy="22" r="1.2" fill={color} opacity="0.35" />
          <circle cx="11" cy="16" r="1.2" fill={color} opacity="0.35" />
          <circle cx="17" cy="11" r="1.2" fill={color} opacity="0.35" />
          <circle cx="22" cy="7"  r="1.2" fill={color} opacity="0.35" />
          {/* outlier good deal */}
          <circle cx="9" cy="24" r="2" fill="#1a6c4d" />
        </svg>
      </span>
      <span style={{ fontSize: s, fontWeight: 600, letterSpacing: '-0.025em', color }}>curva</span>
    </span>
  );
}

// ─── Score chip (% above/below curve) ────────────────────────────────
function ScoreBadge({ score, size = 'sm' }) {
  const tone = D.scoreTone(score);
  const label = D.scoreLabel(score);
  const arrow = score >= 0.04 ? '↓' : score <= -0.04 ? '↑' : '·';
  return (
    <span className={`badge badge--${tone}`} style={size === 'lg' ? { fontSize: 13, padding: '5px 10px' } : null}>
      <span style={{ opacity: 0.7 }}>{arrow}</span>
      {label}
    </span>
  );
}

// ─── Sparkcurve: tiny price/km curve glyph w/ this listing's dot ─────
function SparkCurve({ score, width = 56, height = 22, showLine = true }) {
  // score positive = below curve = green; show a small curve with this dot
  // positioned to imply where this listing sits.
  const pad = 2;
  const w = width - pad * 2;
  const h = height - pad * 2;
  // x is age/km proxy (centered), y depends on score
  const x = pad + w * 0.62;
  // bigger positive score = further below the curve = lower y in chart space
  // (in SVG, larger y = lower visually)
  const tone = D.scoreTone(score);
  const color = tone === 'good' ? '#1a6c4d' : tone === 'warn' ? '#b04421' : '#8b7d5c';
  // curve: from top-right to bottom-left (price drops as km grows)
  const path = `M ${pad} ${pad + h * 0.15} Q ${pad + w * 0.4} ${pad + h * 0.45}, ${pad + w} ${pad + h * 0.9}`;
  // y for the dot: regression at x is roughly pad + h*0.62 ish; offset by score
  const curveY = pad + h * 0.55;
  const dotY = curveY + (-score) * h * 1.6;
  const cy = Math.max(pad + 2, Math.min(pad + h - 2, dotY));
  return (
    <svg width={width} height={height} viewBox={`0 0 ${width} ${height}`} aria-hidden="true">
      {showLine && <path d={path} stroke="rgba(20,17,13,0.18)" strokeWidth="1.25" fill="none" />}
      {/* faint dots */}
      <circle cx={pad + w * 0.18} cy={pad + h * 0.30} r="1" fill="rgba(20,17,13,0.18)" />
      <circle cx={pad + w * 0.36} cy={pad + h * 0.50} r="1" fill="rgba(20,17,13,0.18)" />
      <circle cx={pad + w * 0.55} cy={pad + h * 0.65} r="1" fill="rgba(20,17,13,0.18)" />
      <circle cx={pad + w * 0.78} cy={pad + h * 0.82} r="1" fill="rgba(20,17,13,0.18)" />
      <circle cx={x} cy={cy} r="2.6" fill={color} />
    </svg>
  );
}

// ─── BRL formatted price (auto-styled w/ display font) ────────────────
function Price({ value, size = 'lg', sub }) {
  const cls = size === 'xl' ? 'num-xl' : size === 'lg' ? 'num-lg' : 'num-md';
  return (
    <span style={{ display: 'inline-flex', alignItems: 'baseline', gap: 4 }}>
      <span className="display" style={{ fontSize: size === 'xl' ? 18 : size === 'lg' ? 14 : 11, color: 'var(--mute)', fontWeight: 500 }}>R$</span>
      <span className={cls}>{value.toLocaleString('pt-BR', { maximumFractionDigits: 0 })}</span>
      {sub && <span style={{ fontSize: 11, color: 'var(--mute)' }}>{sub}</span>}
    </span>
  );
}

// ─── Inline filter chip (with optional remove X) ─────────────────────
function Chip({ active, children, onClick, removable, onRemove }) {
  return (
    <button className={`chip ${active ? 'chip--active' : ''}`} onClick={onClick}>
      {children}
      {removable && (
        <span className="chip__x" onClick={(e) => { e.stopPropagation(); onRemove?.(); }} style={{ marginLeft: 2, cursor: 'pointer' }}>×</span>
      )}
    </button>
  );
}

// ─── Curva top bar (used in app screens) ──────────────────────────────
function TopBar({ active = 'dashboard', onNav, onLogin, loggedIn, user }) {
  const items = [
    { id: 'dashboard', label: 'Mapa' },
    { id: 'tabela',    label: 'Anúncios' },
    { id: 'alertas',   label: 'Alertas' },
  ];
  return (
    <div className="hairline-b" style={{
      display: 'flex', alignItems: 'center', gap: 32,
      padding: '14px 28px', background: 'var(--surface)',
      position: 'sticky', top: 0, zIndex: 10,
    }}>
      <div onClick={() => onNav?.('dashboard')} style={{ cursor: 'pointer' }}>
        <Logo size={18} />
      </div>
      <nav style={{ display: 'flex', gap: 4 }}>
        {items.map(it => (
          <button key={it.id}
            onClick={() => onNav?.(it.id)}
            style={{
              border: 0, background: active === it.id ? 'var(--paper)' : 'transparent',
              padding: '7px 12px', borderRadius: 6, cursor: 'pointer',
              fontSize: 13, fontWeight: active === it.id ? 600 : 500,
              color: active === it.id ? 'var(--ink)' : 'var(--mute)',
              fontFamily: 'var(--font-body)',
            }}
          >{it.label}</button>
        ))}
      </nav>
      <div style={{ flex: 1 }} />
      <div style={{ fontSize: 12, color: 'var(--mute)', display: 'flex', alignItems: 'center', gap: 6 }}>
        <span className="dot dot--good dot--pulse" />
        <span className="mono">{D.listings.length.toLocaleString('pt-BR')} anúncios · atualizado há 12 min</span>
      </div>
      {loggedIn ? (
        <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
          <span style={{ fontSize: 13, color: 'var(--mute)' }}>{user || 'guilherme@gmail.com'}</span>
          <div style={{
            width: 30, height: 30, borderRadius: 999, background: 'var(--ink)',
            color: 'var(--paper)', display: 'flex', alignItems: 'center', justifyContent: 'center',
            fontSize: 12, fontWeight: 600,
          }}>G</div>
        </div>
      ) : (
        <>
          <button className="btn btn--ghost btn--sm" onClick={() => onNav?.('login')}>Entrar</button>
          <button className="btn btn--primary btn--sm" onClick={() => onNav?.('cadastro')}>Criar conta</button>
        </>
      )}
    </div>
  );
}

// ─── Listing row / card primitives ────────────────────────────────────
function PhotoStub({ width = 96, height = 72, label, tone = 'paper' }) {
  // monochrome paper placeholder with diagonal hatch
  const bg = tone === 'paper' ? '#ede7d7' : '#e4dec9';
  return (
    <div style={{
      width, height, borderRadius: 6, background: bg, position: 'relative',
      overflow: 'hidden', border: '1px solid rgba(0,0,0,0.05)',
    }}>
      <svg width="100%" height="100%" style={{ position: 'absolute', inset: 0 }}>
        <defs>
          <pattern id={'hp' + width + height} width="6" height="6" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
            <line x1="0" y1="0" x2="0" y2="6" stroke="rgba(20,17,13,0.06)" strokeWidth="2" />
          </pattern>
        </defs>
        <rect width="100%" height="100%" fill={`url(#hp${width}${height})`} />
      </svg>
      {label && (
        <span style={{
          position: 'absolute', bottom: 4, left: 6,
          fontFamily: 'var(--font-mono)', fontSize: 9, color: 'var(--mute)',
          textTransform: 'uppercase', letterSpacing: '0.05em',
        }}>{label}</span>
      )}
    </div>
  );
}

// Export to window
Object.assign(window, {
  CurvaLogo: Logo,
  CurvaScoreBadge: ScoreBadge,
  CurvaSparkCurve: SparkCurve,
  CurvaPrice: Price,
  CurvaChip: Chip,
  CurvaTopBar: TopBar,
  CurvaPhotoStub: PhotoStub,
});
