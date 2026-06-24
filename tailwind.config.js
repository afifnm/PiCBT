/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                primary: {
                    50:  '#f0f0ff',
                    100: '#e4e4ff',
                    200: '#cdcdff',
                    300: '#b0aeff',
                    400: '#9289fe',
                    500: '#7c6af6',
                    600: '#6b4fec',
                    700: '#5b3dd1',
                    800: '#4c33aa',
                    900: '#3f2c87',
                    950: '#261a5c',
                },
                surface: {
                    50:  '#f8f8fc',
                    100: '#f0f0f8',
                    200: '#e4e4f0',
                    300: '#d1d1e4',
                    400: '#a8a8c4',
                    500: '#8080a8',
                    600: '#60608c',
                    700: '#484870',
                    800: '#343458',
                    900: '#222240',
                    950: '#14142c',
                },
            },
            fontFamily: {
                sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
            },
            boxShadow: {
                'soft': '0 2px 8px 0 rgba(107,79,236,0.07), 0 1px 2px 0 rgba(0,0,0,0.04)',
                'soft-md': '0 4px 16px 0 rgba(107,79,236,0.10), 0 2px 4px 0 rgba(0,0,0,0.05)',
                'soft-lg': '0 8px 32px 0 rgba(107,79,236,0.12), 0 4px 8px 0 rgba(0,0,0,0.06)',
            },
            borderRadius: {
                '2xl': '1rem',
                '3xl': '1.25rem',
            },
            transitionTimingFunction: {
                'soft': 'cubic-bezier(0.4, 0, 0.2, 1)',
            },
        },
    },
    plugins: [
        require('@tailwindcss/typography'),
    ],
};
