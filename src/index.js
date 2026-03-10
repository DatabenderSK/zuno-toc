(function (wp) {
	'use strict';

	var el = wp.element.createElement;
	var useState = wp.element.useState;
	var Fragment = wp.element.Fragment;
	var registerBlockType = wp.blocks.registerBlockType;
	var useSelect = wp.data.useSelect;
	var useDispatch = wp.data.useDispatch;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var SelectControl = wp.components.SelectControl;
	var CheckboxControl = wp.components.CheckboxControl;
	var ToggleControl = wp.components.ToggleControl;
	var Button = wp.components.Button;
	var registerPlugin = wp.plugins.registerPlugin;
	var PluginDocumentSettingPanel = wp.editor
		? wp.editor.PluginDocumentSettingPanel
		: (wp.editPost ? wp.editPost.PluginDocumentSettingPanel : null);

	var settings = window.zunoTocSettings || {
		default_style: 'minimal',
		heading_levels: [2, 3],
		toc_title: 'Obsah článku',
		numbering: false,
		show_toggle: true,
		default_collapsed: false,
		accent_color: '',
		font_size: '',
	};

	var FONT_SIZE_PRESETS = [
		{ label: 'Malé (13px)', value: '13px' },
		{ label: 'Predvolené (15px)', value: '15px' },
		{ label: 'Stredné (16px)', value: '16px' },
		{ label: 'Veľké (18px)', value: '18px' },
	];

	var COLOR_PRESETS = [
		{ label: 'Zelená', value: '#5ba462' },
		{ label: 'Modrá', value: '#3b82f6' },
		{ label: 'Fialová', value: '#8b5cf6' },
		{ label: 'Červená', value: '#ef4444' },
		{ label: 'Oranžová', value: '#f59e0b' },
		{ label: 'Tyrkysová', value: '#06b6d4' },
		{ label: 'Ružová', value: '#ec4899' },
		{ label: 'Tmavo modrá', value: '#1e40af' },
	];

	/* ─── Green Zuno Icon (SVG) ─── */

	var zunoIcon = el('svg', {
		width: 24,
		height: 24,
		viewBox: '0 0 24 24',
		fill: 'none',
		xmlns: 'http://www.w3.org/2000/svg',
	},
		el('rect', { x: 2, y: 3, width: 20, height: 18, rx: 3, fill: '#5ba462' }),
		el('line', { x1: 6, y1: 8, x2: 18, y2: 8, stroke: '#fff', strokeWidth: 2, strokeLinecap: 'round' }),
		el('line', { x1: 6, y1: 12, x2: 15, y2: 12, stroke: '#fff', strokeWidth: 2, strokeLinecap: 'round' }),
		el('line', { x1: 6, y1: 16, x2: 12, y2: 16, stroke: '#fff', strokeWidth: 2, strokeLinecap: 'round' })
	);

	/* ─── Helpers ─── */

	var TRANSLITERATION = {
		'á':'a','ä':'a','č':'c','ď':'d','é':'e','í':'i','ĺ':'l','ľ':'l',
		'ň':'n','ó':'o','ô':'o','ŕ':'r','š':'s','ť':'t','ú':'u','ý':'y','ž':'z',
		'Á':'a','Ä':'a','Č':'c','Ď':'d','É':'e','Í':'i','Ĺ':'l','Ľ':'l',
		'Ň':'n','Ó':'o','Ô':'o','Ŕ':'r','Š':'s','Ť':'t','Ú':'u','Ý':'y','Ž':'z',
	};

	function generateId(text, usedIds) {
		var id = text.toLowerCase();
		for (var k in TRANSLITERATION) {
			id = id.split(k).join(TRANSLITERATION[k]);
		}
		id = id.replace(/[^a-z0-9\s-]/g, '')
			.replace(/\s+/g, '-')
			.replace(/-+/g, '-')
			.replace(/^-|-$/g, '');
		if (!id) id = 'heading';

		var base = id;
		var i = 2;
		while (usedIds.indexOf(id) !== -1) {
			id = base + '-' + i;
			i++;
		}
		return id;
	}

	function stripHTML(html) {
		var tmp = document.createElement('div');
		tmp.innerHTML = html;
		return tmp.textContent || tmp.innerText || '';
	}

	function extractHeadings(blocks) {
		var headings = [];
		var usedIds = [];

		function walk(blockList) {
			for (var i = 0; i < blockList.length; i++) {
				var block = blockList[i];
				if (block.name === 'core/heading') {
					var level = block.attributes.level || 2;
					var content = block.attributes.content || '';
					var text = stripHTML(content);
					if (!text) continue;

					var className = block.attributes.className || '';
					if (className.indexOf('no-toc') !== -1) continue;

					var id = generateId(text, usedIds);
					usedIds.push(id);

					headings.push({
						level: level,
						text: text,
						id: id,
						clientId: block.clientId,
					});
				}
				if (block.innerBlocks && block.innerBlocks.length) {
					walk(block.innerBlocks);
				}
			}
		}

		walk(blocks);
		return headings;
	}

	/* ─── SVG Icons ─── */

	function EyeIcon(visible) {
		if (visible) {
			// Open eye
			return el('svg', { width: 16, height: 16, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', strokeWidth: 2 },
				el('path', { d: 'M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z' }),
				el('circle', { cx: 12, cy: 12, r: 3 })
			);
		}
		// Closed eye
		return el('svg', { width: 16, height: 16, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', strokeWidth: 2 },
			el('path', { d: 'M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24' }),
			el('line', { x1: 1, y1: 1, x2: 23, y2: 23 })
		);
	}

	function LinkIcon() {
		return el('svg', { width: 14, height: 14, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', strokeWidth: 2 },
			el('path', { d: 'M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71' }),
			el('path', { d: 'M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71' })
		);
	}

	function EditIcon() {
		return el('svg', { width: 14, height: 14, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', strokeWidth: 2 },
			el('path', { d: 'M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7' }),
			el('path', { d: 'M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z' })
		);
	}

	/* ─── Heading Item Component ─── */

	function HeadingItem(props) {
		var h = props.heading;
		var isExcluded = props.isExcluded;
		var customLabel = props.customLabel;
		var customAnchor = props.customAnchor;
		var onToggleExclude = props.onToggleExclude;
		var onChangeLabel = props.onChangeLabel;
		var onChangeAnchor = props.onChangeAnchor;

		var editingAnchor = useState(false);
		var isEditingAnchor = editingAnchor[0];
		var setEditingAnchor = editingAnchor[1];

		var editingLabel = useState(false);
		var isEditingLabel = editingLabel[0];
		var setEditingLabel = editingLabel[1];

		var anchorVal = useState(customAnchor || h.id);
		var anchorValue = anchorVal[0];
		var setAnchorValue = anchorVal[1];

		var labelVal = useState(customLabel || h.text);
		var labelValue = labelVal[0];
		var setLabelValue = labelVal[1];

		var stripNumbers = props.stripNumbers;

		var isSubItem = h.level > 2;
		var indent = (h.level - 2) * 24;
		var strippedText = stripNumbers ? h.text.replace(/^\d+\.\s*/, '') : h.text;
		var displayText = customLabel || strippedText;

		var classes = 'zuno-toc-editor__item';
		if (isSubItem) classes += ' zuno-toc-editor__item--sub';
		if (isExcluded) classes += ' zuno-toc-editor__item--excluded';

		// Badge color by level.
		var badgeColors = { 2: '#5ba462', 3: '#3b82f6', 4: '#a855f7' };
		var badgeColor = badgeColors[h.level] || '#999';

		var mainRow = el('div', {
			className: classes,
			style: { marginLeft: indent + 'px' },
		},
			// Left: text
			el('span', {
				className: 'zuno-toc-editor__text' + (customLabel ? ' zuno-toc-editor__text--custom' : ''),
			}, displayText),
			// Custom anchor indicator
			customAnchor ? el('span', {
				className: 'zuno-toc-editor__custom-anchor',
				title: 'Vlastný anchor: #' + customAnchor,
			}, '#' + customAnchor) : null,
			// Right: badge + actions
			el('span', { className: 'zuno-toc-editor__actions' },
				// Level badge
				el('span', {
					className: 'zuno-toc-editor__badge',
					style: { background: badgeColor },
				}, 'H' + h.level),
				// Edit text
				el('span', {
					className: 'zuno-toc-editor__action' + (customLabel ? ' zuno-toc-editor__action--active' : ''),
					onClick: function () { setEditingLabel(!isEditingLabel); setEditingAnchor(false); },
					title: 'Zmeniť text v TOC',
				}, EditIcon()),
				// Edit anchor
				el('span', {
					className: 'zuno-toc-editor__action' + (customAnchor ? ' zuno-toc-editor__action--active' : ''),
					onClick: function () { setEditingAnchor(!isEditingAnchor); setEditingLabel(false); },
					title: 'Zmeniť anchor URL',
				}, LinkIcon()),
				// Toggle visibility
				el('span', {
					className: 'zuno-toc-editor__action zuno-toc-editor__action--eye' + (isExcluded ? ' zuno-toc-editor__action--hidden' : ''),
					onClick: function () { onToggleExclude(h.id); },
					title: isExcluded ? 'Zobraziť v TOC' : 'Skryť z TOC',
				}, EyeIcon(!isExcluded))
			)
		);

		// Anchor edit inline.
		var anchorEdit = null;
		if (isEditingAnchor) {
			anchorEdit = el('div', { className: 'zuno-toc-editor__inline-edit', style: { marginLeft: indent + 'px' } },
				el('span', { className: 'zuno-toc-editor__inline-prefix' }, '#'),
				el('input', {
					type: 'text',
					className: 'zuno-toc-editor__inline-input',
					value: anchorValue,
					placeholder: h.id,
					onChange: function (e) { setAnchorValue(e.target.value); },
					onKeyDown: function (e) {
						if (e.key === 'Enter') {
							var val = anchorValue.trim().replace(/^#/, '');
							onChangeAnchor(h.id, val === h.id ? '' : val);
							setEditingAnchor(false);
						}
						if (e.key === 'Escape') {
							setAnchorValue(customAnchor || h.id);
							setEditingAnchor(false);
						}
					},
				}),
				el('span', {
					className: 'zuno-toc-editor__inline-save',
					onClick: function () {
						var val = anchorValue.trim().replace(/^#/, '');
						onChangeAnchor(h.id, val === h.id ? '' : val);
						setEditingAnchor(false);
					},
					title: 'Uložiť',
				}, '\u2713'),
				customAnchor ? el('span', {
					className: 'zuno-toc-editor__inline-reset',
					onClick: function () {
						onChangeAnchor(h.id, '');
						setAnchorValue(h.id);
						setEditingAnchor(false);
					},
					title: 'Resetovať na pôvodný',
				}, '\u2715') : null
			);
		}

		// Label edit inline.
		var labelEdit = null;
		if (isEditingLabel) {
			labelEdit = el('div', { className: 'zuno-toc-editor__inline-edit', style: { marginLeft: indent + 'px' } },
				el('input', {
					type: 'text',
					className: 'zuno-toc-editor__inline-input zuno-toc-editor__inline-input--wide',
					value: labelValue,
					placeholder: h.text,
					onChange: function (e) { setLabelValue(e.target.value); },
					onKeyDown: function (e) {
						if (e.key === 'Enter') {
							onChangeLabel(h.id, labelValue.trim() === h.text ? '' : labelValue.trim());
							setEditingLabel(false);
						}
						if (e.key === 'Escape') {
							setLabelValue(customLabel || h.text);
							setEditingLabel(false);
						}
					},
				}),
				el('span', {
					className: 'zuno-toc-editor__inline-save',
					onClick: function () {
						onChangeLabel(h.id, labelValue.trim() === h.text ? '' : labelValue.trim());
						setEditingLabel(false);
					},
					title: 'Uložiť',
				}, '\u2713'),
				customLabel ? el('span', {
					className: 'zuno-toc-editor__inline-reset',
					onClick: function () {
						onChangeLabel(h.id, '');
						setLabelValue(h.text);
						setEditingLabel(false);
					},
					title: 'Resetovať na pôvodný',
				}, '\u2715') : null
			);
		}

		return el(Fragment, {}, mainRow, anchorEdit, labelEdit);
	}

	/* ─── Edit Component ─── */

	function TocEdit(props) {
		var attributes = props.attributes;
		var setAttributes = props.setAttributes;
		var style = attributes.style || settings.default_style;
		var listStyle = attributes.listStyle || 'default';
		var showToggle = attributes.showToggle || 'default';
		var stripNumbers = attributes.stripNumbers || false;
		var accentColor = attributes.accentColor || '';
		var defaultCollapsed = attributes.defaultCollapsed || 'default';
		var fontSize = attributes.fontSize || 'default';
		var excludedHeadings = attributes.excludedHeadings || [];
		var customLabels = attributes.customLabels || {};
		var customAnchors = attributes.customAnchors || {};

		// Effective values (resolve 'default' to global setting).
		var effectiveNumbering = listStyle === 'numbers' || (listStyle === 'default' && settings.numbering);
		var effectiveShowToggle = showToggle === 'yes' || (showToggle === 'default' && !!settings.show_toggle);
		var effectiveCollapsed = defaultCollapsed === 'collapsed' || (defaultCollapsed === 'default' && !!settings.default_collapsed);
		var effectiveAccent = accentColor || settings.accent_color || '#5ba462';
		var effectiveFontSize = fontSize !== 'default' ? fontSize : (settings.font_size || '15px');

		var blocks = useSelect(function (select) {
			return select('core/block-editor').getBlocks();
		}, []);

		var headings = extractHeadings(blocks);

		var allowedLevels = settings.heading_levels || [2, 3];
		var filteredHeadings = headings.filter(function (h) {
			return allowedLevels.indexOf(h.level) !== -1;
		});

		function toggleExclude(id) {
			var idx = excludedHeadings.indexOf(id);
			var next;
			if (idx === -1) {
				next = excludedHeadings.concat([id]);
			} else {
				next = excludedHeadings.filter(function (x) { return x !== id; });
			}
			setAttributes({ excludedHeadings: next });
		}

		function excludeByLevel(level) {
			var ids = filteredHeadings
				.filter(function (h) { return h.level === level; })
				.map(function (h) { return h.id; });
			var merged = excludedHeadings.slice();
			ids.forEach(function (id) {
				if (merged.indexOf(id) === -1) merged.push(id);
			});
			setAttributes({ excludedHeadings: merged });
		}

		function showAll() {
			setAttributes({ excludedHeadings: [] });
		}

		function changeLabel(headingId, newLabel) {
			var next = Object.assign({}, customLabels);
			if (newLabel) {
				next[headingId] = newLabel;
			} else {
				delete next[headingId];
			}
			setAttributes({ customLabels: next });
		}

		function changeAnchor(headingId, newAnchor) {
			var next = Object.assign({}, customAnchors);
			if (newAnchor) {
				next[headingId] = newAnchor;
			} else {
				delete next[headingId];
			}
			setAttributes({ customAnchors: next });
		}

		// Count by level for bulk actions.
		var levelCounts = {};
		filteredHeadings.forEach(function (h) {
			levelCounts[h.level] = (levelCounts[h.level] || 0) + 1;
		});

		// ─── Canvas Preview ───
		var previewItems = filteredHeadings.map(function (h) {
			return el(HeadingItem, {
				key: h.id,
				heading: h,
				isExcluded: excludedHeadings.indexOf(h.id) !== -1,
				customLabel: customLabels[h.id] || '',
				customAnchor: customAnchors[h.id] || '',
				stripNumbers: stripNumbers,
				onToggleExclude: toggleExclude,
				onChangeLabel: changeLabel,
				onChangeAnchor: changeAnchor,
			});
		});

		// Editor collapse toggle.
		var manualToggleState = useState(false);
		var manualToggle = manualToggleState[0];
		var setManualToggle = manualToggleState[1];
		var isBodyVisible = !effectiveCollapsed || manualToggle;

		var editorClasses = 'zuno-toc-editor zuno-toc-editor--' + style;
		if (effectiveNumbering) editorClasses += ' zuno-toc-editor--numbered';

		var editorStyle = {};
		if (effectiveAccent !== '#5ba462') {
			editorStyle['--zuno-toc-accent'] = effectiveAccent;
		}
		if (effectiveFontSize !== '15px') {
			editorStyle['--zuno-toc-font-size'] = effectiveFontSize;
		}

		var toggleLabel = isBodyVisible ? 'Skryť' : 'Zobraziť';

		var preview = el('div', { className: editorClasses, style: editorStyle },
			el('div', { className: 'zuno-toc-editor__header' },
				el('span', { className: 'zuno-toc-editor__title' }, settings.toc_title),
				effectiveShowToggle ? el('span', {
					className: 'zuno-toc-editor__toggle-preview',
					onClick: function () { setManualToggle(!manualToggle); },
					style: { cursor: 'pointer' },
				}, toggleLabel) : null
			),
			isBodyVisible ? el('div', { className: 'zuno-toc-editor__body' },
				filteredHeadings.length === 0
					? el('p', { className: 'zuno-toc-editor__empty' }, 'Pridajte nadpisy (H2, H3) do článku.')
					: previewItems
			) : null
		);

		// ─── Sidebar ───
		var bulkButtons = [];
		if (levelCounts[3]) {
			bulkButtons.push(
				el(Button, {
					key: 'hide-h3',
					isSecondary: true,
					isSmall: true,
					onClick: function () { excludeByLevel(3); },
					style: { marginRight: '6px', marginBottom: '6px' },
				}, 'Skryť všetky H3')
			);
		}
		if (levelCounts[4]) {
			bulkButtons.push(
				el(Button, {
					key: 'hide-h4',
					isSecondary: true,
					isSmall: true,
					onClick: function () { excludeByLevel(4); },
					style: { marginRight: '6px', marginBottom: '6px' },
				}, 'Skryť všetky H4')
			);
		}
		if (excludedHeadings.length > 0) {
			bulkButtons.push(
				el(Button, {
					key: 'show-all',
					isSecondary: true,
					isSmall: true,
					onClick: showAll,
					style: { marginBottom: '6px' },
				}, 'Zobraziť všetky')
			);
		}

		// Color swatches for sidebar (always same count).
		var colorSwatches = el('div', { className: 'zuno-toc-editor__color-swatches' },
			COLOR_PRESETS.map(function (cp) {
				var isActive = accentColor ? accentColor === cp.value : cp.value === '#5ba462';
				return el('button', {
					key: cp.value,
					className: 'zuno-toc-editor__color-swatch' + (isActive ? ' zuno-toc-editor__color-swatch--active' : ''),
					style: { background: cp.value },
					title: cp.label,
					onClick: function () {
						setAttributes({ accentColor: cp.value === '#5ba462' ? '' : cp.value });
					},
				});
			})
		);

		var sidebar = el(InspectorControls, {},
			el(PanelBody, { title: 'Nastavenia TOC', initialOpen: true },
				el(SelectControl, {
					label: 'Štýl',
					value: style,
					options: [
						{ label: 'Minimálny', value: 'minimal' },
						{ label: 'Zaoblený', value: 'rounded' },
						{ label: 'Tmavý', value: 'dark' },
					],
					onChange: function (val) { setAttributes({ style: val }); },
				}),
				el('div', { style: { marginBottom: '16px' } },
					el('label', {
						className: 'components-base-control__label',
						style: { display: 'block', marginBottom: '4px' },
					}, 'Farba'),
					colorSwatches
				),
				el(SelectControl, {
					label: 'Štýl zoznamu',
					value: listStyle,
					help: listStyle === 'default'
						? 'Používa globálne nastavenie (' + (settings.numbering ? 'číslovanie' : 'odrážky') + ')'
						: null,
					options: [
						{ label: 'Predvolený', value: 'default' },
						{ label: 'Odrážky', value: 'bullets' },
						{ label: 'Číslovanie', value: 'numbers' },
					],
					onChange: function (val) { setAttributes({ listStyle: val }); },
				}),
				el(ToggleControl, {
					label: 'Odstrániť čísla z nadpisov',
					help: stripNumbers
						? 'Čísla ako "1.", "2." sa odstránia z textu v TOC'
						: 'Zobrazí sa pôvodný text nadpisov',
					checked: stripNumbers,
					onChange: function (val) { setAttributes({ stripNumbers: val }); },
				}),
				el(SelectControl, {
					label: 'Skryť/Zobraziť tlačidlo',
					value: showToggle,
					help: showToggle === 'default'
						? 'Používa globálne nastavenie (' + (settings.show_toggle ? 'áno' : 'nie') + ')'
						: null,
					options: [
						{ label: 'Predvolený', value: 'default' },
						{ label: 'Zobraziť', value: 'yes' },
						{ label: 'Skryť', value: 'no' },
					],
					onChange: function (val) { setAttributes({ showToggle: val }); },
				}),
				el(SelectControl, {
					label: 'Predvolene skrytý',
					value: defaultCollapsed,
					help: defaultCollapsed === 'default'
						? 'Používa globálne nastavenie (' + (settings.default_collapsed ? 'áno' : 'nie') + ')'
						: (effectiveCollapsed ? 'TOC bude na fronte zložený' : null),
					options: [
						{ label: 'Predvolený', value: 'default' },
						{ label: 'Rozbalený', value: 'expanded' },
						{ label: 'Skrytý', value: 'collapsed' },
					],
					onChange: function (val) { setAttributes({ defaultCollapsed: val }); },
				}),
				el(SelectControl, {
					label: 'Veľkosť písma',
					value: fontSize,
					help: fontSize === 'default'
						? 'Používa globálne nastavenie (' + (settings.font_size || '15px') + ')'
						: null,
					options: [
						{ label: 'Predvolený', value: 'default' },
					].concat(FONT_SIZE_PRESETS),
					onChange: function (val) { setAttributes({ fontSize: val }); },
				})
			),
			el(PanelBody, { title: 'Nadpisy', initialOpen: false },
				bulkButtons.length > 0 ? el('div', { style: { marginBottom: '12px' } }, bulkButtons) : null,
				filteredHeadings.map(function (h) {
					var isExcluded = excludedHeadings.indexOf(h.id) !== -1;
					return el(CheckboxControl, {
						key: h.id,
						label: 'H' + h.level + ': ' + (customLabels[h.id] || h.text),
						checked: !isExcluded,
						onChange: function () { toggleExclude(h.id); },
					});
				})
			)
		);

		var blockProps = useBlockProps();
		return el('div', blockProps,
			sidebar,
			preview
		);
	}

	/* ─── Register Block ─── */

	registerBlockType('zuno/toc', {
		icon: zunoIcon,
		edit: TocEdit,
		save: function () {
			return null;
		},
	});

	/* ─── Post Sidebar Plugin ─── */

	if (PluginDocumentSettingPanel) {
		function TocPostPanel() {
			var meta = useSelect(function (select) {
				return select('core/editor').getEditedPostAttribute('meta') || {};
			}, []);

			var editPost = useDispatch('core/editor').editPost;

			var tocDisabled = meta._zuno_toc_disabled || false;
			var headingLevels = meta._zuno_toc_heading_levels || '';

			function updateMeta(key, value) {
				var newMeta = {};
				newMeta[key] = value;
				editPost({ meta: newMeta });
			}

			return el(PluginDocumentSettingPanel, {
				name: 'zuno-toc-post-settings',
				title: 'Zuno TOC',
				icon: zunoIcon,
			},
				el(CheckboxControl, {
					label: 'Vypnúť TOC pre tento článok',
					checked: tocDisabled,
					onChange: function (val) { updateMeta('_zuno_toc_disabled', val); },
				}),
				el('div', { style: { marginTop: '12px' } },
					el('p', { style: { marginBottom: '4px', fontWeight: 500 } }, 'Úrovne nadpisov (override)'),
					el('p', { className: 'components-base-control__help', style: { marginTop: 0 } },
						'Nechajte prázdne pre globálne nastavenie. Príklad: 2,3,4'
					),
					el('input', {
						type: 'text',
						className: 'components-text-control__input',
						value: headingLevels,
						placeholder: settings.heading_levels.join(','),
						onChange: function (e) { updateMeta('_zuno_toc_heading_levels', e.target.value); },
					})
				)
			);
		}

		registerPlugin('zuno-toc-post-settings', {
			render: TocPostPanel,
			icon: zunoIcon,
		});
	}

})(window.wp);
