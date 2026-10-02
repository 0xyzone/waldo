import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';
import fs from 'node:fs';

const sslKey = 'D:/wamp64/bin/apache/apache2.4.65/conf/ssl/waldo.key';
const sslCert = 'D:/wamp64/bin/apache/apache2.4.65/conf/ssl/waldo.crt';
const hasSsl = fs.existsSync(sslKey) && fs.existsSync(sslCert);

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/css/filament/kamkaj/theme.css'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        host: 'waldo',
        cors: true,
        https: hasSsl
            ? {
                  key: fs.readFileSync(sslKey),
                  cert: fs.readFileSync(sslCert),
              }
            : false,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});

