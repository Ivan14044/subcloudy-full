/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: ['./src/renderer/index.html', './src/renderer/src/**/*.{vue,js,ts}'],
    theme: {
        extend: {
            keyframes: {
                'fade-in-up': {
                    '0%': { opacity: '0', transform: 'translateY(40px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' }
                },
                'spin-slow-reverse': {
                    '0%': { transform: 'rotate(0deg)' },
                    '100%': { transform: 'rotate(-360deg)' }
                }
            },
            animation: {
                'fade-in-up': 'fade-in-up 0.7s ease-out forwards',
                'spin-slow-reverse': 'spin-slow-reverse 2.5s linear infinite'
            },
            colors: {
                'midnight-alpha': {
                    300: 'rgba(12, 24, 60, 0.3)',
                    500: 'rgba(12, 24, 60, 0.5)',
                    700: 'rgba(12, 24, 60, 0.7)'
                },
                'indigo-soft-alpha': {
                    400: 'rgba(219, 225, 252, 0.4)',
                    500: 'rgba(219, 225, 252, 0.5)'
                },
                'indigo-soft': {
                    100: '#eef1fe',
                    200: '#dbe1fc',
                    300: '#c8d2fa',
                    400: '#aebaf7',
                    500: '#8da0f0',
                    600: '#6f84e3',
                    700: '#5367cd',
                    800: '#3e50ab',
                    900: '#2c3b87'
                }
            },
            fontFamily: {
                sans: [
                    'SFT Schrifted Sans',
                    'system-ui',
                    '-apple-system',
                    'BlinkMacSystemFont',
                    'Segoe UI',
                    'Roboto',
                    'Helvetica Neue',
                    'Arial',
                    'sans-serif'
                ],
                sft: ['SFT Schrifted Sans', 'sans-serif']
            }
        }
    },
    plugins: []
};


