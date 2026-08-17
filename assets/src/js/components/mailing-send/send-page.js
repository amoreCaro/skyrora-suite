/**
 * Сторінка Send: режими отримувачів + schedule + summary + test modal.
 */
export function sendPage() {
	const root = document.getElementById('sk-send-page');
	if (!root || typeof skSendData === 'undefined') {
		return;
	}

	const modeCards = root.querySelectorAll('.sk-send-mode-card');
	const modeInputs = root.querySelectorAll('input[name="sk_send_mode"]');
	const panels = root.querySelectorAll('[data-mode-panel]');
	const submitBtn = document.getElementById('sk_send_submit');
	const submitLabel = document.getElementById('sk_send_submit_label');
	const responseBox = document.getElementById('sk_send_response');
	const subjectInput = document.getElementById('sk_send_subject');
	const scheduleEnabled = document.getElementById('sk_schedule_enabled');
	const scheduleFields = document.getElementById('sk_schedule_fields');
	const scheduleDate = document.getElementById('sk_schedule_date');
	const scheduleTime = document.getElementById('sk_schedule_time');
	const listSelect = document.getElementById('sk_send_list_id');
	const customEmailsInput = document.getElementById('sk_send_custom_emails');
	const summaryRecipientsLabel = document.getElementById('sk_summary_recipients_label');
	const summaryRecipients = document.getElementById('sk_summary_recipients');
	const summarySchedule = document.getElementById('sk_summary_schedule');

	const testModal = document.getElementById('sk_send_test_modal');
	const testOpenBtn = document.getElementById('sk_send_test_open');
	const testSubmitBtn = document.getElementById('sk_send_test_submit');
	const testEmailInput = document.getElementById('sk_send_test_email');
	const testResponseBox = document.getElementById('sk_send_test_response');

	const subscribersPicker = initSubscriberPicker(
		document.getElementById('sk_send_subscribers')
	);

	const addListBtn = root.querySelector('.sk-send-to__empty-action');
	if (addListBtn && skSendData.addListUrl) {
		addListBtn.setAttribute('href', skSendData.addListUrl);
	}

	const getMode = () => root.getAttribute('data-mode') || 'list';

	const escapeHtml = (str) => {
		const div = document.createElement('div');
		div.textContent = str == null ? '' : String(str);
		return div.innerHTML;
	};

	const updateSubmitLabel = () => {
		const label = scheduleEnabled?.checked
			? skSendData.i18n.schedule
			: skSendData.i18n.send;
		if (submitLabel) {
			submitLabel.textContent = label;
		} else if (submitBtn) {
			submitBtn.textContent = label;
		}
	};

	const getSelectedModeTitle = () => {
		const checked = root.querySelector('input[name="sk_send_mode"]:checked');
		const card = checked?.closest('.sk-send-mode-card');
		return card?.querySelector('.sk-send-mode-card__title')?.textContent?.trim() || '';
	};

	const updateSummary = () => {
		const mode = getMode();
		let recipientsText = skSendData.i18n.notSelected;

		if (summaryRecipientsLabel) {
			const modeTitle = getSelectedModeTitle();
			if (modeTitle) {
				summaryRecipientsLabel.textContent = modeTitle;
			}
		}

		if (mode === 'list') {
			const option = listSelect?.selectedOptions?.[0];
			if (option?.value) {
				recipientsText = option.getAttribute('data-name') || option.textContent.trim();
			}
		} else if (mode === 'subscribers') {
			const count = subscribersPicker?.getSelectedIds().length || 0;
			if (count > 0) {
				recipientsText = skSendData.i18n.subscribersCount.replace('%d', String(count));
			}
		} else if (mode === 'custom') {
			const emails = parseEmails(customEmailsInput?.value || '');
			if (emails.length > 0) {
				recipientsText = skSendData.i18n.customEmails.replace('%d', String(emails.length));
			}
		}

		if (summaryRecipients) {
			summaryRecipients.textContent = recipientsText;
		}

		if (summarySchedule) {
			if (scheduleEnabled?.checked && scheduleDate?.value && scheduleTime?.value) {
				summarySchedule.textContent = `${scheduleDate.value} ${scheduleTime.value}`;
			} else {
				summarySchedule.textContent = skSendData.i18n.notScheduled;
			}
		}
	};

	const setMode = (mode) => {
		const next = ['list', 'subscribers', 'custom'].includes(mode) ? mode : 'list';
		root.setAttribute('data-mode', next);

		modeInputs.forEach((input) => {
			input.checked = input.value === next;
		});

		modeCards.forEach((card) => {
			const input = card.querySelector('input[name="sk_send_mode"]');
			card.classList.toggle('is-selected', input?.value === next);
		});

		panels.forEach((panel) => {
			panel.hidden = panel.getAttribute('data-mode-panel') !== next;
		});

		updateSummary();
	};

	modeInputs.forEach((input) => {
		input.addEventListener('change', () => {
			if (input.checked) {
				setMode(input.value);
			}
		});
	});

	const syncSchedule = () => {
		const enabled = Boolean(scheduleEnabled?.checked);
		scheduleFields?.classList.toggle('is-disabled', !enabled);
		if (scheduleDate) {
			scheduleDate.disabled = !enabled;
		}
		if (scheduleTime) {
			scheduleTime.disabled = !enabled;
		}
		updateSubmitLabel();
		updateSummary();
	};

	scheduleEnabled?.addEventListener('change', syncSchedule);
	scheduleDate?.addEventListener('change', updateSummary);
	scheduleTime?.addEventListener('change', updateSummary);
	listSelect?.addEventListener('change', updateSummary);
	customEmailsInput?.addEventListener('input', updateSummary);
	subscribersPicker?.onChange(updateSummary);

	subjectInput?.addEventListener('input', () => {
		subjectInput.classList.remove('is-invalid');
	});

	setMode(getMode());
	syncSchedule();

	const openTestModal = () => {
		if (!testModal) {
			return;
		}
		testModal.hidden = false;
		document.body.classList.add('sk-send-modal-open');
		if (testResponseBox) {
			testResponseBox.innerHTML = '';
		}
		testEmailInput?.focus();
		testEmailInput?.select();
	};

	const closeTestModal = () => {
		if (!testModal) {
			return;
		}
		testModal.hidden = true;
		document.body.classList.remove('sk-send-modal-open');
	};

	testOpenBtn?.addEventListener('click', openTestModal);
	testModal?.querySelectorAll('[data-close-modal]').forEach((el) => {
		el.addEventListener('click', closeTestModal);
	});
	document.addEventListener('keydown', (e) => {
		if (e.key === 'Escape' && testModal && !testModal.hidden) {
			closeTestModal();
		}
	});

	const sendRequest = async ({ mode, extra = {}, button, box }) => {
		const subject = subjectInput?.value?.trim() || '';
		if (!subject) {
			subjectInput?.classList.add('is-invalid');
			subjectInput?.focus();
			if (box) {
				box.innerHTML = `<div class="notice notice-error inline"><p>${escapeHtml(skSendData.i18n.needSubject)}</p></div>`;
			}
			return false;
		}
		subjectInput?.classList.remove('is-invalid');

		const body = new URLSearchParams({
			action: 'sk_send_mailing',
			nonce: skSendData.nonce,
			post_id: String(skSendData.postId),
			subject,
			mode,
		});

		Object.entries(extra).forEach(([key, value]) => {
			if (Array.isArray(value)) {
				value.forEach((item) => body.append(key, item));
			} else if (value != null) {
				body.set(key, String(value));
			}
		});

		if (button) {
			button.disabled = true;
		}
		if (box) {
			box.innerHTML = `<p>${escapeHtml(skSendData.i18n.sending)}</p>`;
		}

		try {
			const res = await fetch(skSendData.ajaxUrl, {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body,
			});
			const data = await res.json();
			if (data.success) {
				if (box) {
					box.innerHTML = `<div class="notice notice-success inline"><p>${escapeHtml(data.data.message)}</p></div>`;
				}
				return true;
			}
			const message = data?.data?.message || skSendData.i18n.failed;
			if (box) {
				box.innerHTML = `<div class="notice notice-error inline"><p>${escapeHtml(message)}</p></div>`;
			}
			return false;
		} catch (e) {
			if (box) {
				box.innerHTML = `<div class="notice notice-error inline"><p>${escapeHtml(skSendData.i18n.failed)}</p></div>`;
			}
			return false;
		} finally {
			if (button) {
				button.disabled = false;
			}
		}
	};

	testSubmitBtn?.addEventListener('click', async () => {
		const email = testEmailInput?.value?.trim() || '';
		if (!email) {
			if (testResponseBox) {
				testResponseBox.innerHTML = `<div class="notice notice-error inline"><p>${escapeHtml(skSendData.i18n.needTestEmail)}</p></div>`;
			}
			testEmailInput?.focus();
			return;
		}

		await sendRequest({
			mode: 'test',
			extra: { test_email: email },
			button: testSubmitBtn,
			box: testResponseBox,
		});
	});

	if (!submitBtn || !responseBox) {
		return;
	}

	submitBtn.addEventListener('click', async () => {
		const mode = getMode();
		const extra = {};

		if (mode === 'list') {
			const listId = listSelect?.value || '';
			if (!listId) {
				responseBox.innerHTML = `<div class="notice notice-error inline"><p>${escapeHtml(skSendData.i18n.needList)}</p></div>`;
				return;
			}
			extra['list_ids[]'] = [listId];
		} else if (mode === 'subscribers') {
			const ids = subscribersPicker?.getSelectedIds() || [];
			if (!ids.length) {
				responseBox.innerHTML = `<div class="notice notice-error inline"><p>${escapeHtml(skSendData.i18n.needSubscribers)}</p></div>`;
				return;
			}
			extra['subscriber_ids[]'] = ids.map(String);
		} else {
			const emails = parseEmails(customEmailsInput?.value || '');
			if (!emails.length) {
				responseBox.innerHTML = `<div class="notice notice-error inline"><p>${escapeHtml(skSendData.i18n.needEmails)}</p></div>`;
				return;
			}
			extra.emails = emails.join(', ');
		}

		if (scheduleEnabled?.checked) {
			extra.schedule = '1';
			extra.schedule_date = scheduleDate?.value || '';
			extra.schedule_time = scheduleTime?.value || '';
		}

		await sendRequest({
			mode,
			extra,
			button: submitBtn,
			box: responseBox,
		});
	});
}

/**
 * Parse comma/space/semicolon separated emails.
 *
 * @param {string} value
 * @returns {string[]}
 */
function parseEmails(value) {
	return String(value || '')
		.split(/[\s,;]+/)
		.map((part) => part.trim())
		.filter((part) => part.includes('@'));
}

/**
 * Subscriber search/select for the Send page.
 *
 * @param {HTMLElement|null} root
 */
function initSubscriberPicker(root) {
	if (!root) {
		return null;
	}

	const searchInput = root.querySelector('#sk_send_subscriber_search');
	const resultsEl = root.querySelector('#sk_send_subscriber_results');
	const selectedEl = root.querySelector('#sk_send_subscriber_selected');
	const changeListeners = [];

	let selected = [];
	let debounceTimer = null;
	let activeIndex = -1;
	let lastResults = [];
	let abortController = null;

	const selectedIds = () => selected.map((item) => item.id);

	const escapeHtml = (str) => {
		const div = document.createElement('div');
		div.textContent = str == null ? '' : String(str);
		return div.innerHTML;
	};

	const notify = () => {
		changeListeners.forEach((fn) => fn());
	};

	const hideResults = () => {
		if (!resultsEl) {
			return;
		}
		resultsEl.hidden = true;
		resultsEl.innerHTML = '';
		activeIndex = -1;
		lastResults = [];
	};

	const renderSelected = () => {
		if (!selectedEl) {
			return;
		}
		selectedEl.innerHTML = '';

		if (!selected.length) {
			const empty = document.createElement('li');
			empty.className = 'sk-send-subscribers__empty';
			empty.textContent = skSendData.i18n.noneSelected;
			selectedEl.appendChild(empty);
			notify();
			return;
		}

		selected.forEach((item) => {
			const li = document.createElement('li');
			li.className = 'sk-send-subscribers__item';

			const meta = document.createElement('div');
			meta.className = 'sk-send-subscribers__item-meta';

			const email = document.createElement('span');
			email.className = 'sk-send-subscribers__item-email';
			email.textContent = item.email || `#${item.id}`;
			meta.appendChild(email);

			if (item.name) {
				const name = document.createElement('span');
				name.className = 'sk-send-subscribers__item-name';
				name.textContent = item.name;
				meta.appendChild(name);
			}

			const removeBtn = document.createElement('button');
			removeBtn.type = 'button';
			removeBtn.className = 'sk-send-subscribers__remove';
			removeBtn.setAttribute('aria-label', `Remove ${item.email || item.id}`);
			removeBtn.textContent = '×';
			removeBtn.addEventListener('click', () => {
				selected = selected.filter((s) => s.id !== item.id);
				renderSelected();
			});

			li.appendChild(meta);
			li.appendChild(removeBtn);
			selectedEl.appendChild(li);
		});

		notify();
	};

	const addSubscriber = (item) => {
		if (!item || selectedIds().includes(item.id)) {
			return;
		}
		selected = [...selected, item];
		renderSelected();
		if (searchInput) {
			searchInput.value = '';
			searchInput.focus();
		}
		hideResults();
	};

	const renderResults = (items) => {
		if (!resultsEl) {
			return;
		}
		lastResults = items || [];
		activeIndex = -1;
		resultsEl.innerHTML = '';

		if (!lastResults.length) {
			const li = document.createElement('li');
			li.className = 'sk-send-subscribers__result sk-send-subscribers__result--empty';
			li.textContent = skSendData.i18n.noResults;
			resultsEl.appendChild(li);
			resultsEl.hidden = false;
			return;
		}

		lastResults.forEach((item, index) => {
			const li = document.createElement('li');
			li.className = 'sk-send-subscribers__result';
			li.setAttribute('role', 'option');
			li.dataset.index = String(index);
			li.innerHTML = `
				<span class="sk-send-subscribers__result-email">${escapeHtml(item.email)}</span>
				${item.name ? `<span class="sk-send-subscribers__result-name">${escapeHtml(item.name)}</span>` : ''}
			`;
			li.addEventListener('mousedown', (e) => {
				e.preventDefault();
				addSubscriber(item);
			});
			resultsEl.appendChild(li);
		});

		resultsEl.hidden = false;
	};

	const highlightActive = () => {
		if (!resultsEl) {
			return;
		}
		const nodes = resultsEl.querySelectorAll(
			'.sk-send-subscribers__result:not(.sk-send-subscribers__result--empty)'
		);
		nodes.forEach((node, i) => {
			node.classList.toggle('is-active', i === activeIndex);
		});
	};

	const search = (query) => {
		if (abortController) {
			abortController.abort();
		}

		abortController = new AbortController();
		const params = new URLSearchParams({
			action: 'sk_search_subscribers',
			nonce: skSendData.searchNonce || '',
			q: (query || '').trim(),
		});

		selectedIds().forEach((id) => {
			params.append('exclude[]', String(id));
		});

		fetch(`${skSendData.ajaxUrl}?${params.toString()}`, {
			method: 'GET',
			credentials: 'same-origin',
			signal: abortController.signal,
		})
			.then((res) => res.json())
			.then((result) => {
				if (!result.success) {
					renderResults([]);
					return;
				}
				renderResults((result.data && result.data.items) || []);
			})
			.catch((err) => {
				if (err && err.name === 'AbortError') {
					return;
				}
				console.error(err);
			});
	};

	if (searchInput) {
		searchInput.addEventListener('input', () => {
			clearTimeout(debounceTimer);
			debounceTimer = setTimeout(() => {
				search(searchInput.value);
			}, 220);
		});

		searchInput.addEventListener('focus', () => {
			search(searchInput.value);
		});

		searchInput.addEventListener('keydown', (e) => {
			const nodes = resultsEl
				? resultsEl.querySelectorAll(
						'.sk-send-subscribers__result:not(.sk-send-subscribers__result--empty)'
					)
				: [];

			if (e.key === 'ArrowDown' && nodes.length) {
				e.preventDefault();
				activeIndex = Math.min(activeIndex + 1, nodes.length - 1);
				highlightActive();
				return;
			}
			if (e.key === 'ArrowUp' && nodes.length) {
				e.preventDefault();
				activeIndex = Math.max(activeIndex - 1, 0);
				highlightActive();
				return;
			}
			if (e.key === 'Enter') {
				e.preventDefault();
				if (resultsEl && !resultsEl.hidden && lastResults.length) {
					const index = activeIndex >= 0 ? activeIndex : 0;
					addSubscriber(lastResults[index]);
				}
				return;
			}
			if (e.key === 'Escape') {
				hideResults();
			}
		});

		searchInput.addEventListener('blur', () => {
			setTimeout(hideResults, 150);
		});
	}

	document.addEventListener('click', (e) => {
		if (!root.contains(e.target)) {
			hideResults();
		}
	});

	renderSelected();

	return {
		getSelectedIds: () => selectedIds().map(String),
		onChange: (fn) => {
			if (typeof fn === 'function') {
				changeListeners.push(fn);
			}
		},
	};
}
