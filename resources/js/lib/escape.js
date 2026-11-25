const htmlEscapes = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };

/**
 * Escape a value before putting it into an HTML string (template literals, .html(), .append()).
 * Prefer textContent / $(...).text() when building a single node.
 *
 * @param {unknown} value
 * @returns {string}
 */
export function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (char) => htmlEscapes[char]);
}
