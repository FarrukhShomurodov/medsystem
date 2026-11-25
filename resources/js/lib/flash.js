import { Alert } from '@tabler/core';

const AUTOHIDE_AFTER = 6000;

const icons = {
    success: 'bx-check-circle',
    info: 'bx-info-circle',
    warning: 'bx-error',
    danger: 'bx-error-circle',
};

/** Close a [data-autohide] message after a while; hovering or focusing it holds it open. */
function autohide(alert) {
    let timer;
    const start = () => {
        timer = setTimeout(() => Alert.getOrCreateInstance(alert).close(), AUTOHIDE_AFTER);
    };
    const stop = () => clearTimeout(timer);

    alert.addEventListener('mouseenter', stop);
    alert.addEventListener('mouseleave', start);
    alert.addEventListener('focusin', stop);
    alert.addEventListener('focusout', start);
    start();
}

/** The messages <x-flash /> rendered with the page. Alerts elsewhere (inside forms) are left alone. */
export function setupFlash() {
    document.querySelectorAll('#flash-messages .alert[data-autohide]').forEach(autohide);
}

/**
 * Show a message the way <x-flash /> does, for a page script (instead of window.alert()).
 *
 *   MedSystem.flash('Назначение отменено');
 *   MedSystem.flash(xhr.responseJSON?.message ?? 'Не удалось сохранить', 'danger');
 *
 * @param {string} message plain text
 * @param {'success'|'info'|'warning'|'danger'} [variant]
 */
export function showFlash(message, variant = 'success') {
    const container = document.getElementById('flash-messages');

    if (!container) {
        window.alert(message);

        return;
    }

    const alert = document.createElement('div');
    alert.className = `alert alert-${variant} alert-dismissible fade show`;
    alert.setAttribute('role', variant === 'danger' || variant === 'warning' ? 'alert' : 'status');

    const icon = document.createElement('i');
    icon.className = `bx ${icons[variant] ?? icons.info} alert-icon`;
    icon.setAttribute('aria-hidden', 'true');

    const text = document.createElement('div');
    text.textContent = message;

    const close = document.createElement('button');
    close.type = 'button';
    close.className = 'btn-close';
    close.dataset.bsDismiss = 'alert';
    close.setAttribute('aria-label', 'Закрыть');

    alert.append(icon, text, close);
    container.append(alert);

    if (variant === 'success' || variant === 'info') {
        autohide(alert);
    }
}
