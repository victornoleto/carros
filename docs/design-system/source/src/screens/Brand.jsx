// Curva — brand identity overview card (single artboard)
// Shows logo, palette, type, principles, key components.

const CurvaBrand = (function () {
  function Brand({ width = 1440 }) {
    return (
      <div className="curva" style={{ width, padding: 56, background: 'var(--paper)' }}>
        {/* Header */}
        <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', marginBottom: 48 }}>
          <div>
            <window.CurvaLogo size={28} />
            <h1 className="display" style={{ fontSize: 64, fontWeight: 600, letterSpacing: '-0.035em', margin: '24px 0 12px', lineHeight: 1 }}>
              Bons negócios ficam<br/>
              <span style={{ color: 'var(--good)' }}>abaixo da curva.</span>
            </h1>
            <p style={{ fontSize: 17, color: 'var(--mute)', maxWidth: 720, lineHeight: 1.5 }}>
              <strong style={{ color: 'var(--ink)' }}>Curva</strong> agrega anúncios de carros usados, normaliza preço × km e
              destaca o que sai do padrão. A identidade visual nasce desse insight: uma curva de regressão, dados densos,
              e um ponto verde marcando a oportunidade.
            </p>
          </div>
          <div style={{ textAlign: 'right', fontFamily: 'var(--font-mono)', fontSize: 11, color: 'var(--mute)', textTransform: 'uppercase', letterSpacing: '0.08em' }}>
            <div>identidade · v0.4</div>
            <div>maio 2026</div>
          </div>
        </div>

        {/* Grid: Logo / Palette / Type / Principles */}
        <div style={{ display: 'grid', gridTemplateColumns: '1.2fr 1fr 1.2fr', gap: 20, marginBottom: 20 }}>
          {/* Logo lock-ups */}
          <Block title="Logo">
            <div style={{ display: 'flex', flexDirection: 'column', gap: 22 }}>
              <div style={{ background: 'var(--surface)', borderRadius: 10, border: '1px solid var(--line)', padding: 32, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                <window.CurvaLogo size={36} />
              </div>
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12 }}>
                <div style={{ background: 'var(--ink)', borderRadius: 10, padding: 22, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                  <window.CurvaLogo size={22} color="var(--paper)" />
                </div>
                <div style={{ background: 'var(--good)', borderRadius: 10, padding: 22, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                  <window.CurvaLogo size={22} color="#fff" />
                </div>
              </div>
              <div style={{ fontSize: 12, color: 'var(--mute)', lineHeight: 1.5 }}>
                A marca é a própria curva de regressão com um outlier verde — o anúncio abaixo da curva.
                Wordmark em <span className="mono" style={{ color: 'var(--ink)' }}>Space Grotesk 600</span>, sempre minúsculo.
              </div>
            </div>
          </Block>

          {/* Palette */}
          <Block title="Paleta">
            <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
              <Swatch hex="#14110d" name="Ink" role="texto principal · ações" tone="ink" />
              <Swatch hex="#f3f0e8" name="Paper" role="fundo · canvas" />
              <Swatch hex="#ffffff" name="Surface" role="cards · superfícies" />
              <Swatch hex="#1a6c4d" name="Good" role="abaixo da curva" />
              <Swatch hex="#b04421" name="Warn" role="acima da curva" />
              <Swatch hex="#8b7d5c" name="Neutral" role="na curva" />
              <Swatch hex="#e3ddcd" name="Line" role="hairlines · bordas" />
            </div>
          </Block>

          {/* Type */}
          <Block title="Tipografia">
            <div style={{ display: 'flex', flexDirection: 'column', gap: 18 }}>
              <div>
                <div className="t-up" style={{ marginBottom: 6 }}>Display · Space Grotesk</div>
                <div className="display" style={{ fontSize: 44, fontWeight: 600, letterSpacing: '-0.03em', lineHeight: 1 }}>
                  R$ 89.900
                </div>
                <div style={{ fontSize: 11, color: 'var(--mute)', marginTop: 4 }} className="mono">SemiBold 600 · tracking −0.03em</div>
              </div>
              <div style={{ borderTop: '1px solid var(--line)', paddingTop: 14 }}>
                <div className="t-up" style={{ marginBottom: 6 }}>Body · Inter</div>
                <div style={{ fontSize: 17 }}>Anúncios abaixo da curva.</div>
                <div style={{ fontSize: 13, color: 'var(--mute)', marginTop: 4 }}>Regular para texto, Semibold 600 para labels.</div>
              </div>
              <div style={{ borderTop: '1px solid var(--line)', paddingTop: 14 }}>
                <div className="t-up" style={{ marginBottom: 6 }}>Mono · JetBrains Mono</div>
                <div className="mono" style={{ fontSize: 16 }}>2021 · 48.300 km · −14% curva</div>
                <div style={{ fontSize: 11, color: 'var(--mute)', marginTop: 4 }}>Sempre que aparecer um dado técnico.</div>
              </div>
            </div>
          </Block>
        </div>

        {/* Bottom row: components + principles */}
        <div style={{ display: 'grid', gridTemplateColumns: '1.6fr 1fr', gap: 20 }}>
          <Block title="Componentes-chave">
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 28, paddingTop: 4 }}>
              {/* score badges */}
              <div>
                <div className="t-up" style={{ marginBottom: 10 }}>Curva-score badge</div>
                <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                    <window.CurvaScoreBadge score={0.18} size="lg" />
                    <span style={{ fontSize: 12, color: 'var(--mute)' }}>oferta excelente</span>
                  </div>
                  <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                    <window.CurvaScoreBadge score={0.06} size="lg" />
                    <span style={{ fontSize: 12, color: 'var(--mute)' }}>levemente abaixo</span>
                  </div>
                  <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                    <window.CurvaScoreBadge score={0.0} size="lg" />
                    <span style={{ fontSize: 12, color: 'var(--mute)' }}>na curva</span>
                  </div>
                  <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                    <window.CurvaScoreBadge score={-0.14} size="lg" />
                    <span style={{ fontSize: 12, color: 'var(--mute)' }}>caro pra média</span>
                  </div>
                </div>
              </div>

              {/* sparkcurves */}
              <div>
                <div className="t-up" style={{ marginBottom: 10 }}>Sparkcurve</div>
                <div style={{ background: 'var(--surface-2)', border: '1px solid var(--line)', borderRadius: 8, padding: 14, display: 'flex', flexDirection: 'column', gap: 10 }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                    <window.CurvaSparkCurve score={0.16} width={80} height={28} />
                    <span style={{ fontSize: 12 }}>abaixo</span>
                  </div>
                  <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                    <window.CurvaSparkCurve score={0.0} width={80} height={28} />
                    <span style={{ fontSize: 12 }}>na curva</span>
                  </div>
                  <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                    <window.CurvaSparkCurve score={-0.12} width={80} height={28} />
                    <span style={{ fontSize: 12 }}>acima</span>
                  </div>
                </div>
                <div style={{ fontSize: 11, color: 'var(--mute)', marginTop: 8 }}>Glyph inline mostrando posição relativa.</div>
              </div>

              {/* buttons */}
              <div>
                <div className="t-up" style={{ marginBottom: 10 }}>Buttons</div>
                <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap' }}>
                  <button className="btn btn--primary">Salvar alerta</button>
                  <button className="btn btn--good">Confirmar</button>
                  <button className="btn btn--ghost">Filtros</button>
                </div>
                <div style={{ display: 'flex', gap: 8, marginTop: 8 }}>
                  <button className="btn btn--primary btn--sm">Small</button>
                  <button className="btn btn--ghost btn--sm">Small ghost</button>
                </div>
              </div>

              {/* chips */}
              <div>
                <div className="t-up" style={{ marginBottom: 10 }}>Filter chips</div>
                <div style={{ display: 'flex', gap: 6, flexWrap: 'wrap' }}>
                  <span className="chip chip--active">Civic</span>
                  <span className="chip chip--active">≤ 80k km</span>
                  <span className="chip">2020+</span>
                  <span className="chip">SP · RJ</span>
                  <span className="chip">Particular</span>
                </div>
              </div>
            </div>
          </Block>

          <Block title="Princípios">
            <ol style={{ margin: 0, padding: 0, listStyle: 'none', display: 'flex', flexDirection: 'column', gap: 14 }}>
              {[
                ['Número antes do adjetivo.', 'Mostre R$ 89.900 grande, antes de qualquer rótulo. Em listagens, números são a coluna principal.'],
                ['A curva é a régua.', 'Toda decisão se compara à mediana de preço × km. O usuário aprende o que é caro e o que é oportunidade.'],
                ['Densidade calma.', 'Hairlines de 1px, paper warm, sem sombras pesadas. Listas longas, mas sempre escaneáveis.'],
                ['Verde é raro.', 'Verde só aparece para anúncios abaixo da curva — nunca decorativo. Mantém o sinal forte.'],
                ['Mono para dados.', 'Km, ano, % curva sempre em JetBrains Mono. Espaços tabulares para alinhar.'],
              ].map(([t, s], i) => (
                <li key={t} style={{ display: 'flex', gap: 14 }}>
                  <span className="mono" style={{ fontSize: 12, color: 'var(--mute-2)', minWidth: 18 }}>{String(i + 1).padStart(2, '0')}</span>
                  <div>
                    <div style={{ fontSize: 14, fontWeight: 600 }}>{t}</div>
                    <div style={{ fontSize: 12, color: 'var(--mute)', marginTop: 2, lineHeight: 1.5 }}>{s}</div>
                  </div>
                </li>
              ))}
            </ol>
          </Block>
        </div>
      </div>
    );
  }

  function Block({ title, children }) {
    return (
      <div style={{ background: 'var(--surface)', borderRadius: 14, border: '1px solid var(--line)', padding: 28 }}>
        <div style={{ display: 'flex', alignItems: 'baseline', gap: 10, marginBottom: 18 }}>
          <span className="t-up">{title}</span>
          <div style={{ flex: 1, height: 1, background: 'var(--line)' }} />
        </div>
        {children}
      </div>
    );
  }

  function Swatch({ hex, name, role, tone }) {
    return (
      <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
        <div style={{
          width: 44, height: 44, borderRadius: 8, background: hex,
          border: '1px solid var(--line)', flex: '0 0 44px',
        }} />
        <div style={{ flex: 1, minWidth: 0 }}>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline' }}>
            <span style={{ fontSize: 13, fontWeight: 600 }}>{name}</span>
            <span className="mono" style={{ fontSize: 10, color: 'var(--mute)' }}>{hex.toUpperCase()}</span>
          </div>
          <div style={{ fontSize: 11, color: 'var(--mute)' }}>{role}</div>
        </div>
      </div>
    );
  }

  return Brand;
})();

window.CurvaBrand = CurvaBrand;
