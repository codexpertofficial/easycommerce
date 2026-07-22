<?php
if ( get_post_type( get_the_ID() ) !== 'product' ) {
	esc_html_e( 'Post type is not product', 'easycommerce' );
	return;
}

use EasyCommerce\Helpers\Utility;
use EasyCommerce\Models\Product as Product_Model;
use EasyCommerce\Models\Attribute;
use EasyCommerce\Models\Attribute_Value;

$product = new Product_Model( get_the_ID() );
$prices  = $product->get_prices();

if ( empty( $prices ) ) return;

$attribute_model       = new Attribute();
$attribute_value_model = new Attribute_Value();
$all_attributes_values = $attribute_value_model->get_all();
$attribute_values      = array();

foreach ( $all_attributes_values as $attributes_value ) {
	$attribute = $attribute_model->get( $attributes_value->attribute_id );
	$attribute_values[ $attributes_value->id ] = array(
		'value' => $attributes_value->value,
		'type'  => $attribute->type ?? 'text',
		'name'  => $attributes_value->name,
		'slug'  => $attributes_value->slug,
	);
}

$attributes_data = array();
foreach ( $prices as $price ) {
	$dynamic_attributes = array();
	
	if ( ! empty( $price['attributes'] ) ) {
		foreach ( $price['attributes'] as $key => $attribute ) {
			$attribute_info           = $attribute_model->get( $attribute->attribute_id );
			$attribute_value          = $attribute_value_model->get( $attribute->value_id );
			$dynamic_attributes[ $key ] = (object) array(
				'id'             => $attribute->id,
				'variation_id'   => $attribute->variation_id,
				'attribute_id'   => $attribute->attribute_id,
				'attribute_slug' => $attribute_info->slug,
				'value_id'       => $attribute->value_id,
				'value_slug'     => $attribute_value->slug,
			);
		}
	}

	$attributes_data[] = array(
		'id'          => $price['id'],
		'price'       => easycommerce_price( $price['regular_price'] ),
		'sale_price'  => $price['price'],
		'price_id'    => $price['price_id'],
		'attributes'  => $dynamic_attributes,
		'stock_count' => $price['stock_quantity'],
		'type'        => $price['type'],
	);
}

$attribute_fields = array();
if ( ! is_admin() && ! empty( $prices[0]['attributes'] ) ) {
	foreach ( $prices[0]['attributes'] as $attr ) {
		if ( ! isset( $attribute_fields[ $attr->attribute_id ] ) ) {
			$attribute = $attribute_model->get( $attr->attribute_id );
			$attribute_fields[ $attr->attribute_id ] = $attribute->slug;
		}
	}
}
do_action( 'easycommerce_block/shop_assets' );
?>

<div class="easycommerce-attributes-wrapper" data-variations="<?php echo esc_attr( wp_json_encode( $attributes_data ) ); ?>">
	<?php foreach ( $attribute_fields as $attribute_id => $attribute_slug ) : ?>
		<div class="attribute_<?php echo esc_attr( $attribute_slug ); ?> easycommerce-vs-wrapper border-b-[1px] mb-3 pb-3 border-ec-table-stock">
			<p class="easycommerce-tax-name mb-2">
				<?php echo esc_html( ucfirst( $attribute_slug ) ); ?>
			</p>
			
			<?php
			$unique_values = array();
			foreach ( $prices as $price ) {
				if ( empty( $price['attributes'] ) ) {
					continue;
				}
				foreach ( $price['attributes'] as $attr ) {
					if ( $attr->attribute_id == $attribute_id && ! isset( $unique_values[ $attr->value_id ] ) ) {
						$unique_values[ $attr->value_id ] = $attribute_values[ $attr->value_id ]['slug'];
					}
				}
			}
			
			foreach ( $unique_values as $value_id => $slug ) :
				$type         = $attribute_values[ $value_id ]['type'] ?? 'text';
				$actual_value = $attribute_values[ $value_id ]['value'] ?? '';
				$name         = $attribute_values[ $value_id ]['name'] ?? '';
				$style 		  = '';
				if ( 'Image' === $type && ! empty( $actual_value ) ) {
					if ( is_numeric( $actual_value ) ) {
						$actual_value = wp_get_attachment_image_url( $actual_value, 'full' );
					}
					$style = "background-image: url('" . esc_url( $actual_value ) . "');";
				} elseif ( 'Color' === $type && ! empty( $actual_value ) ) {
					$style = 'background-color: ' . esc_attr( $actual_value ) . ';';
				}
				?>
				
				<label for="easycommerce_vs_<?php echo esc_attr( $attribute_slug . '_' . $value_id ); ?>" class="inline-block m-1 relative group">
					<input type="checkbox"
						   name="attribute_<?php echo esc_attr( $attribute_slug ); ?>"
						   value="<?php echo esc_attr( $value_id ); ?>"
						   id="easycommerce_vs_<?php echo esc_attr( $attribute_slug . '_' . $value_id ); ?>"
						   class="easycommerce-vs-radio hidden">
					
					<span class="easycommerce-vs-label easycommerce-vs-content inline-block h-[40px] rounded-lg cursor-pointer border-2 transition-all p-[6px] border-ec-table-stock bg-white type-<?php echo esc_attr( strtolower( $type ) ); ?>">
						<span class="inner-box flex items-center h-full w-full rounded-sm bg-cover bg-center" style="<?php echo esc_attr( $style ); ?>">
							<?php if ( ! in_array( $type, array( 'Image', 'Color' ), true ) ) : ?>
								<?php echo esc_html( $name ); ?>
							<?php endif; ?>
						</span>
					</span>

					<div class="absolute left-1/2 -top-8 transform -translate-x-1/2 px-2 py-1 bg-ec-primary text-white text-xs rounded opacity-0 pointer-events-none transition-opacity duration-300 group-hover:opacity-100 whitespace-nowrap z-50">
						<?php echo esc_html( $name ); ?>
					</div>
				</label>
			<?php endforeach; ?>
		</div>
	<?php endforeach; ?>

	<?php
	printf( '<input type="hidden" name="is_variable" value="%d" id="easycommerce_variation_id" />', ! empty( $attribute_fields ) ? 1 : 0 );
	echo '<input type="hidden" name="price_id" value="1" id="easycommerce_variation_price_id" />';
	?>
</div>