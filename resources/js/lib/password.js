/**
 * <button type="button" data-password-toggle="#password" aria-pressed="false" aria-label="Показать пароль">
 * shows or hides the password typed into the field it names.
 */
export function setupPasswordToggles() {
    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-password-toggle]');
        const field = button && document.querySelector(button.dataset.passwordToggle);

        if (!field) {
            return;
        }

        const show = field.type === 'password';
        field.type = show ? 'text' : 'password';

        button.setAttribute('aria-pressed', String(show));
        button.setAttribute('aria-label', show ? 'Скрыть пароль' : 'Показать пароль');
        button.querySelector('.bx')?.classList.replace(show ? 'bx-show' : 'bx-hide', show ? 'bx-hide' : 'bx-show');
    });
}
