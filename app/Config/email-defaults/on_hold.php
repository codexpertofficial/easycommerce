<?php
return array(
	/* translators: the ##...## tokens are merge placeholders substituted with real order data at send time - keep them verbatim. */
	'customer_subject' => __( 'Your Order ###order_id## is On Hold - ##shop_name##', 'easycommerce' ),
	/* translators: the ##...## tokens are merge placeholders substituted with real order data at send time - keep them verbatim. */
	'customer_body'    => __( '
Hi ##customer_name##,

We wanted to give you a heads-up that your order ###order_id## is temporarily on hold. We\'re working to get it moving as soon as possible and will keep you updated.

Order summary:
##product_table##
Items: ##number_of_items##
Total: ##order_total##

You can check on your order here: ##dashboard_page##

If you have any questions or need things sorted quickly, please reach out - we\'re happy to help.

Warm regards,
The ##shop_name## Team
', 'easycommerce' ),

	/* translators: the ##...## tokens are merge placeholders substituted with real order data at send time - keep them verbatim. */
	'admin_subject'    => __( 'Order ###order_id## On Hold - ##customer_name## - ##shop_name##', 'easycommerce' ),
	/* translators: the ##...## tokens are merge placeholders substituted with real order data at send time - keep them verbatim. */
	'admin_body'       => __( '
Hi there,

Order ###order_id## from ##customer_name## has been placed on hold.

##product_table##
Items: ##number_of_items##
Total: ##order_total##

Customer contact:
Email: ##customer_email##
Phone: ##customer_phone##

Please review this order and take any necessary action.

Best regards,
The ##shop_name## Team
', 'easycommerce' ),
);
