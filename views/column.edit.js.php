<?php declare(strict_types = 0);
/*
** Copyright (C) 2001-2024 Zabbix SIA
**
** This program is free software: you can redistribute it and/or modify it under the terms of
** the GNU Affero General Public License as published by the Free Software Foundation, version 3.
**
** This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
** without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
** See the GNU Affero General Public License for more details.
**
** You should have received a copy of the GNU Affero General Public License along with this program.
** If not, see <https://www.gnu.org/licenses/>.
**/


use Modules\TopHostsMonzphere\Includes\CWidgetFieldColumnsList;

?>

window.tophosts_column_edit_form = new class {

	#overlay;
	#dialogue;
	#form;
	#thresholds_table;
	#highlights_table;

	init({form_id, thresholds, highlights, thresholds_colors}) {
		this.#form = document.getElementById(form_id);
		this.#overlay = overlays_stack.getById('tophostsmonzphere-column-edit-overlay');
		this.#dialogue = this.#overlay.$dialogue[0];

		this.#thresholds_table = document.getElementById('thresholds_table');
		this.#highlights_table = document.getElementById('highlights_table');

		// Attach event listeners.
		for (const element_name of ['data', 'display_value_as', 'display', 'aggregate_function', 'history']) {
			const element = this.#form.querySelector(`[name="${element_name}"]`);
			if (element) {
				if (element.tagName === 'Z-SELECT') {
					element.addEventListener('change', () => this.#updateForm());
				}
				else {
					for (const radio of this.#form.querySelectorAll(`[name="${element_name}"]`)) {
						radio.addEventListener('change', () => this.#updateForm());
					}
				}
			}
		}

		colorPalette.setThemeColors(thresholds_colors);

		// Initialize thresholds table.
		this.#thresholds_table.addEventListener('afteradd.dynamicRows', e => {
			const $colorpicker = $('tr.form_row:last input[name$="[color]"]', e.target);
			$colorpicker.colorpicker({appendTo: $colorpicker.closest('.input-color-picker')});
			this.#updateForm();
		});
		this.#thresholds_table.addEventListener('afterremove.dynamicRows', () => this.#updateForm());

		$(this.#thresholds_table).dynamicRows({
			rows: thresholds,
			template: '#thresholds-row-tmpl',
			allow_empty: true,
			dataCallback: (row_data) => {
				if (!('color' in row_data)) {
					row_data.color = this.#getNextColor();
				}
			}
		});

		$('tr.form_row input[name$="[color]"]', this.#thresholds_table).each((i, colorpicker) => {
			$(colorpicker).colorpicker({appendTo: $(colorpicker).closest('.input-color-picker')});
		});

		// Initialize highlights table.
		this.#highlights_table.addEventListener('afteradd.dynamicRows', e => {
			const $colorpicker = $('tr.form_row:last input[name$="[color]"]', e.target);
			$colorpicker.colorpicker({appendTo: $colorpicker.closest('.input-color-picker')});
			this.#updateForm();
		});
		this.#highlights_table.addEventListener('afterremove.dynamicRows', () => this.#updateForm());

		$(this.#highlights_table).dynamicRows({
			rows: highlights,
			template: '#highlights-row-tmpl',
			allow_empty: true,
			dataCallback: (row_data) => {
				if (!('color' in row_data)) {
					row_data.color = this.#getNextColor();
				}
			}
		});

		$('tr.form_row input[name$="[color]"]', this.#highlights_table).each((i, colorpicker) => {
			$(colorpicker).colorpicker({appendTo: $(colorpicker).closest('.input-color-picker')});
		});

		// Trim values on change.
		this.#form.addEventListener('change', (e) => {
			if (e.target.value) {
				e.target.value = e.target.value.trim();
			}
		}, {capture: true});

		// Initialize form elements accessibility.
		this.#updateForm();

		this.#form.style.display = '';
		this.#form.querySelector('[name="name"]').focus();
	}

	#getNextColor() {
		const colors = this.#form.querySelectorAll('.input-color-picker input');
		const used_colors = [];

		for (const color of colors) {
			if (color.value !== '') {
				used_colors.push(color.value);
			}
		}

		return colorPalette.getNextColor(used_colors);
	}

	#updateForm() {
		const data = this.#form.querySelector('[name="data"]').value;
		const data_item_value = (data == <?= CWidgetFieldColumnsList::DATA_ITEM_VALUE ?>);
		const data_text = (data == <?= CWidgetFieldColumnsList::DATA_TEXT ?>);
		const data_host_name = (data == <?= CWidgetFieldColumnsList::DATA_HOST_NAME ?>);

		const display_value_as_el = this.#form.querySelector('[name="display_value_as"]:checked');
		const display_value_as = display_value_as_el ? parseInt(display_value_as_el.value) : <?= CWidgetFieldColumnsList::DISPLAY_VALUE_AS_NUMERIC ?>;
		const display_value_as_numeric = (display_value_as == <?= CWidgetFieldColumnsList::DISPLAY_VALUE_AS_NUMERIC ?>);
		const display_value_as_binary = (display_value_as == <?= CWidgetFieldColumnsList::DISPLAY_VALUE_AS_BINARY ?>);

		const display_el = this.#form.querySelector('[name="display"]:checked');
		const display = display_el ? parseInt(display_el.value) : <?= CWidgetFieldColumnsList::DISPLAY_AS_IS ?>;
		const display_as_is = (display == <?= CWidgetFieldColumnsList::DISPLAY_AS_IS ?>);
		const display_sparkline = (display == <?= CWidgetFieldColumnsList::DISPLAY_SPARKLINE ?>);

		const aggregate_function = parseInt(this.#form.querySelector('[name="aggregate_function"]').value);
		const aggregate_none = (aggregate_function == <?= AGGREGATE_NONE ?>);

		const history_el = this.#form.querySelector('[name="history"]:checked');
		const history_trends = history_el && (parseInt(history_el.value) == <?= CWidgetFieldColumnsList::HISTORY_DATA_TRENDS ?>);

		// Item name field.
		for (const element of this.#form.querySelectorAll('.js-item-row')) {
			element.style.display = data_item_value ? '' : 'none';
		}
		$('#item').multiSelect(data_item_value ? 'enable' : 'disable');

		// Text field.
		for (const element of this.#form.querySelectorAll('.js-text-row')) {
			element.style.display = data_text ? '' : 'none';
		}

		// Display value as field.
		for (const element of this.#form.querySelectorAll('.js-display-value-as-row')) {
			element.style.display = data_item_value ? '' : 'none';
		}

		// Show thumbnail field.
		const show_thumbnail_visible = data_item_value && display_value_as_binary;
		for (const element of this.#form.querySelectorAll('.js-show-thumbnail-row')) {
			element.style.display = show_thumbnail_visible ? '' : 'none';
		}

		// Display field.
		const display_visible = data_item_value && display_value_as_numeric;
		for (const element of this.#form.querySelectorAll('.js-display-row')) {
			element.style.display = display_visible ? '' : 'none';
		}

		// Min/Max fields.
		const min_max_visible = data_item_value && display_value_as_numeric && !display_as_is && !display_sparkline;
		for (const element of this.#form.querySelectorAll('.js-min-row, .js-max-row')) {
			element.style.display = min_max_visible ? '' : 'none';
		}

		// Sparkline configuration.
		for (const element of this.#form.querySelectorAll('.js-sparkline-row')) {
			element.style.display = (data_item_value && display_sparkline) ? '' : 'none';
		}

		// Base color field.
		const base_color_visible = !data_host_name && (!data_item_value || !display_value_as_numeric || display_as_is);
		for (const element of this.#form.querySelectorAll('.js-base-color-row')) {
			element.style.display = base_color_visible ? '' : 'none';
		}

		// Thresholds table.
		const thresholds_visible = data_item_value && display_value_as_numeric;
		for (const element of this.#form.querySelectorAll('.js-thresholds-row')) {
			element.style.display = thresholds_visible ? '' : 'none';
		}

		// Highlights table.
		const highlights_visible = (data_item_value && !display_value_as_numeric) || data_text;
		for (const element of this.#form.querySelectorAll('.js-highlights-row')) {
			element.style.display = highlights_visible ? '' : 'none';
		}

		// Decimal places field.
		const decimal_visible = data_item_value && display_value_as_numeric && !display_sparkline;
		for (const element of this.#form.querySelectorAll('.js-decimal-places-row')) {
			element.style.display = decimal_visible ? '' : 'none';
		}

		// Advanced configuration.
		const advanced_config = document.getElementById('advanced_configuration');
		if (advanced_config) {
			advanced_config.style.display = data_item_value ? '' : 'none';
		}

		// Aggregate function field.
		const aggregate_function_el = this.#form.querySelector('[name="aggregate_function"]');
		if (aggregate_function_el) {
			aggregate_function_el.disabled = !data_item_value;
		}

		// Time period field.
		if (this.#form.fields && this.#form.fields.time_period) {
			this.#form.fields.time_period.disabled = !data_item_value || aggregate_none;
		}

		// History data field.
		for (const element of this.#form.querySelectorAll('[name="history"]')) {
			element.disabled = !data_item_value;
		}

		// Warning icons.
		const display_warning = document.getElementById('tophosts-column-display-warning');
		if (display_warning) {
			display_warning.style.display = (data_item_value && !display_as_is) ? '' : 'none';
		}

		const aggregate_warning = document.getElementById('tophosts-column-aggregate-function-warning');
		if (aggregate_warning) {
			const aggregate_warning_functions = [<?= AGGREGATE_AVG ?>, <?= AGGREGATE_MIN ?>, <?= AGGREGATE_MAX ?>, <?= AGGREGATE_SUM ?>];
			aggregate_warning.style.display = (data_item_value && aggregate_warning_functions.includes(aggregate_function)) ? '' : 'none';
		}

		const history_warning = document.getElementById('tophosts-column-history-data-warning');
		if (history_warning) {
			history_warning.style.display = (data_item_value && history_trends) ? '' : 'none';
		}
	}

	submit(overlay) {
		this.#overlay.setLoading();

		const curl = new Curl(this.#form.getAttribute('action'));
		const fields = getFormFields(this.#form);

		fetch(curl.getUrl(), {
			method: 'POST',
			headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
			body: urlEncodeData(fields)
		})
			.then(response => response.json())
			.then(response => {
				if ('error' in response) {
					throw {error: response.error};
				}

				overlayDialogueDestroy(overlay.dialogueid);

				this.#dialogue.dispatchEvent(new CustomEvent('dialogue.submit', {detail: response}));
			})
			.catch((exception) => {
				for (const element of this.#form.parentNode.children) {
					if (element.matches('.msg-good, .msg-bad, .msg-warning')) {
						element.parentNode.removeChild(element);
					}
				}

				let title, messages;

				if (typeof exception === 'object' && 'error' in exception) {
					title = exception.error.title;
					messages = exception.error.messages;
				}
				else {
					messages = [<?= json_encode(_('Unexpected server error.')) ?>];
				}

				const message_box = makeMessageBox('bad', messages, title)[0];

				this.#form.parentNode.insertBefore(message_box, this.#form);
			})
			.finally(() => {
				this.#overlay.unsetLoading();
			});
	}
};
