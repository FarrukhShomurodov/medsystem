import { existsSync, readdirSync, readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

/*
| Entries: the dashboard stylesheet and script, plus one module per page that needs more than a few
| lines of glue (resources/js/pages/*.js, loaded by that page with @vite('resources/js/pages/x.js')).
|
| jQuery is the one library that is not bundled: the views' inline scripts call $ while the page is
| still being parsed, before any module runs, so the layout loads jQuery's own file as a classic
| <script> (Vite::asset(), hashed like everything else). Every module that imports 'jquery' gets that
| same global instance through the alias below, so select2 and the Bootstrap bridge ($.fn.modal)
| attach to the copy the inline scripts use.
*/

const pages = existsSync('resources/js/pages')
    ? readdirSync('resources/js/pages')
          .filter((file) => file.endsWith('.js'))
          .sort()
          .map((file) => `resources/js/pages/${file}`)
    : [];

/**
 * `@use 'boxicons:classes'` in resources/scss/_icons.scss: boxicons.css without its @font-face,
 * which lists five font formats (eot, svg, ttf ...) for very old browsers and would put all of them
 * in the build. _icons.scss declares the woff2 + woff face instead.
 */
const boxiconsClasses = {
    canonicalize: (url) => (url === 'boxicons:classes' ? new URL(url) : null),
    load: () => ({
        syntax: 'css',
        contents: readFileSync(fileURLToPath(new URL('node_modules/boxicons/css/boxicons.css', import.meta.url)), 'utf8')
            .replace(/@font-face\s*\{[^}]*\}/, ''),
    }),
};

// `make assets-watch` (compose profile "assets") runs the dev server in a container: listen on every
// interface, and point the browser on the host at the published port for the assets and HMR.
const inDocker = process.env.VITE_DOCKER === '1';
const port = Number(process.env.VITE_PORT ?? 5173);

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/scss/app.scss', 'resources/js/app.js', ...pages],
            assets: ['node_modules/jquery/dist/jquery.min.js'],
            refresh: true,
        }),
    ],
    resolve: {
        alias: [
            {
                find: /^jquery$/,
                replacement: fileURLToPath(new URL('resources/js/lib/jquery.js', import.meta.url)),
            },
        ],
    },
    build: {
        // Tabler themes its surfaces with light-dark(): the dark sidebar is a subtree with
        // color-scheme: dark. Older targets make the CSS minifier replace light-dark() with a
        // polyfill that resolves on :root, which paints the sidebar white; these browsers (2024 on)
        // run it natively, like Tabler's own build.
        cssTarget: ['chrome123', 'edge123', 'firefox120', 'safari17.5'],
        // ApexCharts' core alone is ~590 kB minified; only the nurse window loads it, already reduced
        // to the line chart and the three features it uses (resources/js/pages/nurse-window.js).
        chunkSizeWarningLimit: 800,
    },
    css: {
        preprocessorOptions: {
            scss: {
                importers: [boxiconsClasses],
            },
        },
    },
    server: {
        // The PHP side (vendor, storage with its uploads, logs and compiled views) is not source.
        watch: {
            ignored: ['**/vendor/**', '**/storage/**', '**/public/build/**'],
            // A bind-mounted tree on Docker Desktop does not deliver file events reliably.
            ...(inDocker ? { usePolling: true, interval: 300 } : {}),
        },
        ...(inDocker
            ? {
                  host: '0.0.0.0',
                  port,
                  strictPort: true,
                  origin: `http://localhost:${port}`,
                  hmr: { host: 'localhost', clientPort: port },
              }
            : {}),
    },
});
