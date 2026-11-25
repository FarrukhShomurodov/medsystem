/*
| The patient card (admin/patients/show): the detail of a history row, the order and prescription
| modals, the diagnosis picker with its follow-up medical documents, and the prescription actions.
| URLs come from data-* attributes rendered with route(); the modals that are missing for the
| account (no permission) simply have no behaviour here.
*/
import { Modal } from '@tabler/core';
import $ from 'jquery';
import { confirmAction } from '../lib/confirm';
import { initDatepickers } from '../lib/datepicker';
import { showFlash } from '../lib/flash';
import { fetchJson } from '../lib/http';
import { initSelects } from '../lib/select2';
import { setupPrescriptionRows, reindexCompatibleMedications } from './patients/prescriptions';
import { loadSurveyQuestions } from './patients/surveys';
import { fillUrl } from './patients/urls';

const card = document.getElementById('patient-card');
const MESSAGE_KEY = 'patient-card:message';

/** Reload the card and show `message` once it is back (the change is visible only after a reload). */
function reloadWithMessage(message) {
    try {
        sessionStorage.setItem(MESSAGE_KEY, message);
    } catch {
        // No storage (private mode): the reloaded card shows the change without the message.
    }

    window.location.reload();
}

function showStoredMessage() {
    try {
        const message = sessionStorage.getItem(MESSAGE_KEY);
        sessionStorage.removeItem(MESSAGE_KEY);

        if (message) {
            showFlash(message);
        }
    } catch {
        // Nothing stored.
    }
}

/** The reason of a refused AJAX request: Laravel's message, or `fallback`. */
function failure(error, fallback) {
    const status = error?.status ?? error?.xhr?.status;
    const message = error?.data?.message ?? error?.responseJSON?.message;

    return status === 403 ? 'Недостаточно прав для этого действия.' : message || fallback;
}

/** Give the fields of a row cloned from a <template> ids of their own, and point the labels at them. */
function numberFields(row, number) {
    row.querySelectorAll('[data-id]').forEach((field) => {
        field.id = `${field.dataset.id}-${number}`;
    });
    row.querySelectorAll('label[data-for]').forEach((label) => {
        label.htmlFor = `${label.dataset.for}-${number}`;
    });
}

/** Wait until a Bootstrap modal is fully hidden (another modal may open only then). */
function hidden(element) {
    if (!element.classList.contains('show')) {
        return Promise.resolve();
    }

    return new Promise((resolve) => {
        element.addEventListener('hidden.bs.modal', () => resolve(), { once: true });
        Modal.getOrCreateInstance(element).hide();
    });
}

// The detail of a history row ----------------------------------------------------------------

const eventModal = document.getElementById('eventDetailModal');

eventModal?.addEventListener('show.bs.modal', (event) => {
    const trigger = event.relatedTarget;
    const body = eventModal.querySelector('.modal-body');

    if (!trigger?.dataset.type) {
        return;
    }

    body.innerHTML = '<p class="text-secondary mb-0">Загрузка…</p>';

    $.ajax({
        url: card.dataset.historyUrl,
        method: 'POST',
        data: { type: trigger.dataset.type, id: trigger.dataset.id },
        dataType: 'html',
    })
        .done((html) => {
            // Server-rendered Blade partial; it carries no script.
            body.innerHTML = html;
            initSelects(body);
        })
        .fail(() => {
            body.innerHTML = '<p class="text-danger mb-0">Не удалось загрузить данные.</p>';
        });
});

// The status of a service, in the detail of a service row (admin/patients/history/service).
// A status the order can never leave (cancelled, completed) carries its question on its option:
// the change is sent only once the user confirms it. One dialog at a time, so the detail closes
// while the question is open and comes back, with the old status, when the user declines.
$(eventModal ?? []).on('change', '.service_status', async function () {
    const $select = $(this);
    const saved = this.dataset.savedStatus;

    if ($select.val() === saved) {
        return;
    }

    const { question, accept } = this.selectedOptions[0]?.dataset ?? {};

    if (question) {
        await hidden(eventModal);

        if (!(await confirmAction(question, { accept }))) {
            $select.val(saved).trigger('change');
            Modal.getOrCreateInstance(eventModal).show();

            return;
        }
    }

    $select.prop('disabled', true);

    $.ajax({ url: this.dataset.statusUrl, method: 'PATCH', data: { status: $select.val() } })
        .done(() => reloadWithMessage('Статус услуги изменён.'))
        .fail((xhr) => {
            $select.prop('disabled', false).val(saved).trigger('change');
            showFlash(failure(xhr, 'Не удалось изменить статус.'), 'danger');
        });
});

// Prescriptions: extend and cancel ---------------------------------------------------------------

$(document).on('click', '.js-extend-medication, .js-cancel-medication', async function () {
    const cancel = this.classList.contains('js-cancel-medication');
    const yes = await confirmAction(
        cancel ? 'Отменить это назначение? Выданный препарат вернётся на склад.' : 'Продлить это назначение?',
        { accept: cancel ? 'Отменить назначение' : 'Продлить назначение' },
    );

    if (!yes) {
        return;
    }

    this.disabled = true;

    try {
        await fetchJson(this.dataset.url, { method: 'POST' });
        reloadWithMessage(cancel ? 'Назначение отменено.' : 'Назначение продлено.');
    } catch (error) {
        this.disabled = false;
        showFlash(failure(error, cancel ? 'Не удалось отменить назначение.' : 'Не удалось продлить назначение.'), 'danger');
    }
});

// Order a service: the laboratory button lists the services of its type only --------------------

const serviceModal = document.getElementById('createServiceModal');
const serviceSelect = document.getElementById('service_create');
const allServices = serviceSelect ? [...serviceSelect.options].map((option) => option.cloneNode(true)) : [];

serviceModal?.addEventListener('show.bs.modal', async (event) => {
    const url = event.relatedTarget?.dataset.servicesUrl;

    serviceSelect.replaceChildren(...allServices.map((option) => option.cloneNode(true)));
    $(serviceSelect).trigger('change');

    if (!url) {
        return;
    }

    try {
        const services = await fetchJson(url);
        const placeholder = new Option('Выберите услугу', '', true, true);
        placeholder.disabled = true;

        serviceSelect.replaceChildren(placeholder, ...(services ?? []).map((service) => new Option(service.name, service.id)));
        $(serviceSelect).trigger('change');
    } catch {
        showFlash('Не удалось загрузить услуги лаборатории.', 'danger');
    }
});

// A medical document: the questions of the chosen survey ---------------------------------------

const documentModal = document.getElementById('createDocumentModal');
const documentForm = documentModal?.querySelector('form');
const surveySelect = document.getElementById('survey-select');
const questions = document.getElementById('questions-container');
const questionTypes = questions ? { choice: questions.dataset.choiceType, boolean: questions.dataset.booleanType } : null;
const clearButton = '<button type="button" class="btn btn-sm mb-3" id="clear_survey"><i class="bx bx-eraser" aria-hidden="true"></i>Очистить</button>';

$(surveySelect ?? []).on('change', function () {
    if (!this.value) {
        questions.replaceChildren();

        return;
    }

    loadSurveyQuestions(questions, fillUrl(this.dataset.questionsUrl, { survey: this.value }), questionTypes, clearButton);
});

questions?.addEventListener('click', (event) => {
    if (!event.target.closest('#clear_survey')) {
        return;
    }

    if (surveyQueue) {
        // A document of a new diagnosis is fixed: clear its answers, not the document.
        questions.querySelectorAll('input[type="text"]').forEach((input) => {
            input.value = '';
        });
        questions.querySelectorAll('input[type="radio"]').forEach((input) => {
            input.checked = false;
        });
    } else {
        $(surveySelect).val('').trigger('change');
    }
});

// The service the document is attached to (the button in a service row) or none.
documentModal?.addEventListener('show.bs.modal', (event) => {
    if (event.relatedTarget) {
        document.getElementById('modal_service_id').value = event.relatedTarget.dataset.serviceId ?? '';
    }
});

// Diagnoses: search, add (then fill in the documents of the diagnosis), favourites -------------

const diagnosisModal = document.getElementById('diagnosesModal');
const diagnosisSearch = document.getElementById('diagnosisSearchInput');
const foundDiagnoses = document.getElementById('regularDiagnosisList');
const diagnosisStatus = document.getElementById('diagnosisSearchStatus');
const diagnosisTemplate = document.getElementById('diagnosis-option-template');
const SEARCH_DELAY = 250;
let searchTimer = null;
let pendingSearch = null;

function showSearchStatus(message) {
    diagnosisStatus.textContent = message;
    diagnosisStatus.hidden = message === '';
}

/** A picker row for a diagnosis the search found, cloned from the <template>: text only, never HTML. */
function diagnosisOption(diagnosis) {
    const row = diagnosisTemplate.content.firstElementChild.cloneNode(true);

    row.querySelector('[data-diagnosis-name]').textContent = diagnosis.name;
    row.querySelector('[data-diagnosis-code]').textContent = diagnosis.code;
    row.querySelectorAll('[data-diagnosis-id]').forEach((button) => {
        button.dataset.diagnosisId = String(diagnosis.id);
    });

    return row;
}

/** The catalogue is too large for the page: the server returns the first matches, favourites left out. */
async function searchDiagnoses(term) {
    pendingSearch?.abort();

    if (term === '') {
        foundDiagnoses.replaceChildren();
        showSearchStatus('Введите код или название диагноза.');

        return;
    }

    const request = new AbortController();
    pendingSearch = request;
    showSearchStatus('Поиск…');

    try {
        const url = new URL(diagnosisModal.dataset.searchUrl, window.location.href);
        url.searchParams.set('term', term);
        const diagnoses = await fetchJson(url.toString(), { signal: request.signal });

        foundDiagnoses.replaceChildren(...(Array.isArray(diagnoses) ? diagnoses : []).map(diagnosisOption));
        showSearchStatus(foundDiagnoses.children.length === 0 ? 'Ничего не найдено.' : '');
    } catch (error) {
        if (error.name !== 'AbortError') {
            showSearchStatus(failure(error, 'Не удалось найти диагнозы.'));
        }
    }
}

diagnosisSearch?.addEventListener('input', () => {
    const term = diagnosisSearch.value.trim();
    const lowered = term.toLowerCase();

    // The favourites are on the page already: filter them at once.
    document.querySelectorAll('#favoriteDiagnosisList .diagnosis-option').forEach((option) => {
        option.classList.toggle('d-none', lowered !== '' && !option.textContent.toLowerCase().includes(lowered));
    });

    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => searchDiagnoses(term), SEARCH_DELAY);
});

/** The documents linked to the new diagnosis, one modal after the other, each saved without leaving the card. */
let surveyQueue = null;

function lockSurvey(survey) {
    $(surveySelect).val(String(survey.id)).trigger('change').prop('disabled', true);

    // A disabled select is not submitted: post the survey of the queue instead.
    let locked = documentForm.querySelector('input[name="survey_id"]');

    if (!locked) {
        locked = Object.assign(document.createElement('input'), { type: 'hidden', name: 'survey_id' });
        documentForm.append(locked);
    }

    locked.value = survey.id;
}

function unlockSurvey() {
    documentForm.querySelector('input[name="survey_id"]')?.remove();
    $(surveySelect).prop('disabled', false).val('').trigger('change');
}

function nextSurvey() {
    const survey = surveyQueue.surveys[surveyQueue.index];

    if (!survey) {
        const { saved, surveys } = surveyQueue;
        surveyQueue = null;
        unlockSurvey();
        reloadWithMessage(`Диагноз добавлен. Заполнено мед. документов: ${saved} из ${surveys.length}.`);

        return;
    }

    document.getElementById('modal_service_id').value = '';
    lockSurvey(survey);
    Modal.getOrCreateInstance(documentModal).show();
}

documentModal?.addEventListener('hidden.bs.modal', () => {
    if (surveyQueue) {
        surveyQueue.index += 1;
        nextSurvey();
    }
});

documentForm?.addEventListener('submit', async (event) => {
    if (!surveyQueue) {
        return;
    }

    event.preventDefault();

    try {
        await fetchJson(documentForm.action, { method: 'POST', body: new FormData(documentForm) });
        surveyQueue.saved += 1;
        Modal.getOrCreateInstance(documentModal).hide();
    } catch (error) {
        showFlash(failure(error, 'Не удалось сохранить мед. документ.'), 'danger');
    }
});

diagnosisModal?.addEventListener('click', async (event) => {
    const item = event.target.closest('.diagnosis-item');
    const favorite = event.target.closest('.diagnosis-favorite');

    if (item) {
        item.disabled = true;

        try {
            const response = await fetchJson(diagnosisModal.dataset.storeUrl, {
                method: 'POST',
                body: { patient_id: card.dataset.patientId, diagnosis_id: item.dataset.diagnosisId },
            });
            const surveys = response?.surveys ?? [];

            // The document form is only rendered for staff who may fill in documents.
            if (surveys.length === 0 || !documentModal) {
                reloadWithMessage('Диагноз добавлен.');

                return;
            }

            await hidden(diagnosisModal);
            surveyQueue = { surveys, index: 0, saved: 0 };
            nextSurvey();
        } catch (error) {
            item.disabled = false;
            showFlash(failure(error, 'Не удалось добавить диагноз.'), 'danger');
        }
    } else if (favorite) {
        favorite.disabled = true;

        try {
            await fetchJson(diagnosisModal.dataset.favoriteUrl, { method: 'POST', body: { diagnosis_id: favorite.dataset.diagnosisId } });
            toggleFavorite(favorite);
        } catch (error) {
            showFlash(failure(error, 'Не удалось обновить избранные диагнозы.'), 'danger');
        } finally {
            favorite.disabled = false;
        }
    }
});

/** Move a diagnosis between the favourites and the rest, as the server just did. */
function toggleFavorite(button) {
    const isFavorite = button.getAttribute('aria-pressed') !== 'true';
    const label = isFavorite ? 'Убрать из избранного' : 'Добавить в избранное';
    const target = diagnosisModal.querySelector(`[data-list="${isFavorite ? 'favorite' : 'regular'}"]`);

    button.setAttribute('aria-pressed', String(isFavorite));
    button.setAttribute('aria-label', label);
    button.title = label;
    button.querySelector('.bx').className = `bx ${isFavorite ? 'bxs-star' : 'bx-star'}`;

    target.querySelector('.list-group').append(button.closest('.diagnosis-option'));

    diagnosisModal.querySelectorAll('.diagnosis-section[data-list="favorite"]').forEach((section) => {
        section.classList.toggle('d-none', !section.querySelector('.diagnosis-option'));
    });

    if (foundDiagnoses.children.length > 0) {
        showSearchStatus('');
    }
}

// Prescriptions: the rows of the "Назначить медикаменты" modal ---------------------------------

const medicationForm = document.getElementById('medication-form');
const medicationRows = document.getElementById('medications-container');
const medicationTemplate = document.getElementById('medication-template');
let medicationNumber = 0;

function renumberMedications() {
    medicationRows.querySelectorAll('.medication-row').forEach((row, index) => {
        row.querySelector('.medication-title').textContent = `Медикамент №${index + 1}`;
    });
    medicationForm.querySelector('.medications-empty').classList.toggle('d-none', medicationRows.children.length > 0);
    reindexCompatibleMedications(medicationForm);
}

if (medicationForm) {
    setupPrescriptionRows(medicationForm, { substanceUrl: card.dataset.substanceUrl });

    document.getElementById('btn-add-medication').addEventListener('click', () => {
        const fragment = medicationTemplate.content.cloneNode(true);
        const row = fragment.firstElementChild;

        medicationNumber += 1;
        numberFields(row, medicationNumber);
        medicationRows.append(fragment);
        initDatepickers(row);
        renumberMedications();

        // Offer the drugs compatible with the drug selected by default.
        $(row.querySelector('.medication-select')).trigger('change');
        row.querySelector('.medication-select').focus();
    });

    medicationRows.addEventListener('click', (event) => {
        const remove = event.target.closest('.btn-remove-medication');

        if (remove) {
            remove.closest('.medication-row').remove();
            renumberMedications();
        }
    });

    // The times of intake sit in a hidden input, which the browser does not check.
    medicationForm.addEventListener('submit', (event) => {
        const rows = [...medicationRows.querySelectorAll('.medication-row')];
        const withoutTimes = rows.find((row) => row.querySelector('.admission-hidden-input').value === '');

        if (rows.length === 0 || withoutTimes) {
            event.preventDefault();
            showFlash(rows.length === 0 ? 'Добавьте хотя бы один медикамент.' : 'Добавьте время приёма каждому медикаменту.', 'warning');
            withoutTimes?.querySelector('.admission-hour').focus();
        }
    });
}

// Procedures: the rows of the "Назначить процедуры" modal --------------------------------------

const procedureRows = document.getElementById('procedures-container');
const procedureTemplate = document.getElementById('procedure-template');
let procedureNumber = 0;

document.getElementById('btn-add-procedure')?.addEventListener('click', () => {
    const fragment = procedureTemplate.content.cloneNode(true);
    const row = fragment.firstElementChild;

    procedureNumber += 1;
    numberFields(row, procedureNumber);
    procedureRows.append(fragment);
    initSelects(row);
    initDatepickers(row);
});

procedureRows?.addEventListener('click', (event) => {
    event.target.closest('.btn-remove-procedure')?.closest('.procedure-row').remove();
});

// "Заполнить документ": the document form opens on its own page --------------------------------

document.getElementById('select-document-form')?.addEventListener('submit', (event) => {
    event.preventDefault();

    const documentId = document.getElementById('document_id').value;

    if (documentId) {
        window.location.assign(fillUrl(card.dataset.documentUrl, { document: documentId }));
    }
});

showStoredMessage();
