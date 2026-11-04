/**
 * SW Image Layer for YITH Product · ficha de producto
 *
 * 1. Prepara la lista del complemento elegido en los ajustes:
 *    buscador, mensaje «sin resultados» y texto de ayuda.
 * 2. Cuando hay una opción elegida, coloca su imagen sobre la primera
 *    foto de la galería (posición y ancho en % del producto).
 * 3. Si YITH oculta la lista (por ejemplo, se elige «No»), borra la
 *    opción elegida, el buscador y la imagen.
 */
(function ($) {
	'use strict';

	var C = window.swilData || {};
	if (!C.addonId) {
		return;
	}

	/* Sí/no seguros aunque lleguen como texto ("0", "1", "yes") */
	function isOn(v) {
		return v === true || v === 1 || v === '1' || v === 'yes';
	}
	var ENABLED = isOn(C.enabled);
	var SEARCH = isOn(C.search);

	var ADDON = '#yith-wapo-addon-' + C.addonId;
	var OPTION = '.yith-wapo-option';
	var INPUT = 'input.yith-wapo-option-value';
	var wasShown = null;
	var lastSrc = '';
	var queued = false;

	function norm(s) {
		s = (s || '').toString();
		if (s.normalize) {
			s = s.normalize('NFD').replace(/[̀-ͯ]/g, '');
		}
		return s.toLowerCase().replace(/\s+/g, ' ').trim();
	}

	/* ---------- Complemento ---------- */

	function getAddon() {
		return $(ADDON).first();
	}

	/* YITH oculta el complemento con style="display:none" (lógica condicional). */
	function isShown($a) {
		return $a.length > 0 && $a[0].style.display !== 'none' && $a.css('display') !== 'none';
	}

	function getSelected($a) {
		return $a.find(OPTION).filter(function () {
			var $o = $(this);
			return $o.hasClass('selected') || $o.find(INPUT).prop('checked');
		}).first();
	}

	function setup($a) {
		if (!$a.length || $a.hasClass('swil-ready')) {
			return;
		}
		$a.addClass('swil-addon swil-ready');
		$a[0].style.setProperty('--swil-rows', C.rows || 5);

		var $container = $a.find('.options-container').first();
		var $options = $container.find('.options').first();

		if (C.hint) {
			var $hint = $('<p class="swil-hint" role="status"></p>').text(C.hint);
			var $header = $a.find('.addon-header').first();
			if ($header.length) {
				$header.after($hint);
			} else {
				$a.prepend($hint);
			}
		}

		if (SEARCH && $options.length) {
			var $search = $(
				'<div class="swil-search">' +
				'<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.6-3.6"/></svg>' +
				'<input type="search" autocomplete="off" spellcheck="false">' +
				'</div>'
			);
			$search.find('input').attr({ placeholder: C.placeholder || '', 'aria-label': C.placeholder || '' });
			$options.before($search);

			var $empty = $('<p class="swil-empty" hidden></p>').text(C.emptyText || '');
			$options.after($empty);

			$search.on('input', 'input', function () {
				filter($a, this.value);
			});
			/* Intro no envía el formulario del carrito */
			$search.on('keydown', 'input', function (e) {
				if (e.key === 'Enter') {
					e.preventDefault();
				}
			});
		}
	}

	function filter($a, query) {
		var q = norm(query);
		var visible = 0;

		$a.find(OPTION).each(function () {
			var $o = $(this);
			var text = $o.data('swilText');
			if (text === undefined) {
				text = norm($o.find('label').first().text() || $o.find(INPUT).val());
				$o.data('swilText', text);
			}
			var match = q === '' || text.indexOf(q) !== -1;
			$o.toggleClass('swil-hidden', !match);
			if (match) {
				visible++;
			}
		});

		var $empty = $a.find('.swil-empty');
		if (visible === 0) {
			$empty.removeAttr('hidden');
		} else {
			$empty.attr('hidden', 'hidden');
		}
		$a.find('.options').first().scrollTop(0);
	}

	function resetAddon($a) {
		$a.find(OPTION + '.selected').removeClass('selected');
		$a.find(INPUT).filter(function () {
			return this.checked;
		}).prop('checked', false).trigger('change');

		var $input = $a.find('.swil-search input');
		if ($input.length && $input.val() !== '') {
			$input.val('');
		}
		filter($a, '');
	}

	/* ---------- Galería ---------- */

	function getFigure() {
		var $gallery = $('.woocommerce-product-gallery').first();
		return $gallery.find('.woocommerce-product-gallery__image').filter(function () {
			return !$(this).closest('.swiper-slide-duplicate').length;
		}).first();
	}

	function getSwiper() {
		var found = null;
		$('.woocommerce-product-gallery').first().find('.wd-carousel, .swiper, .swiper-container').each(function () {
			if (this.swiper && !this.swiper.destroyed) {
				found = this.swiper;
				return false;
			}
		});
		return found;
	}

	function goToFirstSlide() {
		var sw = getSwiper();
		if (!sw) {
			return;
		}
		if (sw.params && sw.params.loop && sw.realIndex !== 0) {
			sw.slideToLoop(0);
		} else if (sw.activeIndex !== 0) {
			sw.slideTo(0);
		}
	}

	function renderLayer(src) {
		var $figure = getFigure();

		/* Capas que hayan quedado en fotos antiguas (WoodMart puede rehacer la galería) */
		$('.swil-layer').filter(function () {
			return !$figure.length || this.parentNode !== $figure[0];
		}).remove();
		$('.swil-has-layer').not($figure).removeClass('swil-has-layer');

		if (!$figure.length) {
			return;
		}

		if (!src) {
			$figure.children('.swil-layer').remove();
			$figure.removeClass('swil-has-layer');
			return;
		}

		var $layer = $figure.children('.swil-layer');
		if (!$layer.length) {
			$layer = $('<span class="swil-layer" aria-hidden="true"><img alt="" decoding="async"></span>').appendTo($figure);
		}

		var z = C.zone || {};
		$layer[0].style.left = (z.x !== undefined ? z.x : 50) + '%';
		$layer[0].style.top = (z.y !== undefined ? z.y : 35) + '%';
		$layer[0].style.width = (z.w !== undefined ? z.w : 18) + '%';

		var img = $layer.find('img')[0];
		if (img.getAttribute('src') !== src) {
			img.setAttribute('src', src);
		}
		$figure.addClass('swil-has-layer');
	}

	/* ---------- Estado ---------- */

	function refresh() {
		queued = false;

		var $a = getAddon();
		if (!$a.length) {
			renderLayer('');
			return;
		}
		setup($a);

		var shown = isShown($a);
		if (wasShown === true && !shown) {
			resetAddon($a);
		}
		wasShown = shown;

		var $selected = shown ? getSelected($a) : $();
		var hasSelection = $selected.length > 0;
		$a.toggleClass('swil-has-selection', hasSelection);

		/* Aviso de YITH (style="display:block") → resaltar el texto de ayuda */
		var minError = $a.find('.min-error')[0];
		var yithError = !!(minError && minError.style.display && minError.style.display !== 'none');
		$a.toggleClass('swil-error', yithError && !hasSelection);

		var src = '';
		if (ENABLED && hasSelection) {
			src = $selected.find('.image-container img, img').first().attr('src') || '';
		}

		renderLayer(src);

		if (src && src !== lastSrc) {
			goToFirstSlide();
		}
		lastSrc = src;
	}

	function schedule() {
		if (queued) {
			return;
		}
		queued = true;
		(window.requestAnimationFrame || window.setTimeout)(refresh, 16);
	}

	/* ---------- Arranque ---------- */

	$(function () {
		refresh();

		$(document).on('click change', ADDON + ' ' + OPTION + ', ' + ADDON + ' ' + INPUT, function () {
			setTimeout(schedule, 0);
		});
		$(document).on('found_variation reset_data show_variation hide_variation', 'form.variations_form', function () {
			setTimeout(schedule, 60);
		});

		if (!window.MutationObserver) {
			return;
		}

		/* Cambios de YITH: mostrar/ocultar, clase «selected», bloque redibujado */
		var form = document.querySelector('form.cart') || document.body;
		new MutationObserver(function (list) {
			for (var i = 0; i < list.length; i++) {
				var t = list[i].target;
				if (t.nodeType === 1 && t.closest && t.closest('.swil-search, .swil-hint, .swil-empty')) {
					continue;
				}
				schedule();
				return;
			}
		}).observe(form, { subtree: true, childList: true, attributes: true, attributeFilter: ['class', 'style'] });

		/* La galería puede rehacerse al cambiar de variación */
		var gallery = document.querySelector('.woocommerce-product-gallery');
		if (gallery) {
			new MutationObserver(function (list) {
				for (var i = 0; i < list.length; i++) {
					var nodes = [].slice.call(list[i].addedNodes).concat([].slice.call(list[i].removedNodes));
					for (var j = 0; j < nodes.length; j++) {
						var n = nodes[j];
						if (n.nodeType === 1 && !(n.classList && n.classList.contains('swil-layer'))) {
							schedule();
							return;
						}
					}
				}
			}).observe(gallery, { subtree: true, childList: true });
		}
	});
})(jQuery);
