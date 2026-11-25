/**
 * The page's jQuery, loaded by the layout as a classic script before any module (see vite.config.js).
 * `import $ from 'jquery'` resolves here, so every plugin registers on the instance the views'
 * inline scripts use.
 */
const jQuery = window.jQuery;

if (typeof jQuery !== 'function') {
    throw new Error('jQuery is missing: the layout loads it before resources/js/app.js.');
}

export default jQuery;
