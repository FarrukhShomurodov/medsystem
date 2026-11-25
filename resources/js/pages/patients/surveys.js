/*
| Medical documents (surveys) on the patient pages: the questions of a survey drawn as form fields
| (patient card, survey page of an examination map), and the answers of a filled-in survey (map page,
| survey page), whose chosen answers can be copied into the form being filled in.
|
| The question types come from the view (App\Enums\QuestionType values on data attributes), the URLs
| from route() with placeholders (see urls.js). Every value of the server is escaped.
*/
import { escapeHtml } from '../../lib/escape';
import { showFlash } from '../../lib/flash';
import { fetchJson } from '../../lib/http';

/** Answers of a "Да / Нет" question are stored as yes / no. */
export function answerLabel(answer) {
    const value = String(answer ?? '');

    return { yes: 'Да', no: 'Нет' }[value.toLowerCase()] ?? value;
}

function radio(name, id, value, label) {
    return `
        <label class="form-check" for="${id}">
            <input class="form-check-input" type="radio" id="${id}" name="${name}" value="${escapeHtml(value)}" required>
            <span class="form-check-label">${escapeHtml(label)}</span>
        </label>`;
}

/**
 * @param {{id: number, question: string, type: string, options?: {text: string}[]}} question
 * @param {number} number
 * @param {{choice: string, boolean: string}} types
 */
function questionField(question, number, types) {
    const key = escapeHtml(question.id);
    const name = `answers[${key}]`;
    const id = `answer-${key}`;
    const title = `${number}. ${escapeHtml(question.question)}`;

    if (question.type === types.choice || question.type === types.boolean) {
        const options =
            question.type === types.choice
                ? (question.options ?? []).map((option) => [option.text, option.text])
                : [
                      ['yes', 'Да'],
                      ['no', 'Нет'],
                  ];

        return `
            <fieldset class="mb-3" data-question="${key}">
                <legend class="form-label">${title}</legend>
                ${options.map(([value, label], index) => radio(name, `${id}-${index}`, value, label)).join('')}
            </fieldset>`;
    }

    return `
        <div class="mb-3" data-question="${key}">
            <label class="form-label" for="${id}">${title}</label>
            <input type="text" class="form-control" id="${id}" name="${name}" maxlength="2000" required>
        </div>`;
}

function questionsCard(title, questions, colour, types) {
    return `
        <div class="card mb-3">
            <div class="card-status-top bg-${colour}"></div>
            <div class="card-header"><h3 class="card-title">${escapeHtml(title)}</h3></div>
            <div class="card-body">
                ${questions.map((question, index) => questionField(question, index + 1, types)).join('')}
            </div>
        </div>`;
}

/**
 * The questions of a survey (GET api/surveys/{survey}/questions) as the fields of the form.
 *
 * @param {{groups: {title: string, questions: object[]}[], standalone_questions: object[]}} data
 * @param {{choice: string, boolean: string}} types
 */
export function surveyQuestionsHtml(data, types) {
    const cards = (data.groups ?? [])
        .filter((group) => (group.questions ?? []).length > 0)
        .map((group) => questionsCard(group.title, group.questions, 'primary', types));

    if ((data.standalone_questions ?? []).length > 0) {
        cards.push(questionsCard('Общие вопросы', data.standalone_questions, 'secondary', types));
    }

    return cards.length > 0 ? cards.join('') : '<p class="text-secondary">В документе нет вопросов.</p>';
}

/**
 * Load the questions of a survey into `container`. A later call wins over an earlier one still loading.
 *
 * @param {HTMLElement} container
 * @param {string} url
 * @param {{choice: string, boolean: string}} types
 * @param {string} [before] static HTML put above the questions (a "clear" button)
 */
export async function loadSurveyQuestions(container, url, types, before = '') {
    const request = String(Number(container.dataset.request ?? 0) + 1);
    container.dataset.request = request;
    container.innerHTML = '<p class="text-secondary">Загрузка вопросов…</p>';

    try {
        const data = await fetchJson(url);

        if (container.dataset.request === request) {
            container.innerHTML = before + surveyQuestionsHtml(data ?? {}, types);
        }
    } catch {
        if (container.dataset.request === request) {
            container.innerHTML = '<p class="text-danger">Не удалось загрузить вопросы документа.</p>';
        }
    }
}

/**
 * The answers of a filled-in survey (GET api/surveys-response/show/{response}). With `selectable`
 * every answer gets a checkbox and the list ends with the button that copies the chosen ones.
 */
export function surveyResponseHtml(response, { selectable = false } = {}) {
    const answers = response?.answers ?? [];
    const title = `<h3 class="mb-3">${escapeHtml(response?.survey?.title)}</h3>`;

    if (answers.length === 0) {
        return `${title}<p class="text-secondary">Ответов нет.</p>`;
    }

    const items = answers.map((answer, index) => {
        const text = `
            <strong>${index + 1}. ${escapeHtml(answer.question?.question)}</strong><br>
            Ответ: <span class="text-success">${escapeHtml(answerLabel(answer.answer))}</span>`;

        if (!selectable) {
            return `<p class="mb-3">${text}</p>`;
        }

        return `
            <label class="form-check mb-3">
                <input type="checkbox" class="form-check-input" data-copy-answer
                       data-question-id="${escapeHtml(answer.question?.id)}" data-answer="${escapeHtml(answer.answer)}">
                <span class="form-check-label">${text}</span>
            </label>`;
    });

    const apply = selectable
        ? '<button type="button" class="btn btn-primary btn-sm" data-apply-answers>Использовать выбранные</button>'
        : '';

    return title + items.join('') + apply;
}

/** Load a filled-in survey into `container` (the body of the "Ответы документа" modal). */
export async function loadSurveyResponse(container, url, options = {}) {
    container.innerHTML = '<p class="text-secondary">Загрузка…</p>';

    try {
        container.innerHTML = surveyResponseHtml(await fetchJson(url), options);
    } catch {
        container.innerHTML = '<p class="text-danger">Не удалось загрузить ответы документа.</p>';
    }
}

/**
 * Copy the answers ticked in `source` into the fields of `form` that answer the same questions.
 * Answers the form cannot take (no such question, or not one of its options) are reported.
 */
export function applySelectedAnswers(source, form) {
    const problems = [];

    for (const checkbox of source.querySelectorAll('[data-copy-answer]:checked')) {
        const { questionId, answer } = checkbox.dataset;
        const fields = [...form.querySelectorAll(`[name="${CSS.escape(`answers[${questionId}]`)}"]`)];

        if (fields.length === 0) {
            problems.push(`Вопрос №${questionId} не найден в форме.`);
            continue;
        }

        if (fields[0].type === 'radio') {
            const option = fields.find((field) => field.value === answer);

            if (!option) {
                problems.push(`Ответ «${answer}» не найден среди вариантов.`);
                continue;
            }

            option.checked = true;
        } else {
            fields[0].value = answer;
        }

        const label = form.querySelector(`[data-question="${CSS.escape(questionId)}"] > .form-label`);

        if (label && !label.querySelector('.answer-copied')) {
            const note = document.createElement('small');
            note.className = 'answer-copied text-secondary ms-1';
            note.textContent = '(ответ скопирован)';
            label.append(note);
        }
    }

    problems.forEach((problem) => showFlash(problem, 'warning'));
}
