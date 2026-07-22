/**
 * Сторінка Send: таби режиму + multiselect списків + schedule + відправка.
 */
export function sendPage() {
	const root = document.getElementById('sk-send-page');
	if (!root || typeof skSendData === 'undefined') {
		return;
	}

	const tabs = root.querySelectorAll('.sk-send-tabs__tab');
	const panels = root.querySelectorAll('[data-mode-panel]');
	const submitBtn = document.getElementById('sk_send_submit');
	const responseBox = document.getElementById('sk_send_response');
	const subjectInput = document.getElementById('sk_send_subject');
	const scheduleEnabled = document.getElementById('sk_schedule_enabled');
	const scheduleFields = document.getElementById('sk_schedule_fields');
	const scheduleBlock = document.getElementById('sk_send_schedule');

	const listsMs = initListMultiselect(document.getElementById('sk_send_lists'));

	const getMode = () => root.getAttribute('data-mode') || 'test';

	const updateSubmitLabel = () => {
		if (!submitBtn) {
			return;
		}
		const mode = getMode();
		if (mode === 'test') {
			submitBtn.textContent = skSendData.i18n.sendTest;
			return;
		}
		if (scheduleEnabled?.checked) {
			submitBtn.textContent = skSendData.i18n.schedule;
			return;
		}
		submitBtn.textContent = skSendData.i18n.send;
	};

	const setMode = (mode) => {
		root.setAttribute('data-mode', mode);

		tabs.forEach((tab) => {
			const active = tab.getAttribute('data-mode') === mode;
			tab.classList.toggle('is-active', active);
			tab.setAttribute('aria-selected', active ? 'true' : 'false');
		});

		panels.forEach((panel) => {
			panel.hidden = panel.getAttribute('data-mode-panel') !== mode;
		});

		if (scheduleBlock) {
			scheduleBlock.hidden = mode === 'test';
		}

		updateSubmitLabel();
	};

	tabs.forEach((tab) => {
		tab.addEventListener('click', () => {
			setMode(tab.getAttribute('data-mode') || 'test');
		});
	});
	setMode(getMode());

	if (scheduleEnabled && scheduleFields) {
		const syncSchedule = () => {
			scheduleFields.hidden = !scheduleEnabled.checked;
			updateSubmitLabel();
		};
		scheduleEnabled.addEventListener('change', syncSchedule);
		syncSchedule();
	}

	subjectInput?.addEventListener('input', () => {
		subjectInput.classList.remove('is-invalid');
	});

	if (!submitBtn || !responseBox) {
		return;
	}

	submitBtn.addEventListener('click', async () => {
		const mode = getMode();
		const subject = subjectInput?.value?.trim() || '';

		if (!subject) {
			subjectInput?.classList.add('is-invalid');
			subjectInput?.focus();
			responseBox.innerHTML = `<div class="notice notice-error inline"><p>${skSendData.i18n.needSubject}</p></div>`;
			return;
		}
		subjectInput?.classList.remove('is-invalid');

		const body = new URLSearchParams({
			action: 'sk_send_mailing',
			nonce: skSendData.nonce,
			post_id: String(skSendData.postId),
			subject,
			mode,
		});

		if (mode === 'list') {
			const listIds = listsMs?.getSelectedIds() || [];
			if (!listIds.length) {
				responseBox.innerHTML = `<div class="notice notice-error inline"><p>${skSendData.i18n.needList}</p></div>`;
				return;
			}
			listIds.forEach((id) => body.append('list_ids[]', id));
		} else if (mode === 'single') {
			const email = document.getElementById('sk_send_email')?.value?.trim() || '';
			body.set('email', email);
		} else {
			const email = document.getElementById('sk_send_test_email')?.value?.trim() || '';
			body.set('test_email', email);
		}

		if (mode !== 'test' && scheduleEnabled?.checked) {
			body.set('schedule', '1');
			body.set('schedule_date', document.getElementById('sk_schedule_date')?.value || '');
			body.set('schedule_time', document.getElementById('sk_schedule_time')?.value || '');
		}

		submitBtn.disabled = true;
		responseBox.innerHTML = `<p>${skSendData.i18n.sending}</p>`;

		try {
			const res = await fetch(skSendData.ajaxUrl, {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body,
			});
			const data = await res.json();
			if (data.success) {
				responseBox.innerHTML = `<div class="notice notice-success inline"><p>${data.data.message}</p></div>`;
			} else {
				const message = data?.data?.message || skSendData.i18n.failed;
				responseBox.innerHTML = `<div class="notice notice-error inline"><p>${message}</p></div>`;
			}
		} catch (e) {
			responseBox.innerHTML = `<div class="notice notice-error inline"><p>${skSendData.i18n.failed}</p></div>`;
		} finally {
			submitBtn.disabled = false;
		}
	});
}

/**
 * Multiselect списків (chips + dropdown).
 *
 * @param {HTMLElement|null} root
 */
function initListMultiselect(root) {
	if (!root) {
		return null;
	}

	const control = root.querySelector('.sk-multiselect__control');
	const chipsEl = root.querySelector('.sk-multiselect__chips');
	const search = root.querySelector('.sk-multiselect__search');
	const dropdown = root.querySelector('.sk-multiselect__dropdown');
	const valuesEl = root.querySelector('.sk-multiselect__values');
	const options = Array.from(root.querySelectorAll('.sk-multiselect__option'));

	const selected = new Set();

	const getSelectedIds = () => Array.from(selected);

	const syncValues = () => {
		if (!valuesEl) {
			return;
		}
		valuesEl.innerHTML = '';
		getSelectedIds().forEach((id) => {
			const input = document.createElement('input');
			input.type = 'hidden';
			input.name = 'list_ids[]';
			input.value = id;
			valuesEl.appendChild(input);
		});
	};

	const renderChips = () => {
		if (!chipsEl) {
			return;
		}
		chipsEl.innerHTML = '';
		options.forEach((opt) => {
			const id = opt.getAttribute('data-id');
			if (!id || !selected.has(id)) {
				return;
			}
			const chip = document.createElement('span');
			chip.className = 'sk-multiselect__chip';
			chip.innerHTML = `
				<button type="button" class="sk-multiselect__chip-remove" aria-label="Remove" data-id="${id}">×</button>
				<span class="sk-multiselect__chip-label">${opt.getAttribute('data-name') || ''}</span>
				<span class="sk-multiselect__count">${opt.getAttribute('data-count') || '0'}</span>
			`;
			chipsEl.appendChild(chip);
		});
		syncValues();
	};

	const setOpen = (open) => {
		root.classList.toggle('is-open', open);
		if (dropdown) {
			dropdown.hidden = !open;
		}
		search?.setAttribute('aria-expanded', open ? 'true' : 'false');
	};

	const filterOptions = () => {
		const q = (search?.value || '').trim().toLowerCase();
		options.forEach((opt) => {
			const name = (opt.getAttribute('data-name') || '').toLowerCase();
			opt.hidden = Boolean(q) && !name.includes(q);
		});
	};

	const toggleId = (id) => {
		if (!id) {
			return;
		}
		if (selected.has(id)) {
			selected.delete(id);
		} else {
			selected.add(id);
		}
		options.forEach((opt) => {
			const active = selected.has(opt.getAttribute('data-id'));
			opt.classList.toggle('is-selected', active);
			opt.setAttribute('aria-selected', active ? 'true' : 'false');
		});
		renderChips();
	};

	options.forEach((opt) => {
		opt.addEventListener('click', (e) => {
			e.preventDefault();
			toggleId(opt.getAttribute('data-id'));
			search?.focus();
		});
		opt.addEventListener('mouseenter', () => {
			options.forEach((o) => o.classList.remove('is-active'));
			opt.classList.add('is-active');
		});
	});

	chipsEl?.addEventListener('click', (e) => {
		const btn = e.target.closest('.sk-multiselect__chip-remove');
		if (!btn) {
			return;
		}
		e.preventDefault();
		e.stopPropagation();
		toggleId(btn.getAttribute('data-id'));
	});

	control?.addEventListener('click', () => {
		setOpen(true);
		search?.focus();
	});

	search?.addEventListener('focus', () => setOpen(true));
	search?.addEventListener('input', () => {
		setOpen(true);
		filterOptions();
	});

	document.addEventListener('click', (e) => {
		if (!root.contains(e.target)) {
			setOpen(false);
		}
	});

	renderChips();

	return { getSelectedIds };
}
