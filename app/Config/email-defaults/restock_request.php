<?php
return array(
	/* translators: the ##...## tokens are merge placeholders substituted with real product data at send time - keep them verbatim. */
	'customer_subject' => __( '##product_name## is back in stock at ##shop_name##', 'easycommerce' ),
	/* translators: the ##...## tokens are merge placeholders substituted with real product data at send time - keep them verbatim. */
	'customer_body'    => __( '
Hi there,

Good news - ##product_name## is available again.

You asked us to let you know when it came back, so here it is: ##product_url##

Stock is limited, so grab yours before it sells out again.

Warm regards,
The ##shop_name## Team
', 'easycommerce' ),
);
