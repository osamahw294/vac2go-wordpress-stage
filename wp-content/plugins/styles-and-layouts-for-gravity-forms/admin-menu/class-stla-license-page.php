<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Render the License Page in WP dashboard.
 */
class Stla_License_Page {

	/**
	 * Execute the actions and filters.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this, 'setting_fields' ) );
	}

	/**
	 * Register the license page.
	 *
	 * @return void
	 */
	public function register_menu() {
		add_menu_page( 'Styles & Layouts', 'Styles & Layouts for GF', 'manage_options', 'stla_licenses' );
		add_submenu_page( 'stla_licenses', 'Licenses', 'Licenses', 'manage_options', 'stla_licenses', array( $this, 'license_settings' ) );
	}

	/**
	 * Render the HTML of license page.
	 *
	 * @return void
	 */
	public function license_settings() {

		?>
			<!-- Create a header in the default WordPress 'wrap' container -->
	<div class="wrap">

		<!-- Make a call to the WordPress function for rendering errors when settings are saved. -->
		<?php settings_errors(); ?>
		<!-- Create the form that will be used to render our options -->
		<form method="post" action="options.php">
			<?php settings_fields( 'stla_licenses' ); ?>
			<?php do_settings_sections( 'stla_licenses' ); ?>
			<?php submit_button(); ?>
		</form>

	</div><!-- /.wrap -->
		<?php
	}

	/**
	 * Register settings.
	 *
	 * @return void
	 */
	public function setting_fields() {

		// If settings don't exist, create them.
		if ( get_option( 'stla_licenses' ) === false ) {
			add_option( 'stla_licenses' );
		}

		add_settings_section(
			'stla_licenses_section',
			'Add-On Licenses',
			array( $this, 'section_callback' ),
			'stla_licenses'
		);

		do_action( 'stla-license-fields', $this );

		// Register settings.
		register_setting(
			'stla_licenses',
			'stla_licenses',
			array( 'sanitize_callback' => array( $this, 'sanitize_licenses' ) )
		);
	}

	/**
	 * Sanitize the saved license keys.
	 *
	 * Keys are field names defined in code by add-ons, so they are preserved as
	 * given; only the submitted values are cleaned.
	 *
	 * @param mixed $value The submitted option value.
	 * @return mixed The sanitized option value.
	 */
	public function sanitize_licenses( $value ) {

		if ( ! is_array( $value ) ) {
			return is_string( $value ) ? sanitize_text_field( $value ) : '';
		}

		$sanitized = array();
		foreach ( $value as $key => $license ) {
			$sanitized[ $key ] = is_string( $license ) ? sanitize_text_field( $license ) : $license;
		}

		return $sanitized;
	}

	/**
	 * Section callbackt for HTML.
	 *
	 * @return void
	 */
	public function section_callback() {

		echo '<h4> Licence Fields will automatically appear once you install addons for \'Styles & Layouts for Gravity Forms\'. You can check all the available addons <a href="https://wpmonks.com/downloads/addon-bundle/?utm_source=dashboard&utm_medium=licence-page&utm_campaign=styles_layout_plugin">here</a></h4>';
	}
}

new Stla_License_Page();
