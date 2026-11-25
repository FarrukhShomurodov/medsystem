import { Modal } from '@tabler/core';
import $ from './jquery';

/** The session's CSRF token, from <meta name="csrf-token"> in the layout. */
export function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

let reloading = false;

/**
 * The notice before the reload: a modal the user cannot dismiss, only confirm. Unlike
 * window.alert() it does not freeze the page's scripts, the browser cannot suppress it, and it
 * looks like the rest of the dashboard. Its texts are static.
 */
function showSessionExpired() {
    const element = document.createElement('div');
    element.className = 'modal modal-blur fade';
    element.id = 'session-expired-dialog';
    element.tabIndex = -1;
    element.setAttribute('role', 'alertdialog');
    element.setAttribute('aria-labelledby', 'session-expired-title');
    element.setAttribute('aria-describedby', 'session-expired-message');
    element.innerHTML = `
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-status bg-warning"></div>
                <div class="modal-body text-center py-4">
                    <i class="bx bx-time-five confirm-dialog-icon text-warning mb-2" aria-hidden="true"></i>
                    <h2 class="h3 mb-2" id="session-expired-title">Сессия истекла</h2>
                    <div class="text-secondary" id="session-expired-message">
                        Страница будет перезагружена. Несохранённые изменения на ней будут потеряны.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary w-100" data-reload>Перезагрузить страницу</button>
                </div>
            </div>
        </div>`;
    document.body.append(element);

    const reload = element.querySelector('[data-reload]');
    reload.addEventListener('click', () => window.location.reload());
    element.addEventListener('shown.bs.modal', () => reload.focus());

    new Modal(element, { backdrop: 'static', keyboard: false }).show();
}

/**
 * 401: the session is gone; 419: the page holds a stale CSRF token. Reloading sends the user to
 * the login page or gives the page a fresh token, once the user confirmed the notice.
 */
function sessionExpired() {
    if (reloading) {
        return;
    }

    reloading = true;
    showSessionExpired();
}

/**
 * Every jQuery AJAX request sends the CSRF token: the /api/* endpoints run on the admin session
 * and reject writes without it.
 */
export function setupAjax() {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': csrfToken() } });

    $(document).ajaxError((event, xhr) => {
        if (xhr.status === 401 || xhr.status === 419) {
            sessionExpired();
        }
    });
}

/** A JSON endpoint answered with an error status; `data` is its JSON body (Laravel's message, errors). */
export class HttpError extends Error {
    constructor(status, data) {
        super(data?.message ?? `HTTP ${status}`);
        this.name = 'HttpError';
        this.status = status;
        this.data = data;
    }
}

/**
 * fetch() for the dashboard's JSON endpoints: same-origin cookies, the CSRF token on writes,
 * a JSON body for plain objects (FormData as it is), and the 401/419 reload.
 *
 *   const medication = await fetchJson(`/api/medication/show/${id}`);
 *   await fetchJson(url, { method: 'PATCH', body: { status: 'done' } });
 *
 * @param {string} url
 * @param {{method?: string, body?: object|FormData, headers?: Record<string, string>, signal?: AbortSignal}} [options]
 * @returns {Promise<any>} the decoded body (null for an empty one)
 * @throws {HttpError} for a 4xx/5xx answer
 */
export async function fetchJson(url, { method = 'GET', body, headers = {}, signal } = {}) {
    const init = {
        method,
        credentials: 'same-origin',
        signal,
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', ...headers },
    };

    if (!['GET', 'HEAD'].includes(method.toUpperCase())) {
        init.headers['X-CSRF-TOKEN'] = csrfToken();
    }

    if (body instanceof FormData) {
        init.body = body;
    } else if (body !== undefined) {
        init.body = JSON.stringify(body);
        init.headers['Content-Type'] = 'application/json';
    }

    const response = await fetch(url, init);

    if (response.status === 401 || response.status === 419) {
        sessionExpired();
    }

    const data = await response
        .text()
        .then((text) => (text === '' ? null : JSON.parse(text)))
        .catch(() => null);

    if (!response.ok) {
        throw new HttpError(response.status, data);
    }

    return data;
}
