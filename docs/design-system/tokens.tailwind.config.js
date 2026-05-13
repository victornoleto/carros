/**
 * Curva — Tailwind config tokens
 *
 * Drop these into your tailwind.config.js theme.extend. Tokens are also
 * exposed as CSS variables (see tokens.css) so you can use either approach.
 */

module.exports = {
  theme: {
    extend: {
      colors: {
        paper:   { DEFAULT: '#f3f0e8', 2: '#ece7db' },
        surface: { DEFAULT: '#ffffff', 2: '#faf8f3' },
        ink:     { DEFAULT: '#14110d', 2: '#2a251e' },
        mute:    { DEFAULT: '#6e6557', 2: '#97907f' },
        line:    { DEFAULT: '#e3ddcd', 2: '#d3cab5' },
        good:    { DEFAULT: '#1a6c4d', soft: '#d9ebe1', ink: '#0e3e2c' },
        warn:    { DEFAULT: '#b04421', soft: '#f1dbcf' },
        neutral: { DEFAULT: '#8b7d5c', soft: '#ece4d0' },
      },
      fontFamily: {
        display: ['"Space Grotesk"', 'system-ui', 'sans-serif'],
        sans:    ['Inter', 'system-ui', 'sans-serif'],
        mono:    ['"JetBrains Mono"', 'ui-monospace', 'monospace'],
      },
      letterSpacing: {
        tightish:  '-0.02em',
        tighter2:  '-0.025em',
        tightest2: '-0.03em',
        widish:    '0.08em',
      },
      borderRadius: {
        'xs2': '4px',
        'sm2': '6px',
        'md2': '10px',
        'lg2': '14px',
        'xl2': '22px',
      },
      fontSize: {
        // sizes used in the design — add to your scale
        'num-xl': ['32px', { lineHeight: '1', letterSpacing: '-0.03em', fontWeight: '600' }],
        'num-lg': ['22px', { lineHeight: '1', letterSpacing: '-0.025em', fontWeight: '600' }],
      },
      animation: {
        'dot-pulse': 'dot-pulse 1.8s ease-in-out infinite',
      },
      keyframes: {
        'dot-pulse': {
          '0%':   { boxShadow: '0 0 0 0 rgba(26,108,77,.35)' },
          '70%':  { boxShadow: '0 0 0 6px rgba(26,108,77,0)' },
          '100%': { boxShadow: '0 0 0 0 rgba(26,108,77,0)' },
        },
      },
    },
  },
};
