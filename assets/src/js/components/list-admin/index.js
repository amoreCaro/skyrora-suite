/**
 * Вибір підписників на екрані редагування list.
 */
export function listAdmin() {
	const listOverview = document.querySelector(
		'body.taxonomy-list #col-container'
	);

	if (listOverview) {
		const body = document.body;
		const heading = document.querySelector('.wrap > h1');
		const addForm = listOverview.querySelector('#col-left .form-wrap');
		const listCard = listOverview.querySelector('#col-right .col-wrap');
		const postsFilter = listCard?.querySelector('#posts-filter');
		const searchForm = document.querySelector(
			'body.taxonomy-list .wrap > .search-form'
		);
		const statusTabs = document.querySelector(
			'body.taxonomy-list .sk-list-status-tabs'
		);
		const appearanceData = window.skListData?.appearance || {};

		body.classList.add('sk-list-overview');

		if (heading) {
			const addButton = document.createElement('a');
			const titleRow = document.createElement('div');

			addButton.href =
				window.skListData?.addUrl ||
				'admin.php?page=skyrora-mailing-add-list';
			addButton.className = 'sk-list-add-toggle';
			addButton.textContent =
				addForm?.querySelector('h2')?.textContent.trim() ||
				'Add New List';

			titleRow.className = 'sk-list-title-row';
			heading.before(titleRow);
			titleRow.append(heading, addButton);
		}

		if (listCard && postsFilter) {
			if (statusTabs) {
				listCard.prepend(statusTabs);
			}

			const toolbar = document.createElement('div');
			const searchGroup = document.createElement('div');
			const filterButton = document.createElement('button');
			const appearanceButton = document.createElement('button');
			const appearancePanel = document.createElement('div');
			const searchInput = searchForm?.querySelector('input[type="search"]');
			const storageKey = 'sk-list-appearance';
			const columns = [
				['name', 'Name'],
				['description', 'Description'],
				['sk_list_score', 'List score'],
				['sk_subscribed', 'Subscribed'],
				['sk_unconfirmed', 'Unconfirmed'],
				['sk_unsubscribed', 'Unsubscribed'],
				['sk_inactive', 'Inactive'],
				['sk_bounced', 'Bounced'],
				['sk_created', 'Created on'],
			];
			let preferences = {};

			try {
				preferences =
					JSON.parse(window.localStorage.getItem(storageKey) || '{}') ||
					{};
			} catch (e) {
				preferences = {};
			}

			const savePreferences = () => {
				try {
					window.localStorage.setItem(
						storageKey,
						JSON.stringify(preferences)
					);
				} catch (e) {
					// The page remains functional when browser storage is disabled.
				}
			};

			const updateUrl = (changes) => {
				const url = new URL(window.location.href);
				Object.entries(changes).forEach(([key, value]) => {
					url.searchParams.set(key, value);
				});
				url.searchParams.delete('paged');
				window.location.assign(url.toString());
			};

			const applyDensity = (density) => {
				const allowed = ['comfortable', 'balanced', 'compact'];
				const selected = allowed.includes(density)
					? density
					: 'comfortable';
				postsFilter.dataset.density = selected;
				appearancePanel
					.querySelectorAll('[data-density]')
					.forEach((button) => {
						button.classList.toggle(
							'is-active',
							button.dataset.density === selected
						);
					});
			};

			const applyColumnVisibility = () => {
				columns.forEach(([column]) => {
					const isVisible =
						!preferences.columns ||
						preferences.columns[column] !== false;
					const selector =
						column === 'description'
							? '.sk-list-description-inline'
							: `.column-${column}`;

					postsFilter.querySelectorAll(selector).forEach((cell) => {
						cell.classList.toggle(
							'sk-list-column-hidden',
							!isVisible
						);
					});
				});
			};

			postsFilter
				.querySelectorAll('tbody tr')
				.forEach((row) => {
					const nameCell = row.querySelector('.column-name');
					const descriptionCell = row.querySelector(
						'.column-description'
					);
					const description =
						descriptionCell?.textContent.trim() || '';

					if (nameCell && description && description !== '—') {
						const inlineDescription =
							document.createElement('div');
						inlineDescription.className =
							'sk-list-description-inline';
						inlineDescription.textContent = description;
						nameCell.appendChild(inlineDescription);
					}
				});

			toolbar.className = 'sk-list-toolbar';
			searchGroup.className = 'sk-list-toolbar__search';
			filterButton.type = 'button';
			filterButton.className = 'sk-list-toolbar__filter';
			filterButton.setAttribute('aria-label', 'Filter lists');
			filterButton.innerHTML =
				'<span class="dashicons dashicons-filter" aria-hidden="true"></span>';
			filterButton.addEventListener('click', () => searchInput?.focus());

			if (searchInput) {
				searchInput.placeholder = 'Search';
				searchInput.setAttribute('aria-label', 'Search lists');
			}
			if (searchForm) {
				searchGroup.append(searchForm);
			}
			searchGroup.append(filterButton);

			appearanceButton.type = 'button';
			appearanceButton.className = 'sk-list-appearance-toggle';
			appearanceButton.setAttribute('aria-label', 'Appearance');
			appearanceButton.setAttribute('aria-expanded', 'false');
			appearanceButton.innerHTML =
				'<span class="dashicons dashicons-admin-generic" aria-hidden="true"></span>';

			appearancePanel.className = 'sk-list-appearance';
			appearancePanel.hidden = true;
			appearancePanel.innerHTML = `
				<div class="sk-list-appearance__title">Appearance</div>
				<div class="sk-list-appearance__section">
					<div class="sk-list-appearance__label">Sort by</div>
					<div class="sk-list-appearance__sort">
						<select aria-label="Sort lists by">
							<option value="name">Name</option>
							<option value="count">Subscribers</option>
							<option value="term_id">Created on</option>
						</select>
						<div class="sk-list-appearance__order" aria-label="Sort order">
							<button type="button" data-order="asc" aria-label="Ascending">↑</button>
							<button type="button" data-order="desc" aria-label="Descending">↓</button>
						</div>
					</div>
				</div>
				<div class="sk-list-appearance__section">
					<div class="sk-list-appearance__label">Density</div>
					<div class="sk-list-appearance__segments sk-list-appearance__density">
						<button type="button" data-density="comfortable">Comfortable</button>
						<button type="button" data-density="balanced">Balanced</button>
						<button type="button" data-density="compact">Compact</button>
					</div>
				</div>
				<div class="sk-list-appearance__section">
					<div class="sk-list-appearance__label">Items per page</div>
					<div class="sk-list-appearance__segments sk-list-appearance__per-page">
						${[10, 20, 50, 100]
							.map(
								(number) =>
									`<button type="button" data-per-page="${number}">${number}</button>`
							)
							.join('')}
					</div>
				</div>
				<div class="sk-list-appearance__section">
					<div class="sk-list-appearance__label">Properties</div>
					<div class="sk-list-appearance__properties">
						${columns
							.map(
								([column, label]) => `
									<label>
										<input type="checkbox" data-column="${column}">
										<span>${label}</span>
									</label>
								`
							)
							.join('')}
					</div>
				</div>
			`;

			const sortSelect = appearancePanel.querySelector('select');
			sortSelect.value = appearanceData.orderby || 'name';
			sortSelect.addEventListener('change', () => {
				updateUrl({ orderby: sortSelect.value });
			});

			appearancePanel.querySelectorAll('[data-order]').forEach((button) => {
				const isActive =
					button.dataset.order === (appearanceData.order || 'asc');
				button.classList.toggle('is-active', isActive);
				button.addEventListener('click', () => {
					updateUrl({ order: button.dataset.order });
				});
			});

			appearancePanel
				.querySelectorAll('[data-density]')
				.forEach((button) => {
					button.addEventListener('click', () => {
						preferences.density = button.dataset.density;
						savePreferences();
						applyDensity(preferences.density);
					});
				});

			appearancePanel
				.querySelectorAll('[data-per-page]')
				.forEach((button) => {
					const isActive =
						Number(button.dataset.perPage) ===
						Number(appearanceData.perPage || 10);
					button.classList.toggle('is-active', isActive);
					button.addEventListener('click', () => {
						updateUrl({ sk_per_page: button.dataset.perPage });
					});
				});

			appearancePanel
				.querySelectorAll('[data-column]')
				.forEach((checkbox) => {
					const column = checkbox.dataset.column;
					checkbox.checked =
						!preferences.columns ||
						preferences.columns[column] !== false;
					checkbox.addEventListener('change', () => {
						preferences.columns = preferences.columns || {};
						preferences.columns[column] = checkbox.checked;
						savePreferences();
						applyColumnVisibility();
					});
				});

			appearanceButton.addEventListener('click', () => {
				const willOpen = appearancePanel.hidden;
				appearancePanel.hidden = !willOpen;
				appearanceButton.setAttribute(
					'aria-expanded',
					String(willOpen)
				);
			});

			document.addEventListener('click', (event) => {
				if (
					!appearancePanel.hidden &&
					!appearancePanel.contains(event.target) &&
					!appearanceButton.contains(event.target)
				) {
					appearancePanel.hidden = true;
					appearanceButton.setAttribute('aria-expanded', 'false');
				}
			});

			toolbar.append(searchGroup, appearanceButton, appearancePanel);
			if (statusTabs) {
				statusTabs.after(toolbar);
			} else {
				listCard.prepend(toolbar);
			}

			applyDensity(preferences.density || 'comfortable');
			applyColumnVisibility();
		}
	}

	const root = document.querySelector('#sk-list-subscribers');
	const editHeading =
		root?.closest('.wrap')?.querySelector('h1') ||
		(root ? document.querySelector('.wrap h1') : null);
	if (editHeading && !document.querySelector('.sk-list-edit-back')) {
		const backLink = document.createElement('a');
		backLink.className = 'sk-list-edit-back';
		backLink.href =
			window.skListData?.listUrl ||
			'edit-tags.php?taxonomy=list&post_type=subscriber';
		backLink.setAttribute('aria-label', 'Back to lists');
		backLink.innerHTML =
			'<span class="dashicons dashicons-arrow-left-alt2" aria-hidden="true"></span>';
		editHeading.prepend(backLink);
	}

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
