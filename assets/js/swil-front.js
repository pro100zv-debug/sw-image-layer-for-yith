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
	var SCROLL = C.scroll === undefined ? true : isOn(C.scroll);
	var LIGHTBOX = C.lightbox === undefined ? true : isOn(C.lightbox);

	/* El cliente acaba de elegir una opción con el ratón o el dedo */
	var userPick = false;

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

	/* ---------- Desplazamiento hasta la foto ---------- */

	/* Alto de la cabecera fija (WoodMart, Elementor, barra de administración) */
	function headerOffset() {
		var bottom = 0;
		var els = document.querySelectorAll('#wpadminbar, header, .whb-header, .whb-sticky-header, .whb-main-header, .elementor-sticky--active, .site-header');
		for (var i = 0; i < els.length; i++) {
			var el = els[i];
			var pos = window.getComputedStyle(el).position;
			if (pos !== 'fixed' && pos !== 'sticky') {
				continue;
			}
			var r = el.getBoundingClientRect();
			if (r.height > 0 && r.top <= 1 && r.bottom > bottom && r.bottom < window.innerHeight / 2) {
				bottom = r.bottom;
			}
		}
		return bottom;
	}

	function scrollToGallery() {
		var gallery = document.querySelector('.woocommerce-product-gallery');
		if (!gallery) {
			return;
		}
		var target = Math.max(0, Math.round(window.pageYOffset + gallery.getBoundingClientRect().top - headerOffset() - 16));
		if (Math.abs(window.pageYOffset - target) < 8) {
			return;
		}
		try {
			window.scrollTo({ top: target, behavior: 'smooth' });
		} catch (e) {
			window.scrollTo(0, target);
		}
	}

	/* ---------- Vista ampliada (lightbox) ----------
	 * Se crea una copia de la foto grande con la imagen elegida ya dibujada
	 * y se pone en los atributos que leen los lightbox (href y data-large_image).
	 * Así funciona con cualquier lightbox, con zoom y gestos incluidos.
	 */
	var big = { key: '', url: '', loading: '' };

	function isBlob(u) {
		return typeof u === 'string' && u.indexOf('blob:') === 0;
	}

	function sameOrigin(u) {
		try {
			return new URL(u, window.location.href).origin === window.location.origin;
		} catch (e) {
			return true;
		}
	}

	function loadImage(u) {
		return new Promise(function (resolve, reject) {
			var im = new Image();
			if (!sameOrigin(u)) {
				im.crossOrigin = 'anonymous';
			}
			im.onload = function () { resolve(im); };
			im.onerror = reject;
			im.src = u;
		});
	}

	function buildComposite(photoUrl, layerUrl, z) {
		return Promise.all([loadImage(photoUrl), loadImage(layerUrl)]).then(function (imgs) {
			var photo = imgs[0], layer = imgs[1];
			var W = photo.naturalWidth, H = photo.naturalHeight;
			if (!W || !H || !layer.naturalWidth) {
				throw new Error('size');
			}
			var canvas = document.createElement('canvas');
			canvas.width = W;
			canvas.height = H;
			var ctx = canvas.getContext('2d');
			ctx.drawImage(photo, 0, 0, W, H);
			var lw = W * z.w / 100;
			var lh = lw * layer.naturalHeight / layer.naturalWidth;
			ctx.imageSmoothingQuality = 'high';
			ctx.drawImage(layer, W * z.x / 100 - lw / 2, H * z.y / 100 - lh / 2, lw, lh);
			return new Promise(function (resolve, reject) {
				canvas.toBlob(function (blob) {
					if (blob) {
						resolve(URL.createObjectURL(blob));
					} else {
						reject(new Error('blob'));
					}
				}, 'image/jpeg', 0.92);
			});
		});
	}

	/* Guarda una sola vez el valor original para que WooCommerce pueda restaurarlo al cambiar de variación */
	function keepOriginal($el, attr) {
		if ($el.length && $el.attr('data-o_' + attr) === undefined) {
			var v = $el.attr(attr);
			$el.attr('data-o_' + attr, v ? v : '');
		}
	}

	function setBigAttrs($link, $img, url) {
		if ($link.length) {
			keepOriginal($link, 'href');
			$link.attr('href', url);
		}
		if ($img.length && $img.attr('data-large_image') !== undefined) {
			keepOriginal($img, 'data-large_image');
			$img.attr('data-large_image', url);
		}
	}

	function restoreBig($figure) {
		var fig = $figure[0];
		if (!fig || !fig.hasAttribute('data-swil-orig-href')) {
			return;
		}
		var $link = $figure.find('a').first();
		var $img = $figure.find('img').not('.swil-layer img').first();
		if (isBlob($link.attr('href'))) {
			$link.attr('href', fig.getAttribute('data-swil-orig-href'));
		}
		if (isBlob($img.attr('data-large_image'))) {
			$img.attr('data-large_image', fig.getAttribute('data-swil-orig-large'));
		}
		fig.removeAttribute('data-swil-orig-href');
		fig.removeAttribute('data-swil-orig-large');
	}

	function updateBig($figure, src) {
		if (!LIGHTBOX || !$figure.length || !window.Promise || !window.URL || !URL.createObjectURL) {
			return;
		}
		var fig = $figure[0];
		var $link = $figure.find('a').first();
		var $img = $figure.find('img').not('.swil-layer img').first();

		/* Foto original actual: si el tema la ha cambiado (otra variación), se toma la nueva */
		var href = $link.attr('href') || '';
		var large = $img.attr('data-large_image') || '';
		if (!isBlob(href) && !isBlob(large)) {
			fig.setAttribute('data-swil-orig-href', href);
			fig.setAttribute('data-swil-orig-large', large);
		}

		if (!src) {
			restoreBig($figure);
			big.key = '';
			return;
		}

		var photo = fig.getAttribute('data-swil-orig-large') || fig.getAttribute('data-swil-orig-href') || $img.attr('src') || '';
		if (!photo || isBlob(photo)) {
			return;
		}

		var z = C.zone || {};
		var zone = { x: z.x !== undefined ? z.x : 50, y: z.y !== undefined ? z.y : 35, w: z.w !== undefined ? z.w : 18 };
		var key = photo + '|' + src + '|' + zone.x + '|' + zone.y + '|' + zone.w;

		if (key === big.key && big.url) {
			setBigAttrs($link, $img, big.url);
			return;
		}
		if (key === big.loading) {
			return;
		}
		big.loading = key;

		buildComposite(photo, src, zone).then(function (url) {
			if (big.loading !== key) {
				URL.revokeObjectURL(url);
				return;
			}
			big.loading = '';
			if (big.url) {
				URL.revokeObjectURL(big.url);
			}
			big.key = key;
			big.url = url;
			if (lastSrc === src && getFigure()[0] === fig) {
				setBigAttrs($link, $img, url);
			}
		}).catch(function () {
			/* Imagen de otro dominio sin permiso, por ejemplo: se queda la foto normal */
			big.loading = '';
		});
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

		var changed = src && src !== lastSrc;
		lastSrc = src;

		updateBig(getFigure(), src);

		if (changed) {
			goToFirstSlide();
			if (userPick && SCROLL) {
				scrollToGallery();
			}
		}
		userPick = false;
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

		$(document).on('click change', ADDON + ' ' + OPTION + ', ' + ADDON + ' ' + INPUT, function (e) {
			if (e.originalEvent) {
				userPick = true;
			}
			setTimeout(schedule, 0);
		});
		$(document).on('found_variation reset_data show_variation hide_variation', 'form.variations_form', function () {
			setTimeout(schedule, 60);
			setTimeout(schedule, 400);
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
