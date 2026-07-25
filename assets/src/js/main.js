import '../css/main.css';
import { subscriberAdmin } from './components/subscriber-admin/index.js';
import { emailAdmin } from './components/email-admin/index.js';
import { listAdmin } from './components/list-admin/index.js';
import { sendPage, mailingNextButton } from './components/mailing-send/index.js';

const init = () => {
    subscriberAdmin();
    emailAdmin();
    listAdmin();
    sendPage();
    mailingNextButton();

    window.requestAnimationFrame(() => {
        document.body.classList.add('sk-plugin-page--ready');
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
