import { Modal } from '@tabler/core';
import { beforeEach, describe, expect, it, vi } from 'vitest';

vi.mock('@tabler/core', () => ({
    Modal: vi.fn(function Modal(element) {
        this.element = element;
        this.show = vi.fn();
    }),
}));

/** A fresh copy of lib/http.js: it remembers that the session expired once. */
async function http() {
    vi.resetModules();

    return import('../../../resources/js/lib/http');
}

/** fetch() answering every request with `status` and the JSON `body` (nothing when undefined). */
function answer(status, body) {
    const fetch = vi.fn(async () => new Response(body === undefined ? null : JSON.stringify(body), { status }));
    vi.stubGlobal('fetch', fetch);

    return fetch;
}

beforeEach(() => {
    document.head.innerHTML = '<meta name="csrf-token" content="token-123">';
    document.body.innerHTML = '';
    vi.mocked(Modal).mockClear();
});

describe('fetchJson', () => {
    it('reads a JSON answer, with the session cookie and no CSRF token on a read', async () => {
        const { fetchJson } = await http();
        const fetch = answer(200, [{ id: 1 }]);

        await expect(fetchJson('/api/patients?term=ка')).resolves.toEqual([{ id: 1 }]);

        const [url, init] = fetch.mock.calls[0];

        expect(url).toBe('/api/patients?term=ка');
        expect(init).toMatchObject({ method: 'GET', credentials: 'same-origin' });
        expect(init.headers).toMatchObject({ Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' });
        expect(init.headers).not.toHaveProperty('X-CSRF-TOKEN');
        expect(init.body).toBeUndefined();
    });

    it('sends a write with the CSRF token and a plain object as JSON', async () => {
        const { fetchJson } = await http();
        const fetch = answer(200, { success: true });

        await fetchJson('/api/receptions', { method: 'POST', body: { patient_id: 5 } });

        const [, init] = fetch.mock.calls[0];

        expect(init.headers).toMatchObject({ 'X-CSRF-TOKEN': 'token-123', 'Content-Type': 'application/json' });
        expect(init.body).toBe('{"patient_id":5}');
    });

    it('sends FormData as it is, for the browser to set its multipart type', async () => {
        const { fetchJson } = await http();
        const fetch = answer(200, null);
        const form = new FormData();
        form.append('file', 'scan');

        await fetchJson('/api/files', { method: 'POST', body: form });

        const [, init] = fetch.mock.calls[0];

        expect(init.body).toBe(form);
        expect(init.headers).not.toHaveProperty('Content-Type');
    });

    it('answers null for an empty body', async () => {
        const { fetchJson } = await http();
        answer(204);

        await expect(fetchJson('/api/document/finish/action', { method: 'POST' })).resolves.toBeNull();
    });

    it('throws an HttpError that carries the status and Laravel\'s message', async () => {
        const { fetchJson, HttpError } = await http();
        answer(422, { message: 'Это время уже занято.', errors: { start_time: ['Это время уже занято.'] } });

        const error = await fetchJson('/api/receptions', { method: 'POST', body: {} }).catch((thrown) => thrown);

        expect(error).toBeInstanceOf(HttpError);
        expect(error).toMatchObject({ status: 422, message: 'Это время уже занято.' });
        expect(error.data.errors.start_time).toEqual(['Это время уже занято.']);
    });

    it('throws an HttpError with the status when the error page is not JSON', async () => {
        const { fetchJson } = await http();
        vi.stubGlobal('fetch', vi.fn(async () => new Response('<html>Server Error</html>', { status: 500 })));

        await expect(fetchJson('/api/patients')).rejects.toMatchObject({ name: 'HttpError', status: 500, message: 'HTTP 500', data: null });
    });
});

describe('an expired session', () => {
    it.each([401, 419])('shows one dialog that only reloads the page on %i, however many requests fail', async (status) => {
        const { fetchJson } = await http();
        answer(status, { message: 'Unauthenticated.' });

        await Promise.allSettled([fetchJson('/api/a'), fetchJson('/api/b'), fetchJson('/api/c')]);

        const dialogs = document.querySelectorAll('#session-expired-dialog');

        expect(dialogs).toHaveLength(1);
        expect(dialogs[0].getAttribute('role')).toBe('alertdialog');
        expect(Modal).toHaveBeenCalledOnce();
        expect(Modal).toHaveBeenCalledWith(dialogs[0], { backdrop: 'static', keyboard: false });
        expect(vi.mocked(Modal).mock.instances[0].show).toHaveBeenCalledOnce();
    });

    it('shows nothing for a refused request of a live session', async () => {
        const { fetchJson } = await http();
        answer(403, { message: 'This action is unauthorized.' });

        await expect(fetchJson('/api/a')).rejects.toMatchObject({ status: 403 });
        expect(document.querySelector('#session-expired-dialog')).toBeNull();
    });
});
