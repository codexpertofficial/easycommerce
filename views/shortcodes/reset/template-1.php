<?php
// Replace default reset password form
$action 		= isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
$reset_key 		= isset( $_GET['key'] ) ? sanitize_text_field( $_GET['key'] ) : '';
$reset_login 	= isset( $_GET['login'] ) ? sanitize_text_field( $_GET['login'] ) : '';

if ( $action === 'ecrp' && !empty( $reset_key ) && !empty( $reset_login ) ) :
    ?>
    <form id="easycommerce-reset-password-form" method="post">
        <div class="w-[614px] bg-white rounded-xl py-16 px-9 mx-auto">
            <h3 class="!text-black !font-inter !text-2xl !font-semibold !leading-8 !mb-2 text-center">
                <?php esc_html_e( 'Set New Password', 'easycommerce' ); ?>
            </h3>
            <p class="font-inter text-base font-medium leading-[26px] !text-ec-placeholder text-center mb-6">
                <?php esc_html_e( 'Enter your new password below.', 'easycommerce' ); ?>
            </p>

            <div class="w-full flex flex-col gap-4">
                <div class="flex flex-col gap-2">
                    <label for="new-password" class="font-inter font-medium text-base leading-[26px] text-black">
                        <?php esc_html_e( 'New Password', 'easycommerce' ); ?>
                    </label>
                    <input type="password" id="new-password" class="easycommerce-register-input" name="new-password" placeholder="Enter new password" required />
                </div>
                <div class="flex flex-col gap-2">
                    <label for="confirm-password" class="font-inter font-medium text-base leading-[26px] text-black">
                        <?php esc_html_e( 'Confirm Password', 'easycommerce' ); ?>
                    </label>
                    <input type="password" id="confirm-password" class="easycommerce-register-input" name="confirm-password" placeholder="Confirm new password" required />
                </div>
            </div>

            <input type="hidden" id="reset-key" value="<?php echo esc_attr($reset_key); ?>">
            <input type="hidden" id="reset-login" value="<?php echo esc_attr($reset_login); ?>">

            <div>
                <button class="w-full h-12 bg-ec-primary text-white font-inter font-medium text-base leading-[26px] rounded-md mt-8 hover:!bg-ec-primary hover:text-white focus:!bg-ec-primary focus:text-white" type="submit">
                    <?php esc_html_e( 'Reset Password', 'easycommerce' ); ?>
                </button>
            </div>
            <p class="text-red-600 mt-3 font-inter text-base hidden" id="easycommerce-reset-password-error-message"></p>
        </div>
    </form>
    <?php
else :
    // Default reset password form
    ?>
    <form id="easycommerce-reset-form" method="post">
        <div class="w-[614px] bg-white rounded-xl py-16 px-9 mx-auto">
            <h3 class="!text-black !font-inter !text-2xl !font-semibold !leading-8 !mb-2 text-center">
                <?php esc_html_e( 'Reset your password', 'easycommerce' ); ?>
            </h3>
            <p class="font-inter text-base font-medium leading-[26px] !text-ec-placeholder text-center">
                <?php esc_html_e( 'Enter a valid e-mail to receive instruction on how to reset your password.', 'easycommerce' ); ?>
            </p>

            <div class="w-full flex flex-col gap-[10px]">
                <div class="flex flex-col gap-2">
                    <label for="easycommerce-reset-email-username" class="font-inter font-medium text-base leading-[26px] text-black">
                        <?php esc_html_e( 'Email or username', 'easycommerce' ); ?>
                    </label>
                    <input type="text" id="easycommerce-reset-email-username" class="easycommerce-register-input" name="easycommerce-reset-email-username" placeholder="Email or username" required />
                </div>
            </div>
            <div>
                <button class="w-full h-12 bg-ec-primary text-white font-inter font-medium text-base leading-[26px] rounded-md mt-8 hover:!bg-ec-primary hover:text-white focus:!bg-ec-primary focus:text-white" type="submit" name="easycommerce-reset-password">
                    <?php esc_html_e( 'Reset Password', 'easycommerce' ); ?>
                </button>
            </div>
            <p class="text-red-600 mt-3 font-inter text-base hidden" id="easycommerce-reset-error-message"></p>
            <div class="mt-4">
                <p class="text-center">
                    <?php esc_html_e( "Don't have an account?", 'easycommerce' ); ?>
                    <a class="text-ec-primary font-inter font-medium text-base leading-[26px] hover:!text-ec-primary focus:text-ec-primary !no-underline" href="<?php echo esc_url( easycommerce_registration_page( true ) ); ?>">
                        <?php esc_html_e( 'Sign Up', 'easycommerce' ); ?>
                    </a>
                </p>
            </div>
        </div>
    </form>
<?php endif; ?>
