import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            // AQUÍ ESTÁ EL CAMBIO IMPORTANTE:
            // Agregamos 'resources/css/pedidos.css' a la lista de entrada (input)
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                'resources/css/pedidos.css' 
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});