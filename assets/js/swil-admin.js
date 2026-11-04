/* SW Image Layer · vista previa en la pestaña del producto */
jQuery(function ($) {
	'use strict';

	var $preview = $('#swil-preview');
	if (!$preview.length) {
		return;
	}

	var $layer = $preview.find('.swil-preview-layer');

	function num(id, def) {
		var v = parseFloat(String($(id).val()).replace(',', '.'));
		return isNaN(v) ? def : v;
	}

	function update() {
		$layer.css({
			left: num('#swil_x', 50) + '%',
			top: num('#swil_y', 35) + '%',
			width: num('#swil_w', 18) + '%'
		});
		$preview.toggleClass('is-off', !$('#swil_enabled').is(':checked'));
	}

	$('#swil_x, #swil_y, #swil_w').on('input change', update);
	$('#swil_enabled').on('change', update);
	update();
});
