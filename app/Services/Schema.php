<?php
namespace EasyCommerce\Services;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Models\Product;
use EasyCommerce\Helpers\Utility;

/**
 * Builds and prints schema.org JSON-LD for the storefront.
 *
 * Currently covers the single-product page: Product (with category, tags as
 * keywords, and brand) + Offer/AggregateOffer + AggregateRating (when review
 * data exists) + BreadcrumbList. Hooked from Controllers\Front\Init::product_head()
 * on `wp_head`.
 *
 * Defers automatically when a major SEO plugin (Yoast, RankMath, AIOSEO) is
 * active and configured to output its own Product schema, to avoid duplicate
 * markup in Search Console.
 */
class Schema {

	/**
	 * Print the JSON-LD block for the current singular product, if applicable.
	 *
	 * @param Product $product
	 *
	 * @return void
	 */
	public function output_product( Product $product ) {

		if ( $this->is_seo_plugin_handling_schema() ) {
			return;
		}

		$graph = array_filter(
			array(
				$this->build_product_node( $product ),
				$this->build_breadcrumb_node( $product ),
			)
		);

		if ( empty( $graph ) ) {
			return;
		}

		$payload = array(
			'@context' => 'https://schema.org',
			'@graph'   => array_values( $graph ),
		);

		/**
		 * Filters the full JSON-LD payload before it's printed.
		 *
		 * @param array   $payload The @graph payload.
		 * @param Product $product The current product.
		 */
		$payload = apply_filters( 'easycommerce_product_schema', $payload, $product );

		printf(
			'<script type="application/ld+json">%s</script>' . "\n",
			wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
		);
	}

	/**
	 * Product node: category, brand, keywords (tags), offers, rating.
	 *
	 * @param Product $product
	 *
	 * @return array
	 */
	protected function build_product_node( Product $product ) {

		$node = array(
			'@type'       => 'Product',
			'@id'         => $product->get_url() . '#product',
			'name'        => $product->get_title(),
			'url'         => $product->get_url(),
			'description' => $this->plain_text_excerpt( $product ),
		);

		$image = $this->get_image_url( $product );
		if ( $image ) {
			$node['image'] = $image;
		}

		// Category — schema.org's `category` property takes free text; join
		// multiple assigned categories since a product can have more than one.
		$categories = $product->get_categories();
		if ( ! empty( $categories ) ) {
			$node['category'] = implode( ' > ', wp_list_pluck( $categories, 'name' ) );
		}

		// Tags — no dedicated schema.org "tags" property; `keywords` is the
		// standard property Google reads for this on Product.
		$tags = $product->get_tags();
		if ( ! empty( $tags ) ) {
			$node['keywords'] = implode( ', ', wp_list_pluck( $tags, 'name' ) );
		}

		// Brand — prefer the actual product_brand taxonomy; fall back to the
		// site name only when no brand term is assigned, since Google treats
		// a present-but-wrong brand as worse than an absent one.
		$brands = $product->get_brands();
		if ( ! empty( $brands ) ) {
			$node['brand'] = array(
				'@type' => 'Brand',
				'name'  => reset( $brands )->name,
			);
		} else {
			$fallback_brand = apply_filters( 'easycommerce_product_schema_brand_fallback', get_bloginfo( 'name' ), $product );
			if ( $fallback_brand ) {
				$node['brand'] = array(
					'@type' => 'Brand',
					'name'  => $fallback_brand,
				);
			}
		}

		$product_type = $this->get_product_type( $product );

		$offer = $this->build_offer_node( $product, $product_type );
		if ( $offer ) {
			$node['offers'] = $offer;
		}

		$rating = $this->build_rating_node( $product );
		if ( $rating ) {
			$node['aggregateRating'] = $rating;
		}

		if ( $product_type ) {
			$node['additionalProperty'] = array(
				array(
					'@type' => 'PropertyValue',
					'name'  => 'Product Type',
					'value' => ucfirst( $product_type ),
				),
			);
		}

		return $node;
	}

	/**
	 * Whether the product is digital, physical, or a mix of both, derived
	 * from its variations' actual `type` field rather than assumed — a
	 * product can have both digital and physical variations at once.
	 *
	 * Schema.org has no dedicated enum for this on Product, so it's surfaced
	 * as an additionalProperty PropertyValue, which is the pattern Google's
	 * own structured-data examples use for attributes without a native field.
	 *
	 * @param Product $product
	 *
	 * @return string 'digital', 'physical', 'mixed', or '' if it has no variations yet.
	 */
	protected function get_product_type( Product $product ) {

		$variations = $product->get_variations();

		if ( empty( $variations ) ) {
			return '';
		}

		$types = array_unique(
			array_map(
				function ( $variation ) {
					return $variation->get_type() === 'digital' ? 'digital' : 'physical';
				},
				$variations
			)
		);

		if ( count( $types ) > 1 ) {
			return 'mixed';
		}

		return reset( $types );
	}

	/**
	 * Offer (single price) or AggregateOffer (variable/ranged price) node.
	 *
	 * Product::get_price( false ) returns either a plain numeric string for a
	 * single-priced product, or a "min-max" range string for a variable one
	 * (see Product::get_price()) — branch on that rather than re-deriving the
	 * range ourselves.
	 *
	 * @param Product $product
	 * @param string  $product_type 'digital', 'physical', 'mixed', or '' — from get_product_type().
	 *                              Used only to decide whether itemCondition applies.
	 *
	 * @return array|null
	 */
	protected function build_offer_node( Product $product, $product_type = '' ) {

		$raw_price = $product->get_price( false );

		if ( '' === $raw_price || false === $raw_price || null === $raw_price ) {
			return null;
		}

		$currency = Utility::get_option( 'general', 'store', 'currency' ) ?: 'USD';
		$currency = apply_filters( 'easycommerce_schema_default_currency', $currency, $product );

		$stock       = $product->get_stock();
		$out_of_stock = ( null !== $stock && $stock <= 0 );
		$availability = $out_of_stock ? 'https://schema.org/OutOfStock' : 'https://schema.org/InStock';

		// itemCondition ("New"/"Used") describes physical goods; a digital
		// download has no condition, so omit the field rather than print a
		// misleading "New" on something that was never a physical unit.
		$include_condition = ( 'digital' !== $product_type );

		if ( is_string( $raw_price ) && false !== strpos( $raw_price, '-' ) ) {
			list( $low, $high ) = array_map( 'trim', explode( '-', $raw_price, 2 ) );

			$offer = array(
				'@type'         => 'AggregateOffer',
				'url'           => $product->get_url(),
				'priceCurrency' => $currency,
				'lowPrice'      => $low,
				'highPrice'     => $high,
				'offerCount'    => count( $product->get_prices( false ) ),
				'availability'  => $availability,
			);

			return $offer;
		}

		$offer = array(
			'@type'         => 'Offer',
			'url'           => $product->get_url(),
			'priceCurrency' => $currency,
			'price'         => (string) $raw_price,
			'availability'  => $availability,
		);

		if ( $include_condition ) {
			$offer['itemCondition'] = 'https://schema.org/NewCondition';
		}

		return $offer;
	}

	/**
	 * AggregateRating node, built from Product::get_rating() / get_rating_count().
	 * Omitted entirely when there are no reviews yet — Google penalizes
	 * fabricated/default ratings, so absence is preferable to a fake value.
	 *
	 * @param Product $product
	 *
	 * @return array|null
	 */
	protected function build_rating_node( Product $product ) {

		$count = (int) $product->get_rating_count();

		if ( $count < 1 ) {
			return null;
		}

		$average = $product->get_rating();

		if ( ! is_numeric( $average ) ) {
			return null;
		}

		return array(
			'@type'       => 'AggregateRating',
			'ratingValue' => (string) round( (float) $average, 1 ),
			'reviewCount' => $count,
		);
	}

	/**
	 * BreadcrumbList node: Shop > category (if any) > Product.
	 *
	 * @param Product $product
	 *
	 * @return array|null
	 */
	protected function build_breadcrumb_node( Product $product ) {

		$items    = array();
		$position = 1;

		$shop_page_id = function_exists( 'easycommerce_shop_page' ) ? easycommerce_shop_page() : 0;
		if ( $shop_page_id ) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $position++,
				'name'     => get_the_title( $shop_page_id ) ?: __( 'Shop', 'easycommerce' ),
				'item'     => get_permalink( $shop_page_id ),
			);
		}

		$categories = $product->get_categories();
		if ( ! empty( $categories ) ) {
			$term    = reset( $categories );
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $position++,
				'name'     => $term->name,
				'item'     => get_term_link( (int) $term->id, 'product_cat' ),
			);
		}

		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $position,
			'name'     => $product->get_title(),
			'item'     => $product->get_url(),
		);

		if ( count( $items ) < 2 ) {
			return null;
		}

		return array(
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $items,
		);
	}

	/**
	 * True if an active SEO plugin is already configured to emit Product
	 * schema for this post type, so we don't double up.
	 *
	 * @return bool
	 */
	protected function is_seo_plugin_handling_schema() {

		$defer = false;

		// Yoast SEO: emits its own Product/Article graph via wpseo_json_ld
		// pipeline whenever the plugin is active; no reliable per-post
		// "schema disabled" flag exposed publicly, so presence == defer.
		if ( defined( 'WPSEO_VERSION' ) ) {
			$defer = true;
		}

		// Rank Math: schema is opt-in per post via its own meta box
		// (rank_math_rich_snippet). Only defer when *this* post is set to Product.
		if ( defined( 'RANK_MATH_VERSION' ) ) {
			$snippet_type = get_post_meta( get_the_ID(), 'rank_math_rich_snippet', true );
			$defer        = $defer || ( 'product' === $snippet_type );
		}

		// All in One SEO: similar opt-in per-post schema type meta.
		if ( defined( 'AIOSEO_VERSION' ) ) {
			$aioseo_type = get_post_meta( get_the_ID(), '_aioseo_schema_type', true );
			$defer       = $defer || ( 'Product' === $aioseo_type );
		}

		/**
		 * Filters whether EasyCommerce should skip printing its own schema
		 * because another plugin is expected to handle it.
		 *
		 * @param bool $defer
		 */
		return apply_filters( 'easycommerce_defer_product_schema_to_seo_plugin', $defer );
	}

	/**
	 * Best available image URL: gallery-size thumbnail, falling back to the
	 * raw post thumbnail if the product-specific size isn't registered.
	 *
	 * @param Product $product
	 *
	 * @return string
	 */
	protected function get_image_url( Product $product ) {
		$thumbnail = $product->get_thumbnail();

		if ( is_array( $thumbnail ) && ! empty( $thumbnail['url'] ) ) {
			return $thumbnail['url'];
		}

		return get_the_post_thumbnail_url( $product->get_id(), 'full' ) ?: '';
	}

	/**
	 * Plain-text, schema-safe excerpt (JSON-LD strings must not contain markup).
	 * Prefers the merchant-written summary, falls back to the description meta,
	 * then to the post excerpt.
	 *
	 * @param Product $product
	 *
	 * @return string
	 */
	protected function plain_text_excerpt( Product $product ) {
		$raw = $product->get_summary();

		if ( '' === $raw || null === $raw ) {
			$raw = $product->get_description();
		}

		if ( '' === $raw || null === $raw ) {
			$raw = get_the_excerpt( $product->get_id() );
		}

		$raw = wp_strip_all_tags( (string) $raw );

		return wp_trim_words( $raw, 55, '…' );
	}
}