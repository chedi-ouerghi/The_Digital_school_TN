/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './index.html',
    './src/**/*.{vue,js,ts,jsx,tsx,html}'
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        // Primary - Emerald
        primary: {
          50: '#ECFDF5',
          100: '#D1FAE5',
          200: '#A7F3D0',
          300: '#6EE7B7',
          400: '#34D399',
          500: '#10B981',
          600: '#059669',
          700: '#047857',
          800: '#065F46',
          900: '#064E3B',
          950: '#022C22',
        },
        // Secondary - Blue
        secondary: {
          50: '#EFF6FF',
          100: '#DBEAFE',
          200: '#BFDBFE',
          300: '#93C5FD',
          400: '#60A5FA',
          500: '#3B82F6',
          600: '#2563EB',
          700: '#1D4ED8',
          800: '#1E40AF',
          900: '#1E3A8A',
          950: '#172554',
        },
        // Semantic colors
        success: '#10B981',
        warning: '#F59E0B',
        error: '#EF4444',
        info: '#3B82F6',
        // Neutral colors - Slate scale
        slate: {
          50: '#F8FAFC',
          100: '#F1F5F9',
          200: '#E2E8F0',
          300: '#CBD5E1',
          400: '#94A3B8',
          500: '#64748B',
          600: '#475569',
          700: '#334155',
          800: '#1E293B',
          900: '#0F172A',
          950: '#020617',
        },
        // Background colors
        background: '#FFFFFF',
        surface: '#F8FAFC',
        elevated: '#FFFFFF',
        // Border colors
        border: {
          light: '#E2E8F0',
          medium: '#CBD5E1',
          dark: '#94A3B8',
          primary: '#10B981',
          accent: '#064E3B',
        },
        // Text colors
        text: {
          primary: '#1E293B',
          secondary: '#64748B',
          tertiary: '#94A3B8',
          muted: '#94A3B8',
          inverted: '#FFFFFF',
        },
        brand: {
          blue: '#35A7FF',
          dark: '#38618C',
          green: '#01FF19',
          red: '#FF5964',
          'red-light': '#FF8B94',
          navy: '#071B2C',
        },
        // Gradients
        gradients: {
          primary: 'linear-gradient(135deg, #10B981, #059669)',
          secondary: 'linear-gradient(135deg, #3B82F6, #1D4ED8)',
          hero: 'linear-gradient(135deg, #10B981, #3B82F6)',
        },
      },
      borderRadius: {
        '4xl': '2rem',
        '5xl': '2.5rem',
      },
      boxShadow: {
        'primary-sm': '0 4px 14px 0 rgba(16, 185, 129, 0.3)',
        'primary-md': '0 10px 30px 0 rgba(16, 185, 129, 0.3)',
        'primary-lg': '0 20px 50px 0 rgba(16, 185, 129, 0.2)',
        'secondary-sm': '0 4px 14px 0 rgba(59, 130, 246, 0.3)',
        'secondary-md': '0 10px 30px 0 rgba(59, 130, 246, 0.3)',
      },
      spacing: {
        '18': '4.5rem',
        '88': '22rem',
        '128': '32rem',
      },
      animation: {
        'float': 'float 3s ease-in-out infinite',
        'glow': 'glow 2s ease-in-out infinite alternate',
        'shimmer': 'shimmer 2s linear infinite',
        'fade-in-up': 'fadeInUp 0.3s ease-out',
        'scale-in': 'scaleIn 0.3s ease-out',
      },
      keyframes: {
        float: {
          '0%, 100%': { transform: 'translateY(0)' },
          '50%': { transform: 'translateY(-10px)' },
        },
        glow: {
          '0%': { boxShadow: '0 0 20px rgba(16, 185, 129, 0.3)' },
          '100%': { boxShadow: '0 0 40px rgba(16, 185, 129, 0.5)' },
        },
        shimmer: {
          '0%': { backgroundPosition: '-200% 0' },
          '100%': { backgroundPosition: '200% 0' },
        },
        fadeInUp: {
          from: { opacity: '0', transform: 'translateY(10px)' },
          to: { opacity: '1', transform: 'translateY(0)' },
        },
        scaleIn: {
          from: { opacity: '0', transform: 'scale(0.95)' },
          to: { opacity: '1', transform: 'scale(1)' },
        },
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
      },
    },
  },
  plugins: [],
}