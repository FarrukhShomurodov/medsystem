import { jQueryFactory } from 'jquery/factory';

// The layout loads jQuery as a classic script before any module; lib/jquery.js reads it from window.
window.jQuery = jQueryFactory(window);
window.$ = window.jQuery;
