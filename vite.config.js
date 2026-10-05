import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';
import fs from 'node:fs';

const possibleSslPaths = [
    {
        key: '/Applications/MAMP/conf/apache/ssl/waldo.key',
        cert: '/Applications/MAMP/conf/apache/ssl/waldo.crt',
    },
    {
        key: 'D:/wamp64/bin/apache/apache2.4.65/conf/ssl/waldo.key',
        cert: 'D:/wamp64/bin/apache/apache2.4.65/conf/ssl/waldo.crt',
    },
    {
        key: 'C:/wamp64/bin/apache/apache2.4.65/conf/ssl/waldo.key',
        cert: 'C:/wamp64/bin/apache/apache2.4.65/conf/ssl/waldo.crt',
    },
];

const activeSsl = possibleSslPaths.find(
    (p) => fs.existsSync(p.key) && fs.existsSync(p.cert)
);

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
        https: activeSsl
            ? {
                  key: fs.readFileSync(activeSsl.key),
                  cert: fs.readFileSync(activeSsl.cert),
              }
            : false,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});

