<?php
return array(
    'customer_subject' => 'Welcome to ##shop_name## - You\'re All Set!',
    'customer_body'    => '
Hi ##customer_name##,

Welcome to ##shop_name##! We\'re so glad you\'re here. Your account is ready to go.

With your account you can:
<ul>
    <li>Track and manage your orders in one place</li>
    <li>Browse your full purchase history</li>
    <li>Download digital purchases anytime</li>
    <li>Check out quicker next time</li>
</ul>

One quick step - please set your password to secure your account:
##password_reset_link##

Then you\'re ready to start shopping: ##shop_page##

Thanks for joining us. We hope you find exactly what you\'re looking for!

Warm regards,
The ##shop_name## Team
',

    'admin_subject'    => 'New Customer: ##customer_name## - ##shop_name##',
    'admin_body'       => '
Hi there,

A new customer just created an account at ##shop_name##.

<ul>
    <li>Name: ##customer_name##</li>
    <li>Email: ##customer_email##</li>
    <li>Registered: ##registration_date##</li>
</ul>

You can view and manage their profile from the admin dashboard.

Best regards,
The ##shop_name## Team
',
);
