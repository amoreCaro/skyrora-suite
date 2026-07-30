/**
 * Кнопки Preview/Save/Next у Skyrora header (з логотипом) + stepper для CPT mailing.
 */
export function mailingNextButton() {
	if (typeof skMailingNext === 'undefined') {
		return;
	}

	const savePost = async () => {
		if (!window.wp?.data?.dispatch) {
			return;
		}

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
	};

	const goToSend = async (btn) => {
		const label = btn?.textContent;
		if (btn) {
			btn.disabled = true;
		}

		try {
			await savePost();
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

	const previewPost = async (btn) => {
		const label = btn.textContent;
		const previewWindow = window.open('', '_blank');
		btn.disabled = true;
		btn.textContent = skMailingNext.i18n.saving || 'Saving…';

		try {
			await savePost();
			const editor = window.wp?.data?.select?.('core/editor');
			const postId = editor?.getCurrentPostId?.() || skMailingNext.postId;
			const previewUrl =
				editor?.getEditedPostPreviewLink?.() ||
				skMailingNext.previewUrl ||
				(postId ? `${window.location.origin}/?p=${postId}&preview=true` : '');

			if (!previewUrl) {
				previewWindow?.close();
				window.alert(skMailingNext.i18n.needSave || 'Please save the template first.');
				return;
			}

			if (previewWindow) {
				previewWindow.location.href = previewUrl;
			} else {
				window.open(previewUrl, '_blank', 'noopener');
			}
		} catch (e) {
			previewWindow?.close();
			// Gutenberg shows the save error in its own notice.
		} finally {
			btn.disabled = false;
			btn.textContent = label;
		}
	};

	const createPreviewButton = () => {
		const previewBtn = document.createElement('button');
		previewBtn.type = 'button';
		previewBtn.className = 'components-button sk-mailing-preview-title-btn';
		previewBtn.textContent = skMailingNext.i18n.preview || 'Preview';
		previewBtn.addEventListener('click', () => previewPost(previewBtn));
		return previewBtn;
	};

	const createActions = () => {
		const actions = document.createElement('div');
		actions.className = 'sk-mailing-title-actions';

		const previewBtn = createPreviewButton();

		const saveBtn = document.createElement('button');
		saveBtn.type = 'button';
		saveBtn.className = 'components-button is-primary sk-mailing-save-title-btn';
		saveBtn.textContent = skMailingNext.i18n.save || 'Save';
		saveBtn.addEventListener('click', async () => {
			const label = saveBtn.textContent;
			saveBtn.disabled = true;
			saveBtn.textContent = skMailingNext.i18n.saving || 'Saving…';
			try {
				await savePost();
			} catch (e) {
				// Gutenberg shows the save error in its own notice.
			} finally {
				saveBtn.disabled = false;
				saveBtn.textContent = label;
			}
		});

		const nextBtn = document.createElement('button');
		nextBtn.type = 'button';
		nextBtn.className = 'components-button is-primary sk-mailing-next-header-btn';
		nextBtn.textContent = skMailingNext.i18n.next || 'Next';
		nextBtn.addEventListener('click', () => goToSend(nextBtn));

		actions.append(previewBtn, saveBtn, nextBtn);
		return actions;
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

	const injectHeaderActions = () => {
		const bar = document.querySelector('.sk-mailing-editor-steps');
		if (!bar) {
			return false;
		}

		const existingActions = bar.querySelector('.sk-mailing-title-actions');
		if (existingActions) {
			if (!existingActions.querySelector('.sk-mailing-preview-title-btn')) {
				existingActions.prepend(createPreviewButton());
			}
			return true;
		}

		bar.appendChild(createActions());
		document.body.classList.add('sk-mailing-title-actions-ready');
		return true;
	};

	const tryInject = () => {
		const stepsOk = injectSteps();
		const actionsOk = stepsOk && injectHeaderActions();
		return actionsOk;
	};

	if (!tryInject()) {
		const observer = new MutationObserver(() => {
			if (tryInject()) {
				observer.disconnect();
				window.clearInterval(injectInterval);
			}
		});
		observer.observe(document.body, { childList: true, subtree: true });
		const injectInterval = window.setInterval(() => {
			if (tryInject()) {
				observer.disconnect();
				window.clearInterval(injectInterval);
			}
		}, 250);
		setTimeout(() => {
			observer.disconnect();
			window.clearInterval(injectInterval);
		}, 20000);
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
