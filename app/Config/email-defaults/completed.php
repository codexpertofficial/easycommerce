<?php
return array(
	'customer_subject' => 'Your Order ###order_id## is Complete - ##shop_name##',
	'customer_body'    => '
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
',

	'admin_subject'    => 'Order ###order_id## Completed - ##customer_name## - ##shop_name##',
	'admin_body'       => '
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
',
);
