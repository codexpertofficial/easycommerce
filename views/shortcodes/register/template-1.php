<form id="easycommerce-registration-form" method="post">
	<div class="w-[614px] bg-white rounded-xl py-16 px-9 mx-auto">
		<h3 class="!text-black !font-inter !text-2xl !font-semibold !leading-8 !mb-2">
			<?php esc_html_e( 'Registration', 'easycommerce' ); ?>
		</h3>
		<p class="font-inter text-base font-medium leading-[26px] !text-ec-placeholder">
			<?php esc_html_e( 'Welcome Back! Sign in and let the greenery spark your joy', 'easycommerce' ); ?>
		</p>

		<div class="w-full flex flex-col gap-[10px]">
			<div class="flex flex-col gap-2 mb-6">
				<label for="easycommerce-register-username" class="font-inter font-medium text-base leading-[26px] text-black"> <?php esc_html_e( 'Username', 'easycommerce' ); ?> </label>
				<input type="text" id="easycommerce-register-username" class="easycommerce-register-input" name="easycommerce-register-username" placeholder="User Name" required />
			</div>
			<div class="flex flex-col gap-2 mb-6">
				<label for="easycommerce-register-email" class="font-inter font-medium text-base leading-[26px] text-black"> <?php esc_html_e( 'Email Address', 'easycommerce' ); ?> </label>
				<input type="email" id="easycommerce-register-email" class="easycommerce-register-input" name="easycommerce-register-email" placeholder="Enter your Email" required />
			</div>
			<div class="flex flex-col gap-2 mb-6">
				<label for="easycommerce-register-new-password" class="font-inter font-medium text-base leading-[26px] text-black"> <?php esc_html_e( 'New password', 'easycommerce' ); ?> </label>
				<input type="password" id="easycommerce-register-new-password" class="easycommerce-register-input" name="easycommerce-register-new-password" placeholder="Enter new password" required />
			</div>

			<div class="flex flex-col gap-2">
				<label for="easycommerce-register-confirm-password" class="font-inter font-medium text-base leading-[26px] text-black"> <?php esc_html_e( 'Confirm password', 'easycommerce' ); ?> </label>
				<input type="password" id="easycommerce-register-confirm-password" class="easycommerce-register-input" name="easycommerce-register-confirm-password" placeholder="Confirm new password" required />
			</div>
			<!-- <div class="mt-4 flex items-center justify-between">
				<div class="flex items-center gap-2">
					<input id="easycommere-register-remember" class="easycommerce-input-checkoutbox easycommerce-login-register-checkbox" type="checkbox" />
					<label class="text-ec-body font-inter font-normal text-base leading-[26px]" for="easycommere-register-remember" > <?php esc_html_e( 'Remember Me', 'easycommerce' ); ?> </label>
				</div>
				<div>
					<a class="font-inter text-ec-secondary font-normal text-base leading-[26px] hover:!text-ec-primary focus:textec-primary !no-underline" href="#" > <?php esc_html_e( 'Forgot Password?', 'easycommerce' ); ?></a>
				</div>
			</div> -->
		</div>
		<div>
			<?php wp_nonce_field( 'easycommerce_register_action', 'easycommerce_register_nonce' ); ?>
			<button class="w-full h-12 bg-ec-primary text-white font-inter font-medium text-base leading-[26px] rounded-md mt-8 hover:!bg-ec-secondary hover:text-white transition duration-300 focus:!bg-ec-primary focus:text-white" type="submit" name="easycommerce-register-submit" > <?php esc_html_e( 'Sign up', 'easycommerce' ); ?> </button>
		</div>
		<p class="text-red-600 mt-3 font-inter text-base hidden" id="easycommerce-registration-error-message"></p>
		<div class="mt-4">
			<span class="text-ec-placeholder text-normal font-inter text-base leading-[26px]">
				<?php esc_html_e( 'By creating an account, you agree to our', 'easycommerce' ); ?>
				<a class="hover:text-ec-secondary" href="<?php echo esc_url( easycommerce_terms_of_service_page( true ) ); ?>">
					<?php esc_html_e( 'Terms of Service', 'easycommerce' ); ?>
				</a>
				<?php esc_html_e( 'and', 'easycommerce' ); ?>
				<a class="hover:text-ec-secondary" href="<?php echo esc_url( easycommerce_privacy_policy_page( true ) ); ?>">
					<?php esc_html_e( 'Privacy Policy', 'easycommerce' ); ?>
				</a>
			</span>
		</div>
		<div class="mt-4">
			<p class="text-center">
				<?php esc_html_e( 'Don’t have an account?', 'easycommerce' ); ?>
				<a
					class="text-ec-primary font-inter font-normal text-base leading-[26px] hover:!text-ec-primary focus:textec-primary !no-underline"
					href="<?php echo esc_url( easycommerce_dashboard_page( true ) ); ?>"
				>
					<?php esc_html_e( 'Log in', 'easycommerce' ); ?>
				</a>
			</p>
		</div>
	</div>
</form>
