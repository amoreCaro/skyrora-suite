/**
 * Кнопка Next + stepper у Gutenberg header для CPT mailing.
 */
export function mailingNextButton() {
	if (typeof skMailingNext === 'undefined') {
		return;
	}

	const goToSend = async (btn) => {
		const label = btn?.textContent;
		if (btn) {
			btn.disabled = true;
			btn.textContent = skMailingNext.i18n.saving || 'Saving…';
		}

		try {
			if (window.wp?.data?.dispatch) {
				await window.wp.data.dispatch('core/editor').savePost();
				const isSaving = () => window.wp.data.select('core/editor').isSavingPost();
				const isDirty = () => window.wp.data.select('core/editor').isEditedPostDirty();
				await new Promise((resolve) => {
					const start = Date.now();
					const tick = () => {
						if ((!isSaving() && !isDirty()) || Date.now() - start > 15000) {
							resolve();
							return;
						}
						requestAnimationFrame(tick);
					};
					tick();
				});
			}
		} catch (e) {
			// continue
		}

		const postId =
			window.wp?.data?.select?.('core/editor')?.getCurrentPostId?.() ||
			skMailingNext.postId;

		if (!postId) {
			if (btn) {
				btn.disabled = false;
				btn.textContent = label;
			}
			window.alert(skMailingNext.i18n.needSave || 'Please save the template first.');
			return;
		}

		const url = new URL(skMailingNext.sendUrl, window.location.origin);
		url.searchParams.set('post_id', String(postId));
		window.location.href = url.toString();
	};

	const injectNextButton = () => {
		const settings = document.querySelector('.edit-post-header__settings, .editor-header__settings');
		if (!settings || settings.querySelector('.sk-mailing-next-header-btn')) {
			return Boolean(settings?.querySelector('.sk-mailing-next-header-btn'));
		}

		const btn = document.createElement('button');
		btn.type = 'button';
		btn.className = 'components-button is-primary sk-mailing-next-header-btn';
		btn.textContent = skMailingNext.i18n.next || 'Next';
		btn.addEventListener('click', () => goToSend(btn));

		const moreMenu =
			settings.querySelector('.edit-post-more-menu, .editor-header__dropdown') ||
			[...settings.querySelectorAll('[aria-haspopup="menu"]')].at(-1);
		let insertBefore = moreMenu;

		while (insertBefore?.parentElement && insertBefore.parentElement !== settings) {
			insertBefore = insertBefore.parentElement;
		}

		if (insertBefore?.parentElement === settings) {
			settings.insertBefore(btn, insertBefore);
		} else {
			settings.appendChild(btn);
		}
		return true;
	};

	const injectSteps = () => {
		if (document.querySelector('.sk-mailing-editor-steps')) {
			return true;
		}

		const source = document.getElementById('sk-mailing-steps-source');
		const header = document.querySelector(
			'.edit-post-header, .editor-header, .interface-interface-skeleton__header'
		);
		if (!source || !header) {
			return false;
		}

		const bar = document.createElement('div');
		bar.className = 'sk-mailing-editor-steps';
		bar.innerHTML = source.innerHTML;
		header.insertAdjacentElement('afterend', bar);
		return true;
	};

	const tryInject = () => {
		const nextOk = injectNextButton();
		const stepsOk = injectSteps();
		return nextOk && stepsOk;
	};

	if (!tryInject()) {
		const observer = new MutationObserver(() => {
			if (tryInject()) {
				observer.disconnect();
			}
		});
		observer.observe(document.body, { childList: true, subtree: true });
		setTimeout(() => observer.disconnect(), 20000);
	}

	document.addEventListener('click', async (event) => {
		const link = event.target.closest?.('#sk_mailing_next_btn, .sk-mailing-next-btn');
		if (!link) {
			return;
		}
		event.preventDefault();
		await goToSend(null);
	});
}
