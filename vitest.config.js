import { defineConfig } from 'vitest/config';
import build from './vite.config.js';

/*
| The tests of the page scripts (tests/js, `npm test`). They resolve imports the way the build does
| (the 'jquery' alias of vite.config.js) and run in happy-dom, a DOM without a browser. The Laravel
| plugin, which serves the dev server and writes the build manifest, is left out.
*/
export default defineConfig({
    resolve: build.resolve,
    test: {
        include: ['tests/js/**/*.test.js'],
        environment: 'happy-dom',
        setupFiles: ['tests/js/setup.js'],
        restoreMocks: true,
        unstubGlobals: true,
    },
});
