/**
 * Design System for BitChest
 * Premium + Modern + Minimal + Elegant
 * 
 * Color Palette:
 * - Primary: Emerald (Success, Growth, Trust)
 * - Secondary: Blue (Technology, Professional)
 * - Accent: Dark Slate (Contrast, Sophistication)
 * 
 * Typography:
 * - Font Family: Inter (Clean, Modern, Readable)
 * - Hierarchy: Clear scaling from xs to 6xl
 * 
 * Spacing:
 * - Based on Tailwind's 4px (rem) scale
 * - Consistent padding and margins
 * 
 * Shadows:
 * - Subtle, layered shadows for depth
 * - Glassmorphism effects where appropriate
 * 
 * Radius:
 * - Consistent border-radius across all components
 * - Smooth, rounded corners for modern feel
 */

// ============================================
// COLOR PALETTE
// ============================================

export const colors = {
  // Brand Colors
  primary: {
    50: '#ECFDF5',
    100: '#D1FAE5',
    200: '#A7F3D0',
    300: '#6EE7B7',
    400: '#34D399',
    500: '#10B981',    // Main Primary
    600: '#059669',
    700: '#047857',
    800: '#065F46',
    900: '#064E3B',    // Dark Accent
    950: '#022C22',
  },

  secondary: {
    50: '#EFF6FF',
    100: '#DBEAFE',
    200: '#BFDBFE',
    300: '#93C5FD',
    400: '#60A5FA',
    500: '#3B82F6',    // Main Secondary
    600: '#2563EB',
    700: '#1D4ED8',
    800: '#1E40AF',
    900: '#1E3A8A',
    950: '#172554',
  },

  // Semantic Colors
  success: '#10B981',
  warning: '#F59E0B',
  error: '#EF4444',
  info: '#3B82F6',

  // Neutral Colors
  white: '#FFFFFF',
  black: '#000000',

  // Slate Scale (for text and backgrounds)
  slate: {
    50: '#F8FAFC',
    100: '#F1F5F9',
    200: '#E2E8F0',
    300: '#CBD5E1',
    400: '#94A3B8',
    500: '#64748B',
    600: '#475569',
    700: '#334155',
    800: '#1E293B',    // Text Primary
    900: '#0F172A',
    950: '#020617',
  },

  // Background Colors
  background: '#FFFFFF',
  surface: '#F8FAFC',
  elevated: '#FFFFFF',

  // Border Colors
  border: {
    light: '#E2E8F0',
    medium: '#CBD5E1',
    dark: '#94A3B8',
    primary: '#10B981',
    accent: '#064E3B',
  },

  // Text Colors
  text: {
    primary: '#1E293B',    // Slate 800
    secondary: '#64748B',  // Slate 500
    tertiary: '#94A3B8',   // Slate 400
    muted: '#94A3B8',
    inverted: '#FFFFFF',
  },

  // Gradients
  gradients: {
    primary: 'linear-gradient(135deg, #10B981, #059669)',
    secondary: 'linear-gradient(135deg, #3B82F6, #1D4ED8)',
    hero: 'linear-gradient(135deg, #10B981, #3B82F6)',
    glass: 'linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05))',
  },

  // Legacy compatibility for existing components
  // Note: These use different names to avoid conflict with the object properties above
  get primaryColor() { return this.primary[500]; },
  get secondaryColor() { return this.secondary[500]; },
  get accentColor() { return this.slate[900]; },
  get textPrimaryColor() { return this.text.primary; },
  get textSecondaryColor() { return this.text.secondary; },
  get textTertiaryColor() { return this.text.tertiary; },
  get borderLightColor() { return this.border.light; },
  get borderAccentColor() { return this.border.primary; },

  hexWithOpacity: (hex: string, opacity: number) => {
    const normalized = hex.replace('#', '');
    const value = normalized.length === 3
      ? normalized.split('').map((character) => character + character).join('')
      : normalized;

    const red = Number.parseInt(value.slice(0, 2), 16);
    const green = Number.parseInt(value.slice(2, 4), 16);
    const blue = Number.parseInt(value.slice(4, 6), 16);

    return `rgba(${red}, ${green}, ${blue}, ${opacity})`;
  },
} as const;

// ============================================
// TYPOGRAPHY
// ============================================

export const typography = {
  fontFamily: {
    sans: 'Inter, system-ui, -apple-system, sans-serif',
    mono: 'ui-monospace, SFMono-Regular, Menlo, monospace',
  },

  fontSize: {
    xs: '0.75rem',      // 12px
    sm: '0.875rem',     // 14px
    base: '1rem',       // 16px
    lg: '1.125rem',     // 18px
    xl: '1.25rem',      // 20px
    '2xl': '1.5rem',     // 24px
    '3xl': '1.875rem',   // 30px
    '4xl': '2.25rem',    // 36px
    '5xl': '3rem',       // 48px
    '6xl': '3.75rem',    // 60px
    '7xl': '4.5rem',     // 72px
    '8xl': '6rem',       // 96px
  },

  fontWeight: {
    normal: 400,
    medium: 500,
    semibold: 600,
    bold: 700,
    extrabold: 800,
    black: 900,
  },

  lineHeight: {
    tight: 1.25,
    normal: 1.5,
    relaxed: 1.75,
    loose: 2,
  },

  letterSpacing: {
    tight: '-0.025em',
    normal: '0',
    wide: '0.025em',
    wider: '0.05em',
  },

  // Hierarchy presets
  hero: {
    fontSize: 'clamp(2.5rem, 5vw, 4rem)',
    fontWeight: 800,
    lineHeight: 1.1,
    letterSpacing: '-0.025em',
    color: colors.text.primary,
  },

  sectionTitle: {
    fontSize: 'clamp(2rem, 4vw, 3rem)',
    fontWeight: 700,
    lineHeight: 1.2,
    letterSpacing: '-0.015em',
    color: colors.text.primary,
  },

  subsectionTitle: {
    fontSize: 'clamp(1.5rem, 3vw, 2.25rem)',
    fontWeight: 600,
    lineHeight: 1.3,
    color: colors.text.primary,
  },

  body: {
    fontSize: '1.125rem',
    fontWeight: 400,
    lineHeight: 1.75,
    color: colors.text.secondary,
  },

  label: {
    fontSize: '0.875rem',
    fontWeight: 500,
    lineHeight: 1.5,
    color: colors.text.primary,
  },

  small: {
    fontSize: '0.75rem',
    fontWeight: 500,
    lineHeight: 1.5,
    color: colors.text.tertiary,
  },
} as const;

// ============================================
// SPACING
// ============================================

export const spacing = {
  // Base unit: 0.25rem (4px)
  base: '0.25rem',

  // Scale
  0: '0',
  1: '0.25rem',   // 4px
  2: '0.5rem',    // 8px
  3: '0.75rem',   // 12px
  4: '1rem',      // 16px
  5: '1.25rem',   // 20px
  6: '1.5rem',    // 24px
  8: '2rem',      // 32px
  10: '2.5rem',   // 40px
  12: '3rem',     // 48px
  16: '4rem',     // 64px
  20: '5rem',     // 80px
  24: '6rem',     // 96px
  32: '8rem',     // 128px
  40: '10rem',    // 160px

  // Container widths
  container: {
    sm: '640px',
    md: '768px',
    lg: '1024px',
    xl: '1280px',
    '2xl': '1536px',
    max: '1280px',  // Optimal line length for readability
  },

  // Section spacing
  section: {
    paddingY: {
      sm: '4rem',     // 64px
      md: '6rem',     // 96px
      lg: '8rem',     // 128px
    },
  },
} as const;

// ============================================
// BORDER RADIUS
// ============================================

export const radius = {
  none: '0',
  sm: '0.25rem',   // 4px
  md: '0.375rem',  // 6px
  lg: '0.5rem',    // 8px
  xl: '0.75rem',   // 12px
  '2xl': '1rem',    // 16px
  '3xl': '1.5rem',  // 24px
  full: '9999px',

  // Component-specific
  button: '0.75rem',     // xl
  card: '1rem',         // 2xl
  input: '0.5rem',      // lg
  pill: '9999px',       // full
} as const;

// ============================================
// SHADOWS
// ============================================

export const shadows = {
  // Light shadows (for white/light backgrounds)
  sm: '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
  md: '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
  lg: '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
  xl: '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)',

  // Colored shadows (for primary/secondary elements)
  primary: {
    sm: '0 4px 14px 0 rgba(16, 185, 129, 0.3)',
    md: '0 10px 30px 0 rgba(16, 185, 129, 0.3)',
    lg: '0 20px 50px 0 rgba(16, 185, 129, 0.2)',
  },
  secondary: {
    sm: '0 4px 14px 0 rgba(59, 130, 246, 0.3)',
    md: '0 10px 30px 0 rgba(59, 130, 246, 0.3)',
    lg: '0 20px 50px 0 rgba(59, 130, 246, 0.2)',
  },

  // Glassmorphism
  glass: {
    sm: '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
    md: '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
    inner: 'inset 0 2px 4px 0 rgba(255, 255, 255, 0.1)',
  },

  // Inner shadows
  inner: {
    sm: 'inset 0 1px 2px 0 rgba(0, 0, 0, 0.05)',
    md: 'inset 0 2px 4px 0 rgba(0, 0, 0, 0.08)',
  },
} as const;

// ============================================
// TRANSITIONS & ANIMATIONS
// ============================================

export const transitions = {
  // Base transitions
  base: 'all 0.2s ease-in-out',
  fast: 'all 0.15s ease-in-out',
  slow: 'all 0.3s ease-in-out',

  // Specific transitions
  colors: 'color 0.2s ease-in-out, background-color 0.2s ease-in-out',
  opacity: 'opacity 0.2s ease-in-out',
  transform: 'transform 0.2s ease-in-out',
  shadow: 'box-shadow 0.2s ease-in-out',

  // Hover effects
  hover: {
    scale: 'transform 0.2s ease-in-out',
    translateY: 'transform 0.2s ease-in-out',
    shadow: 'box-shadow 0.2s ease-in-out',
  },

  // Bounce effect
  bounce: 'transform 0.3s ease-out',

  // Spin
  spin: 'transform 0.8s linear',
} as const;

// ============================================
// Z-INDEX LAYERS
// ============================================

export const zIndex = {
  base: 0,
  dropdown: 100,
  sticky: 200,
  fixed: 300,
  modalBackdrop: 400,
  modal: 500,
  popover: 600,
  tooltip: 700,
  toast: 800,
  loading: 900,
  max: 9999,
} as const;

// ============================================
// BREAKPOINTS
// ============================================

export const breakpoints = {
  sm: '640px',
  md: '768px',
  lg: '1024px',
  xl: '1280px',
  '2xl': '1536px',
} as const;

// ============================================
// UTILITY FUNCTIONS
// ============================================

/**
 * Generate a CSS variable name from a path
 */
export function cssVar(path: string): string {
  return `var(--${path.replace(/\./g, '-')})`;
}

/**
 * Generate Tailwind arbitrary value
 */
export function tw(path: string, value: string | number): string {
  return `[${path}:${value}]`;
}

/**
 * Generate a hex color with opacity
 */
export function hexWithOpacity(hex: string, opacity: number): string {
  // Remove # if present
  const cleanHex = hex.replace('#', '');

  // Parse r, g, b values
  const r = parseInt(cleanHex.substring(0, 2), 16);
  const g = parseInt(cleanHex.substring(2, 4), 16);
  const b = parseInt(cleanHex.substring(4, 6), 16);

  return `rgba(${r}, ${g}, ${b}, ${opacity})`;
}

/**
 * Generate CSS custom properties for colors
 */
export function generateColorVariables(): Record<string, string> {
  const vars: Record<string, string> = {};

  for (const [key, value] of Object.entries(colors)) {
    if (typeof value === 'string') {
      vars[`--color-${key}`] = value;
    } else if (typeof value === 'object' && value !== null) {
      for (const [shade, color] of Object.entries(value)) {
        if (typeof color === 'string') {
          vars[`--color-${key}-${shade}`] = color;
        }
      }
    }
  }

  return vars;
}

// ============================================
// EXPORT DEFAULT
// ============================================

export default {
  colors,
  typography,
  spacing,
  radius,
  shadows,
  transitions,
  zIndex,
  breakpoints,
  cssVar,
  tw,
  hexWithOpacity,
  generateColorVariables,
};
