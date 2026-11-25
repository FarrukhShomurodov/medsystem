/*
| The dashboard script, loaded on every page by admin/layouts/app (after the classic jQuery file):
| Bootstrap (bundled in Tabler), the CSRF setup of AJAX calls, the single initialiser of each
| widget (select2, date pickers, wizards, row lists), the delete confirmations, the flash messages
| and the password show/hide buttons (also on
| the sign-in page, which loads the same two files). Page scripts reach the shared helpers
| through window.MedSystem (inline glue) or by importing resources/js/lib/* (page modules).
*/
import './lib/jquery';
// Bootstrap's components and their data API (data-bs-toggle="modal", "dropdown", "collapse"...).
import '@tabler/core';
import { confirmAction, setupConfirmations } from './lib/confirm';
import { pageData } from './lib/data';
import { initDatepickers } from './lib/datepicker';
import { escapeHtml } from './lib/escape';
import { setupFlash, showFlash } from './lib/flash';
import { csrfToken, fetchJson, HttpError, setupAjax } from './lib/http';
import { setupPasswordToggles } from './lib/password';
import { initRowLists } from './lib/rows';
import { initSelects } from './lib/select2';
import { initSteppers, stepperOf } from './lib/stepper';
import * as time from './lib/time';

/**
 * Initialise the widgets under `root`: select2 (.select2), date pickers (.flatpickr-date,
 * .flatpickr-date-time, .flatpickr-clock), wizards (.bs-stepper.wizard-vertical) and lists of
 * form rows ([data-row-list], lib/rows.js). Runs once for the page; call it again on a fragment a
 * page script inserts. Widgets already set up are skipped.
 *
 * @param {ParentNode} [root]
 */
function initWidgets(root = document) {
    initSelects(root);
    initDatepickers(root);
    initSteppers(root);
    initRowLists(root);
}

// What the views' inline scripts use besides jQuery's $: the only global app.js sets.
window.MedSystem = Object.freeze({
    confirm: confirmAction,
    csrfToken,
    escapeHtml,
    fetchJson,
    flash: showFlash,
    HttpError,
    initWidgets,
    pageData,
    stepperOf,
    time,
});

setupAjax();
setupConfirmations();
setupFlash();
setupPasswordToggles();
initWidgets();
