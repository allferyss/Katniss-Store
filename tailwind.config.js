/** @type {import('tailwindcss').Config} */
export default {
    // app/Enums disertakan agar kelas warna status tidak ikut terbuang saat build.
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './app/Enums/**/*.php',
    ],
    theme: {
        extend: {
            colors: {
                ink: '#0b0b14',
                surface: '#13131f',
                surface2: '#1b1b2c',
                line: '#2b2b40',
                brand: {
                    300: '#c4b5fd',
                    400: '#a78bfa',
                    500: '#8b5cf6',
                    600: '#7c3aed',
                    700: '#6d28d9',
                },
            },
            fontFamily: {
                sans: ['ui-sans-serif', 'system-ui', '-apple-system', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif'],
            },
        },
    },
    plugins: [],
};
