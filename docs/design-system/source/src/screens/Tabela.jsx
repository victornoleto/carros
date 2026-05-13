// Curva — Tabela de anúncios (public listings table)
// Dense, sortable, with the curve-score column as primary signal.

const CurvaTabela = (function () {
  const D = window.CARROS_DATA;
  const { useState, useMemo } = React;

  function SortCaret({ dir }) {
    return (
      <span style={{ fontSize: 9, opacity: dir ? 1 : 0.3, marginLeft: 4 }}>
        {dir === 'asc' ? '▲' : dir === 'desc' ? '▼' : '◆'}
      </span>
    );
  }

  function Th({ children, sort, dir, onSort, align = 'left', width }) {
    return (
      <th onClick={() => onSort?.(sort)}
        style={{ textAlign: align, cursor: sort ? 'pointer' : 'default', userSelect: 'none', width }}>
        {children}
        {sort && <SortCaret dir={dir} />}
      </th>
    );
  }

  function Tabela({ loggedIn = false, onNav, width = 1440, hideTopBar = false }) {
    const [sort, setSort] = useState({ key: 'score', dir: 'desc' });
    const [page, setPage] = useState(1);
    const pageSize = 14;
    const [query, setQuery] = useState('');

    const sorted = useMemo(() => {
      const list = D.listings.filter(l =>
        !query || (l.make + ' ' + l.model + ' ' + l.version).toLowerCase().includes(query.toLowerCase())
      );
      const k = sort.key;
      list.sort((a, b) => {
        const va = a[k], vb = b[k];
        if (typeof va === 'string') return sort.dir === 'asc' ? va.localeCompare(vb) : vb.localeCompare(va);
        return sort.dir === 'asc' ? va - vb : vb - va;
      });
      return list;
    }, [sort, query]);

    const total = sorted.length;
    const pages = Math.ceil(total / pageSize);
    const view = sorted.slice((page - 1) * pageSize, page * pageSize);

    const doSort = (key) => {
      if (sort.key === key) setSort({ key, dir: sort.dir === 'asc' ? 'desc' : 'asc' });
      else setSort({ key, dir: 'desc' });
    };
    const dirFor = (key) => sort.key === key ? sort.dir : null;

    return (
      <div className="curva" style={{ width, minHeight: 900, display: 'flex', flexDirection: 'column' }}>
        {!hideTopBar && <window.CurvaTopBar active="tabela" onNav={onNav} loggedIn={loggedIn} />}

        {/* header */}
        <div style={{
          display: 'flex', alignItems: 'flex-end', justifyContent: 'space-between',
          padding: '28px 28px 20px', gap: 20,
        }}>
          <div>
            <div className="t-up" style={{ marginBottom: 8 }}>Anúncios agregados</div>
            <h1 className="display" style={{ fontSize: 34, fontWeight: 600, letterSpacing: '-0.03em', margin: 0 }}>
              {D.listings.length.toLocaleString('pt-BR')} oportunidades <span style={{ color: 'var(--mute-2)' }}>de 4 fontes</span>
            </h1>
            <div style={{ fontSize: 13, color: 'var(--mute)', marginTop: 6 }}>
              Ordenado por <span style={{ color: 'var(--ink)', fontWeight: 600 }}>posição na curva</span>. Anúncios abaixo da curva aparecem primeiro.
            </div>
          </div>
          <div style={{ display: 'flex', gap: 8, alignItems: 'center' }}>
            <input className="input" placeholder="Buscar modelo ou versão"
              value={query} onChange={e => { setQuery(e.target.value); setPage(1); }}
              style={{ width: 260, fontSize: 13 }} />
            <button className="btn btn--ghost btn--sm">Filtros</button>
            <button className="btn btn--primary btn--sm">Salvar busca</button>
          </div>
        </div>

        {/* Quick filter chips */}
        <div style={{
          display: 'flex', alignItems: 'center', gap: 8,
          padding: '0 28px 18px', flexWrap: 'wrap',
        }}>
          <span className="t-up" style={{ marginRight: 4 }}>Atalhos</span>
          {['Abaixo da curva', '≤ 80k km', '2020+', 'Particular', 'SP capital', 'Civic Touring', 'Recém-listados'].map((c, i) => (
            <button key={c} className={`chip ${i < 2 ? 'chip--active' : ''}`} style={{ fontSize: 12 }}>{c}</button>
          ))}
        </div>

        {/* Table */}
        <div style={{ padding: '0 28px 24px', flex: 1 }}>
          <div style={{ background: 'var(--surface)', border: '1px solid var(--line)', borderRadius: 10, overflow: 'hidden' }}>
            <table className="tbl">
              <colgroup>
                <col style={{ width: 80 }} />
                <col />
                <col style={{ width: 80 }} />
                <col style={{ width: 130 }} />
                <col style={{ width: 130 }} />
                <col style={{ width: 130 }} />
                <col style={{ width: 160 }} />
                <col style={{ width: 130 }} />
                <col style={{ width: 130 }} />
                <col style={{ width: 80 }} />
              </colgroup>
              <thead>
                <tr>
                  <Th>foto</Th>
                  <Th sort="model" dir={dirFor('model')} onSort={doSort}>Marca · Modelo · Versão</Th>
                  <Th sort="year" dir={dirFor('year')} onSort={doSort} align="right">Ano</Th>
                  <Th sort="price" dir={dirFor('price')} onSort={doSort} align="right">Preço</Th>
                  <Th sort="km" dir={dirFor('km')} onSort={doSort} align="right">Km</Th>
                  <Th sort="score" dir={dirFor('score')} onSort={doSort} align="right">vs. curva</Th>
                  <Th sort="city" dir={dirFor('city')} onSort={doSort}>Cidade · UF</Th>
                  <Th sort="provider" dir={dirFor('provider')} onSort={doSort}>Provider</Th>
                  <Th sort="daysAgo" dir={dirFor('daysAgo')} onSort={doSort}>Atualizado</Th>
                  <Th align="right"></Th>
                </tr>
              </thead>
              <tbody>
                {view.map(l => {
                  const tone = D.scoreTone(l.score);
                  return (
                    <tr key={l.id}>
                      <td>
                        <window.CurvaPhotoStub width={56} height={42} />
                      </td>
                      <td>
                        <div style={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
                          <span style={{ fontWeight: 600, fontSize: 14 }}>{l.make} {l.model}</span>
                          <span style={{ fontSize: 12, color: 'var(--mute)' }}>{l.version}</span>
                        </div>
                      </td>
                      <td style={{ textAlign: 'right' }}>
                        <span className="mono" style={{ fontSize: 13 }}>{l.year}</span>
                      </td>
                      <td style={{ textAlign: 'right' }}>
                        <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'flex-end', gap: 2 }}>
                          <window.CurvaPrice value={l.price} size="md" />
                          <span className="mono" style={{ fontSize: 10, color: 'var(--mute-2)' }}>
                            justo {D.fmtBRLshort(l.fair)}
                          </span>
                        </div>
                      </td>
                      <td style={{ textAlign: 'right' }}>
                        <span className="mono" style={{ fontSize: 13 }}>{l.km.toLocaleString('pt-BR')}</span>
                      </td>
                      <td style={{ textAlign: 'right' }}>
                        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'flex-end', gap: 6 }}>
                          <window.CurvaSparkCurve score={l.score} width={48} height={20} />
                          <window.CurvaScoreBadge score={l.score} />
                        </div>
                      </td>
                      <td>
                        <span style={{ fontSize: 13 }}>{l.city}</span>
                        <span style={{ fontSize: 11, color: 'var(--mute)', marginLeft: 4 }}>· {l.uf}</span>
                      </td>
                      <td>
                        <span className="mono" style={{ fontSize: 12, color: 'var(--mute)' }}>{l.provider}</span>
                      </td>
                      <td>
                        <span style={{ fontSize: 12, color: 'var(--mute)' }}>
                          {l.daysAgo === 0 ? 'hoje' : l.daysAgo === 1 ? 'ontem' : `há ${l.daysAgo}d`}
                        </span>
                      </td>
                      <td style={{ textAlign: 'right' }}>
                        <button className="btn btn--ghost btn--sm" style={{ padding: '5px 9px' }}>
                          abrir ↗
                        </button>
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
            {/* footer */}
            <div style={{
              display: 'flex', alignItems: 'center', justifyContent: 'space-between',
              padding: '12px 16px', background: 'var(--surface-2)',
              borderTop: '1px solid var(--line)',
            }}>
              <span style={{ fontSize: 12, color: 'var(--mute)' }} className="mono">
                {(page - 1) * pageSize + 1}–{Math.min(page * pageSize, total)} de {total.toLocaleString('pt-BR')}
              </span>
              <div style={{ display: 'flex', gap: 4, alignItems: 'center' }}>
                <button className="btn btn--ghost btn--sm" onClick={() => setPage(Math.max(1, page - 1))}>←</button>
                {Array.from({ length: Math.min(5, pages) }).map((_, i) => {
                  const p = i + 1;
                  return (
                    <button key={p}
                      onClick={() => setPage(p)}
                      className={`btn btn--sm ${p === page ? 'btn--primary' : 'btn--ghost'}`}
                      style={{ minWidth: 28, justifyContent: 'center' }}>
                      {p}
                    </button>
                  );
                })}
                <span style={{ color: 'var(--mute)', fontSize: 12 }}>…</span>
                <button className="btn btn--ghost btn--sm" style={{ minWidth: 28, justifyContent: 'center' }}>{pages}</button>
                <button className="btn btn--ghost btn--sm" onClick={() => setPage(Math.min(pages, page + 1))}>→</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }

  return Tabela;
})();

window.CurvaTabela = CurvaTabela;
