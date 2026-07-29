<?php
use EasyCommerce\Helpers\Utility;
use EasyCommerce\Models\Location;
use EasyCommerce\Models\Cart;
use EasyCommerce\Models\Order;
use EasyCommerce\Models\Customer;
use EasyCommerce\Traits\Cache;
use EasyCommerce\Models\Product;
use EasyCommerce\Models\Product_Variation;
use EasyCommerce\Models\Coupon as Coupon_Model;

function easycommerce_dev_store( $path = '' ) {
	$home = defined( 'EASYCOMMERCE_STORE' ) ? untrailingslashit( EASYCOMMERCE_STORE ) : 'https://my.easycommerce.dev';
	$path = ltrim( $path, '/' );

	return "{$home}/{$path}";
}

function easycommerce_dev_docs( $path = '' ) {
	$home = defined( 'EASYCOMMERCE_DOCS' ) ? untrailingslashit( EASYCOMMERCE_DOCS ) : 'https://easycommerce.dev/docs';
	$path = ltrim( $path, '/' );

	return "{$home}/{$path}";
}

/**
 * Whether a Generative AI feature is enabled in settings.
 *
 * Reads the raw option array directly instead of Utility::get_option(): that
 * helper treats a stored "0" as empty and falls back to its default, which would
 * make the disabled state unreachable for a default-on checkbox. Defaults to
 * enabled when the feature key has never been saved (matches the field defaults
 * in app/Config/settings.php → ai → generative-ai).
 *
 * @param string $feature Feature key: 'text_generator', 'image_generator',
 *                        'template_generator', 'attribute_generator'.
 * @return bool
 */
function easycommerce_is_ai_feature_enabled( $feature ) {
	$generative = get_option( 'easycommerce-ai-generative-ai' );

	if ( ! is_array( $generative ) || ! isset( $generative[ $feature ] ) ) {
		return true;
	}

	return '0' !== (string) $generative[ $feature ];
}

/**
 * Whether the stock availability badge is enabled in store settings.
 *
 * Reads the option array directly rather than Utility::get_option(), which
 * treats a saved "off" value as empty. Defaults to enabled on a fresh install
 * where the setting has not been saved yet.
 *
 * @return bool
 */
function easycommerce_is_stock_badge_enabled() {
	$store = get_option( 'easycommerce-general-store' );

	if ( ! is_array( $store ) || ! isset( $store['stock-badge'] ) ) {
		return true;
	}

	return '0' !== (string) $store['stock-badge'];
}

/**
 * Returns the home URL of the WordPress site.
 *
 * @param string $path    Optional. Path relative to the home URL.
 * @param int    $blog_id Optional. ID of the blog in a multisite installation.
 *
 * @return string Home URL with optional path appended.
 */
function easycommerce_home_url( $path = '', $blog_id = null ) {
	return get_home_url( $blog_id, $path );
}

/**
 * Returns the EasyCommerce community space URL.
 *
 * Single source for every community link in the admin (dashboard card, header,
 * setup wizard, help page), surfaced to the SPA as `EASYCOMMERCE.community_url`.
 *
 * @return string Community URL.
 */
function easycommerce_community_url() {
	return apply_filters( 'easycommerce_community_url', 'https://community.codexpert.io/c/space/easycommerce' );
}

function easycommerce_shop_page( $url = false ) {
	$page_id = Utility::get_option( 'general', 'store', 'shop' );

	if ( $url !== false ) {
		return get_permalink( $page_id );
	}

	return $page_id;
}

function easycommerce_checkout_page( $url = false ) {
	$page_id = Utility::get_option( 'general', 'store', 'checkout' );

	if ( $url !== false ) {
		return get_permalink( $page_id );
	}

	return $page_id;
}

function easycommerce_dashboard_page( $url = false ) {
	$page_id = Utility::get_option( 'general', 'store', 'dashboard' );

	if ( $url !== false ) {
		return get_permalink( $page_id );
	}

	return $page_id;
}

function easycommerce_registration_page( $url = false ) {
	$page_id = Utility::get_option( 'general', 'store', 'registration' );

	if ( $url !== false ) {
		return get_permalink( $page_id );
	}

	return $page_id;
}

function easycommerce_terms_of_service_page( $url = false ) {
	$page_id = Utility::get_option( 'general', 'store', 'terms-of-service' );

	if ( $url !== false ) {
		return get_permalink( $page_id );
	}

	return $page_id;
}

function easycommerce_privacy_policy_page( $url = false ) {
	$page_id = Utility::get_option( 'general', 'store', 'privacy-policy' );

	if ( $url !== false ) {
		return get_permalink( $page_id );
	}

	return $page_id;
}

function easycommerce_reset_password_page( $url = false ) {
	$page_id = Utility::get_option( 'general', 'store', 'reset-password' );

	if ( $url !== false ) {
		return get_permalink( $page_id );
	}

	return $page_id;
}

function easycommerce_payment_page( $url = false ) {
	$page_id = Utility::get_option( 'general', 'store', 'payment' );

	if ( $url !== false ) {
		return get_permalink( $page_id );
	}

	return $page_id;
}

/**
 * Ensure the core store pages (dashboard, shop, checkout, payment) exist.
 *
 * Idempotent: reuses any already-created pages stored in the
 * `easycommerce-general-store` option and only creates the missing ones, so
 * it is safe to run on install (via the background cron) and again from the
 * setup wizard without producing duplicate pages.
 *
 * @param array $overrides Optional page overrides keyed by slug (page ID or 'create').
 * @return array The resolved store option group.
 */
function easycommerce_ensure_store_pages( $overrides = array() ) {
	$group = get_option( 'easycommerce-general-store', array() );

	if ( ! is_array( $group ) ) {
		$group = array();
	}

	// Apply caller-supplied overrides (e.g. from the setup wizard).
	if ( is_array( $overrides ) ) {
		foreach ( $overrides as $key => $value ) {
			$group[ $key ] = $value;
		}
	}

	$page_template = ! empty( $group['page-template'] ) ? $group['page-template'] : 'full-width-layout.php';

	$pages = array(
		'dashboard' => array(
			'title'   => 'Dashboard',
			'content' => '[easycommerce-dashboard]',
		),
		'shop'      => array(
			'title'   => 'Shop',
			'content' => '<!-- wp:easycommerce/template-2 {"ProductPerPage":9,"columns":3} /-->',
		),
		'checkout'  => array(
			'title'   => 'Checkout',
			'content' => '[easycommerce-checkout]',
		),
		'payment'   => array(
			'title'   => 'Payment',
			'content' => '[easycommerce-payment]',
		),
	);

	foreach ( $pages as $slug => $page ) {
		$current = $group[ $slug ] ?? '';

		// Reuse an existing, valid page.
		if ( ! empty( $current ) && 'create' !== $current && get_post( $current ) ) {
			continue;
		}

		$page_id = Utility::create_post(
			array(
				'type'    => 'page',
				'title'   => $page['title'],
				'content' => $page['content'],
			)
		);

		if ( $page_id ) {
			update_post_meta( $page_id, '_wp_page_template', $page_template );
			$group[ $slug ] = $page_id;
		}
	}

	update_option( 'easycommerce-general-store', $group );

	return $group;
}

/**
 * Resolve a store-pattern image URL (hero backgrounds, promo/category art).
 *
 * Centralised + filterable so the image source can be switched between the CDN
 * and a locally-bundled directory without touching pattern markup. Default is
 * the CDN; filter `easycommerce_pattern_image_base` to point at bundled assets
 * (e.g. EASYCOMMERCE_ASSETS_URL . 'public/img/patterns/').
 *
 * @param string $file Image filename (e.g. gadget-hero-1.jpg); see PATTERN-IMAGES.md.
 * @return string Full image URL.
 */
function easycommerce_pattern_image( $file ) {
	$base = apply_filters( 'easycommerce_pattern_image_base', 'https://cdn.easycommerce.dev/images/patterns/' );

	return trailingslashit( $base ) . ltrim( $file, '/' );
}

/**
 * Returns the ready-made store designs registry.
 *
 * @return array Design id => design definition (see app/Config/store-designs.php).
 */
function easycommerce_store_designs() {
	global $easycommerce_store_designs;

	return is_array( $easycommerce_store_designs ) ? $easycommerce_store_designs : array();
}

/**
 * Returns a single store design definition, or null.
 *
 * @param string $design_id Design id, e.g. grocery|gadget|furniture.
 * @return array|null
 */
function easycommerce_get_store_design( $design_id ) {
	$designs = easycommerce_store_designs();

	return isset( $designs[ $design_id ] ) ? $designs[ $design_id ] : null;
}

/**
 * Expand nested `wp:pattern` references into their registered block markup.
 *
 * Page patterns are composed of section patterns via `wp:pattern` references.
 * Expanding them into a page's content produces self-contained, individually
 * editable markup (rather than opaque pattern-reference blocks).
 *
 * @param string $content Block markup that may contain wp:pattern references.
 * @param int    $depth   Internal recursion guard.
 * @return string Expanded block markup.
 */
function easycommerce_expand_pattern( $content, $depth = 0 ) {
	if ( $depth > 5 || false === strpos( $content, 'wp:pattern' ) ) {
		return $content;
	}

	if ( ! class_exists( 'WP_Block_Patterns_Registry' ) ) {
		return $content;
	}

	$registry = WP_Block_Patterns_Registry::get_instance();

	$expanded = preg_replace_callback(
		'/<!--\s*wp:pattern\s+(\{.*?\})\s*\/-->/s',
		function ( $matches ) use ( $registry, $depth ) {
			$attrs = json_decode( $matches[1], true );
			$slug  = isset( $attrs['slug'] ) ? $attrs['slug'] : '';

			if ( '' === $slug || ! $registry->is_registered( $slug ) ) {
				return $matches[0];
			}

			$pattern = $registry->get_registered( $slug );

			return easycommerce_expand_pattern( $pattern['content'], $depth + 1 );
		},
		$content
	);

	return null === $expanded ? $content : $expanded;
}

/**
 * Apply a ready-made store design.
 *
 * Creates (or idempotently updates) the design's home and shop pages from its
 * page patterns, aligns the shop grid to the design's shop
 * template, persists the applied design id and its colour/typography preset,
 * and tags the created pages as design content so they can be removed cleanly.
 *
 * Does NOT set the static front page — that is handled at wizard completion
 * (#3174), with an "ask before overwrite" prompt. Theme-aware preset rendering
 * is handled in #3175; here we only persist the preset and fire an action.
 *
 * @param string $design_id Design id, e.g. grocery|gadget|furniture.
 * @return array|false The updated `easycommerce-general-store` group, or false if unknown.
 */
function easycommerce_apply_store_design( $design_id ) {
	$design = easycommerce_get_store_design( $design_id );

	if ( ! $design ) {
		return false;
	}

	$group = get_option( 'easycommerce-general-store', array() );
	if ( ! is_array( $group ) ) {
		$group = array();
	}

	$page_template = ! empty( $group['page-template'] ) ? $group['page-template'] : 'full-width-layout.php';
	$shop_template = ! empty( $design['shop_template'] ) ? $design['shop_template'] : 'template-2';

	$titles = array(
		'home' => __( 'Home', 'easycommerce' ),
		'shop' => __( 'Shop', 'easycommerce' ),
	);

	foreach ( $design['pages'] as $slug => $pattern_slug ) {
		$content = easycommerce_expand_pattern(
			'<!-- wp:pattern {"slug":"' . $pattern_slug . '"} /-->'
		);

		// Align the shop grid to this design's shop template (patterns ship template-2).
		if ( 'shop' === $slug && 'template-2' !== $shop_template ) {
			$content = str_replace(
				'easycommerce/template-2',
				'easycommerce/' . $shop_template,
				$content
			);
		}

		$current = $group[ $slug ] ?? '';

		if ( ! empty( $current ) && 'create' !== $current && get_post( $current ) ) {
			// Idempotent update — refresh content, never duplicate.
			wp_update_post(
				array(
					'ID'           => $current,
					'post_content' => $content,
				)
			);
			$page_id = $current;
		} else {
			$page_id = Utility::create_post(
				array(
					'type'    => 'page',
					'title'   => $titles[ $slug ] ?? ucfirst( $slug ),
					'content' => $content,
				)
			);

			if ( $page_id ) {
				$group[ $slug ] = $page_id;
			}
		}

		if ( $page_id ) {
			update_post_meta( $page_id, '_wp_page_template', $page_template );
			update_post_meta( $page_id, '_easycommerce_design_page', $design_id );
		}
	}

	update_option( 'easycommerce-general-store', $group );
	update_option( 'easycommerce_store_design', $design_id );
	update_option( 'easycommerce_store_design_preset', $design['preset'] );

	/**
	 * Fires after a store design is applied.
	 *
	 * Theme compatibility (#3175) hooks this to render the colour/typography
	 * preset via theme.json tokens (block themes) or scoped CSS (classic themes).
	 *
	 * @param string $design_id The applied design id.
	 * @param array  $design    The design definition.
	 * @param array  $group     The updated store pages group.
	 */
	do_action( 'easycommerce_store_design_applied', $design_id, $design, $group );

	return $group;
}

/**
 * Whether the active theme is a block (FSE) theme.
 *
 * @return bool
 */
function easycommerce_is_block_theme() {
	return function_exists( 'wp_is_block_theme' ) && wp_is_block_theme();
}

/**
 * Whether the active theme can render the store designs acceptably.
 *
 * Both block and classic themes are supported (block themes use theme.json
 * tokens; classic themes use the scoped store-patterns.css fallback). This is a
 * hook point so an add-on can flag a specific broken theme and let the wizard
 * (#3174) show an honest notice instead of shipping something broken.
 *
 * @return bool
 */
function easycommerce_is_theme_supported() {
	/**
	 * Filter whether the active theme supports the store designs.
	 *
	 * @param bool   $supported  Default true.
	 * @param string $stylesheet Active theme stylesheet slug.
	 */
	return (bool) apply_filters( 'easycommerce_theme_supported', true, get_stylesheet() );
}

/**
 * Returns the colour/typography preset of the applied store design, or null.
 *
 * @return array|null
 */
function easycommerce_store_design_preset() {
	$preset = get_option( 'easycommerce_store_design_preset' );

	return is_array( $preset ) ? $preset : null;
}

/**
 * Default design tokens, merged beneath a design's own `tokens`.
 *
 * Guarantees the CSS generator always has a complete token set — even for a
 * custom design registered via the `easycommerce_store_designs` filter without
 * a full `tokens` array. These defaults reproduce the neutral pre-token look
 * (medium radius, subtle shadow, 8px rhythm) so custom designs render sanely.
 *
 * @return array
 */
function easycommerce_store_design_default_tokens() {
	return array(
		'radius'        => array( 'card' => '8px', 'button' => '6px', 'image' => '8px' ),
		'shadow'        => array( 'card' => '0 1px 2px rgba(17,17,17,0.06)', 'hover' => '0 10px 28px -14px rgba(17,17,17,0.28)' ),
		'card'          => array( 'fill' => '#FFFFFF', 'border' => '1px solid #ECECEC' ),
		'space'         => array( 8, 16, 24, 32, 48, 64, 80, 96 ),
		'section'       => array( 'rhythm' => 'gap', 'divider' => 'none', 'alt_bg' => 'transparent' ),
		'button'        => array( 'radius' => '6px', 'transform' => 'none', 'tracking' => '0', 'weight' => '600', 'size' => '15px', 'pad' => '12px 24px', 'hover' => 'darken' ),
		'heading'       => array( 'weight' => '700', 'tracking' => '0', 'transform' => 'none', 'h2' => '32px', 'line' => '1.2' ),
		'product_title' => '',
	);
}

/**
 * Build the scoped CSS that renders the applied design's full token system onto
 * the store patterns — a distinct design system per design, not a re-skin.
 *
 * Emits: an 8-step spacing scale (redefining the `--wp--preset--spacing--*`
 * tokens the patterns consume, so rhythm changes with no pattern edits), radius
 * and elevation custom properties, the button system, product-card treatment,
 * section transitions and heading scale. Colours/fonts included.
 *
 * Scoped to `.ec-pattern` (every pattern's root) and `.ec-product-collection`
 * (our grid block's wrapper) — both EasyCommerce-only, so the skin never leaks
 * onto the native shop archive or the rest of the theme. Loads on both block
 * and classic themes; the applied design is the single source of truth, so
 * `.ec-pattern` scoping is sufficient (only one design is live per site).
 *
 * @param string $scope Optional selector prefix (e.g. `.editor-styles-wrapper `
 *                      to mirror the design inside the block editor canvas).
 * @return string CSS, or '' when no design is applied.
 */
function easycommerce_store_design_preset_css( $scope = '' ) {
	// Prefer the live registry design (token tweaks apply without re-apply);
	// fall back to the stored preset snapshot for older/custom applies.
	$design_id = get_option( 'easycommerce_store_design' );
	$design    = $design_id ? easycommerce_get_store_design( $design_id ) : null;
	$preset    = ( $design && ! empty( $design['preset'] ) ) ? $design['preset'] : easycommerce_store_design_preset();

	if ( empty( $preset ) ) {
		return '';
	}

	$colors = isset( $preset['colors'] ) ? $preset['colors'] : array();
	$fonts  = isset( $preset['typography'] ) ? $preset['typography'] : array();

	// Shallow-merge the design's tokens over the defaults (one level per group).
	// Associative groups (radius, button…) merge; numeric lists (space) and
	// scalars replace wholesale — array_merge on a list would append, not
	// overwrite, silently keeping the default scale.
	$tokens = easycommerce_store_design_default_tokens();
	if ( ! empty( $preset['tokens'] ) && is_array( $preset['tokens'] ) ) {
		foreach ( $preset['tokens'] as $k => $v ) {
			$is_assoc = is_array( $v ) && array_keys( $v ) !== range( 0, count( $v ) - 1 );

			$tokens[ $k ] = ( $is_assoc && isset( $tokens[ $k ] ) && is_array( $tokens[ $k ] ) )
				? array_merge( $tokens[ $k ], $v )
				: $v;
		}
	}

	// --- sanitizers -------------------------------------------------------
	$color = function ( $value ) {
		$value = is_string( $value ) ? trim( $value ) : '';
		if ( 'transparent' === $value || 'none' === $value ) {
			return $value;
		}
		return ( $value && sanitize_hex_color( $value ) ) ? $value : '';
	};
	$font = function ( $value ) {
		$value = is_string( $value ) ? trim( $value ) : '';
		return preg_replace( '/[^A-Za-z0-9 ,"\'\-]/', '', $value );
	};
	// Composite CSS values (shadow, border shorthand, padding): strip anything
	// that could break out of a declaration. Note ! is stripped — the only
	// !important in the output is emitted as a literal by this function.
	$css_val = function ( $value ) {
		return preg_replace( '/[^0-9a-zA-Z.,()#%\/\-\s]/', '', (string) $value );
	};
	// Strict single length/number/keyword token.
	$len = function ( $value ) {
		$value = preg_replace( '/[^0-9a-zA-Z.%\-]/', '', (string) $value );
		return '' === $value ? '0' : $value;
	};
	$keyword = function ( $value, $allowed, $fallback ) {
		return in_array( $value, $allowed, true ) ? $value : $fallback;
	};

	// --- resolved values --------------------------------------------------
	$accent  = $color( $colors['accent'] ?? '' ) ?: '#7351FD';
	$text    = $color( $colors['text'] ?? '' ) ?: '#1f1f29';
	$surface = $color( $colors['surface'] ?? '' );
	$border  = $color( $colors['border'] ?? '' );
	$heading = $font( $fonts['heading'] ?? '' );
	$body    = $font( $fonts['body'] ?? '' );

	$r_card = $len( $tokens['radius']['card'] ?? '8px' );
	$r_btn  = $len( $tokens['button']['radius'] ?? ( $tokens['radius']['button'] ?? '6px' ) );
	$r_img  = $len( $tokens['radius']['image'] ?? '8px' );

	$sh_card  = $css_val( $tokens['shadow']['card'] ?? 'none' ) ?: 'none';
	$sh_hover = $css_val( $tokens['shadow']['hover'] ?? 'none' ) ?: 'none';

	$card_fill   = $color( $tokens['card']['fill'] ?? '' ) ?: 'transparent';
	$card_border = $css_val( $tokens['card']['border'] ?? 'none' ) ?: 'none';

	$b_tr  = $keyword( $tokens['button']['transform'] ?? 'none', array( 'none', 'uppercase', 'lowercase', 'capitalize' ), 'none' );
	$b_trk = $len( $tokens['button']['tracking'] ?? '0' );
	$b_wt  = $len( $tokens['button']['weight'] ?? '600' );
	$b_sz  = $len( $tokens['button']['size'] ?? '15px' );
	$b_pad = $css_val( $tokens['button']['pad'] ?? '12px 24px' );
	$b_hov = $keyword( $tokens['button']['hover'] ?? 'darken', array( 'darken', 'lift', 'invert' ), 'darken' );

	$h_wt  = $len( $tokens['heading']['weight'] ?? '700' );
	$h_trk = $len( $tokens['heading']['tracking'] ?? '0' );
	$h_tr  = $keyword( $tokens['heading']['transform'] ?? 'none', array( 'none', 'uppercase', 'lowercase', 'capitalize' ), 'none' );
	$h_h2  = $len( $tokens['heading']['h2'] ?? '32px' );
	$h_ln  = $len( $tokens['heading']['line'] ?? '1.2' );

	$sec_div = $css_val( $tokens['section']['divider'] ?? 'none' );
	$sec_alt = $color( $tokens['section']['alt_bg'] ?? '' );

	// --- selector prefixing + tiny rule helper ----------------------------
	$p    = is_string( $scope ) ? $scope : '';
	$rule = function ( $selectors, $decl ) use ( $p ) {
		$parts = array_map(
			function ( $s ) use ( $p ) {
				return $p . trim( $s );
			},
			explode( ',', $selectors )
		);
		return implode( ',', $parts ) . '{' . $decl . '}';
	};

	// 1. Spacing rhythm — redefine the preset scale the patterns consume.
	$keys  = array( 10, 20, 30, 40, 50, 60, 70, 80 );
	$space = array_values( (array) ( $tokens['space'] ?? array() ) );
	$scale = '';
	foreach ( $keys as $i => $k ) {
		$val    = isset( $space[ $i ] ) ? intval( $space[ $i ] ) : 0;
		$scale .= '--wp--preset--spacing--' . $k . ':' . $val . 'px;';
	}

	// 2. Design custom properties + spacing scale on the pattern root.
	$vars  = $scale;
	$vars .= '--ec-accent:' . $accent . ';--ec-text:' . $text . ';';
	if ( $surface ) {
		$vars .= '--ec-surface:' . $surface . ';';
	}
	if ( $border ) {
		$vars .= '--ec-border:' . $border . ';';
	}
	$vars .= '--ec-radius-card:' . $r_card . ';--ec-radius-btn:' . $r_btn . ';--ec-radius-img:' . $r_img . ';';
	$vars .= '--ec-shadow-card:' . $sh_card . ';--ec-shadow-hover:' . $sh_hover . ';';
	$vars .= '--ec-card-fill:' . $card_fill . ';--ec-card-border:' . $card_border . ';';
	if ( $heading ) {
		$vars .= '--ec-font-heading:' . $heading . ';';
	}
	if ( $body ) {
		$vars .= '--ec-font-body:' . $body . ';';
	}
	$css = $rule( '.ec-pattern', $vars );

	// 3. Base typography + colour (hero white text keeps its higher-specificity rule).
	$css .= $rule( '.ec-pattern', 'color:var(--ec-text);' . ( $body ? 'font-family:var(--ec-font-body);' : '' ) );
	if ( $heading ) {
		$css .= $rule( '.ec-pattern h1,.ec-pattern h2,.ec-pattern h3,.ec-pattern h4', 'font-family:var(--ec-font-heading);' );
	}
	// Section titles adopt the design's heading scale; hero H1s keep their per-page inline size.
	$css .= $rule(
		'.ec-pattern h2.wp-block-heading',
		'font-weight:' . $h_wt . ';letter-spacing:' . $h_trk . ';text-transform:' . $h_tr . ';line-height:' . $h_ln . ';font-size:' . $h_h2 . ';'
	);

	// 4. Button system — shape, fill, hover behaviour.
	$btn_base  = 'border-radius:var(--ec-radius-btn);font-weight:' . $b_wt . ';font-size:' . $b_sz . ';text-transform:' . $b_tr . ';letter-spacing:' . $b_trk . ';padding:' . $b_pad . ';line-height:1.2;display:inline-block;transition:transform .18s ease,box-shadow .18s ease,background-color .18s ease,color .18s ease,filter .18s ease,opacity .18s ease;';
	$css      .= $rule( '.ec-pattern .wp-block-button:not(.is-style-outline) .wp-block-button__link,.ec-pattern .wp-element-button', 'background-color:var(--ec-accent);border:1px solid var(--ec-accent);color:#fff;' . $btn_base );
	$css      .= $rule( '.ec-pattern .is-style-outline .wp-block-button__link', 'background-color:transparent;border:1px solid var(--ec-accent);color:var(--ec-accent);' . $btn_base );
	if ( 'lift' === $b_hov ) {
		$css .= $rule( '.ec-pattern .wp-block-button__link:hover,.ec-pattern .wp-element-button:hover', 'transform:translateY(-2px);box-shadow:0 14px 26px -12px rgba(17,17,17,0.4);' );
	} elseif ( 'invert' === $b_hov ) {
		$css .= $rule( '.ec-pattern .is-style-outline .wp-block-button__link:hover', 'background-color:var(--ec-accent);color:#fff;' );
		$css .= $rule( '.ec-pattern .wp-block-button:not(.is-style-outline) .wp-block-button__link:hover,.ec-pattern .wp-element-button:hover', 'opacity:0.82;' );
	} else { // darken
		$css .= $rule( '.ec-pattern .wp-block-button:not(.is-style-outline) .wp-block-button__link:hover,.ec-pattern .wp-element-button:hover', 'filter:brightness(0.9);' );
		$css .= $rule( '.ec-pattern .is-style-outline .wp-block-button__link:hover', 'background-color:var(--ec-accent);color:#fff;' );
	}
	// Heroes: keep the CTA legible on the cover image regardless of design accent.
	$css .= $rule( '.ec-pattern-hero .is-style-outline .wp-block-button__link', 'border-color:#fff;color:#fff;' );
	$css .= $rule( '.ec-pattern-hero .is-style-outline .wp-block-button__link:hover', 'background-color:#fff;color:#111;' );

	// 5. Images inside patterns adopt the design's corner language.
	$css .= $rule( '.ec-pattern .wp-block-image img', 'border-radius:var(--ec-radius-img);' );

	// 6. Product-collection cards — the shared card, re-skinned per design.
	$css .= $rule( '.ec-product-collection .easycommerce-single-product', 'background:var(--ec-card-fill);border:var(--ec-card-border);border-radius:var(--ec-radius-card);box-shadow:var(--ec-shadow-card);overflow:hidden;transition:transform .2s ease,box-shadow .2s ease;' );
	$css .= $rule( '.ec-product-collection .easycommerce-single-product:hover', 'box-shadow:var(--ec-shadow-hover);' . ( 'lift' === $b_hov ? 'transform:translateY(-4px);' : '' ) );
	// A visible card surface (fill/border/shadow) needs inner gutters - the
	// shared card markup only pads the top of its text block (`pt-4`), so
	// title/price/CTA sit flush against the card edge. Surface-less cards
	// keep text flush with the image edge on purpose (minimal's editorial look).
	if ( 'transparent' !== $card_fill || 'none' !== $card_border || 'none' !== $sh_card ) {
		$css .= $rule( '.ec-product-collection .easycommerce-single-product > div + div', 'padding-left:16px;padding-right:16px;padding-bottom:16px;' );
	}
	// Card CTAs (Add to cart / Choose) join the design's button system - left
	// to their inspector defaults they break the skin at the conversion point.
	// Shape/colour/case only; the card keeps its own padding and font size.
	$css .= $rule(
		'.ec-product-collection .easycommerce-add-to-cart-shop,.ec-product-collection .easycommerce-shop-choose-btn',
		'background-color:var(--ec-accent);border:1px solid var(--ec-accent);color:#fff;border-radius:var(--ec-radius-btn);font-size:' . $b_sz . ';line-height:1.2;font-weight:' . $b_wt . ';text-transform:' . $b_tr . ';letter-spacing:' . $b_trk . ';width:auto;white-space:nowrap;transition:transform .18s ease,box-shadow .18s ease,filter .18s ease,opacity .18s ease;'
	);
	$card_btn_hover = 'lift' === $b_hov
		? 'transform:translateY(-2px);box-shadow:0 10px 20px -10px rgba(17,17,17,0.4);'
		: ( 'invert' === $b_hov ? 'opacity:0.82;' : 'filter:brightness(0.9);' );
	$css           .= $rule(
		'.ec-product-collection .easycommerce-add-to-cart-shop:hover,.ec-product-collection .easycommerce-shop-choose-btn:hover,.ec-product-collection .easycommerce-add-to-cart-shop:focus,.ec-product-collection .easycommerce-shop-choose-btn:focus',
		'background-color:var(--ec-accent);color:#fff;' . $card_btn_hover
	);
	// Uniform square thumbnails so cards in a grid row line up (product images
	// arrive at mixed aspect ratios — e.g. a tall digital-download placeholder).
	$css .= $rule( '.ec-product-collection .easycommerce-thumbnail-img', 'aspect-ratio:1/1;object-fit:cover;width:100%;height:auto;' );
	if ( ! empty( $tokens['product_title'] ) ) {
		$tt   = $keyword( $tokens['product_title'], array( 'none', 'uppercase', 'lowercase', 'capitalize' ), 'none' );
		$css .= $rule( '.ec-product-collection .easycommerce-product-title-shop', 'text-transform:' . $tt . ';letter-spacing:0.06em;font-size:13px;' );
	}

	// 7. Inline-radius overrides — two patterns hard-code radius/borders inline
	//    (category covers, testimonial cards); only !important can win there.
	$css .= $rule( '.ec-pattern-categories .wp-block-cover', 'border-radius:var(--ec-radius-card)!important;overflow:hidden;' );
	$css .= $rule( '.ec-pattern-testimonials .wp-block-column>.wp-block-group', 'border-radius:var(--ec-radius-card)!important;border:var(--ec-card-border)!important;background:var(--ec-card-fill);box-shadow:var(--ec-shadow-card);' );

	// 8. Section transitions — edge (dividers + tint) / gap (tint only) / rule
	//    (thin hairline). Driven by the section rhythm tokens.
	$sections = '.ec-pattern-categories,.ec-pattern-featured,.ec-pattern-best-sellers,.ec-pattern-new-arrivals,.ec-pattern-testimonials,.ec-pattern-trust,.ec-pattern-newsletter,.ec-pattern-faq';
	if ( $sec_div && 'none' !== $sec_div ) {
		$css .= $rule( $sections, 'border-top:' . $sec_div . ';' );
	}
	if ( $sec_alt && 'transparent' !== $sec_alt ) {
		$css .= $rule( '.ec-pattern-best-sellers,.ec-pattern-trust,.ec-pattern-testimonials', 'background-color:' . $sec_alt . ';' );
	}

	return $css;
}

function send_reset_password_email( $user_data, $key ) {
    $user_login = $user_data->user_login;
    $user_email = $user_data->user_email;
    $site_name  = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

    $reset_url = add_query_arg(
        array(
            'action' => 'ecrp',
            'key'    => $key,
            'login'  => rawurlencode( $user_login ),
        ),
        easycommerce_reset_password_page( true )
    );

    /* translators: %s: site name. */
    $subject = sprintf( __( '[%s] Password Reset', 'easycommerce' ), $site_name );

    $message  = __( 'Someone has requested a password reset for the following account:', 'easycommerce' ) . "\r\n\r\n";
    /* translators: %s: site name. */
    $message .= sprintf( __( 'Site Name: %s', 'easycommerce' ), $site_name ) . "\r\n\r\n";
    /* translators: %s: username of the account. */
    $message .= sprintf( __( 'Username: %s', 'easycommerce' ), $user_login ) . "\r\n\r\n";
    $message .= __( 'If this was a mistake, just ignore this email and nothing will happen.', 'easycommerce' ) . "\r\n\r\n";
    $message .= __( 'To reset your password, visit the following address:', 'easycommerce' ) . "\r\n\r\n";
    $message .= $reset_url . "\r\n";

    return wp_mail( $user_email, $subject, $message );
}

function easycommerce_rest_base() {
	return rest_url( '/easycommerce/v1' );
}

function easycommerce_single_product_patterns() {
	return array(
		'template-1' => __( 'Template 1', 'easycommerce' ),
		'template-2' => __( 'Template 2', 'easycommerce' ),
	);
}

function easycommerce_checkout_template() {
	return Utility::get_option( 'checkout', 'settings', 'checkout_template', 'template-1' );
}

function easycommerce_checkout_templates() {
	return array(
		'template-1' => __( 'Template 1', 'easycommerce' ),
		'template-2' => __( 'Template 2', 'easycommerce' ),
		'template-3' => __( 'Template 3', 'easycommerce' ),
	);
}

function easycommerce_refund_reasons() {
	return array(
		'requested_by_customer' => __( 'Request by Customer', 'easycommerce' ),
		'duplicate'             => __( 'Duplicate', 'easycommerce' ),
		'fraudulent'            => __( 'Fraudulent', 'easycommerce' ),
	);
}

function easycommerce_product_sort_options() {
	return array(
		'low-to-high'    => __( 'Low to High', 'easycommerce' ),
		'high-to-low'    => __( 'High to Low', 'easycommerce' ),
		'newest'         => __( 'Newest', 'easycommerce' ),
		'oldest'         => __( 'Oldest', 'easycommerce' ),
		'best-selling'   => __( 'Best Selling', 'easycommerce' ),
		'lowest-selling' => __( 'Lowest Selling', 'easycommerce' ),
		'top-rating'     => __( 'Top rating', 'easycommerce' ),
		'lowest-rating'  => __( 'Lowest rating', 'easycommerce' ),
	);
}

function easycommerce_email_events() {
	return array(
		'pending'    => __( 'Pending Order', 'easycommerce' ),
		'processing' => __( 'Processing Order', 'easycommerce' ),
		'completed'  => __( 'Completed Order', 'easycommerce' ),
		'cancelled'  => __( 'Cancelled Order', 'easycommerce' ),
		'on_hold'    => __( 'On-hold Order', 'easycommerce' ),
		'refunded'   => __( 'Refunded Order', 'easycommerce' ),
		'partially_refunded' => __( 'Partially Refunded Order', 'easycommerce' ),
		'failed'     => __( 'Failed Order', 'easycommerce' ),
	);
}

function easycommerce_email_default( $event ) {
	$default = include EASYCOMMERCE_PLUGIN_DIR . "app/Config/email-defaults/{$event}.php";

	return $default;
}

function easycommerce_order_statuses() {
	return array(
		'pending'    			=> __( 'Pending', 'easycommerce' ),
		'processing' 			=> __( 'Processing', 'easycommerce' ),
		'completed'  			=> __( 'Completed', 'easycommerce' ),
		'cancelled'  			=> __( 'Cancelled', 'easycommerce' ),
		'on_hold'    			=> __( 'On Hold', 'easycommerce' ),
		'partially_refunded'	=> __( 'Partially Refunded', 'easycommerce' ),
		'refunded'   			=> __( 'Refunded', 'easycommerce' ),
		'failed'   				=> __( 'Failed', 'easycommerce' ),
	);
}

function easycommerce_get_all_payment_methods() {

    $methods = array(
        'cash-on-delivery' => array(
            'label' => __('Cash on Delivery', 'easycommerce'),
            'is_addon' => false
        ),
        'stripe' => array(
            'label' => __('Stripe', 'easycommerce'),
            'is_addon' => false
        ),
        'paypal' => array(
            'label' => __('PayPal', 'easycommerce'),
            'is_addon' => false
        ),
        'square' => array(
            'label' => __('Square', 'easycommerce'),
            'is_addon' => false
        ),
        'braintree' => array(
            'label' => __('Braintree', 'easycommerce'),
            'is_addon' => false
        ),
        'mollie' => array(
            'label' => __('Mollie', 'easycommerce'),
            'is_addon' => false
        ),
        'paddle' => array(
            'label' => __('Paddle', 'easycommerce'),
            'is_addon' => true
        ),
        'bank' => array(
            'label' => __('Bank Transfer', 'easycommerce'),
            'is_addon' => true
        ),
    );

    // Add icons (custom uploaded or default)
    $methods = easycommerce_add_payment_method_icons( $methods );

    return $methods;
}

function easycommerce_product_statuses() {
	return array(
		'publish' => __( 'Live', 'easycommerce' ),
		'draft'   => __( 'Draft', 'easycommerce' ),
		'trash'   => __( 'Trash', 'easycommerce' ),
	);
}

function easycommerce_global_default_status() {
	return Utility::get_option( 'order', 'settings', 'default_order_status' ) ?? 'pending';
}

function easycommerce_fulfill_statuses() {
	return array(
		'unfulfilled'         => __( 'Unfulfilled', 'easycommerce' ),
		'fulfilled'           => __( 'Fulfilled', 'easycommerce' ),
		'partially_fulfilled' => __( 'Partially Fulfilled', 'easycommerce' ),
		'shipped'             => __( 'Shipped', 'easycommerce' ),
		'delivered'           => __( 'Delivered', 'easycommerce' ),
		'returned'            => __( 'Returned', 'easycommerce' ),
	);
}

function easycommerce_length_units() {
	return array(
        array(
            'value' => 'mm',
            'label' => __('Millimeter', 'easycommerce')
        ),
        array(
            'value' => 'cm',
            'label' => __('Centimeter', 'easycommerce')
        ),
        array(
            'value' => 'in',
            'label' => __('Inch', 'easycommerce')
        ),
        array(
            'value' => 'm',
            'label' => __('Meter', 'easycommerce')
        ),
		array(
            'value' => 'ft',
            'label' => __('Foot', 'easycommerce')
        ),
		array(
            'value' => 'yd',
            'label' => __('Yard', 'easycommerce')
        ),
    );
}

function easycommerce_weight_units() {
	return array(
        array(
            'value' => 'g',
            'label' => __('Gram', 'easycommerce')
        ),
        array(
            'value' => 'kg',
            'label' => __('Kilogram', 'easycommerce')
        ),
        array(
            'value' => 'lb',
            'label' => __('Pound', 'easycommerce')
        ),
        array(
            'value' => 'oz',
            'label' => __('Ounce', 'easycommerce')
        ),
    );
}

function easycommerce_weight_unit_conversion( $baseUnit = 'g' ) {
	$conversions = [
        'g' => [
            'g'  => 1,          // Grams
            'kg' => 1000,       // Kilograms to grams
            'lb' => 453.592,    // Pounds to grams
            'oz' => 28.3495,    // Ounces to grams
        ],
        'kg' => [
            'kg' => 1,          // Kilograms to kilograms
            'lb' => 0.453592,   // Pounds to kilograms
            'oz' => 0.0283495,  // Ounces to kilograms
            'g'  => 0.001,      // Grams to kilograms
        ]
    ];

    return isset( $conversions[$baseUnit] ) ? $conversions[$baseUnit] : $conversions['g'];
}

function easycommerce_time_units() {
	return array(
		'second' => __( 'Second', 'easycommerce' ),
		'minute' => __( 'Minute', 'easycommerce' ),
		'hour'   => __( 'Hour', 'easycommerce' ),
		'day'    => __( 'Day', 'easycommerce' ),
		'week'   => __( 'Week', 'easycommerce' ),
		'month'  => __( 'Month', 'easycommerce' ),
		'year'   => __( 'Year', 'easycommerce' ),
	);
}

function easycommerce_get_field_factory( $type ) {

	if ( $type == 'switch' ) {
		$type = 'switcher';
	} elseif ( $type == 'wysiwyg' ) {
		$type = 'WYSIWYG';
	}

	return '\\EasyCommerce\\Helpers\\Field\\' . ucfirst( $type );
}

function easycommerce_product_post_type() {
	return apply_filters( 'easycommerce_product_post_type', 'product' );
}

function easycommerce_get_cart() {
	$cart = new Cart();
	return $cart;
}

function easycommerce_get_cart_hash() {
	if ( isset( $_GET['cart'] ) ) {
		return sanitize_text_field( $_GET['cart'] );
	} elseif ( is_user_logged_in() ) {
		return get_user_meta( get_current_user_id(), '_easycommerce_cart_hash', true );
	} else {
		return isset( $_COOKIE['easycommerce_cart_hash'] ) ? sanitize_text_field( $_COOKIE['easycommerce_cart_hash'] ) : null;
	}
}

function easycommerce_add_payment_method_icons( $methods ) {
	// Add icons to payment methods
	foreach ( $methods as $key => $method ) {
		if ( empty( $method['icon'] ) ) {
			// Check for custom uploaded logo first
			$logo_option_key = $key . '_logo';
			$icon_id = Utility::get_option( 'payment', $key, $logo_option_key, '' );
			$icon_url = '';

			if ( wp_attachment_is_image( $icon_id ) ) {
				$icon_url = wp_get_attachment_url( $icon_id );
			}

			// If no custom logo, use default icon
			if ( empty( $icon_url ) ) {
				$icon_file = str_replace( '_', '-', $key ) . '.svg';
				$icon_path = EASYCOMMERCE_PLUGIN_DIR . 'assets/payment/img/' . $icon_file;
				if ( file_exists( $icon_path ) ) {
					$icon_url = EASYCOMMERCE_ASSETS_URL . 'payment/img/' . $icon_file;
				}
			}

			if ( ! empty( $icon_url ) ) {
				$methods[ $key ]['icon'] = $icon_url;
			}
		}
	}

	return $methods;
}

function easycommerce_payment_methods() {
	$methods = apply_filters( 'easycommerce_payment_methods', array() );
	return easycommerce_add_payment_method_icons( $methods );
}

/**
 * @return array of payment method IDs
 */
function easycommerce_active_payment_methods() {
	$methods = Utility::get_option( 'payment', 'methods', 'active_methods', array() );
	return (array) $methods;
}

function easycommerce_payment_method_class( $method ) {
	$payment_methods = easycommerce_payment_methods();

	if ( ! isset( $payment_methods[ $method ] ) || ! class_exists( $payment_methods[ $method ]['class'] ) ) {
		return false;
	}

	return $payment_method = new $payment_methods[ $method ]['class']();
}

function easycommerce_cart_redirect() {
	return apply_filters( 'easycommerce_cart_redirect', easycommerce_checkout_page() );
}

function easycommerce_order_redirect( $order_id ) {
	$dashboard_page = easycommerce_dashboard_page( true );
	$redirect_page  = isset( $dashboard_page ) ? $dashboard_page : easycommerce_home_url();
	$redirect_url   = "{$redirect_page}/#orders/{$order_id}";

	return apply_filters( 'easycommerce_order_redirect', $redirect_url, $order_id );
}

function easycommerce_date_ranges() {
	return apply_filters(
		'easycommerce_date_ranges',
		array(
			'today'      => __( 'Today', 'easycommerce' ),
			'yesterday'  => __( 'Yesterday', 'easycommerce' ),
			'this-week'  => __( 'This Week', 'easycommerce' ),
			'last-week'  => __( 'Last Week', 'easycommerce' ),
			'last-7'     => __( 'Last 7 Days', 'easycommerce' ),
			'this-month' => __( 'This Month', 'easycommerce' ),
			'last-month' => __( 'Last Month', 'easycommerce' ),
			'last-30'    => __( 'Last 30 Days', 'easycommerce' ),
			'this-year'  => __( 'This Year', 'easycommerce' ),
			'last-year'  => __( 'Last Year', 'easycommerce' ),
		)
	);
}

// get_range_dates
function easycommerce_get_range_dates( $range, $custom_from = null, $custom_to = null ) {
    $today = date('Y-m-d');

    switch ( $range ) {
        case 'today':
            return ['from' => "$today 00:00:00", 'to' => "$today 23:59:59"];

        case 'yesterday':
            $yesterday = date( 'Y-m-d', strtotime( '-1 day' ) );
            return ['from' => "$yesterday 00:00:00", 'to' => "$yesterday 23:59:59"];

        case 'this-week':
            $start = date( 'Y-m-d', strtotime( 'monday this week' ) );
            $end   = date( 'Y-m-d', strtotime( 'sunday this week' ) );
            return ['from' => "$start 00:00:00", 'to' => "$end 23:59:59"];

        case 'last-week':
            $start = date( 'Y-m-d', strtotime( 'monday last week' ) );
            $end   = date( 'Y-m-d', strtotime( 'sunday last week' ) );
            return ['from' => "$start 00:00:00", 'to' => "$end 23:59:59"];

        case 'last-7':
            $start = date('Y-m-d', strtotime( '-6 days' ) );
            return ['from' => "$start 00:00:00", 'to' => "$today 23:59:59"];

        case 'this-month':
            $start = date( 'Y-m-01' );
            $end   = date( 'Y-m-t' );
            return ['from' => "$start 00:00:00", 'to' => "$end 23:59:59"];

        case 'last-month':
            $start = date( 'Y-m-01', strtotime( 'first day of last month' ) );
            $end   = date( 'Y-m-t', strtotime( 'last day of last month' ) );
            return ['from' => "$start 00:00:00", 'to' => "$end 23:59:59"];

        case 'last-30':
            $start = date( 'Y-m-d', strtotime( '-29 days' ) );
            return ['from' => "$start 00:00:00", 'to' => "$today 23:59:59"];

        case 'this-year':
            $start = date( 'Y-01-01' );
            $end   = date( 'Y-12-31' );
            return ['from' => "$start 00:00:00", 'to' => "$end 23:59:59"];

        case 'last-year':
            $year  = date( 'Y ') - 1;
            $start = "$year-01-01";
            $end   = "$year-12-31";
            return ['from' => "$start 00:00:00", 'to' => "$end 23:59:59"];

        case 'custom':
            if ( $custom_from && $custom_to ) {
                return [
                    'from' => date('Y-m-d 00:00:00', strtotime( $custom_from ) ),
                    'to'   => date('Y-m-d 23:59:59', strtotime( $custom_to ) )
                ];
            }
            return null;

        default:
            return null;
    }
}

/**
 * The cache wrapper
 */
function easycommerce_cache() {
	$cacher = new class() {
		use Cache;
	};

	return $cacher;
}

/**
 * Get an associative list of countries
 *
 * @return array [ 'BD' => 'Bangladesh', ... ]
 */
function easycommerce_countries() {
	
	$cacher = easycommerce_cache();

	if ( false === ( $countries = $cacher->get_cache( 'countries' ) ) ) {
		$countries = array_column( Location::get_countries(), 'name', 'iso2' );

		$cacher->set_cache( 'countries', $countries, MONTH_IN_SECONDS );
	}

	return $countries;
}

/**
 * Get an associative list of currencies
 *
 * @return []
 */
function easycommerce_currencies( $label = 'currency_name' ) {

	$cacher = easycommerce_cache();

	if ( false == ( $currencies = $cacher->get_cache( "currencies-{$label}" ) ) ) {
		$currencies = wp_list_pluck( Location::get_currencies(), $label, 'currency' );

		$cacher->set_cache( "currencies-{$label}", $currencies, MONTH_IN_SECONDS );
	}

	return $currencies;
}

function easycommerce_currency_format_options() {
	return array(
		'us'        => '$12,345.67',        // US, Canada
		'us_after'  => '12,345.67$',        // Rare, but seen in Quebec
		'eu'        => '12.345,67 $',       // Germany, France, Spain
		'eu_before' => '$12.345,67',        // Alternative EU style
		'ch'        => 'USD 12’345.67',     // Switzerland
		'iso'       => 'USD 12,345.67',     // ISO format
		'iso_after' => '12,345.67 USD',     // Alternate ISO
		'plain'     => '12,345.67',         // No symbol/code
	);
}

function easycommerce_menus() {
	$licensed 	= apply_filters( 'easycommerce-pro_licensed', false );
	$pro_active = apply_filters( 'easycommerce-pro_activated', false );
	$pro_menu = ( $licensed || $pro_active ) ? [
		'page_title' 	=> __( 'EasyCommerce', 'easycommerce' ),
		'menu_title' 	=> __( 'Pro', 'easycommerce' ),
		'slug' 			=> 'easycommerce#/pro',
		'icon'			=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/pro.svg',
		'hover_icon'	=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/pro-hover.svg',
	] : [
		'page_title' 	=> __( 'EasyCommerce', 'easycommerce' ),
		'menu_title' 	=> __( 'Get Pro', 'easycommerce' ),
		'slug' 			=> 'easycommerce#/get-pro',
		'icon'			=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/pro.svg',
		'hover_icon'	=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/pro-hover.svg',
	];

	return apply_filters( 'easycommerce_menus', [
		[
			'title' 		=> __( 'EasyCommerce', 'easycommerce' ),
			'menu_title' 	=> __( 'EasyCommerce', 'easycommerce' ),
			'capability' 	=> 'manage_options',
			'slug' 			=> 'easycommerce',
			'callback'		=> function() {
				printf(
					'<div class="wrap">
						<div id="easycommerce_render">%1$s</div>
					</div>',
					esc_html__( 'Loading..', 'easycommerce' )
				);
			},
			'icon' 			=> EASYCOMMERCE_ASSETS_URL . 'admin/img/icons/logo/easycommerce.png',
			'position' 		=> 2,
			'submenus' => [
				[
					'page_title' 	=> __( 'Dashboard', 'easycommerce' ),
					'menu_title' 	=> __( 'Dashboard', 'easycommerce' ),
					'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
					'slug' 			=> 'easycommerce',
					'icon'			=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/dashboard.svg',
					'hover_icon'	=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/dashboard-hover.svg',
				],
				[
					'page_title' 	=> __( 'Products', 'easycommerce' ),
					'menu_title' 	=> __( 'Products', 'easycommerce' ),
					'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
					'slug' 			=> 'easycommerce#/products',
					'icon'			=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/products.svg',
					'hover_icon'	=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/products-hover.svg',

					'submenus' => [
						[
							'page_title' 	=> __( 'All Products', 'easycommerce' ),
							'menu_title' 	=> __( 'All Products', 'easycommerce' ),
							'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
							'slug' 			=> 'easycommerce#/products',
						],
						[
							'page_title' 	=> __( 'Add Product', 'easycommerce' ),
							'menu_title' 	=> __( 'Add Product', 'easycommerce' ),
							'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
							'slug' 			=> 'easycommerce#/products/add',
						],
						[
							'page_title' 	=> __( 'Attributes', 'easycommerce' ),
							'menu_title' 	=> __( 'Attributes', 'easycommerce' ),
							'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
							'slug' 			=> 'easycommerce#/attributes',
						],
						[
							'page_title' 	=> __( 'Categories', 'easycommerce' ),
							'menu_title' 	=> __( 'Categories', 'easycommerce' ),
							'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
							'slug' 			=> 'easycommerce#/categories',
						],
						[
							'page_title' 	=> __( 'Tags', 'easycommerce' ),
							'menu_title' 	=> __( 'Tags', 'easycommerce' ),
							'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
							'slug' 			=> 'easycommerce#/tags',
						],
						[
							'page_title' 	=> __( 'Brands', 'easycommerce' ),
							'menu_title' 	=> __( 'Brands', 'easycommerce' ),
							'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
							'slug' 			=> 'easycommerce#/brands',
						],
					]
				],
				[
					'page_title' 	=> __( 'Orders', 'easycommerce' ),
					'menu_title' 	=> __( 'Orders', 'easycommerce' ),
					'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
					'slug' 			=> 'easycommerce#/orders',
					'icon'			=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/orders.svg',
					'hover_icon'	=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/orders-hover.svg',
				],
				[
					'page_title' 	=> __( 'Refunds', 'easycommerce' ),
					'menu_title' 	=> __( 'Refunds', 'easycommerce' ),
					'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
					'slug' 			=> 'easycommerce#/refunds',
					'icon'			=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/refunds.svg',
					'hover_icon'	=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/refunds-hover.svg',
				],
				[
					'page_title' 	=> __( 'Abandoned Carts', 'easycommerce' ),
					'menu_title' 	=> __( 'Abandoned Carts', 'easycommerce' ),
					'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
					'slug' 			=> 'easycommerce#/abandoned-cart',
					'icon'			=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/abandoned-cart.svg',
					'hover_icon'	=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/abandoned-cart-hover.svg',
				],
				[
					'page_title' 	=> __( 'Transactions', 'easycommerce' ),
					'menu_title' 	=> __( 'Transactions', 'easycommerce' ),
					'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
					'slug' 			=> 'easycommerce#/transactions',
					'icon'			=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/transactions.svg',
					'hover_icon'	=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/transactions-hover.svg',
				],
				[
					'page_title' 	=> __( 'Customers', 'easycommerce' ),
					'menu_title' 	=> __( 'Customers', 'easycommerce' ),
					'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
					'slug' 			=> 'easycommerce#/customers',
					'icon'			=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/customers.svg',
					'hover_icon'	=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/customers-hover.svg',
				],
				[
					'page_title' 	=> __( 'Reviews', 'easycommerce' ),
					'menu_title' 	=> __( 'Reviews', 'easycommerce' ),
					'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
					'slug' 			=> 'easycommerce#/reviews',
					'icon'			=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/reviews.svg',
					'hover_icon'	=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/reviews-hover.svg',
				],
				[
					'page_title' 	=> __( 'Coupons', 'easycommerce' ),
					'menu_title' 	=> __( 'Coupons', 'easycommerce' ),
					'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
					'slug' 			=> 'easycommerce#/coupons',
					'icon'			=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/coupons.svg',
					'hover_icon'	=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/coupons-hover.svg',
				],
				[
					'page_title' 	=> __( 'Reports', 'easycommerce' ),
					'menu_title' 	=> __( 'Reports', 'easycommerce' ),
					'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
					'slug' 			=> 'easycommerce#/reports',
					'icon'			=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/reports.svg',
					'hover_icon'	=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/reports-hover.svg',
					'submenus' => [
						[
							'page_title' 	=> __( 'Overview', 'easycommerce' ),
							'menu_title' 	=> __( 'Overview', 'easycommerce' ),
							'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
							'slug' 			=> 'easycommerce#/reports',
						],
						[
							'page_title' 	=> __( 'Orders', 'easycommerce' ),
							'menu_title' 	=> __( 'Orders', 'easycommerce' ),
							'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
							'slug' 			=> 'easycommerce#/reports/orders',
						],
						[
							'page_title' 	=> __( 'Revenues', 'easycommerce' ),
							'menu_title' 	=> __( 'Revenues', 'easycommerce' ),
							'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
							'slug' 			=> 'easycommerce#/reports/revenues',
						],
						[
							'page_title' 	=> __( 'Products', 'easycommerce' ),
							'menu_title' 	=> __( 'Products', 'easycommerce' ),
							'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
							'slug' 			=> 'easycommerce#/reports/products',
						],
						[
							'page_title' 	=> __( 'Customers', 'easycommerce' ),
							'menu_title' 	=> __( 'Customers', 'easycommerce' ),
							'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
							'slug' 			=> 'easycommerce#/reports/customers',
						],
					]
				],
				[
					'page_title' 	=> __( 'Settings', 'easycommerce' ),
					'menu_title' 	=> __( 'Settings', 'easycommerce' ),
					'capability' 	=> 'easycommerce_view_store', // Allow admin and editors
					'slug' 			=> 'easycommerce-settings',
					'icon'			=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/settings.svg',
					'hover_icon'	=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/settings-hover.svg',
					'callback' 		=> fn() => do_action( 'easycommerce-settings' )
				],
				[
					'page_title' 	=> __( 'Addons', 'easycommerce' ),
					'menu_title' 	=> __( 'Addons', 'easycommerce' ),
					'slug' 			=> 'easycommerce#/addons',
					'icon'			=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/addons.svg',
					'hover_icon'	=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/addons-hover.svg',
				],
				[
					'page_title' 	=> __( 'Help & Support', 'easycommerce' ),
					'menu_title' 	=> __( 'Help & Support', 'easycommerce' ),
					'slug' 			=> 'easycommerce#/help',
					'icon'			=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/help.svg',
					'hover_icon'	=> EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/help-hover.svg',
				
				],
				$pro_menu,
			]
		],
		[
			'title' 		=> __( 'Wizard', 'easycommerce' ),
			'menu_title' 	=> __( 'Wizard', 'easycommerce' ),
			'capability' 	=> 'manage_options',
			'slug' 			=> 'easycommerce-wizard',
			'callback'		=> function() {
				printf(
					'<div class="wrap">
						<div id="easycommerce_wizard_render">%1$s</div>
					</div>',
					esc_html__( 'Loading..', 'easycommerce' )
				);
			},
			'icon' 			=> 'dashicons-cart',
			'position' 		=> 2,
			'submenus' => [
				[
					'page_title' 	=> __( 'Wizard', 'easycommerce' ),
					'menu_title' 	=> __( 'Wizard', 'easycommerce' ),
					'slug' 			=> 'easycommerce-wizard',
				]
			]
		]
	]);
}

/**
 * @see app/Config/settings.php
 */
function easycommerce_settings_menus() {
	include EASYCOMMERCE_PLUGIN_DIR . 'app/Config/settings.php';

	return $easycommerce_settings_menus;
}

function easycommerce_render_sidebar() {
	$sidebar	= '';
	$menus		= easycommerce_menus();
	$user_icon	= EASYCOMMERCE_ASSETS_URL . 'admin/img/icons/header-default-user-icon.png';

	foreach ( $menus as $menu ) {

		// Match on the slug: the title is translated, so comparing it empties the sidebar in other locales.
		if ( 'easycommerce' !== $menu['slug'] ) continue;

		$sidebar .= '<div class="py-2 px-4 w-[240px] bg-white items-center gap-4 h-full">';
		$sidebar .= '<ul class="menu-title">';

		if ( ! empty( $menu['submenus'] ) ) {
			foreach ( $menu['submenus'] as $submenu ) {
				$sidebar .= easycommerce_render_menu_item( $submenu );
			}
		}

		$sidebar .= '</ul>';

		$sidebar .= '
			<div id="easycommerce-connect-box" class="flex flex-col items-center gap-4 bg-ec-table-stock p-8 rounded-xl mt-[70px] mb-[30px]"></div>
		</div>';

	}

	return $sidebar;
}

function easycommerce_render_menu_item( $item ) {
	$has_submenus = ! empty( $item['submenus'] );
	$is_settings  = $item['slug'] === 'easycommerce-settings';
	$menu_url     = admin_url( 'admin.php?page=' . $item['slug'] );

	$output = '<li>';
	$output .= '<a href="' . esc_url( $menu_url ) . '" class="hover:bg-[var(--color-ec-active)] hover:text-inherit hover:rounded-[8px] flex items-center text-sm p-3 my-2 gap-2 focus:ring-0 ' .
				( $has_submenus ? 'submenu-toggle' : '' ) .
				( $is_settings ? ' easycommerce-active-menu text-ec-primary' : 'text-ec-title' ) .
			   '">';

	if ( ! empty( $item['icon'] ) ) {
		$icon_url = ( $is_settings && ! empty( $item['hover_icon'] ) ) ? $item['hover_icon'] : $item['icon'];
		$output .= '<img src="' . esc_url( $icon_url ) . '" alt="" class="menu-icon" style="width: 20px; height: 20px;" />';
	}

	$output .= esc_html( $item['menu_title'] );

	if ( $has_submenus ) {
		$arrow = EASYCOMMERCE_ASSETS_URL . 'admin/img/menu/up.png';
		$output .= '<img src="' . esc_url( $arrow ) . '" class="ml-auto arrow-icon transition-transform duration-300 rotate-180" style="width: 11px;" />';
	}

	$output .= '</a>';

	if ( $has_submenus ) {
		$output .= '<ul class="submenu-items border-l border-[#ECE6FF] ml-[30px] pl-3" style="display: none;">';
		foreach ( $item['submenus'] as $submenu ) {
			$output .= easycommerce_render_menu_item( $submenu );
		}
		$output .= '</ul>';
	}

	$output .= '</li>';

	return $output;
}

/**
 * @see app/Config/order-fields.php
 */
function easycommerce_checkout_fields( $section = '' ) {

	global $easycommerce_checkout_fields;

	if ( empty( $easycommerce_checkout_fields ) ) {
		include_once EASYCOMMERCE_PLUGIN_DIR . 'app/Config/checkout-fields.php';
	}

	if ( $section != '' && array_key_exists( $section, $easycommerce_checkout_fields ) ) {
		return $easycommerce_checkout_fields[ $section ];
	}

	return $easycommerce_checkout_fields;
}

function easycommerce_custom_checkout_fields( $sections = '' ) {
	global $easycommerce_custom_checkout_fields;

	if ( empty( $easycommerce_custom_checkout_fields ) ) {
		$easycommerce_custom_checkout_fields = get_option( 'easycommerce_checkout_editor_billing_fields', array() );
	}
	if ( $sections != '' && is_array( $easycommerce_custom_checkout_fields ) && array_key_exists( $sections, $easycommerce_custom_checkout_fields ) ) {
		return $easycommerce_custom_checkout_fields[ $sections ];
	}

	return $easycommerce_custom_checkout_fields;
}

function easycommerce_get_file_type( $filename ) {
	$icon_map = array(
		'pdf'  => 'pdf',
		'doc'  => 'word',
		'docx' => 'word',
		'xls'  => 'excel',
		'xlsx' => 'excel',
		'jpg'  => 'image',
		'jpeg' => 'image',
		'png'  => 'image',
		'gif'  => 'image',
		'zip'  => 'archive',
		'rar'  => 'archive',
		'txt'  => 'alt',
		'mp3'  => 'audio',
		'mp4'  => 'video',
		'bin'  => 'code',
		'exe'  => 'code',
	);

	$ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );

	return isset( $icon_map[ $ext ] ) ? $icon_map[ $ext ] : 'file';
}

function easycommerce_format_size( $bytes ) {
	if ( $bytes > -1024 && $bytes < 1024 ) {
		return $bytes . ' B';
	}

	$suffixes = 'KMGTPE';
	$index    = 0;

	while ( $bytes <= -999950 || $bytes >= 999950 ) {
		$bytes /= 1024;
		++$index;
	}

	return sprintf( '%.1f %sB', $bytes / 1024.0, $suffixes[ $index ] );
}

function easycommerce_currency() {
	$currency = Utility::get_option( 'payment', 'pricing', 'currency', 'USD' );

	return apply_filters( 'easycommerce_currency', $currency );
}

function easycommerce_currency_format() {
	return Utility::get_option( 'payment', 'pricing', 'format', 'us' );
}

function easycommerce_currency_symbol() {
	$currency      = easycommerce_currency();
	$currency_list = easycommerce_currencies( 'currency_symbol' );
	$symbol        = $currency_list[ $currency ] ?? '$';

	return apply_filters( 'easycommerce_currency_symbol', $symbol, $currency );
}

/**
 * Formats price
 */
function easycommerce_price( $price ) {
	$symbol = easycommerce_currency_symbol();
	$code   = easycommerce_currency();
	$format = easycommerce_currency_format();


	// choose separators
	if ( in_array( $format, array( 'eu', 'eu_before' ), true ) ) {
		$ts = '.';
		$ds = ',';
	} elseif ( $format === 'ch' ) {
		$ts = "'";
		$ds = '.';
	} else {
		$ts = ',';
		$ds = '.';
	}

	$amount = number_format( $price, 2, $ds, $ts );

	switch ( $format ) {
		case 'us_after':
			return "{$amount}{$symbol}";
		case 'eu_before':
			return "{$symbol}{$amount}";
		case 'eu':
			return "{$amount} {$symbol}";
		case 'iso':
			return "{$code} {$amount}";
		case 'iso_after':
			return "{$amount} {$code}";
		case 'ch':
			return "{$code}{$amount}";
		case 'plain':
			return $amount;
		default: // covers 'us', 'uk', 'ch' (symbol before), etc.
			return "{$symbol}{$amount}";
	}
}

function easycommerce_secure_download( $media_id ) {
	return add_query_arg(
		array(
			'action'   => 'easycommerce-download',
			'media_id' => $media_id,
		),
		admin_url()
	);
}

function easycommerce_get_business_types() {
	return apply_filters(
		'easycommerce_business_types',
		array(
			''               => __( 'Select Business Type', 'easycommerce' ),
			'art-antiques'   => __( 'Art and Antiques', 'easycommerce' ),
			'clothing'       => __( 'Clothing & Apparel', 'easycommerce' ),
			'beauty'         => __( 'Beauty & Cosmetics', 'easycommerce' ),
			'electronics'    => __( 'Electronics & Gadgets', 'easycommerce' ),
			'home-kitchen'   => __( 'Home & Kitchen Goods', 'easycommerce' ),
			'pet-supplies'   => __( 'Pet Supplies', 'easycommerce' ),
			'fitness'        => __( 'Sporting Goods & Fitness Equipment', 'easycommerce' ),
			'software'       => __( 'Software & SaaS', 'easycommerce' ),
			'ebooks'         => __( 'eBooks & Online Courses', 'easycommerce' ),
			'digital-art'    => __( 'Digital Art & Graphics', 'easycommerce' ),
			'themes-plugins' => __( 'Website Themes & Plugins', 'easycommerce' ),
			'consulting'     => __( 'Consulting & Coaching Services', 'easycommerce' ),
			'subscriptions'  => __( 'Subscription Boxes', 'easycommerce' ),
			'gourmet-foods'  => __( 'Gourmet & Specialty Foods', 'easycommerce' ),
			'coffee-tea'     => __( 'Coffee & Tea Products', 'easycommerce' ),
			'automotive'     => __( 'Automotive Parts & Accessories', 'easycommerce' ),
		)
	);
}

function easycommerce_get_product( $product_id ) {
	$product = new Product( $product_id );

	return $product;
}

function easycommerce_order_placeholders( $order_id ) {

	$order         = new Order( $order_id );
	$product_items = $order->get_items();
	$customer      = new Customer( $order->get_customer_id() );

	// Start table HTML
	$product_table = '<table style="width:100%; border-collapse:collapse; font-family:Arial, sans-serif;">
	    <thead>
	        <tr>
	            <th style="border:1px solid #ddd; padding:8px; background:#f2f2f2;">Product</th>
	            <th style="border:1px solid #ddd; padding:8px; background:#f2f2f2;">Variation</th>
	            <th style="border:1px solid #ddd; padding:8px; background:#f2f2f2;">Qty</th>
	            <th style="border:1px solid #ddd; padding:8px; background:#f2f2f2;">Rate</th>
	            <th style="border:1px solid #ddd; padding:8px; background:#f2f2f2;">Subtotal</th>
	        </tr>
	    </thead>
	    <tbody>';

	// Start list HTML
	$product_list = '<ul>';

	// Loop through order items
	foreach ( $order->get_items() as $item ) {
		$product   = new Product( $item->product_id );
		$variation = new Product_Variation( $item->variation_id );

		// Append table row
		$product_table .= '<tr>
	        <td style="border:1px solid #ddd; padding:8px;">' . $product->get_title() . '</td>
	        <td style="border:1px solid #ddd; padding:8px;">' . $variation->get_name( false ) . '</td>
	        <td style="border:1px solid #ddd; padding:8px;">' . $item->quantity . '</td>
	        <td style="border:1px solid #ddd; padding:8px;">' . easycommerce_price( $item->rate ) . '</td>
	        <td style="border:1px solid #ddd; padding:8px;">' . easycommerce_price( $item->price ) . '</td>
	    </tr>';

		// Append list item
		$product_list .= '<li>' . $product->get_title() . '</li>';
	}

	// Close table and list
	$product_table .= '</tbody></table>';
	$product_list  .= '</ul>';

	$placeholders = array(

		// Order details
		'##order_id##'                     => $order_id,

		// Customer basic details
		'##customer_name##'                => esc_html( $customer->get_name() ),
		'##customer_email##'               => esc_html( $customer->get_email() ),
		'##customer_phone##'               => esc_html( $customer->get_phone() ),

		// Billing details
		'##billing_first_name##'           => esc_html( $customer->get_first_name('billing') ),
		'##billing_last_name##'            => esc_html( $customer->get_last_name('billing') ),
		'##billing_email##'                => esc_html( $customer->get_email('billing') ),
		'##billing_phone##'                => esc_html( $customer->get_phone('billing') ),
		'##billing_address_1##'            => esc_html( $customer->get_address_1('billing') ),
		'##billing_address_2##'            => esc_html( $customer->get_address_2('billing') ),
		'##billing_country##'              => esc_html( $customer->get_country('billing') ),
		'##billing_state##'                => esc_html( $customer->get_state('billing') ),
		'##billing_city##'                 => esc_html( $customer->get_city('billing') ),
		'##billing_postcode##'             => esc_html( $customer->get_postcode('billing') ),
		'##billing_address##'              => esc_html( $customer->get_address('billing') ),

		// Shipping details
		'##shipping_first_name##'          => esc_html( $customer->get_first_name( 'shipping' ) ),
		'##shipping_last_name##'           => esc_html( $customer->get_last_name( 'shipping' ) ),
		'##shipping_email##'               => esc_html( $customer->get_email( 'shipping' ) ),
		'##shipping_phone##'               => esc_html( $customer->get_phone( 'shipping' ) ),
		'##shipping_address_1##'           => esc_html( $customer->get_address_1( 'shipping' ) ),
		'##shipping_address_2##'           => esc_html( $customer->get_address_2( 'shipping' ) ),
		'##shipping_country##'             => esc_html( $customer->get_country( 'shipping' ) ),
		'##shipping_state##'               => esc_html( $customer->get_state( 'shipping' ) ),
		'##shipping_city##'                => esc_html( $customer->get_city( 'shipping' ) ),
		'##shipping_postcode##'            => esc_html( $customer->get_postcode( 'shipping' ) ),
		'##shipping_address##'             => esc_html( $customer->get_address( 'shipping' ) ),

		// Customer order statistics
		'##customer_total_spent##'         => $customer->get_total_spent(),
		'##customer_total_order_count##'   => $customer->get_order_count(),
		'##customer_average_order_value##' => $customer->get_aov(),

		'##product_list##'                 => $product_list,
		'##product_table##'                => $product_table,
		'##number_of_items##'              => count( $order->get_items() ),
		'##order_total##'                  => easycommerce_price( $order->get_total() ),
		'##refunded_amount##'              => easycommerce_price( $order->get_total_refunded() ),
	);

	return apply_filters( 'easycommerce_order_placeholders', $placeholders, $order_id );
}

function easycommerce_cart_placeholders( $cart ) {

	// Start table HTML
	$product_table = '<table style="width:100%; border-collapse:collapse; font-family:Arial, sans-serif;">
	    <thead>
	        <tr>
	            <th style="border:1px solid #ddd; padding:8px; background:#f2f2f2;">Product</th>
	            <th style="border:1px solid #ddd; padding:8px; background:#f2f2f2;">Variation</th>
	            <th style="border:1px solid #ddd; padding:8px; background:#f2f2f2;">Qty</th>
	            <th style="border:1px solid #ddd; padding:8px; background:#f2f2f2;">Rate</th>
	            <th style="border:1px solid #ddd; padding:8px; background:#f2f2f2;">Subtotal</th>
	        </tr>
	    </thead>
	    <tbody>';

	// Start list HTML
	$product_list = '<ul>';

	// Loop through order items
	foreach ( $cart->get_items() as $product_id => $variations ) {
		foreach ( $variations as $variation_id => $item ) {
			$product   = new Product( $product_id );
			$variation = new Product_Variation( $variation_id );

			// Append table row
			$product_table .= '<tr>
		        <td style="border:1px solid #ddd; padding:8px;">' . $product->get_title() . '</td>
		        <td style="border:1px solid #ddd; padding:8px;">' . $variation->get_name( false ) . '</td>
		        <td style="border:1px solid #ddd; padding:8px;">' . $item['quantity'] . '</td>
		        <td style="border:1px solid #ddd; padding:8px;">' . easycommerce_price( $item['rate'] ) . '</td>
		        <td style="border:1px solid #ddd; padding:8px;">' . easycommerce_price( $item['price'] ) . '</td>
		    </tr>';

			// Append list item
			$product_list .= '<li>' . $product->get_title() . '</li>';
		}
	}

	// Close table and list
	$product_table .= '</tbody></table>';
	$product_list  .= '</ul>';

	$placeholders = array(
		// General cart details
		'##hash##'               => $cart->get_hash(),
		'##name##'               => esc_html( $cart->get_customer_name() ),
		'##customer_name##'      => esc_html( $cart->get_customer_name() ),
		'##email##'              => esc_html( $cart->get_customer_email() ),
		'##cart_link##'          => $cart->get_link(),
		'##cart_total##'         => easycommerce_price( $cart->get_amount( 'total' ) ),
		'##amount##'             => easycommerce_price( $cart->get_amount( 'total' ) ),
		'##number_of_items##'    => $cart->get_item_count(),
		'##order_status##'       => esc_html( $cart->get_status() ),
		'##random_coupon_code##' => (Utility::get_option( 'abandoned-cart', 'settings', 'random_coupon_discount_percentage', 0 ) > 0) ? easycommerce_generate_random_coupon_code() : '',

		// Billing details
		'##billing_phone##'      => esc_html( $cart->get_phone() ),
		'##billing_address_1##'  => esc_html( $cart->get_address_1() ),
		'##billing_address_2##'  => esc_html( $cart->get_address_2() ),
		'##billing_country##'    => esc_html( $cart->get_country() ),
		'##billing_state##'      => esc_html( $cart->get_state() ),
		'##billing_city##'       => esc_html( $cart->get_city() ),
		'##billing_postcode##'   => esc_html( $cart->get_postcode() ),

		// Shipping details
		'##shipping_phone##'     => esc_html( $cart->get_phone( 'shipping' ) ),
		'##shipping_address_1##' => esc_html( $cart->get_address_1( 'shipping' ) ),
		'##shipping_address_2##' => esc_html( $cart->get_address_2( 'shipping' ) ),
		'##shipping_country##'   => esc_html( $cart->get_country( 'shipping' ) ),
		'##shipping_state##'     => esc_html( $cart->get_state( 'shipping' ) ),
		'##shipping_city##'      => esc_html( $cart->get_city( 'shipping' ) ),
		'##shipping_postcode##'  => esc_html( $cart->get_postcode( 'shipping' ) ),

		'##product_list##'       => $product_list,
		'##product_table##'      => $product_table,

	);

	return $placeholders;
}

function easycommerce_generate_random_coupon_code() {
	$offer = Utility::get_option( 'abandoned-cart', 'settings', 'random_coupon_discount_percentage', 0 );
	if ( $offer <= 0 ) {
		return false;
	}

	$coupon_model = new Coupon_Model();
	$random_code  = strtoupper( substr( str_shuffle( 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789' ), 0, 8 ) );

	$coupon_id = $coupon_model->create(
		array(
			'name'   => $random_code,
			'code'   => $random_code,
			'type'   => 'percentage',
			'offer'  => $offer,
			'active' => 1,
		)
	);

	return $coupon_id ? $random_code : false;
}

function easycommerce_is_api_connected() {
	$api = get_option( 'easycommerce_api' );
	if ( ! empty( $api ) && ! empty( $api->email ) ) {
		return true;
	}

	if ( apply_filters( 'easycommerce-pro_licensed', false ) ) {
		return true;
	}

	return false;
}

/**
 * Returns true if the current page is a product page.
 *
 * @return bool
 */
function easycommerce_is_product(): bool {
	return is_singular( 'product' );
}

/**
 * Returns true if the current page is the shop page.
 *
 * @return bool
 */
function easycommerce_is_shop(): bool {
	return is_page( easycommerce_shop_page() );
}

/**
 * Returns true if the current page is the checkout page.
 *
 * @return bool
 */
function easycommerce_is_checkout(): bool {
	return is_page( easycommerce_checkout_page() ) || easycommerce_is_payment_page();
}

function easycommerce_is_payment_page(): bool {
	return is_page( easycommerce_payment_page() );
}

/**
 * Returns true if the current page is the account page.
 *
 * @return bool
 */
function easycommerce_is_dashboard(): bool {
	return is_page( easycommerce_dashboard_page() );
}

/**
 * Returns true if the current page is one of the EasyCommerce pages.
 *
 * @return bool
 */
function is_easycommerce_page(): bool {
	return easycommerce_is_product()
	|| easycommerce_is_shop()
	|| easycommerce_is_checkout()
	|| easycommerce_is_dashboard()
	|| easycommerce_is_payment_page();
}

/**
 * Import a remote image URL into WP Media Library.
 *
 * @param string $url     Remote image URL.
 * @param int    $post_id Optional post ID to attach to (0 = unattached).
 * @param string $desc    Optional attachment description.
 * @return array|WP_Error {
 *     On success: [
 *         'attachment_id' => int,
 *         'url'           => string,
 *         'file'          => string, // full path
 *         'mime'          => string,
 *         'width'         => int,
 *         'height'        => int,
 *         'sizes'         => array,
 *     ]
 *     On failure: WP_Error
 * }
 */
function easycommerce_import_image( $url, $post_id = 0, $desc = '' ) {
    if ( empty( $url ) ) {
        return new WP_Error( 'no_url', 'No URL provided.' );
    }

    // WP helpers
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    // download temp file
    $tmp = download_url( $url );
    if ( is_wp_error( $tmp ) ) {
        return $tmp;
    }

    // derive filename
    $path = wp_parse_url( $url, PHP_URL_PATH );
    $filename = $path ? wp_basename( $path ) : '';
    $filename = sanitize_file_name( $filename );
    if ( ! $filename ) {
        $ext = 'jpg';
        $filename = 'easycommerce-' . time() . '.' . $ext;
    }

    $file_array = array(
        'name'     => $filename,
        'tmp_name' => $tmp,
    );

    // sideload into uploads and create attachment
    $attach_id = media_handle_sideload( $file_array, $post_id, $desc );

    // cleanup temp on error
    if ( is_wp_error( $attach_id ) ) {
        @unlink( $tmp );
        return $attach_id;
    }

    // gather details
    $attachment_url = wp_get_attachment_url( $attach_id );
    $file_path      = get_attached_file( $attach_id );
    $mime_type      = get_post_mime_type( $attach_id );
    $meta           = wp_get_attachment_metadata( $attach_id );

    $width  = isset( $meta['width'] ) ? (int) $meta['width'] : 0;
    $height = isset( $meta['height'] ) ? (int) $meta['height'] : 0;
    $sizes  = isset( $meta['sizes'] ) ? $meta['sizes'] : array();

    return array(
        'attachment_id' => (int) $attach_id,
        'url'           => $attachment_url,
        'file'          => $file_path,
        'mime'          => $mime_type,
        'width'         => $width,
        'height'        => $height,
        'sizes'         => $sizes,
    );
}

function easycommerce_import_encoded_image( $data, $post_id = 0, $desc = '' ) {
	if ( empty( $data ) || ! preg_match( '/^data:image\/(\w+);base64,/', $data, $type ) ) {
		return new WP_Error( 'invalid_data', 'Invalid image data.' );
	}

	$data = substr( $data, strpos( $data, ',' ) + 1 );
	$data = base64_decode( $data );
	if ( $data === false ) {
		return new WP_Error( 'decode_error', 'Base64 decode failed.' );
	}

	// create temp file
	$tmp = tmpfile();
	if ( ! $tmp ) {
		return new WP_Error( 'temp_file_error', 'Could not create temp file.' );
	}

	fwrite( $tmp, $data );
	$meta = stream_get_meta_data( $tmp );
	$tmp_path = $meta['uri'];

	// derive filename
	$ext = strtolower( $type[1] );
	$filename = 'easycommerce-' . time() . '.' . $ext;

	$file_array = array(
		'name'     => sanitize_file_name( $filename ),
		'tmp_name' => $tmp_path,
	);

	// sideload into uploads and create attachment
	$attach_id = media_handle_sideload( $file_array, $post_id, $desc );

	// cleanup temp
	fclose( $tmp );

	if ( is_wp_error( $attach_id ) ) {
		return $attach_id;
	}

	// gather details
	$attachment_url = wp_get_attachment_url( $attach_id );
	$file_path      = get_attached_file( $attach_id );
	$mime_type      = get_post_mime_type( $attach_id );
	$meta           = wp_get_attachment_metadata( $attach_id );

	$width  = isset( $meta['width'] ) ? (int) $meta['width'] : 0;
	$height = isset( $meta['height'] ) ? (int) $meta['height'] : 0;
	$sizes  = isset( $meta['sizes'] ) ? $meta['sizes'] : array();

	return array(
		'attachment_id' => (int) $attach_id,
		'url'           => $attachment_url,
		'file'          => $file_path,
		'mime'          => $mime_type,
		'width'         => $width,
		'height'        => $height,
		'sizes'         => $sizes,
	);
}

function easycommerce_is_paid_addon_active() {
    return apply_filters( 'easycommerce_is_paid_addon_active', false );
}

/**
 * Single source of truth for the client's AI credit/plan state.
 *
 * One option (`easycommerce_ai`) holds everything: plan, monthly allowance,
 * usage, remaining balance, next reset date and the last hub-sync timestamp. It
 * replaces the legacy `easycommerce_ai_credits` + `easycommerce_ai_plan` options
 * and the status transient, migrating them once on first read.
 *
 * @return array { plan, plan_name, limit, used, remaining, next_refresh, synced_at }
 */
function easycommerce_ai_data() {

	$defaults = array(
		'plan'         => 'free',
		'plan_name'    => '',
		'limit'        => 0,
		'used'         => 0,
		'remaining'    => 100,
		'next_refresh' => '',
		'synced_at'    => 0,
	);

	$data = get_option( 'easycommerce_ai' );

	// One-time migration from the legacy split keys.
	if ( ! is_array( $data ) ) {
		$legacy_credits = get_option( 'easycommerce_ai_credits' );

		$data = array(
			'remaining' => is_numeric( $legacy_credits ) ? (int) $legacy_credits : $defaults['remaining'],
			'plan'      => get_option( 'easycommerce_ai_plan', $defaults['plan'] ),
		);

		update_option( 'easycommerce_ai', wp_parse_args( $data, $defaults ) );
		delete_option( 'easycommerce_ai_credits' );
		delete_option( 'easycommerce_ai_plan' );
		delete_transient( 'easycommerce_ai_status' );
	}

	return wp_parse_args( is_array( $data ) ? $data : array(), $defaults );
}

/**
 * Merge a partial update into the single AI state option.
 *
 * @param array $patch Keys to overwrite.
 * @return array The full, updated state.
 */
function easycommerce_ai_update( array $patch ) {
	$data = array_merge( easycommerce_ai_data(), $patch );
	update_option( 'easycommerce_ai', $data );
	return $data;
}

function easycommerce_get_ai_credits() {
    $credits = easycommerce_ai_data()['remaining'];
    if( ! is_numeric( $credits ) ) {
    	$credits = 100;
    }

    return apply_filters( 'easycommerce_ai_credits', $credits );
}

/**
 * Server-side onboarding/engagement snapshot for the deactivation survey.
 *
 * Every value is derived from existing WordPress state (options, post counts,
 * the ec_orders table) — nothing is tracked client-side, so it can't be spoofed
 * and costs the user no effort. Booleans are emitted as 'yes'/'no' strings and
 * counts are bucketed so the values map cleanly onto FluentCRM custom-field
 * filters/segments on the hub. Attached to the feedback body by
 * Connectivity::feedback() and forwarded to the CRM as ec_* custom fields.
 *
 * @return array
 */
function easycommerce_onboarding_snapshot() {

	// is_plugin_active() lives in wp-admin includes, which are NOT loaded in the
	// REST context this runs in.
	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$yes_no = function ( $flag ) {
		return $flag ? 'yes' : 'no';
	};

	$bucket = function ( $n ) {
		$n = (int) $n;
		if ( $n <= 0 ) {
			return '0';
		}
		if ( $n <= 10 ) {
			return '1-10';
		}
		if ( $n <= 100 ) {
			return '11-100';
		}
		return '100+';
	};

	global $wpdb;

	$activated       = (int) get_option( 'easycommerce_activated' );
	$order_count     = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}ec_orders" );
	$product_count   = (int) wp_count_posts( 'product' )->publish;
	$active_gateways = easycommerce_active_payment_methods();
	$ai              = easycommerce_ai_data();
	$api             = get_option( 'easycommerce_api' );

	// Recency: days since the most recent order. Distinguishes "tried and
	// abandoned day one" from "used for months, then left" — tenure alone can't.
	$last_order = $wpdb->get_var( "SELECT MAX(created_at) FROM {$wpdb->prefix}ec_orders" );
	$last_order_days = $last_order ? (int) floor( ( time() - strtotime( $last_order ) ) / DAY_IN_SECONDS ) : null;

	// Active plugins as a CSV of slugs (dir/file.php → dir), for conflict analysis.
	$active_plugins = array_map(
		function ( $plugin ) {
			return strtok( $plugin, '/' );
		},
		(array) get_option( 'active_plugins', array() )
	);

	// The four onboarding milestones (unordered — a user can hit them in any order).
	$wizard_completed   = (bool) get_option( 'easycommerce-setup_wizard' );
	$payment_configured = ! empty( $active_gateways );
	$has_product        = $product_count > 0;
	$ai_connected       = ! empty( $api->email );

	// A convenience 0-4 score for coarse "stalled user" segments; the per-step
	// booleans below are what real segmentation filters on.
	$score = (int) $wizard_completed + (int) $payment_configured + (int) $has_product + (int) $ai_connected;

	$snapshot = array(
		'days_active'        => $activated ? (int) floor( ( time() - $activated ) / DAY_IN_SECONDS ) : 0,
		'wizard_completed'   => $yes_no( $wizard_completed ),
		'payment_configured' => $yes_no( $payment_configured ),
		'has_product'        => $yes_no( $has_product ),
		'ai_connected'       => $yes_no( $ai_connected ),
		'onboarding_score'   => $score,
		'last_order_days'    => $last_order_days,
		'product_bucket'     => $bucket( $product_count ),
		'order_bucket'       => $bucket( $order_count ),
		'deactivation_count' => (int) get_option( 'easycommerce_deactivation_count', 0 ),
		'business_type'      => (string) Utility::get_option( 'general', 'business', 'business_type', '' ),
		'store_design'       => (string) get_option( 'easycommerce_store_design', '' ),
		'active_gateways'    => implode( ',', (array) $active_gateways ),
		'ai_plan'            => isset( $ai['plan'] ) ? $ai['plan'] : 'free',
		'ai_credits_used'    => isset( $ai['used'] ) ? (int) $ai['used'] : 0,
		'addons'             => implode( ',', array_keys( (array) get_option( 'easycommerce_addons', array() ) ) ),
		'active_plugins'     => implode( ',', $active_plugins ),
		'active_theme'       => (string) get_option( 'stylesheet' ),
		'plugin_version'     => defined( 'EASYCOMMERCE_VERSION' ) ? EASYCOMMERCE_VERSION : '',
		'wp_version'         => get_bloginfo( 'version' ),
		'php_version'        => PHP_VERSION,
		'locale'             => get_locale(),
		'multisite'          => $yes_no( is_multisite() ),
		'woo_active'         => $yes_no( is_plugin_active( 'woocommerce/woocommerce.php' ) ),
	);

	/**
	 * Filters the deactivation onboarding snapshot before it is sent to the hub.
	 *
	 * @since 1.45
	 * @param array $snapshot The derived onboarding/engagement values.
	 */
	return apply_filters( 'easycommerce_onboarding_snapshot', $snapshot );
}

/**
 * The telemetry event vocabulary understood by the hub.
 *
 * @since 1.45
 * @return string[]
 */
function easycommerce_telemetry_events() {
	return array( 'feedback', 'integration_request', 'setup_wizard', 'deactivation' );
}

/**
 * Resolve which telemetry event a submission represents.
 *
 * An explicit event wins. Anything else falls back to the legacy shape so an
 * older caller (or a third-party one) still classifies correctly.
 *
 * @since 1.45
 * @param string $event       Explicit event name, if any.
 * @param int    $deactivated Legacy deactivation flag.
 * @param string $subject     Submitted subject.
 * @return string
 */
function easycommerce_telemetry_event( $event, $deactivated = 0, $subject = '' ) {
	$event = sanitize_key( (string) $event );

	if ( in_array( $event, easycommerce_telemetry_events(), true ) ) {
		return $event;
	}

	if ( 1 === (int) $deactivated ) {
		return 'deactivation';
	}

	if ( 'setup_wizard' === (string) $subject ) {
		return 'setup_wizard';
	}

	return 'feedback';
}

/**
 * Whether the store owner consented to sharing diagnostics/usage data.
 *
 * Gates the passive site snapshot only — what the owner actually typed into a
 * feedback or deactivation form is their own submission and always goes.
 *
 * Sources, in precedence order:
 * - `easycommerce_share_data`, the standalone mirror written by the wizard. This
 *   is authoritative: the `general-business` option group it also lives in gets
 *   replaced wholesale on every settings save, so the group copy cannot be
 *   trusted to survive.
 * - `share_data` in the `easycommerce-general-business` group (the wizard's
 *   Business step checkbox), for installs saved before the mirror existed.
 * - `_easycommerce-no_tracking`, written by the wizard's older `dont_share_data`
 *   field (it was never read anywhere until now).
 *
 * Installs that predate the checkbox have none of these; they keep their existing
 * behaviour rather than silently going dark.
 *
 * @since 1.45
 * @return bool
 */
function easycommerce_can_share_data() {
	$consent = true;

	if ( get_option( '_easycommerce-no_tracking' ) ) {
		$consent = false;
	}

	$business = (array) get_option( 'easycommerce-general-business', array() );

	if ( isset( $business['share_data'] ) ) {
		$consent = (bool) (int) $business['share_data'];
	}

	$standalone = get_option( 'easycommerce_share_data', null );

	if ( ! is_null( $standalone ) ) {
		$consent = (bool) (int) $standalone;
	}

	/**
	 * Filters whether diagnostics may be shared with the hub.
	 *
	 * @since 1.45
	 * @param bool $consent Whether sharing is permitted.
	 */
	return (bool) apply_filters( 'easycommerce_can_share_data', $consent );
}

function easycommerce_deduct_ai_credits( $deduct = 1, $credits = null ) {

	if( is_null( $credits ) ) {
		$credits = easycommerce_get_ai_credits();
	}

	easycommerce_ai_update( array( 'remaining' => $credits - $deduct ) );
}

/**
 * Fetch the authoritative monthly AI credit status from the hub.
 *
 * Returns the plan, monthly allowance, usage, remaining balance and the next
 * reset date from the single `easycommerce_ai` option. Re-syncs from the hub
 * when the cached state is older than 10 minutes (tracked via `synced_at`).
 * Falls back to the locally-stored state when the hub is unreachable or no
 * account is connected.
 *
 * @param bool $force Skip the freshness check and re-fetch.
 * @return array See easycommerce_ai_data().
 */
function easycommerce_ai_status( $force = false ) {

	$data = easycommerce_ai_data();

	// Serve the stored state while it is still fresh.
	if ( ! $force && $data['synced_at'] && ( time() - (int) $data['synced_at'] ) < 10 * MINUTE_IN_SECONDS ) {
		return $data;
	}

	$api = get_option( 'easycommerce_api' );
	if ( empty( $api->email ) ) {
		return $data;
	}

	$response = wp_remote_get(
		easycommerce_dev_store( '/wp-json/easycommerce/v1/hub/ai/status' ),
		array(
			'timeout' => 15,
			'headers' => array( 'email' => $api->email ),
		)
	);

	if ( is_wp_error( $response ) ) {
		return $data;
	}

	$body = json_decode( wp_remote_retrieve_body( $response ) );
	if ( empty( $body->success ) || empty( $body->data ) ) {
		return $data;
	}

	return easycommerce_ai_update(
		array(
			'plan'         => $body->data->plan ?? 'free',
			'plan_name'    => $body->data->plan_name ?? '',
			'limit'        => (int) ( $body->data->limit ?? 0 ),
			'used'         => (int) ( $body->data->used ?? 0 ),
			'remaining'    => (int) ( $body->data->remaining ?? 0 ),
			'next_refresh' => $body->data->next_refresh ?? '',
			'synced_at'    => time(),
		)
	);
}

function easycommerce_get_conflicting_plugins() {
    return apply_filters( 'easycommerce-conflicting_plugins', array(
        'easycommerce-stripe/easycommerce-stripe.php',
        'easycommerce-paypal/easycommerce-paypal.php',
        'easycommerce-square/easycommerce-square.php',
        'easycommerce-mollie/easycommerce-mollie.php',
        'easycommerce-braintree/easycommerce-braintree.php',
        'easycommerce-cash-on-delivery/easycommerce-cash-on-delivery.php',
        'easycommerce-csv-importer/easycommerce-csv-importer.php',
    ) );
}

function easycommerce_detect_external_plugins_for_migration() {
    $active_plugins = array(
        'woocommerce/woocommerce.php'	=> 'WooCommerce',
        'easy-digital-downloads/easy-digital-downloads.php'	=> 'Easy Digital Downloads',
	);

    $detected = null;

    foreach ( $active_plugins as $main_file => $name ) {
        if ( is_plugin_active( $main_file ) ) {
            $detected = $name;
            break;
        }
    }

    return $detected;
}

function easycommerce_get_migratable_platforms() {
	$active_plugins = array(
		'woocommerce/woocommerce.php'                       => 'WooCommerce',
		'easy-digital-downloads/easy-digital-downloads.php' => 'Easy Digital Downloads',
	);

	$detected = [];

	foreach ( $active_plugins as $main_file => $name ) {
		if ( is_plugin_active( $main_file ) ) {
			$detected[] = $name;
		}
	}

	return $detected;
}

function easycommerce_is_compatible_theme_active() {
	$current_theme 			= wp_get_theme();
	$current_theme_slug 	= $current_theme->get_template();

	$compatible_theme_list 	= easycommerce_compatible_themes();

	if ( in_array( $current_theme_slug, $compatible_theme_list ) ) {
		return true;
	}

	return false;
}

function easycommerce_compatible_themes() {
	$themes = array_map(
		fn( $file ) => pathinfo( $file, PATHINFO_FILENAME ),
		glob( EASYCOMMERCE_PLUGIN_DIR . '/assets/public/css/themes/' . '*.css' )
	);

	return apply_filters( 'easycommerce_compatible_themes', $themes );
}

function easycommerce_get_user_country() {
	$cacher = easycommerce_cache();

    $ip = sanitize_text_field( $_SERVER['REMOTE_ADDR'] );

    if ( $ip === '127.0.0.1' || $ip === '::1' ) {
        $ip = '8.8.8.8'; // Use Google DNS for testing, or set default country
    }

    if ( $cached = $cacher->get_cache( 'user_country_' . md5( $ip ) ) ) {
        return $cached;
    }

    $country = null;

    // Get country from IP API
    $response = wp_remote_get( "https://ipapi.co/{$ip}/country_code/", [
        'timeout' => 10
    ] );

    if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
        $country = trim( wp_remote_retrieve_body( $response ) );
    }

    if ( ! $country || strlen( $country ) !== 2 ) {
        $country = 'US'; // Default
    }

    $cacher->set_cache( 'user_country_' . md5( $ip ), $country, DAY_IN_SECONDS );

    return $country;
}

function easycommerce_business_address_1() {
	$settings = get_option( 'easycommerce-general-business', array() );

	return $settings['address_1'] ?? '';
}
function easycommerce_business_address_2() {
	$settings = get_option( 'easycommerce-general-business', array() );

	return $settings['address_2'] ?? '';
}
function easycommerce_business_state() {
	$settings = get_option( 'easycommerce-general-business', array() );

	return $settings['state'] ?? '';
}
function easycommerce_business_city() {
	$settings = get_option( 'easycommerce-general-business', array() );

	return $settings['city'] ?? '';
}
function easycommerce_business_postcode() {
	$settings = get_option( 'easycommerce-general-business', array() );

	return $settings['postcode'] ?? '';
}
function easycommerce_business_country() {
	$settings = get_option( 'easycommerce-general-business', array() );

	return $settings['country'] ?? '';
}
function easycommerce_business_full_address(): string {
	$address = array(
		easycommerce_business_address_1(),
		easycommerce_business_address_2(),
		easycommerce_business_state(),
		easycommerce_business_city(),
		easycommerce_business_country(),
		easycommerce_business_postcode(),
	);

	return implode( ', ', $address );
}
