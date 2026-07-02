<?php
use EasyCommerce\Helpers\Utility;

/**
 * Load EasyCommerce settings menus
 */
$menus = easycommerce_settings_menus();

/**
 * Determine the active menu and submenu based on URL parameters, defaulting to the first menu if none are set.
 */
$active_menu_id 	= isset( $_GET['menu'] ) && array_key_exists( $_GET['menu'], $menus ) ? sanitize_key( $_GET['menu'] ) : array_key_first( $menus );
$active_menu    	= $menus[ $active_menu_id ];
$submenus          	= isset( $active_menu['submenus'] ) && is_array( $active_menu['submenus'] ) ? $active_menu['submenus'] : array();
$active_submenu_id 	= isset( $_GET['submenu'] ) && array_key_exists( $_GET['submenu'], $submenus ) ? sanitize_key( $_GET['submenu'] ) : array_key_first( $submenus );
$active_submenu 	= isset( $submenus[$active_submenu_id] ) ? $submenus[$active_submenu_id] : [];
$admin_menu   		= admin_url( 'admin.php' );
$option_key   		= "easycommerce-{$active_menu_id}-{$active_submenu_id}";
$saved_option 		= get_option( $option_key );

// Default button configs
$default_buttons = [
    'reset' => [
        'show' => true,
        'text' => __( 'Reset Settings', 'easycommerce' ),
    ],
    'save'  => [
        'show' => true,
        'text' => __( 'Save Settings', 'easycommerce' ),
    ],
];

// Reset button
$reset_conf = $active_submenu['reset_button'] ?? true;
if ( is_array( $reset_conf ) ) {
    $reset_conf = wp_parse_args( $reset_conf, $default_buttons['reset'] );
    $reset_show = true;
} elseif ( $reset_conf === true ) {
    $reset_conf = $default_buttons['reset'];
    $reset_show = true;
} else {
    $reset_conf = $default_buttons['reset'];
    $reset_show = false;
}

// Save button
$save_conf = $active_submenu['save_button'] ?? true;
if ( is_array( $save_conf ) ) {
    $save_conf = wp_parse_args( $save_conf, $default_buttons['save'] );
    $save_show = true;
} elseif ( $save_conf === true ) {
    $save_conf = $default_buttons['save'];
    $save_show = true;
} else {
    $save_conf = $default_buttons['save'];
    $save_show = false;
}

?>
<div id="easycommerce-settings-wrap" class="bg-ec-main-bg">

	<!-- Header section for EasyCommerce settings -->
	<div id="easycommerce-settings-header" class="h-[80px]"></div>
	<div class="flex">
		<div>
			<?php echo easycommerce_render_sidebar(); ?>
		</div>
		<div class="flex-1 px-6 py-4">
			<div class="flex items-center justify-between product-panel-title mb-4">
				<h3>
					<?php esc_html_e( 'Settings', 'easycommerce' ); ?>
				</h3>
				<div id="easycommerce-settings-content-actions">
					<?php if ( ! empty( $reset_conf['show'] ) ) : ?>
						<button id="easycommerce-reset-settings"
								class="font-inter bg-white group border border-ec-primary py-[11px] px-4 rounded-lg font-normal text-ec-primary hover:text-white hover:bg-ec-primary focus:shadow-none focus:text-white focus:bg-ec-secondary lg:text-sm md:text-xs sm:text-sm transition-all ease-in-out duration-500 leading-[26px] mr-4"
								data-option_key="<?php echo esc_attr( $option_key ); ?>">
							<?php echo esc_html( $reset_conf['text'] ?? $default_buttons['reset']['text'] ); ?>
						</button>
					<?php endif; ?>

					<?php if ( ! empty( $save_conf['show'] ) ) : ?>
						<button id="easycommerce-save-settings"
								class="font-inter bg-ec-primary py-[11px] px-4 rounded-lg font-normal text-white hover:bg-ec-secondary focus:shadow-none focus:bg-ec-secondary lg:text-sm md:text-xs sm:text-sm transition-all ease-in-out duration-500 leading-[26px]"
								data-option_key="<?php echo esc_attr( $option_key ); ?>">
							<?php echo esc_html( $save_conf['text'] ?? $default_buttons['save']['text'] ); ?>
						</button>
					<?php endif; ?>					
				</div>
			</div>
			<div class="w-full flex bg-white rounded-xl flex-col">
				<div class="flex p-6">
					<!-- Sidebar for main menu navigation -->
					<div class="w-[267px] border border-ec-table-stock rounded-lg">
						<div class="" id="easycommerce-settings-sidebar">
							<div id="easycommerce-settings-menus">
								<ul id="easycommerce-settings-menus-list">
								<?php
								/**
								 * Loop through each main menu item and render it in the sidebar.
								 */
								foreach ( $menus as $menu_id => $menu ) {
									$is_active = $active_menu_id == $menu_id;
									printf(
										'<li id="%1$s" class="mb-0">
											<a class="flex items-center text-sm hover:bg-ec-accent p-4 focus:shadow-none group %2$s" href="%3$s">
												<img src="%4$s" class="w-5 mr-3 block rtl:ml-3">
												<img src="%5$s" class="w-5 mr-3 hidden rtl:ml-3">
												%6$s
											</a>
										</li>',
										esc_attr( $menu_id ),
										$is_active ? 'text-ec-primary hover:text-ec-primary bg-ec-accent' : '',
										esc_url(
											add_query_arg(
												array(
													'page' => 'easycommerce-settings',
													'menu' => $menu_id,
												),
												$admin_menu
											)
										),
										esc_html( $is_active ? $menu['hover-icon'] : $menu['icon'] ),
										esc_html( $menu['hover-icon'] ),
										esc_html( $menu['label'] )
									);
								}
								?>
								</ul>
							</div>
						</div>
					</div>

					<!-- Main content area where submenu content is displayed -->
					<div class="w-full min-h-screen pb-0" id="easycommerce-settings-<?php echo esc_attr( $active_menu_id ); ?>">
						<div class="w-full min-h-full pl-6 bg-white rounded-lg rtl:pr-6 rtl:pl-0" id="easycommerce-settings-content">
							<?php if ( ! isset( $active_menu['hide_form'] ) || true !== $active_menu['hide_form'] ) { ?>
							<form class="easycommerce-settings-form" data-option_key="<?php echo esc_attr( $option_key ); ?>" data-reload="<?php echo esc_attr( $active_menu['reload'] ?? '' ); ?>" id="" method="post">
							<?php } ?>

								<div class="flex items-center justify-between mb-4">
									<div id="easycommerce-settings-content-label">
										<h3 class="text-ec-body text-xl leading-8 font-semibold"><?php echo esc_html( $active_menu['label'] ); ?></h3>
									</div>
								</div>
				
								<!-- Submenu navigation for the active main menu -->
								<div id="easycommerce-settings-submenus" class="submenu-scroll-container">
									<ul id="easycommerce-settings-submenus-list" class="terms-filters-container flex gap-2 <?php echo count( $submenus ) > 1 ? 'border-b border-ec-primary' : ''; ?>">
										<?php
										// Render submenu tabs only if there are multiple submenus.
										if ( count( $submenus ) > 1 ) {
											foreach ( $submenus as $submenu_id => $submenu ) {
												$is_submenu_active = $active_submenu_id == $submenu_id;
												printf(
													'<li id="%1$s" class="%2$s text-center inline-block"><a class="%3$s block !py-2 text-ec-body text-[14px] hover:text-ec-body focus:outline-none focus:shadow-none focus:text-ec-body" href="%4$s">%5$s</a></li>',
													esc_attr( $submenu_id ),
													esc_attr( $active_submenu_id == $submenu_id ? 'active' : '' ),
													esc_attr( $is_submenu_active ? 'text-ec-primary hover:text-ec-primary' : '' ),
													esc_url(
														add_query_arg(
															array(
																'page'    => 'easycommerce-settings',
																'menu'    => $active_menu_id,
																'submenu' => $submenu_id,
															),
															$admin_menu
														)
													),
													esc_html( $submenu['label'] ),
												);
											}
										}
										?>
									</ul>
								</div>
				
								<!-- Sections for the active submenu, containing fields and custom content -->
								<div id="easycommerce-settings-sections" class="<?php echo count( $submenus ) > 1 ? 'mt-6' : ''; ?>">
									<?php
									$sections = $menus[ $active_menu_id ]['submenus'][ $active_submenu_id ]['sections'];

									foreach ( $sections as $section_id => $section ) {
										printf( '<div class="easycommerce-settings-section border border-ec-border rounded-lg p-6 mb-6" id="easycommerce-settings-section-%1$s">', esc_attr( $section_id ) );

										if ( ! empty( $section['label'] ) ) {
											printf( '<h2 id="easycommerce-settings-section-heading-%1$s" class="text-ec-body text-xl leading-8 font-medium mb-3">%2$s</h2>', esc_attr( $section_id ), esc_html( $section['label'] ) );
										}

										if ( ! empty( $section['desc'] ) ) {
											printf( '<p id="easycommerce-settings-section-desc-%1$s" class="text-ec-body text-sm mb-10 font-normal" >%2$s</p>', esc_attr( $section_id ), wp_kses_post( $section['desc'] ) );
										}

										if ( isset( $section['content'] ) && ! empty( isset( $section['content'] ) ) ) {
											echo $section['content'];
										} elseif ( isset( $section['template'] ) && file_exists( $section['template'] ) ) {
											include_once $section['template'];
										}
										// Render each field in the section using a Field Factory class.
										elseif ( isset( $section['fields'] ) ) {
											foreach ( $section['fields'] as $field_id => $field ) {

												if ( class_exists( $field_factory = easycommerce_get_field_factory( $field['type'] ) ) ) {

													// Populate the field value with saved data if available.
													if ( isset( $saved_option[ $field_id ] ) ) {
														$field['value'] = $saved_option[ $field_id ];
													}

													// For geo-dependent selects, store saved value as data attr so JS can restore it after async population.
													if ( in_array( $field_id, array( 'state', 'city' ) ) ) {
														$field['atts']                    = $field['atts'] ?? array();
														$field['atts']['data-geo']         = $field_id;
														$field['atts']['data-saved-value'] = $saved_option[ $field_id ] ?? '';
													}

													$field_obj = new $field_factory( $field );
													echo $field_obj->render();
												}
											}

											// Render a Save button within each section.
											printf(
												'<div class="text-right"><button type="submit" class="font-inter bg-ec-primary group 
															border border-ec-primary py-[11px] px-4 rounded-lg text-white font-normal hover:text-white 
															hover:bg-ec-secondary focus:shadow-none focus:text-white focus:bg-ec-secondary hover:border-ec-secondary focus:border-ec-secondary
															lg:text-sm md:text-xs sm:text-sm transition-all ease-in-out duration-500 leading-[26px]" value="%1$s">%1$s</button></div>',
												esc_attr( __( 'Save Settings', 'easycommerce' ) )
											);

										}

										printf( '</div><!-- #%1$s -->', esc_attr( $section_id ) );
									}
									?>
								</div>
				
							<?php if ( ! isset( $active_menu['hide_form'] ) || true !== $active_menu['hide_form'] ) { ?>
							</form>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>