<?php
/**
 * Ready-made store designs registry.
 *
 * Each design bundles the store page patterns (from #3171) into a complete,
 * visually distinct storefront plus a full design-token preset. The registry
 * is pure data — page compositions are referenced by pattern SLUG and resolved
 * to markup at apply time (see easycommerce_apply_store_design()), because
 * patterns register on `init`, after this config file loads.
 *
 * Designs map onto the three shop templates:
 *   grocery/furniture/boutique → template-1, beauty/gadget → template-2,
 *   fashion → template-3.
 *
 * The `preset` carries three layers that make each design feel like its own
 * design system, not a re-skin of a shared one:
 *   - colors:     palette + surface/border/muted for cards and sections.
 *   - typography: heading + body font pairing.
 *   - tokens:     the deliberate visual system — radius scale, elevation
 *                 (shadow vs border vs flat), 8-step spacing rhythm, section
 *                 transition style, card treatment, button system, heading
 *                 scale. Rendered to scoped CSS by
 *                 easycommerce_store_design_preset_css().
 *
 * @see easycommerce_store_designs()
 * @see easycommerce_apply_store_design()
 * @see easycommerce_store_design_preset_css()
 */

defined( 'ABSPATH' ) || exit;

global $easycommerce_store_designs;

$easycommerce_store_designs = apply_filters(
	'easycommerce_store_designs',
	array(

		/*
		 * GROCERY (formerly "classic") — traditional retail / department-store.
		 * Warm neutrals, serif headings, tight corners, hairline-bordered flat
		 * cards, hard section edges with an alternating cream tint. Depth comes
		 * from borders and delineation, not float. Moderate 8px rhythm.
		 */
		'grocery' => array(
			'label'         => __( 'Grocery', 'easycommerce' ),
			'description'   => __( 'Grid-led, sidebar filters, conventional retail feel.', 'easycommerce' ),
			'screenshot'    => EASYCOMMERCE_ASSETS_URL . 'admin/img/designs/grocery.webp',
			'shop_template' => 'template-1',
			'pages'         => array(
				'home' => 'easycommerce/store-page-homepage-grocery',
				'shop' => 'easycommerce/store-page-shop',
			),
			'preset'        => array(
				'colors'     => array(
					'primary'    => '#7351FD',
					'background' => '#FFFFFF',
					'text'       => '#26221B',
					'accent'     => '#6D4AFF',
					'surface'    => '#FAF8F4',
					'border'     => '#E7E1D6',
					'muted'      => '#7A7266',
				),
				'typography' => array(
					'heading' => 'Georgia, "Times New Roman", serif',
					'body'    => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
				),
				'tokens'     => array(
					'radius'  => array( 'card' => '4px', 'button' => '3px', 'image' => '4px' ),
					'shadow'  => array(
						'card'  => '0 1px 0 rgba(38,34,27,0.05)',
						'hover' => '0 8px 22px -14px rgba(38,34,27,0.35)',
					),
					'card'    => array( 'fill' => '#FFFFFF', 'border' => '1px solid #E7E1D6' ),
					'space'   => array( 8, 14, 20, 28, 40, 52, 68, 84 ),
					'section' => array( 'rhythm' => 'edge', 'divider' => '1px solid #ECE6DB', 'alt_bg' => '#FAF8F4' ),
					'button'  => array(
						'radius'    => '3px',
						'transform' => 'uppercase',
						'tracking'  => '0.06em',
						'weight'    => '600',
						'size'      => '13px',
						'pad'       => '13px 26px',
						'hover'     => 'darken',
					),
					'heading' => array(
						'weight'    => '700',
						'tracking'  => '0',
						'transform' => 'none',
						'h2'        => '30px',
						'line'      => '1.2',
					),
				),
			),
		),

		/*
		 * BEAUTY (formerly "modern") — contemporary DTC / SaaS-store.
		 * Cool high-contrast ink + vivid orange accent, geometric sans (Poppins),
		 * medium-large radius, soft elevation shadows that lift on hover,
		 * borderless image-bleed cards, airy gap-based section transitions.
		 */
		'beauty'  => array(
			'label'         => __( 'Beauty', 'easycommerce' ),
			'description'   => __( 'Full-width, large imagery, featured-product-led.', 'easycommerce' ),
			'screenshot'    => EASYCOMMERCE_ASSETS_URL . 'admin/img/designs/beauty.webp',
			'shop_template' => 'template-2',
			'pages'         => array(
				'home' => 'easycommerce/store-page-homepage-beauty',
				'shop' => 'easycommerce/store-page-shop',
			),
			'preset'        => array(
				'colors'     => array(
					'primary'    => '#111827',
					'background' => '#FFFFFF',
					'text'       => '#111827',
					'accent'     => '#F97316',
					'surface'    => '#F9FAFB',
					'border'     => '#ECEFF3',
					'muted'      => '#6B7280',
				),
				'typography' => array(
					'heading' => '"Poppins", -apple-system, BlinkMacSystemFont, sans-serif',
					'body'    => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
				),
				'tokens'     => array(
					'radius'  => array( 'card' => '16px', 'button' => '10px', 'image' => '12px' ),
					'shadow'  => array(
						'card'  => '0 12px 32px -14px rgba(17,24,39,0.22)',
						'hover' => '0 22px 46px -16px rgba(17,24,39,0.32)',
					),
					'card'    => array( 'fill' => '#FFFFFF', 'border' => 'none' ),
					'space'   => array( 8, 16, 28, 44, 64, 88, 112, 140 ),
					'section' => array( 'rhythm' => 'gap', 'divider' => 'none', 'alt_bg' => '#F9FAFB' ),
					'button'  => array(
						'radius'    => '10px',
						'transform' => 'none',
						'tracking'  => '0',
						'weight'    => '600',
						'size'      => '15px',
						'pad'       => '14px 30px',
						'hover'     => 'lift',
					),
					'heading' => array(
						'weight'    => '800',
						'tracking'  => '-0.01em',
						'transform' => 'none',
						'h2'        => '40px',
						'line'      => '1.1',
					),
				),
			),
		),

		/*
		 * GADGET — dark tech / electronics store.
		 * Ink-navy banner mosaic with an electric-blue accent and a yellow deal
		 * kicker, cool grey flat cards on white, medium radius, lift hovers.
		 * Deal-led, dense above the fold, airy gap-based sections below.
		 */
		'gadget' => array(
			'label'         => __( 'Gadget', 'easycommerce' ),
			'description'   => __( 'Dark banner mosaic, deal-led electronics feel.', 'easycommerce' ),
			'screenshot'    => EASYCOMMERCE_ASSETS_URL . 'admin/img/designs/gadget.webp',
			'shop_template' => 'template-2',
			'pages'         => array(
				'home' => 'easycommerce/store-page-homepage-gadget',
				'shop' => 'easycommerce/store-page-shop',
			),
			'preset'        => array(
				'colors'     => array(
					'primary'    => '#0B1120',
					'background' => '#FFFFFF',
					'text'       => '#0F172A',
					'accent'     => '#2563EB',
					'surface'    => '#F4F6F8',
					'border'     => '#E3E8EF',
					'muted'      => '#64748B',
				),
				'typography' => array(
					'heading' => '"Space Grotesk", "Inter", -apple-system, BlinkMacSystemFont, sans-serif',
					'body'    => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
				),
				'tokens'     => array(
					'radius'  => array( 'card' => '10px', 'button' => '8px', 'image' => '10px' ),
					'shadow'  => array(
						'card'  => '0 6px 18px -10px rgba(11,17,32,0.16)',
						'hover' => '0 18px 38px -14px rgba(11,17,32,0.32)',
					),
					'card'    => array( 'fill' => '#F4F6F8', 'border' => 'none' ),
					'space'   => array( 8, 14, 22, 32, 46, 62, 80, 100 ),
					'section' => array( 'rhythm' => 'gap', 'divider' => 'none', 'alt_bg' => '#F4F6F8' ),
					'button'  => array(
						'radius'    => '8px',
						'transform' => 'none',
						'tracking'  => '0',
						'weight'    => '600',
						'size'      => '14px',
						'pad'       => '12px 26px',
						'hover'     => 'lift',
					),
					'heading' => array(
						'weight'    => '700',
						'tracking'  => '-0.02em',
						'transform' => 'none',
						'h2'        => '28px',
						'line'      => '1.15',
					),
				),
			),
		),

		/*
		 * FURNITURE — warm banner-led showroom.
		 * White with a warm-grey band tint and a burnt-orange accent, hairline
		 * warm-bordered cards, small radius, edge-to-edge sections divided by
		 * warm hairlines. Banner-heavy, room-and-deal-led merchandising.
		 */
		'furniture' => array(
			'label'         => __( 'Furniture', 'easycommerce' ),
			'description'   => __( 'Warm, banner-led showroom with orange accents.', 'easycommerce' ),
			'screenshot'    => EASYCOMMERCE_ASSETS_URL . 'admin/img/designs/furniture.webp',
			'shop_template' => 'template-1',
			'pages'         => array(
				'home' => 'easycommerce/store-page-homepage-furniture',
				'shop' => 'easycommerce/store-page-shop',
			),
			'preset'        => array(
				'colors'     => array(
					'primary'    => '#23303E',
					'background' => '#FFFFFF',
					'text'       => '#2A2F36',
					'accent'     => '#E86A17',
					'surface'    => '#F7F6F3',
					'border'     => '#EAE6E0',
					'muted'      => '#8A857D',
				),
				'typography' => array(
					'heading' => '"DM Sans", "Segoe UI", -apple-system, BlinkMacSystemFont, sans-serif',
					'body'    => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
				),
				'tokens'     => array(
					'radius'  => array( 'card' => '6px', 'button' => '4px', 'image' => '6px' ),
					'shadow'  => array(
						'card'  => '0 1px 3px rgba(43,33,25,0.06)',
						'hover' => '0 12px 26px -12px rgba(43,33,25,0.28)',
					),
					'card'    => array( 'fill' => '#FFFFFF', 'border' => '1px solid #EFECE6' ),
					'space'   => array( 8, 16, 24, 36, 52, 68, 88, 108 ),
					'section' => array( 'rhythm' => 'edge', 'divider' => '1px solid #EFECE6', 'alt_bg' => '#F7F6F3' ),
					'button'  => array(
						'radius'    => '4px',
						'transform' => 'none',
						'tracking'  => '0',
						'weight'    => '600',
						'size'      => '14px',
						'pad'       => '12px 28px',
						'hover'     => 'darken',
					),
					'heading' => array(
						'weight'    => '700',
						'tracking'  => '0',
						'transform' => 'none',
						'h2'        => '30px',
						'line'      => '1.2',
					),
				),
			),
		),

		/*
		 * FASHION — bold youth / streetwear.
		 * Black ink on white with saturated yellow bands (inline #f2d22e in the
		 * pattern), chunky uppercase headings, pill buttons, surface-less
		 * editorial cards with a soft cream band tint. Loud where the promos
		 * are, flush and image-led everywhere else.
		 */
		'fashion' => array(
			'label'         => __( 'Fashion', 'easycommerce' ),
			'description'   => __( 'Bold yellow-and-black streetwear feel.', 'easycommerce' ),
			'screenshot'    => EASYCOMMERCE_ASSETS_URL . 'admin/img/designs/fashion.webp',
			'shop_template' => 'template-3',
			'pages'         => array(
				'home' => 'easycommerce/store-page-homepage-fashion',
				'shop' => 'easycommerce/store-page-shop',
			),
			'preset'        => array(
				'colors'     => array(
					'primary'    => '#141414',
					'background' => '#FFFFFF',
					'text'       => '#141414',
					'accent'     => '#141414',
					'surface'    => '#FDF6D8',
					'border'     => '#ECECEC',
					'muted'      => '#6F6F6F',
				),
				'typography' => array(
					'heading' => '"Archivo", "Poppins", -apple-system, BlinkMacSystemFont, sans-serif',
					'body'    => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
				),
				'tokens'     => array(
					'radius'  => array( 'card' => '12px', 'button' => '999px', 'image' => '12px' ),
					'shadow'  => array(
						'card'  => 'none',
						'hover' => '0 16px 34px -16px rgba(20,20,20,0.25)',
					),
					'card'    => array( 'fill' => 'transparent', 'border' => 'none' ),
					'space'   => array( 8, 16, 26, 40, 56, 76, 96, 120 ),
					'section' => array( 'rhythm' => 'gap', 'divider' => 'none', 'alt_bg' => '#FDF6D8' ),
					'button'  => array(
						'radius'    => '999px',
						'transform' => 'uppercase',
						'tracking'  => '0.08em',
						'weight'    => '700',
						'size'      => '13px',
						'pad'       => '14px 30px',
						'hover'     => 'lift',
					),
					'heading' => array(
						'weight'    => '800',
						'tracking'  => '0.01em',
						'transform' => 'uppercase',
						'h2'        => '28px',
						'line'      => '1.1',
					),
				),
			),
		),

		/*
		 * BOUTIQUE — ink-and-amber storefront (from the "Threadline" handoff).
		 * Ink #101820 surfaces and gradients with amber #F5A623 inline on dark,
		 * Plus Jakarta Sans with tight-tracked 800 headings and mono-style
		 * uppercase kickers, hairline #EEF0F2 bordered cards (radius 16), no
		 * resting shadow but a deep lift shadow on hover. Accent is ink so the
		 * shared button system stays legible; amber CTAs are inline in the
		 * pattern where the design wants dark-on-amber.
		 */
		'boutique' => array(
			'label'         => __( 'Boutique', 'easycommerce' ),
			'description'   => __( 'Ink-and-amber storefront with a deal band.', 'easycommerce' ),
			'screenshot'    => EASYCOMMERCE_ASSETS_URL . 'admin/img/designs/boutique.webp',
			'shop_template' => 'template-1',
			'pages'         => array(
				'home' => 'easycommerce/store-page-homepage-boutique',
				'shop' => 'easycommerce/store-page-shop',
			),
			'preset'        => array(
				'colors'     => array(
					'primary'    => '#101820',
					'background' => '#FFFFFF',
					'text'       => '#16202A',
					'accent'     => '#101820',
					'surface'    => '#F4F5F6',
					'border'     => '#EEF0F2',
					'muted'      => '#737D87',
				),
				'typography' => array(
					'heading' => '"Plus Jakarta Sans", -apple-system, BlinkMacSystemFont, sans-serif',
					'body'    => '"Plus Jakarta Sans", -apple-system, BlinkMacSystemFont, sans-serif',
				),
				'tokens'     => array(
					'radius'  => array( 'card' => '16px', 'button' => '11px', 'image' => '12px' ),
					'shadow'  => array(
						'card'  => 'none',
						'hover' => '0 16px 30px -18px rgba(16,24,32,0.3)',
					),
					'card'    => array( 'fill' => '#FFFFFF', 'border' => '1px solid #EEF0F2' ),
					'space'   => array( 8, 14, 20, 28, 36, 48, 60, 72 ),
					'section' => array( 'rhythm' => 'gap', 'divider' => 'none', 'alt_bg' => '#FBFBFC' ),
					'button'  => array(
						'radius'    => '11px',
						'transform' => 'none',
						'tracking'  => '0',
						'weight'    => '700',
						'size'      => '15px',
						'pad'       => '15px 30px',
						'hover'     => 'lift',
					),
					'heading' => array(
						'weight'    => '800',
						'tracking'  => '-0.02em',
						'transform' => 'none',
						'h2'        => '28px',
						'line'      => '1.1',
					),
				),
			),
		),
	)
);
