/**
 * Data a Blade view hands to its script in a JSON block:
 *
 *   <script type="application/json" id="calendar-data">@json(['staff' => $staff->id, 'weekStart' => $weekStart])</script>
 *
 *   const { staff, weekStart } = pageData('calendar-data');
 *
 * @json escapes <, >, & and quotes, so the block cannot be closed early by the data.
 *
 * @param {string} id the id of the <script type="application/json"> element
 * @returns {any} the decoded value, or null when the page has no such block
 */
export function pageData(id) {
    const element = document.getElementById(id);

    return element ? JSON.parse(element.textContent) : null;
}
