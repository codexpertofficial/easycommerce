<?php
return array(
	/* translators: the ##...## tokens are merge placeholders substituted with real order data at send time - keep them verbatim. */
	'customer_subject' => __( 'Your Order ###order_id## Has Been Cancelled - ##shop_name##', 'easycommerce' ),
	/* translators: the ##...## tokens are merge placeholders substituted with real order data at send time - keep them verbatim. */
	'customer_body'    => __( '
Hi ##customer_name##,

We\'re sorry to let you know that your order ###order_id## has been cancelled. We understand that\'s not what you were hoping to hear.

Here\'s a summary of the cancelled order:
##product_table##
Items: ##number_of_items##
Total: ##order_total##

If you\'d still like to get these items, you\'re welcome to place a new order at any time: ##shop_page##

If you have any questions or think this was a mistake, please don\'t hesitate to reach out - we\'d love to make it right.

Warm regards,
The ##shop_name## Team
', 'easycommerce' ),

	/* translators: the ##...## tokens are merge placeholders substituted with real order data at send time - keep them verbatim. */
	'admin_subject'    => __( 'Order ###order_id## Cancelled - ##customer_name## - ##shop_name##', 'easycommerce' ),
	/* translators: the ##...## tokens are merge placeholders substituted with real order data at send time - keep them verbatim. */
	'admin_body'       => __( '
Hi there,

Order ###order_id## from ##customer_name## has been cancelled.

##product_table##
Items: ##number_of_items##
Total: ##order_total##

Customer contact:
Email: ##customer_email##
Phone: ##customer_phone##

If this needs your attention, you can reach the customer directly or review the order in your dashboard.

Best regards,
The ##shop_name## Team
', 'easycommerce' ),
);
