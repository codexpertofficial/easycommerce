<?php
	$all_survey = array(
		'temporary_deactivation' => __( 'Temporary deactivation', 'easycommerce' ),
		'lack_of_features'       => __( 'Lack of features', 'easycommerce' ),
		'better_alternative'     => __( 'Found better alternative', 'easycommerce' ),
		'compatibility_issues'   => __( 'Compatibility issues', 'easycommerce' ),
		'poor_ui-ux'             => __( 'Poor UI/UX', 'easycommerce' ),
		'limited_customization'  => __( 'Limited customization', 'easycommerce' ),
		'bugs_and_errors'        => __( 'Bugs and errors', 'easycommerce' ),
		'performance_issues'     => __( 'Performance issues', 'easycommerce' ),
	);

	$keys = array_keys( $all_survey );
	shuffle( $keys );
	$shuffled_survey = [];
	foreach ( $keys as $key ) {
		$shuffled_survey[ $key ] = $all_survey[ $key ];
	}
	$shuffled_survey['others'] = __( 'Others', 'easycommerce' );

	$get_user   = wp_get_current_user()->display_name;

	$deactivation_url = wp_nonce_url( admin_url( 'plugins.php?action=deactivate&plugin=easycommerce/easycommerce.php' ), 'deactivate-plugin_easycommerce/easycommerce.php' );
	?>
<div id="easycommerce-survey-wrap" style="display: none;" class="easycommerce-servey-wrapper">
	<div id="easycommerce-survey" class="easycommerce-servey-container relative">

		<div class="easycommerce-survey-cross-icon easycommerce-survey-cross">

			<svg fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
				<path clip-rule="evenodd" fill-rule="evenodd"
					d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z">
				</path>
			</svg>

		</div>

		<div class="easycommerce-survey-header">
			<img src="<?php echo EASYCOMMERCE_ASSETS_URL . '/admin/img/survey.gif'; ?>" alt="">
			<h2><?php esc_html_e( 'Thanks for trying! What made you deactivate EasyCommerce?', 'easycommerce' ); ?></h2>

			<p><?php esc_html_e( 'Building a store is hard, and so is building the tool behind it. Your honest feedback is what makes EasyCommerce better, and we read every single response.', 'easycommerce' ); ?></p>
		</div>

		<form method="post" id="easycommerce-survey-form" action="<?php echo esc_attr( $deactivation_url ); ?>">
			<div class="easycommerce-survey-reason-wrap">
				<?php
				foreach ( $shuffled_survey as $reason => $label ) {
					printf(
						'<label for="easycommerce-survey-item_%1$s" class="easycommerce-survey-item-wrap">
								<p>%2$s</p>
                                <input type="radio" name="reason" id="easycommerce-survey-item_%1$s" class="easycommerce-survey-reason easycommerce-input-checkoutbox easycommerce-survey-item-checkbox" value="%1$s" required>
							</label>',
						$reason,
						esc_html( $label )
					)
					?>
					<?php
				}
				?>
			</div>

			<div id="easycommerce-survey-message">
				<textarea name="message" id="easycommerce-survey-comment" class="easycommerce-survey-comment"
					placeholder="<?php esc_attr_e( 'What would have made you stay? (optional, but it really helps)', 'easycommerce' ); ?>"></textarea>
			</div>

			<p class="easycommerce-survey-support">
				<?php
				printf(
					/* translators: %s: support link */
					esc_html__( 'Stuck on something? %s and we\'ll help you sort it out before you go.', 'easycommerce' ),
					'<a href="https://support.easycommerce.dev" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Reach out to support', 'easycommerce' ) . '</a>'
				);
				?>
			</p>

			<div class="easycommerce-submit-deactive">
				<div>
					<a class="easycommerce-survey-skip-deactive" href="<?php echo esc_attr( $deactivation_url ); ?>"><?php echo esc_html( __( 'Deactivate without feedback', 'easycommerce' ) ); ?></a>
				</div>
				<div class="easycommerce-survey-bottom-wrapper">
					<button type="button"
						class="easycommerce-survey-bottom easycommerce-survey-cross-icon-bottom easycommerce-survey-cross"><?php esc_html_e( 'Cancel', 'easycommerce' ); ?></button>
					<button type="submit"
						class="easycommerce-survey-bottom">
						<?php echo esc_html( __( 'Send &amp; Deactivate', 'easycommerce' ) ); ?>
						<div class="loader">

						</div>
					</button>
				</div>
			</div>
		</form>

	</div>
</div>