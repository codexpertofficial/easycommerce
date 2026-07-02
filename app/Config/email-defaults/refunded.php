<?php
return array(
	'customer_subject' => 'Your Refund for Order ###order_id## Has Been Processed - ##shop_name##',
	'customer_body'    => '
Hi ##customer_name##,

Your refund for order ###order_id## has been processed. The amount should appear in your account within a few business days, depending on your payment provider.

Refund details:
##product_table##
Items: ##number_of_items##
Refunded: ##refunded_amount##

Billed to:
##billing_first_name## ##billing_last_name##
##billing_address##

We\'re sorry things didn\'t work out this time. If there\'s anything we can do better or if you have questions, please reach out - we\'d love to hear from you.

Warm regards,
The ##shop_name## Team
',

	'admin_subject'    => 'Refund Issued - Order ###order_id## - ##customer_name## - ##shop_name##',
	'admin_body'       => '
Hi there,

A full refund has been processed for order ###order_id## from ##customer_name##.

##product_table##
Items: ##number_of_items##
Total refunded: ##refunded_amount##

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
