<?php
return array(
	'customer_subject' => 'We\'ve Got Your Order ###order_id## - ##shop_name##',
	'customer_body'    => '
Hi ##customer_name##,

Thanks for your order! We\'ve received it and it\'s currently awaiting payment confirmation. Once your payment clears, we\'ll get things moving right away.

Here\'s what you ordered:
##product_table##
Items: ##number_of_items##
Total: ##order_total##

If you still need to complete your payment, you can do so here: ##checkout_page##

Questions? We\'re always here - just reach out.

Warm regards,
The ##shop_name## Team
',

	'admin_subject'    => 'New Pending Order ###order_id## - ##customer_name## - ##shop_name##',
	'admin_body'       => '
Hi there,

A new order ###order_id## from ##customer_name## is pending payment.

##product_table##
Items: ##number_of_items##
Total: ##order_total##

Customer contact:
Email: ##customer_email##
Phone: ##customer_phone##

Checkout page: ##checkout_page##

Please review if any action is needed on your end.

Best regards,
The ##shop_name## Team
',
);
