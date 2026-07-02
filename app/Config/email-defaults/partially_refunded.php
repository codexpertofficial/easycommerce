<?php
return array(
	'customer_subject' => 'Partial Refund Processed for Order ###order_id## - ##shop_name##',
	'customer_body'    => '
Hi ##customer_name##,

We\'ve processed a partial refund for your order ###order_id##. The amount should appear in your account within a few business days, depending on your payment provider.

Your remaining items are unaffected and will continue as normal.

Order items:
##product_table##
Items: ##number_of_items##
Refunded: ##refunded_amount##

Billed to:
##billing_first_name## ##billing_last_name##
##billing_address##

If you have any questions about this refund, please don\'t hesitate to reach out.

Warm regards,
The ##shop_name## Team
',

	'admin_subject'    => 'Partial Refund Issued - Order ###order_id## - ##customer_name## - ##shop_name##',
	'admin_body'       => '
Hi there,

A partial refund has been processed for order ###order_id## from ##customer_name##.

##product_table##
Items: ##number_of_items##
Refunded: ##refunded_amount##

Billed to:
##billing_first_name## ##billing_last_name##
##billing_address##

Customer contact:
Email: ##customer_email##
Phone: ##customer_phone##

Best regards,
The ##shop_name## Team
',
);
