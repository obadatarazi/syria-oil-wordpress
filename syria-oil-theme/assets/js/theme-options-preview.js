/**
 * Syria Oil Theme Options — live previews (CSF admin).
 */
(function ($) {
	'use strict';

	var cfg = window.syriaOilOptionsPreview || { defaults: {} };

	function fieldVal(fieldId, fallback) {
		var $input = $('[data-depend-id="' + fieldId + '"]').filter('input, select, textarea').first();
		if (!$input.length) {
			$input = $('input[name$="[' + fieldId + ']"], select[name$="[' + fieldId + ']"]').first();
		}
		var v = $input.length ? String($input.val() || '').trim() : '';
		return v || fallback;
	}

	function readColor(fieldId, fallback) {
		var v = fieldVal(fieldId, fallback);
		return /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(v) ? v : fallback;
	}

	function readNumber(fieldId, fallback) {
		var n = parseInt(fieldVal(fieldId, String(fallback)), 10);
		return isNaN(n) ? fallback : n;
	}

	function mediaUrl(fieldId, dataFallback) {
		var $wrap = $('[data-depend-id="' + fieldId + '"]').closest('.csf-field');
		if (!$wrap.length) {
			$wrap = $('.csf-field-media').has('input[name$="[' + fieldId + ']"]');
		}
		var $img = $wrap.find('.csf--preview img, .csf-image-preview img').first();
		if ($img.length && $img.attr('src')) {
			return $img.attr('src');
		}
		return dataFallback || '';
	}

	function fontStack(name) {
		if (!name || name === 'system-ui') {
			return 'system-ui, -apple-system, "Segoe UI", Tahoma, sans-serif';
		}
		var pairs = {
			'IBM Plex Sans Arabic': '"IBM Plex Sans Arabic", "IBM Plex Sans", sans-serif',
			'IBM Plex Sans': '"IBM Plex Sans", "IBM Plex Sans Arabic", sans-serif',
			'Noto Kufi Arabic': '"Noto Kufi Arabic", "IBM Plex Sans", sans-serif',
			'DM Sans': '"DM Sans", "IBM Plex Sans", sans-serif'
		};
		return pairs[name] || '"' + name + '", sans-serif';
	}

	function bannerEdgeColor(primary, secondary, accent, ink, depth) {
		var preset = fieldVal('banner_edge_color_preset', 'primary');
		var custom = readColor('banner_edge_color_custom', depth || primary);
		var map = {
			primary: primary,
			depth: depth || custom,
			secondary: secondary,
			accent: accent,
			ink: ink,
			custom: custom
		};
		return map[preset] || primary;
	}

	function setPreviewCss($el, vars) {
		if (!$el.length) {
			return;
		}
		var style = $el.attr('style') || '';
		Object.keys(vars).forEach(function (key) {
			style = style.replace(new RegExp('--' + key + ':[^;]+;?', 'g'), '');
			style += '--' + key + ':' + vars[key] + ';';
		});
		$el.attr('style', style);
	}

	function applyColorsPreview() {
		var $pane = $('#syria-oil-options-preview');
		if (!$pane.length) {
			return;
		}
		var d = cfg.defaults || {};
		var primary = readColor('color_primary', d.color_primary || '#007a3d');
		var secondary = readColor('color_secondary', d.color_secondary || '#f7f9fb');
		var accent = readColor('color_accent', d.color_accent || '#ce1126');
		var depth = readColor('color_depth', d.color_depth || '#321010');
		var ink = readColor('color_ink', d.color_ink || '#0f1419');
		var scale = fieldVal('font_scale', 'medium');
		var sizes = { small: '15px', medium: '16px', large: '18px' };
		var heading = fieldVal('font_heading', 'Noto Kufi Arabic');
		var body = fieldVal('font_body', 'IBM Plex Sans Arabic');

		setPreviewCss($pane, {
			'preview-primary': primary,
			'preview-secondary': secondary,
			'preview-accent': accent,
			'preview-depth': depth,
			'preview-ink': ink,
			'preview-root': sizes[scale] || '16px',
			'preview-font-heading': fontStack(heading),
			'preview-font-body': fontStack(body)
		});

		// Sync admin shell accent from primary.
		$('.syria-oil-csf.csf-options').css('--so-admin-primary', primary);
		$('.so-admin-dashboard').css('--so-dash-primary', primary);
	}

	function applyBrandingPreview() {
		var $pane = $('#syria-oil-branding-preview');
		if (!$pane.length) {
			return;
		}
		var device = $pane.find('.so-preview__device-tabs button.is-active').data('device') || 'desktop';
		var isMobile = device === 'mobile';
		var headerH = readNumber(isMobile ? 'logo_mobile_height' : 'logo_desktop_height', isMobile ? 48 : 72);
		var headerW = readNumber(isMobile ? 'logo_mobile_max_width' : 'logo_desktop_max_width', isMobile ? 160 : 280);
		var footerH = readNumber(isMobile ? 'logo_footer_mobile_height' : 'logo_footer_height', isMobile ? 40 : 56);
		var footerW = readNumber(isMobile ? 'logo_footer_mobile_max_width' : 'logo_footer_max_width', isMobile ? 140 : 220);
		var navSize = readNumber('header_nav_font_size', 14) + 'px';
		var logo = mediaUrl('logo_primary', $pane.data('logo') || cfg.logoUrl || '');
		var logoDark = mediaUrl('logo_dark', $pane.data('logo-dark') || cfg.logoDarkUrl || logo);

		$pane.find('.so-preview__branding-mock').attr('data-device', device);
		$pane.find('.so-preview__logo--header').attr({ src: logo, height: headerH }).css({ maxHeight: headerH + 'px', maxWidth: headerW + 'px' });
		$pane.find('.so-preview__logo--footer').attr({ src: logoDark || logo, height: footerH }).css({ maxHeight: footerH + 'px', maxWidth: footerW + 'px' });
		$pane.find('.so-preview__logo-wrap--header').toggleClass('has-image', !!logo);
		$pane.find('.so-preview__logo-wrap--footer').toggleClass('has-image', !!(logoDark || logo));

		setPreviewCss($pane, { 'preview-nav-size': navSize });
	}

	function applyTypographyPreview() {
		var $pane = $('#syria-oil-typography-preview');
		if (!$pane.length) {
			return;
		}
		var d = cfg.defaults || {};
		var scale = fieldVal('font_scale', 'medium');
		var sizes = { small: '15px', medium: '16px', large: '18px' };
		var heading = fieldVal('font_heading', 'Noto Kufi Arabic');
		var body = fieldVal('font_body', 'IBM Plex Sans Arabic');
		var navSize = readNumber('header_nav_font_size', 14) + 'px';

		setPreviewCss($pane, {
			'preview-root': sizes[scale] || '16px',
			'preview-font-heading': fontStack(heading),
			'preview-font-body': fontStack(body),
			'preview-nav-size': navSize,
			'preview-primary': readColor('color_primary', d.color_primary || '#007a3d'),
			'preview-accent': readColor('color_accent', d.color_accent || '#ce1126'),
			'preview-ink': readColor('color_ink', d.color_ink || '#0f1419')
		});
	}

	function applyLayoutPreview() {
		var $pane = $('#syria-oil-layout-preview');
		if (!$pane.length) {
			return;
		}
		var mode = fieldVal('hero_featured_sidebar', 'below');
		$pane.find('.so-preview__layout-card').removeClass('is-active');
		$pane.find('.so-preview__layout-card[data-mode="' + mode + '"]').addClass('is-active');

		var d = cfg.defaults || {};
		setPreviewCss($pane, {
			'preview-primary': readColor('color_primary', d.color_primary || '#007a3d'),
			'preview-accent': readColor('color_accent', d.color_accent || '#ce1126')
		});
	}

	function applyBannerPreview() {
		var $pane = $('#syria-oil-banner-preview');
		if (!$pane.length) {
			return;
		}
		var d = cfg.defaults || {};
		var primary = readColor('color_primary', d.color_primary || '#007a3d');
		var secondary = readColor('color_secondary', d.color_secondary || '#f7f9fb');
		var accent = readColor('color_accent', d.color_accent || '#ce1126');
		var depth = readColor('color_depth', d.color_depth || '#321010');
		var ink = readColor('color_ink', d.color_ink || '#0f1419');
		var mode = fieldVal('banner_edge_mode', 'none');
		var edge = bannerEdgeColor(primary, secondary, accent, ink, depth);
		var flag = mediaUrl('banner_flag_image', $pane.data('flag') || cfg.flagUrl || '');
		var emblemOn = fieldVal('show_syrian_emblem', '0') === '1' || fieldVal('show_syrian_emblem', '') === 'true';
		var emblem = mediaUrl('banner_emblem_image', $pane.data('emblem') || cfg.emblemUrl || '');
		var $edge = $pane.find('.so-preview__banner-edge');
		var showColor = mode === 'color' || mode === 'both';
		var showImage = mode === 'image' || mode === 'both';

		$edge.removeClass('has-image').css('background-image', '');
		if (showImage && flag) {
			$edge.addClass('has-image');
		}

		setPreviewCss($pane, {
			'preview-primary': primary,
			'preview-ink': ink,
			'preview-banner-edge': edge,
			'preview-banner-edge-opacity': showColor || showImage ? '1' : '0',
			'preview-banner-flag': flag ? 'url("' + flag.replace(/"/g, '') + '")' : 'none',
			'preview-banner-emblem': emblem ? 'url("' + emblem.replace(/"/g, '') + '")' : 'none',
			'preview-banner-emblem-opacity': emblemOn && emblem ? '0.35' : '0'
		});
	}

	function isSwitcherOn(fieldId) {
		var v = fieldVal(fieldId, '0');
		return v === '1' || v === 'true' || v === 'on';
	}

	function applyTopBarPreview() {
		var $pane = $('#syria-oil-topbar-preview');
		if (!$pane.length) {
			return;
		}
		var show = isSwitcherOn('show_top_bar');
		var text = fieldVal('top_bar_text', '');
		var showDate = isSwitcherOn('show_top_bar_date');
		var linkLabel = fieldVal('top_bar_link_label', '');
		var linkUrl = fieldVal('top_bar_link_url', '');
		var sampleDate = $pane.data('sample-date') || '';
		var d = cfg.defaults || {};

		$pane.find('.so-preview__topbar-stage').toggleClass('is-hidden', !show);
		$pane.find('.so-preview__topbar-text').text(text || 'رسالة الشريط العلوي').toggle(!!text || show);
		$pane.find('.so-preview__topbar-date').text(sampleDate).toggle(showDate);
		var $link = $pane.find('.so-preview__topbar-link');
		if (linkLabel && linkUrl) {
			$link.text(linkLabel).attr('href', linkUrl).show();
		} else if (linkLabel) {
			$link.text(linkLabel).attr('href', '#').show();
		} else {
			$link.hide();
		}

		setPreviewCss($pane, {
			'preview-primary': readColor('color_primary', d.color_primary || '#007a3d'),
			'preview-ink': readColor('color_ink', d.color_ink || '#0f1419')
		});
	}

	function parseSponsors(raw) {
		return String(raw || '')
			.split(/\r?\n/)
			.map(function (line) {
				return line.trim();
			})
			.filter(Boolean)
			.map(function (line) {
				var parts = line.split('|');
				return (parts[0] || '').trim();
			})
			.filter(Boolean);
	}

	function applyFooterPreview() {
		var $pane = $('#syria-oil-footer-preview');
		if (!$pane.length) {
			return;
		}
		var d = cfg.defaults || {};
		var fd = cfg.footerDefaults || {};
		var cols = fieldVal('footer_columns', $pane.data('cols') || '3');
		var tagline = fieldVal('footer_tagline', '');
		var explore = fieldVal('footer_explore_title', '') || fd.explore || 'Explore';
		var cats = fieldVal('footer_cats_title', '') || fd.cats || 'Categories';
		var contact = fieldVal('footer_contact_title', '') || fd.contact || 'Contact';
		var sponsorsTitle = fieldVal('footer_sponsors_title', '') || fd.sponsors || '';
		var bottom = fieldVal('footer_bottom_text', '');
		var sponsors = parseSponsors(fieldVal('footer_sponsors', ''));
		var pumpOn = isSwitcherOn('show_footer_oil_pump');
		var logo = mediaUrl('logo_dark', $pane.data('logo') || cfg.logoDarkUrl || cfg.logoUrl || '');

		$pane.find('.so-preview__footer-grid')
			.removeClass('so-preview__footer-grid--3 so-preview__footer-grid--4')
			.addClass('so-preview__footer-grid--' + cols);
		$pane.find('.so-preview__footer-col--widget').toggle(cols === '4');
		$pane.find('.so-preview__footer-tagline').text(tagline || 'شعار الموقع أو وصف قصير');
		$pane.find('.so-preview__footer-explore').text(explore);
		$pane.find('.so-preview__footer-cats').text(cats);
		$pane.find('.so-preview__footer-contact').text(contact);
		$pane.find('.so-preview__footer-sponsors-title').text(sponsorsTitle);
		$pane.find('.so-preview__footer-sponsors-wrap').toggle(!!sponsorsTitle || sponsors.length > 0);
		var $row = $pane.find('.so-preview__footer-sponsors-row').empty();
		if (sponsors.length) {
			sponsors.slice(0, 4).forEach(function (name) {
				$row.append($('<span/>').text(name));
			});
		} else {
			$row.append('<span>…</span><span>…</span><span>…</span>');
		}
		$pane.find('.so-preview__footer-bottom-text').text(bottom || '© …');
		$pane.find('.so-preview__footer-pump').toggle(pumpOn);
		$pane.find('.so-preview__footer-logo').attr('src', logo);
		$pane.find('.so-preview__footer-logo-wrap').toggleClass('has-image', !!logo);

		setPreviewCss($pane, {
			'preview-primary': readColor('color_primary', d.color_primary || '#007a3d')
		});
	}

	function applyAll() {
		applyColorsPreview();
		applyBrandingPreview();
		applyTypographyPreview();
		applyLayoutPreview();
		applyBannerPreview();
		applyTopBarPreview();
		applyFooterPreview();
	}

	$(function () {
		applyAll();

		$(document).on(
			'change input csf-change',
			'.csf-field-color input, .csf-field-select select, .csf-field-radio input, .csf-field-number input, .csf-field-switcher input, .csf-field-media input, .csf-field-text input, .csf-field-textarea textarea',
			applyAll
		);

		$(document).on('click', '#syria-oil-branding-preview .so-preview__device-tabs button', function () {
			var $btn = $(this);
			$btn.siblings().removeClass('is-active');
			$btn.addClass('is-active');
			applyBrandingPreview();
		});

		// CSF media picker updates preview asynchronously.
		setInterval(applyAll, 1500);
	});
})(jQuery);
