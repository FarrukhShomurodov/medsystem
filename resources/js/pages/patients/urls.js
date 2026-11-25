/**
 * Fill the placeholders of a URL the view generated with route() for ids it does not know yet:
 *
 *   data-questions-url="{{ route('api.surveys.questions', ['survey' => '__survey__']) }}"
 *   fillUrl(element.dataset.questionsUrl, { survey: 12 })  →  "/api/surveys/12/questions"
 *
 * @param {string} template
 * @param {Record<string, string|number>} values placeholder name (without the underscores) → value
 * @returns {string}
 */
export function fillUrl(template, values) {
    return String(template).replace(/__([a-z_]+?)__/g, (placeholder, name) =>
        name in values ? encodeURIComponent(String(values[name])) : placeholder,
    );
}
