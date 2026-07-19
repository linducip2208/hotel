import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

const dynamicColors = [
    'indigo', 'violet', 'purple', 'fuchsia', 'pink', 'rose', 'red', 'orange',
    'amber', 'yellow', 'lime', 'green', 'emerald', 'teal', 'cyan', 'sky',
    'blue', 'slate', 'gray', 'zinc', 'neutral', 'stone',
];
const dynamicShades = ['50', '100', '200', '300', '400', '500', '600', '700', '800', '900'];

export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    safelist: [
        {
            pattern: new RegExp(`^(bg|text|border|ring)-(${dynamicColors.join('|')})-(${dynamicShades.join('|')})$`),
            variants: ['hover', 'focus', 'group-hover'],
        },
        {
            pattern: new RegExp(`^(bg|border)-(${dynamicColors.join('|')})-50\\/40$`),
            variants: ['hover'],
        },
        {
            pattern: new RegExp(`^(from|to|via)-(${dynamicColors.join('|')})-(${dynamicShades.join('|')})$`),
        },
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    50:  '#eef2ff',
                    100: '#e0e7ff',
                    200: '#c7d2fe',
                    300: '#a5b4fc',
                    400: '#818cf8',
                    500: '#6366f1',
                    600: '#4f46e5',
                    700: '#4338ca',
                    800: '#3730a3',
                    900: '#312e81',
                    950: '#1e1b4b',
                },
                navy: {
                    700: '#1a2744',
                    800: '#111827',
                    900: '#0d1520',
                },
            },
            boxShadow: {
                'card': '0 1px 3px 0 rgb(0 0 0/.06),0 1px 2px -1px rgb(0 0 0/.06)',
                'card-hover': '0 8px 24px -4px rgb(0 0 0/.12)',
            },
        },
    },
    plugins: [forms, typography],
};
