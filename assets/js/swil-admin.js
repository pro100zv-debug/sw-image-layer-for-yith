/* SW Image Layer · vista previa en la pestaña del producto */
jQuery(function ($) {
	'use strict';

	var $preview = $('#swil-preview');
	if (!$preview.length) {
		return;
	}

	var $layer = $preview.find('.swil-preview-layer');

	/* Campo vacío → valor por defecto (el que se ve en gris dentro del campo) */
	function num(id, fallback) {
		var $f = $(id);
		var raw = String($f.val() || '').replace(',', '.');
		if (raw === '') {
			raw = String($f.attr('placeholder') || '').replace(',', '.');
		}
		var v = parseFloat(raw);
		return isNaN(v) ? fallback : v;
	}

	function update() {
		$layer.css({
			left: num('#swil_x', 50) + '%',
			top: num('#swil_y', 35) + '%',
			width: num('#swil_w', 18) + '%'
		});
		$preview.toggleClass('is-off', $('#swil_hidden').is(':checked'));
	}

	$('#swil_x, #swil_y, #swil_w').on('input change', update);
	$('#swil_hidden').on('change', update);
	update();
});
