/*
| The medical document editor (surveys/create|edit): questions without a group, groups of
| questions in tabs, and the answer options of "choice" questions. Every row is cloned from a
| <template> of surveys/_form.blade.php; this module only picks the posting names:
|
|   questions[q][question|type|options][o][text|score]
|   groups[g][title], groups[g][questions][q][...]
|
| q and g only have to be unique within the form (the server keeps the order of the rows), so
| new rows take the next free number from data-next-question / data-next-group. Existing rows
| keep their names and hidden ids, which is how the server updates them in place.
*/
import { Tab } from '@tabler/core';
import { confirmAction } from '../lib/confirm';

const editor = document.querySelector('[data-survey-editor]');

/** An id-safe form of a name prefix: "groups[1][questions][4]" → "groups-1-questions-4". */
const idOf = (prefix) => prefix.replace(/[^A-Za-z0-9_]+/g, '-').replace(/^-|-$/g, '');

function setUp(root) {
    const choiceType = root.dataset.choiceType;
    const tabs = root.querySelector('[data-group-tabs]');
    const panes = root.querySelector('[data-group-panes]');
    const newGroupTitle = root.querySelector('[data-group-new-title]');
    let nextQuestion = Number(root.dataset.nextQuestion ?? 0);
    let nextGroup = Number(root.dataset.nextGroup ?? 0);

    /**
     * The template's markup with its placeholders replaced. The replacements are prefixes built
     * from numbers by this module, never user text, so the markup stays as Blade escaped it.
     */
    function fromTemplate(name, replacements) {
        let html = root.querySelector(`template[data-survey-template="${name}"]`).innerHTML;

        for (const [placeholder, value] of replacements) {
            html = html.replaceAll(placeholder, value);
        }

        const holder = document.createElement('template');
        holder.innerHTML = html.trim();

        return holder.content.firstElementChild;
    }

    function syncOptions(question) {
        const isChoice = question.querySelector('[data-question-type]').value === choiceType;
        const options = question.querySelector('[data-options]');

        options.classList.toggle('d-none', !isChoice);

        // Only a choice question has options: the server would refuse required option texts left in a hidden list.
        if (!isChoice) {
            options.querySelector('[data-option-list]').replaceChildren();
        }
    }

    function addQuestion(list) {
        const prefix = `${list.dataset.prefix}[${nextQuestion++}]`;
        const question = fromTemplate('question', [
            ['__QUESTION__-', `${idOf(prefix)}-`],
            ['__QUESTION__', prefix],
        ]);

        list.append(question);
        syncOptions(question);
        question.querySelector('input[type="text"]').focus();
    }

    function addOption(question) {
        const list = question.querySelector('[data-option-list]');
        const used = [...list.querySelectorAll('input[name$="[text]"]')].map((input) => Number(/\[options]\[(\d+)]/.exec(input.name)?.[1] ?? -1));
        const prefix = `${question.dataset.prefix}[options][${Math.max(-1, ...used) + 1}]`;
        const option = fromTemplate('option', [['__OPTION__', prefix]]);

        list.append(option);
        option.querySelector('input').focus();
    }

    function addGroup() {
        const title = newGroupTitle.value.trim();

        if (title === '') {
            newGroupTitle.classList.add('is-invalid');
            newGroupTitle.focus();

            return;
        }

        const index = String(nextGroup++);
        const tab = fromTemplate('group-tab', [['__GROUP__', index]]);
        const pane = fromTemplate('group-pane', [['__GROUP__', index]]);

        tab.querySelector('[data-group-label]').textContent = title;
        pane.querySelector('[data-group-title]').value = title;
        tabs.append(tab);
        panes.append(pane);

        newGroupTitle.value = '';
        Tab.getOrCreateInstance(tab.querySelector('[data-bs-toggle="tab"]')).show();
        addQuestion(pane.querySelector('[data-question-list]'));
    }

    async function removeGroup(pane) {
        const label = tabs.querySelector(`[data-bs-target="#${pane.id}"]`);
        const questions = pane.querySelectorAll('[data-question]').length;

        if (questions > 0 && !(await confirmAction(`Удалить группу «${label.textContent.trim()}» вместе с её вопросами (${questions})?`))) {
            return;
        }

        label.closest('.nav-item').remove();
        pane.remove();

        const general = tabs.querySelector('[data-bs-toggle="tab"]');
        Tab.getOrCreateInstance(general).show();
        general.focus();
    }

    root.addEventListener('click', (event) => {
        const button = event.target.closest('button');

        if (!button || !root.contains(button)) {
            return;
        }

        if (button.matches('[data-group-add]')) {
            addGroup();
        } else if (button.matches('[data-group-remove]')) {
            removeGroup(button.closest('[data-group-pane]'));
        } else if (button.matches('[data-question-add]')) {
            addQuestion(button.closest('.tab-pane').querySelector('[data-question-list]'));
        } else if (button.matches('[data-question-remove]')) {
            const pane = button.closest('.tab-pane');
            button.closest('[data-question]').remove();
            pane.querySelector('[data-question-add]').focus();
        } else if (button.matches('[data-option-add]')) {
            addOption(button.closest('[data-question]'));
        } else if (button.matches('[data-option-remove]')) {
            const question = button.closest('[data-question]');
            button.closest('[data-option]').remove();
            question.querySelector('[data-option-add]').focus();
        }
    });

    root.addEventListener('change', (event) => {
        if (event.target.matches('[data-question-type]')) {
            syncOptions(event.target.closest('[data-question]'));
        }
    });

    // The tab shows the group's title as it is typed.
    root.addEventListener('input', (event) => {
        if (event.target.matches('[data-group-title]')) {
            const pane = event.target.closest('[data-group-pane]');
            tabs.querySelector(`[data-bs-target="#${pane.id}"]`).textContent = event.target.value.trim() || 'Без названия';
        } else if (event.target === newGroupTitle) {
            newGroupTitle.classList.remove('is-invalid');
        }
    });

    // A required field the browser stops the submission on may sit in a group that is not shown:
    // open that group, so the browser can point at the field.
    let reporting = false;

    (root.closest('form') ?? root).addEventListener(
        'invalid',
        (event) => {
            // The browser fires one event per invalid field, in order: only the first one counts.
            if (reporting) {
                return;
            }

            reporting = true;
            setTimeout(() => {
                reporting = false;
            });

            const pane = event.target.closest('[data-group-panes] > .tab-pane');

            if (pane && !pane.classList.contains('active')) {
                Tab.getOrCreateInstance(tabs.querySelector(`[data-bs-target="#${pane.id}"]`)).show();
            }
        },
        true,
    );

    // Enter in the new group's title creates the group instead of submitting the document.
    newGroupTitle.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            addGroup();
        }
    });
}

if (editor) {
    setUp(editor);
}
