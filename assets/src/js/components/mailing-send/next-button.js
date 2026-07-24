/**
 * Кнопки Preview/Save/Next біля заголовка + stepper для CPT mailing.
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

	const getTitleWrapper = () => {
		const editorDocuments = [document];
		document
			.querySelectorAll('.block-editor-iframe__container iframe, iframe[name="editor-canvas"]')
			.forEach((iframe) => {
				try {
					if (iframe.contentDocument) {
						editorDocuments.push(iframe.contentDocument);
					}
				} catch (e) {
					// Ignore inaccessible third-party iframes.
				}
			});

		for (const editorDocument of editorDocuments) {
			const titleWrapper = editorDocument.querySelector(
				'.editor-visual-editor__post-title-wrapper, .edit-post-visual-editor__post-title-wrapper'
			);
			if (titleWrapper) {
				return titleWrapper;
			}
		}
		return null;
	};

	const injectIframeStyles = (editorDocument) => {
		if (editorDocument === document || editorDocument.getElementById('sk-mailing-title-actions-style')) {
			return;
		}

		const style = editorDocument.createElement('style');
		style.id = 'sk-mailing-title-actions-style';
		style.textContent = `
			.editor-visual-editor__post-title-wrapper,
			.edit-post-visual-editor__post-title-wrapper {
				display: flex;
				align-items: center;
				justify-content: center;
				position: relative;
			}
			.sk-mailing-title-actions {
				display: flex;
				align-items: center;
				gap: 8px;
				position: absolute;
				right: 12px;
				flex: 0 0 auto;
			}
			.sk-mailing-title-actions button {
				display: inline-flex;
				align-items: center;
				justify-content: center;
				min-width: 112px;
				min-height: 36px;
				padding: 6px 16px;
				border: 1px solid transparent;
				border-radius: 2px;
				color: #fff;
				font: 600 13px/1.2 -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
				cursor: pointer;
			}
			.sk-mailing-title-actions button:disabled {
				opacity: .65;
				cursor: default;
			}
			.sk-mailing-save-title-btn { background: #3858e9; border-color: #3858e9; }
			.sk-mailing-save-title-btn:hover { background: #2b46c7; border-color: #2b46c7; }
			.sk-mailing-title-actions .sk-mailing-preview-title-btn {
				background: #fff;
				border-color: #3858e9;
				color: #3858e9;
			}
			.sk-mailing-title-actions .sk-mailing-preview-title-btn:hover {
				background: #f0f4ff;
				border-color: #2b46c7;
				color: #2b46c7;
			}
			.sk-mailing-title-actions .sk-mailing-next-header-btn {
				background: #252a2e !important;
				border-color: #252a2e !important;
				color: #fff !important;
			}
			.sk-mailing-title-actions button.sk-mailing-next-header-btn:hover {
				background: #3c434a !important;
				border-color: #3c434a !important;
				color: #fff !important;
			}
			@media (max-width: 700px) {
				.editor-visual-editor__post-title-wrapper,
				.edit-post-visual-editor__post-title-wrapper { flex-wrap: wrap; }
				.sk-mailing-title-actions {
					position: static;
					width: 100%;
					margin-left: 0;
					padding: 8px 0 0;
				}
			}
		`;
		editorDocument.head.appendChild(style);
	};

	const createPreviewButton = (editorDocument) => {
		const previewBtn = editorDocument.createElement('button');
		previewBtn.type = 'button';
		previewBtn.className = 'components-button sk-mailing-preview-title-btn';
		previewBtn.textContent = skMailingNext.i18n.preview || 'Preview';
		previewBtn.addEventListener('click', () => previewPost(previewBtn));
		return previewBtn;
	};

	const injectTitleActions = () => {
		const titleWrapper = getTitleWrapper();
		if (!titleWrapper) {
			return false;
		}

		injectIframeStyles(titleWrapper.ownerDocument);

		const editorDocument = titleWrapper.ownerDocument;
		const existingActions = titleWrapper.querySelector('.sk-mailing-title-actions');
		if (existingActions) {
			if (!existingActions.querySelector('.sk-mailing-preview-title-btn')) {
				existingActions.prepend(createPreviewButton(editorDocument));
			}
			return true;
		}

		const actions = editorDocument.createElement('div');
		actions.className = 'sk-mailing-title-actions';

		const previewBtn = createPreviewButton(editorDocument);

		const saveBtn = editorDocument.createElement('button');
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

		const nextBtn = editorDocument.createElement('button');
		nextBtn.type = 'button';
		nextBtn.className = 'components-button is-primary sk-mailing-next-header-btn';
		nextBtn.textContent = skMailingNext.i18n.next || 'Next';
		nextBtn.addEventListener('click', () => goToSend(nextBtn));

		actions.append(previewBtn, saveBtn, nextBtn);
		titleWrapper.appendChild(actions);
		document.body.classList.add('sk-mailing-title-actions-ready');
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
		const actionsOk = injectTitleActions();
		const stepsOk = injectSteps();
		return actionsOk && stepsOk;
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
