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
?>


window.widget_form = new class extends CWidgetForm {

	#form;
	#list_columns;
	#templateid;
	#column_index;

	init({templateid}) {
		this.#form = this.getForm();
		this.#templateid = templateid;

		this.#list_columns = document.getElementById('list_columns');

		new CSortable(this.#list_columns.querySelector('tbody'), {
			selector_handle: 'div.<?= ZBX_STYLE_DRAG_ICON ?>',
			freeze_end: 1
		});

		this.#list_columns.addEventListener('click', (e) => this.#processColumnsAction(e));
	}

	#processColumnsAction(e) {
		const target = e.target;

		let column_popup;

		switch (target.getAttribute('name')) {
			case 'add':
				this.#column_index = this.#list_columns.querySelectorAll('tr').length;

				column_popup = PopUp(
					'widget.tophostsmonzphere.column.edit',
					{templateid: this.#templateid},
					{
						dialogueid: 'tophostsmonzphere-column-edit-overlay',
						dialogue_class: 'modal-popup-generic'
					}
				).$dialogue[0];
				column_popup.addEventListener('dialogue.submit', (e) => this.#updateColumns(e));
				column_popup.addEventListener('dialogue.close', this.#removeColorpicker);
				break;

			case 'edit':
				const form_fields = getFormFields(this.#form);

				this.#column_index = target.closest('tr').querySelector('[name="sortorder[columns][]"]').value;

				column_popup = PopUp(
					'widget.tophostsmonzphere.column.edit',
					{...form_fields.columns[this.#column_index], edit: 1, templateid: this.#templateid},
					{
						dialogueid: 'tophostsmonzphere-column-edit-overlay',
						dialogue_class: 'modal-popup-generic'
					}
				).$dialogue[0];
				column_popup.addEventListener('dialogue.submit', (e) => this.#updateColumns(e));
				column_popup.addEventListener('dialogue.close', this.#removeColorpicker);
				break;

			case 'remove':
				target.closest('tr').remove();
				this.reload();
				break;
		}
	}

	#updateColumns(e) {
		const data = e.detail;

		if (data.edit) {
			this.#form.querySelectorAll(`[name^="columns[${this.#column_index}][`)
				.forEach((node) => node.remove());

			delete data.edit;
		}
		else {
			this.#addVar(`sortorder[columns][]`, this.#column_index);
		}

		if (data.thresholds) {
			for (const [key, value] of Object.entries(data.thresholds)) {
				this.#addVar(`columns[${this.#column_index}][thresholds][${key}][color]`, value.color);
				this.#addVar(`columns[${this.#column_index}][thresholds][${key}][threshold]`, value.threshold);
			}

			delete data.thresholds;
		}

		if (data.highlights) {
			for (const [key, value] of Object.entries(data.highlights)) {
				this.#addVar(`columns[${this.#column_index}][highlights][${key}][color]`, value.color);
				this.#addVar(`columns[${this.#column_index}][highlights][${key}][pattern]`, value.pattern);
			}

			delete data.highlights;
		}

		if (data.time_period) {
			for (const [key, value] of Object.entries(data.time_period)) {
				this.#addVar(`columns[${this.#column_index}][time_period][${key}]`, value);
			}

			delete data.time_period;
		}

		if (data.sparkline) {
			for (const [key, value] of Object.entries(data.sparkline)) {
				if (typeof value === 'object' && value !== null) {
					for (const [subkey, subvalue] of Object.entries(value)) {
						this.#addVar(`columns[${this.#column_index}][sparkline][${key}][${subkey}]`, subvalue);
					}
				}
				else {
					this.#addVar(`columns[${this.#column_index}][sparkline][${key}]`, value);
				}
			}

			delete data.sparkline;
		}

		for (const [key, value] of Object.entries(data)) {
			this.#addVar(`columns[${this.#column_index}][${key}]`, value);
		}

		this.reload();
	}

	#addVar(name, value) {
		const input = document.createElement('input');
		input.setAttribute('type', 'hidden');
		input.setAttribute('name', name);
		input.setAttribute('value', value);
		this.#form.appendChild(input);
	}

	// Need to remove function after sub-popups auto close.
	#removeColorpicker() {
		$('#color_picker').hide();
	}
};
