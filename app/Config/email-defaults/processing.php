<?php
return array(
	/* translators: the ##...## tokens are merge placeholders substituted with real order data at send time - keep them verbatim. */
	'customer_subject' => __( 'Your Order ###order_id## is on Its Way - ##shop_name##', 'easycommerce' ),
	/* translators: the ##...## tokens are merge placeholders substituted with real order data at send time - keep them verbatim. */
	'customer_body'    => __( '
Hi ##customer_name##,

Good news - your order ###order_id## has been confirmed and we\'re getting it ready for you!

What you ordered:
##product_table##
Items: ##number_of_items##
Total: ##order_total##

Shipping to:
##shipping_first_name## ##shipping_last_name##
##shipping_address##

We\'ll send you another update once it ships. In the meantime, you can keep an eye on your order here: ##dashboard_page##

Thanks for shopping with us - we appreciate it!

Warm regards,
The ##shop_name## Team
', 'easycommerce' ),

	/* translators: the ##...## tokens are merge placeholders substituted with real order data at send time - keep them verbatim. */
	'admin_subject'    => __( 'Order ###order_id## Processing - ##customer_name## - ##shop_name##', 'easycommerce' ),
	/* translators: the ##...## tokens are merge placeholders substituted with real order data at send time - keep them verbatim. */
	'admin_body'       => __( '
Hi there,

Order ###order_id## from ##customer_name## is now processing and being prepared for shipment.

##product_table##
Items: ##number_of_items##
Total: ##order_total##

Shipping to:
##shipping_first_name## ##shipping_last_name##
##shipping_address##

Customer contact:
Email: ##customer_email##
Phone: ##customer_phone##

Manage this order: ##dashboard_page##

Best regards,
The ##shop_name## Team
', 'easycommerce' ),
);
