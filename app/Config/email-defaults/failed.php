<?php
return array(
	/* translators: the ##...## tokens are merge placeholders substituted with real order data at send time - keep them verbatim. */
	'customer_subject' => __( '##shop_name## – Payment Failed for Order ###order_id##', 'easycommerce' ),
	/* translators: the ##...## tokens are merge placeholders substituted with real order data at send time - keep them verbatim. */
	'customer_body'    => __( '
Hi ##customer_name##,

Unfortunately, we were unable to process the payment for your order ###order_id##. This may have happened due to one of the following reasons:
<ul>
    <li>The card was declined or had insufficient funds</li>
    <li>Incorrect card details or billing information</li>
    <li>The payment was not authorized or timed out</li>
    <li>A temporary issue with the payment provider</li>
</ul>

Order Summary:
##product_table##
Items in Order: ##number_of_items##
Total Amount: ##order_total##

Your items are still saved. You can retry the payment or place a new order anytime at: ##checkout_page##

Need help? Just reach out – we’re here to support you.
', 'easycommerce' ),

	/* translators: the ##...## tokens are merge placeholders substituted with real order data at send time - keep them verbatim. */
	'admin_subject'    => __( '##shop_name## – Payment Failed for Order ###order_id##', 'easycommerce' ),
	/* translators: the ##...## tokens are merge placeholders substituted with real order data at send time - keep them verbatim. */
	'admin_body'       => __( '
Hello,

Payment for order ###order_id## from ##customer_name## has failed. The failure may have occurred due to:
<ul>
    <li>Card declined or insufficient funds</li>
    <li>Invalid card or billing details</li>
    <li>Unauthorized or timed-out payment</li>
    <li>A temporary payment gateway issue</li>
</ul>

Order Details:
##product_table##
Number of Items: ##number_of_items##
Total Amount: ##order_total##

The customer may retry payment from: ##checkout_page##
Please follow up if needed.
', 'easycommerce' ),
);
