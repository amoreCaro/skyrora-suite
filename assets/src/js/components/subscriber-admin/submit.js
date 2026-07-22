/**
 * Валідація email (post_title) + перевірка унікальності при submit форми підписника.
 */
const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

export function submit() {
    const emailInput = document.querySelector('#title');
    const errorBox = document.querySelector('#sk_email_error');

    if (!emailInput || !errorBox) return;

    const form = emailInput.closest('form');
    if (!form) return;

    const submitBtn = form.querySelector('button[type="submit"], input[type="submit"], #publish');
    const data = window.skSubscriberData || {};

    const showError = (message) => {
        errorBox.textContent = message;
        errorBox.classList.add('sk-subscriber__error--visible');
        emailInput.classList.add('sk-subscriber__title--invalid');
    };

    const clearError = () => {
        errorBox.classList.remove('sk-subscriber__error--visible');
        emailInput.classList.remove('sk-subscriber__title--invalid');
    };

    emailInput.addEventListener('input', () => {
        if (emailInput.classList.contains('sk-subscriber__title--invalid')) {
            clearError();
        }
    });

    form.addEventListener('submit', function (e) {
        if (form.dataset.skValidated === '1') return;

        e.preventDefault();
        e.stopImmediatePropagation();

        const value = emailInput.value.trim();
        let valid = true;

        if (!value) {
            showError('Please enter your email address');
            valid = false;
        } else if (!EMAIL_RE.test(value)) {
            showError('Please enter a valid email address');
            valid = false;
        } else {
            clearError();
        }

        if (!valid) {
            emailInput.focus();
            return;
        }

        if (submitBtn) submitBtn.disabled = true;

        const postIdInput = document.querySelector('#post_ID');
        const postId = postIdInput
            ? postIdInput.value
            : String(data.postId || 0);

        fetch(data.ajaxUrl || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'sk_check_subscriber_email',
                nonce: data.nonce || '',
                email: value,
                post_id: postId,
            }),
            credentials: 'same-origin',
        })
            .then((res) => res.json())
            .then((result) => {
                if (result.success) {
                    clearError();
                    form.dataset.skValidated = '1';

                    if (e.submitter) {
                        e.submitter.click();
                    } else {
                        HTMLFormElement.prototype.submit.call(form);
                    }
                } else {
                    showError(
                        (result.data && result.data.message) ||
                            'This email already exists'
                    );
                    emailInput.focus();
                    if (submitBtn) submitBtn.disabled = false;
                }
            })
            .catch((err) => {
                console.error(err);
                showError('Could not verify email. Please try again.');
                emailInput.focus();
                if (submitBtn) submitBtn.disabled = false;
            });
    });
}
