/**
 * Вибір підписників на екрані редагування list.
 */
export function listAdmin() {
	const root = document.querySelector('#sk-list-subscribers');
	if (!root) return;

	const searchInput = root.querySelector('#sk_list_subscriber_search');
	const resultsEl = root.querySelector('#sk_list_subscriber_results');
	const selectedEl = root.querySelector('#sk_list_subscriber_selected');
	const inputsEl = root.querySelector('#sk_list_subscriber_inputs');
	const countEl = root.querySelector('#sk_list_subscriber_count');
	const data = window.skListData || {};

	let selected = [];
	try {
		selected = JSON.parse(root.dataset.selected || '[]') || [];
	} catch (e) {
		selected = [];
	}

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

	const updateCount = () => {
		if (!countEl) return;
		const n = selected.length;
		countEl.textContent =
			n === 1 ? '1 subscriber' : `${n} subscribers`;
	};

	const syncHiddenInputs = () => {
		if (!inputsEl) return;
		inputsEl.innerHTML = '';
		selected.forEach((item) => {
			const input = document.createElement('input');
			input.type = 'hidden';
			input.name = 'sk_list_subscribers[]';
			input.value = String(item.id);
			inputsEl.appendChild(input);
		});
	};

	const renderSelected = () => {
		if (!selectedEl) return;
		selectedEl.innerHTML = '';

		if (!selected.length) {
			const empty = document.createElement('li');
			empty.className = 'sk-list-subscribers__empty';
			empty.textContent = 'No subscribers selected yet.';
			selectedEl.appendChild(empty);
			updateCount();
			syncHiddenInputs();
			return;
		}

		selected.forEach((item) => {
			const li = document.createElement('li');
			li.className = 'sk-list-subscribers__item';
			li.dataset.id = String(item.id);

			const meta = document.createElement('div');
			meta.className = 'sk-list-subscribers__item-meta';

			const email = document.createElement('span');
			email.className = 'sk-list-subscribers__item-email';
			email.textContent = item.email || `#${item.id}`;

			meta.appendChild(email);

			if (item.name) {
				const name = document.createElement('span');
				name.className = 'sk-list-subscribers__item-name';
				name.textContent = item.name;
				meta.appendChild(name);
			}

			const removeBtn = document.createElement('button');
			removeBtn.type = 'button';
			removeBtn.className = 'sk-list-subscribers__remove';
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

		updateCount();
		syncHiddenInputs();
	};

	const hideResults = () => {
		if (!resultsEl) return;
		resultsEl.hidden = true;
		resultsEl.innerHTML = '';
		activeIndex = -1;
		lastResults = [];
	};

	const addSubscriber = (item) => {
		if (!item || selectedIds().includes(item.id)) return;
		selected = [...selected, item];
		renderSelected();
		if (searchInput) {
			searchInput.value = '';
			searchInput.focus();
		}
		hideResults();
	};

	const renderResults = (items) => {
		if (!resultsEl) return;
		lastResults = items || [];
		activeIndex = -1;
		resultsEl.innerHTML = '';

		if (!lastResults.length) {
			const li = document.createElement('li');
			li.className = 'sk-list-subscribers__result sk-list-subscribers__result--empty';
			li.textContent = 'No subscribers found.';
			resultsEl.appendChild(li);
			resultsEl.hidden = false;
			return;
		}

		lastResults.forEach((item, index) => {
			const li = document.createElement('li');
			li.className = 'sk-list-subscribers__result';
			li.setAttribute('role', 'option');
			li.dataset.index = String(index);
			li.innerHTML = `
				<span class="sk-list-subscribers__result-email">${escapeHtml(item.email)}</span>
				${item.name ? `<span class="sk-list-subscribers__result-name">${escapeHtml(item.name)}</span>` : ''}
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
		if (!resultsEl) return;
		const nodes = resultsEl.querySelectorAll('.sk-list-subscribers__result:not(.sk-list-subscribers__result--empty)');
		nodes.forEach((node, i) => {
			node.classList.toggle('is-active', i === activeIndex);
		});
	};

	const search = (query) => {
		if (abortController) {
			abortController.abort();
		}

		const q = (query || '').trim();

		abortController = new AbortController();

		const params = new URLSearchParams({
			action: 'sk_search_subscribers',
			nonce: data.nonce || '',
			q,
			term_id: String(data.termId || 0),
		});

		selectedIds().forEach((id) => {
			params.append('exclude[]', String(id));
		});

		fetch(`${data.ajaxUrl || '/wp-admin/admin-ajax.php'}?${params.toString()}`, {
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
				const items = (result.data && result.data.items) || [];
				renderResults(items);
			})
			.catch((err) => {
				if (err && err.name === 'AbortError') return;
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
			if (e.key === 'Enter') {
				e.preventDefault();
				if (resultsEl && !resultsEl.hidden && lastResults.length) {
					const index = activeIndex >= 0 ? activeIndex : 0;
					addSubscriber(lastResults[index]);
				}
				return;
			}

			if (resultsEl && !resultsEl.hidden && lastResults.length) {
				if (e.key === 'ArrowDown') {
					e.preventDefault();
					activeIndex = Math.min(activeIndex + 1, lastResults.length - 1);
					highlightActive();
					return;
				}
				if (e.key === 'ArrowUp') {
					e.preventDefault();
					activeIndex = Math.max(activeIndex - 1, 0);
					highlightActive();
					return;
				}
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
}
