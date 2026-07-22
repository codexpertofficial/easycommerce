<?php
return array(
	/* translators: the ##...## tokens are merge placeholders substituted with real order data at send time - keep them verbatim. */
	'customer_subject' => __( 'Your Order ###order_id## is Complete - ##shop_name##', 'easycommerce' ),
	/* translators: the ##...## tokens are merge placeholders substituted with real order data at send time - keep them verbatim. */
	'customer_body'    => __( '
Hi ##customer_name##,

Your order ###order_id## has been completed - thank you for shopping with ##shop_name##! We hope you love what you ordered.

Order summary:
##product_table##
Items: ##number_of_items##
Total: ##order_total##

Delivered to:
##shipping_first_name## ##shipping_last_name##
##shipping_address##

You can view your order history anytime from your account: ##dashboard_page##

If anything isn\'t right, we\'re here to help - just get in touch.

Warm regards,
The ##shop_name## Team
', 'easycommerce' ),

	/* translators: the ##...## tokens are merge placeholders substituted with real order data at send time - keep them verbatim. */
	'admin_subject'    => __( 'Order ###order_id## Completed - ##customer_name## - ##shop_name##', 'easycommerce' ),
	/* translators: the ##...## tokens are merge placeholders substituted with real order data at send time - keep them verbatim. */
	'admin_body'       => __( '
Hi there,

Order ###order_id## from ##customer_name## has been marked as completed.

##product_table##
Items: ##number_of_items##
Total: ##order_total##

Shipped to:
##shipping_first_name## ##shipping_last_name##
##shipping_address##

Customer contact:
Email: ##customer_email##
Phone: ##customer_phone##

Full order details: ##dashboard_page##

Best regards,
The ##shop_name## Team
', 'easycommerce' ),
);
