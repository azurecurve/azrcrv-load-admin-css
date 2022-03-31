<?php
/**
 * ------------------------------------------------------------------------------
 * Plugin Name: Load Admin CSS
 * Description: Change the styling of your admin dashboard with custom CSS.
 * Version: 1.0.2
 * Author: azurecurve
 * Author URI: https://development.azurecurve.co.uk/classicpress-plugins/
 * Plugin URI: https://development.azurecurve.co.uk/classicpress-plugins/link-managements/
 * Text Domain: azrcrv-lacss
 * Domain Path: /languages
 * ------------------------------------------------------------------------------
 * This is free software released under the terms of the General Public License,
 * version 2, or later. It is distributed WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. Full
 * text of the license is available at https://www.gnu.org/licenses/rrl-2.0.html.
 * ------------------------------------------------------------------------------
 */

// Declare the namespace.
namespace azurecurve\LoadAdminCSS;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

// include plugin menu.
require_once dirname( __FILE__ ) . '/pluginmenu/menu.php';
add_action( 'admin_init', 'azrcrv_create_plugin_menu_lacss' );

// include update client
require_once dirname( __FILE__ ) . '/libraries/updateclient/UpdateClient.class.php';

/**
 * Setup registration activation hook, actions, filters and shortcodes.
 *
 * @since 1.0.0
 */

// add actions.
add_action( 'admin_menu', __NAMESPACE__ . '\\create_admin_menu' );
add_action( 'admin_init', __NAMESPACE__ . '\\register_admin_styles' );
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\enqueue_admin_styles' );
add_action( 'admin_init', __NAMESPACE__ . '\\register_admin_scripts' );
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\enqueue_admin_scripts' );
add_action( 'init', __NAMESPACE__ . '\\register_frontend_styles' );
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_frontend_styles' );
add_action( 'plugins_loaded', __NAMESPACE__ . '\\load_languages' );
add_action( 'admin_post_azrcrv_lacss_save_options', __NAMESPACE__ . '\\save_options' );
// add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\load_custom_admin_css' );

// add filters.
add_filter( 'plugin_action_links', __NAMESPACE__ . '\\add_plugin_action_link', 10, 2 );

/**
 * Register admin styles.
 *
 * @since 1.0.0
 */
function register_admin_styles() {
	wp_register_style( 'azrcrv-lacss-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), '1.0.0' );
	wp_register_style( 'azrcrv-lacss-pluginmenu-admin-styles', plugins_url( 'pluginmenu/css/style.css', __FILE__ ), array(), '1.0.0' );
}

/**
 * Enqueue admin styles.
 *
 * @since 1.0.0
 */
function enqueue_admin_styles() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['page'] ) && ( $_GET['page'] == 'azrcrv-lacss' ) ) {
		wp_enqueue_style( 'azrcrv-lacss-admin-styles' );
		wp_enqueue_style( 'azrcrv-lacss-pluginmenu-admin-styles' );
	}

	$options = get_option_with_defaults( 'azrcrv-lacss' );

	if ( $options['enable']['internal'] == 1 ) {

		if ( strlen( $options['css']['internal'] ) > 0 ) {

			wp_register_style( 'azrcrv-lacss-admin-custom-style', plugins_url( 'assets/css/admin-custom.css', __FILE__ ), array(), '1.0.0' );
			wp_enqueue_style( 'azrcrv-lacss-admin-custom-style' );
			wp_add_inline_style( 'azrcrv-lacss-admin-custom-style', esc_html( $options['css']['internal'] ) );

		}
	}

}

/**
 * Register admin scripts.
 *
 * @since 1.0.0
 */
function register_admin_scripts() {
	wp_register_script( 'azrcrv-lacss-admin-jquery', plugins_url( 'assets/jquery/admin.js', __FILE__ ), array(), '1.0.0', true );
}

/**
 * Enqueue admin styles.
 *
 * @since 1.0.0
 */
function enqueue_admin_scripts() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['page'] ) && ( $_GET['page'] == 'azrcrv-lacss' ) ) {
		wp_enqueue_script( 'azrcrv-lacss-admin-jquery' );
	}
}

/**
 * Register frontend styles.
 *
 * @since 1.0.0
 */
function register_frontend_styles() {
	wp_register_style( 'azrcrv-lacss-styles', plugins_url( 'assets/css/styles.css', __FILE__ ), array(), '1.0.0' );
}

/**
 * Enqueue frontend styles.
 *
 * @since 1.0.0
 */
function enqueue_frontend_styles() {
	wp_enqueue_style( 'azrcrv-lacss-styles' );
}

/**
 * Load language files.
 *
 * @since 1.0.0
 */
function load_languages() {
	$plugin_rel_path = basename( dirname( __FILE__ ) ) . '/languages';
	load_plugin_textdomain( 'azrcrv-lacss', false, $plugin_rel_path );
}

/**
 * Get options including defaults.
 *
 * @since 1.0.0
 */
function get_option_with_defaults( $option_name ) {

	$defaults = array(
		'enable' => array(
			'internal' => 1,
		),
		'css'    => array(
			'internal' => '',
		),
	);

	$options = get_option( $option_name, $defaults );

	$options = recursive_parse_args( $options, $defaults );

	return $options;

}

/**
 * Recursively parse options to merge with defaults.
 *
 * @since 1.0.0
 */
function recursive_parse_args( $args, $defaults ) {
	$new_args = (array) $defaults;

	foreach ( $args as $key => $value ) {
		if ( is_array( $value ) && isset( $new_args[ $key ] ) ) {
			$new_args[ $key ] = recursive_parse_args( $value, $new_args[ $key ] );
		} else {
			$new_args[ $key ] = $value;
		}
	}

	return $new_args;
}

/**
 * Add action link on plugins page.
 *
 * @since 1.0.0
 */
function add_plugin_action_link( $links, $file ) {
	static $this_plugin;

	if ( ! $this_plugin ) {
		$this_plugin = plugin_basename( __FILE__ );
	}

	if ( $file == $this_plugin ) {
		$settings_link = '<a href="' . esc_url_raw( admin_url( 'admin.php?page=azrcrv-lacss' ) ) . '"><img src="' . esc_url_raw( plugins_url( '/pluginmenu/images/logo.svg', __FILE__ ) ) . '" style="padding-top: 2px; margin-right: -5px; height: 16px; width: 16px;" alt="azurecurve" />' . esc_html__( 'Settings', 'azrcrv-lacss' ) . '</a>';
		array_unshift( $links, $settings_link );
	}

	return $links;
}

/**
 * Add to menu.
 *
 * @since 1.0.0
 */
function create_admin_menu() {

	add_submenu_page(
		'azrcrv-plugin-menu',
		esc_html__( 'Load Admin CSS Settings', 'azrcrv-lacss' ),
		esc_html__( 'Load Admin CSS', 'azrcrv-lacss' ),
		'manage_options',
		'azrcrv-lacss',
		__NAMESPACE__ . '\\display_options'
	);

}

/**
 * Load admin css.
 *
 * @since 1.0.0
 */
function load_admin_style() {
	wp_register_style( 'r-css', plugins_url( 'assets/css/admin.css', __FILE__ ), false, '1.0.0' );
	wp_enqueue_style( 'r-css' );
}

/**
 * Display Settings page.
 *
 * @since 1.0.0
 */
function display_options() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'azrcrv-lacss' ) );
	}

	global $wpdb;

	// Retrieve plugin configuration options from database.
	$options = get_option_with_defaults( 'azrcrv-lacss' );

	echo '<div id="azrcrv-lacss-general" class="wrap">';

	?>
		<h1>
			<?php
				echo '<a href="https://development.azurecurve.co.uk/classicpress-plugins/"><img src="' . esc_html( plugins_url( '/pluginmenu/images/logo.svg', __FILE__ ) ) . '" style="padding-right: 6px; height: 20px; width: 20px;" alt="azurecurve | Development" /></a>';
				echo esc_html( get_admin_page_title() );
			?>
		</h1>
		<?php

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['settings-updated'] ) ) {
			echo '<div class="notice notice-success is-dismissible">
					<p><strong>' . esc_html__( 'Settings have been saved.', 'azrcrv-lacss' ) . '</strong></p>
				</div>';
		}

		$tab_1_label = esc_html__( 'Load Admin CSS Options', 'azrcrv-lacss' );
		$tab_1       = '<table class="form-table azrcrv-lacss">
		
					<tr>
					
						<th scope="row" colspan=2 class="section-heading">
							
								<h2 class="azrcrv-lacss">' . esc_html__( 'Enable', 'azrcrv-lacss' ) . '</h2>
							
						</th>
	
					</tr>
					
						<td>
							
							<label for="enable-internal"><input name="enable-internal" type="checkbox" id="enable-internal" value="1" ' . checked( '1', esc_attr( $options['enable']['internal'] ), false ) . ' />' . esc_html__( 'Enable internal css.', 'azrcrv-lacss' ) . '</label>
														
						</td>
	
					</tr>
		
					<tr>
					
						<th scope="row" colspan=2 class="section-heading">
							
								<h2 class="azrcrv-lacss">' . esc_html__( 'CSS', 'azrcrv-lacss' ) . '</h2>
							
						</th>
	
					</tr>
		
					<tr>
					
						<th scope="row">
							
								' . esc_html__( 'Internal CSS', 'azrcrv-lacss' ) . '
							
						</th>
					
						<td>
							
							<textarea name="css-internal" rows="10" cols="50" id="css-internal" class="large-text">' . esc_textarea( $options['css']['internal'] ) . '</textarea>
							
						</td>
	
					</tr>
					
				</table>';

		$tab_3_label = esc_html__( 'Instructions', 'azrcrv-lacss' );
		$tab_3       = '<table class="form-table azrcrv-lacss">
		
					<tr>
					
						<th scope="row" colspan=2 class="section-heading">
							
								<h2 class="azrcrv-lacss">' . esc_html__( 'Shortcode Usage', 'azrcrv-lacss' ) . '</h2>
							
						</th>
	
					</tr>
		
					<tr>
					
						<td scope="row" colspan=2>
						
							<p>' .
								sprintf( esc_html__( 'Internal CSS can be enabled and defined on the %s tab.', 'azrcrv-lacss' ), '<em>Load Admin CSS Options</em> tab', '<code>id</code>' ) . '
									
									<p>' . esc_html__( 'An example piece of CSS for changing the font-size of the standard ClassicPress admin textarea is:', 'azrcrv-lacss' ) . '</p>
									
									<p><pre><code>textarea.wp-editor-area{
	font-size: 1.33em;
}</code></pre></p>
									
							</p>
						
						</td>
					
					</tr>
					
				</table>';

		$plugin_array = get_option( 'azrcrv-plugin-menu' );

		$tab_4_plugins = '';
		foreach ( $plugin_array as $plugin_name => $plugin_details ) {
			if ( $plugin_details['retired'] == 0 ) {
				$alternative_color = '';
				if ( isset( $plugin_details['bright'] ) and $plugin_details['bright'] == 1 ) {
					$alternative_color = 'bright-';
				}
				if ( isset( $plugin_details['premium'] ) and $plugin_details['premium'] == 1 ) {
					$alternative_color = 'premium-';
				}
				if ( is_plugin_active( $plugin_details['plugin_link'] ) ) {
					$tab_4_plugins .= "<a href='{$plugin_details['admin_URL']}' class='azrcrv-{$alternative_color}plugin-index'>{$plugin_name}</a>";
				} else {
					$tab_4_plugins .= "<a href='{$plugin_details['dev_URL']}' class='azrcrv-{$alternative_color}plugin-index'>{$plugin_name}</a>";
				}
			}
		}

		$tab_4_label = esc_html__( 'Other Plugins', 'azrcrv-lacss' );
		$tab_4       = '<table class="form-table azrcrv-lacss">
		
					<tr>
					
						<td scope="row" colspan=2>
						
							<p>' .
								sprintf( esc_html__( '%1$s was one of the first plugin developers to start developing for ClassicPress; all plugins are available from %2$s and are integrated with the %3$s plugin for fully integrated, no hassle, updates.', 'azrcrv-lacss' ), '<strong>azurecurve | Development</strong>', '<a href="https://development.azurecurve.co.uk/classicpress-plugins/">azurecurve | Development</a>', '<a href="https://directory.classicpress.net/plugins/update-manager/">Update Manager</a>' )
							. '</p>
							<p>' .
								sprintf( esc_html__( 'Other plugins available from %s are:', 'azrcrv-lacss' ), '<strong>azurecurve | Development</strong>' )
							. '</p>
						
						</td>
					
					</tr>
					
					<tr>
					
						<td scope="row" colspan=2>
						
							' . $tab_4_plugins . '
							
						</td>
	
					</tr>
					
				</table>';

		?>
		<form method="post" action="admin-post.php">

				<input type="hidden" name="action" value="azrcrv_lacss_save_options" />

				<?php
					// <!-- Adding security through hidden referer field -->.
					wp_nonce_field( 'azrcrv-lacss', 'azrcrv-lacss-nonce' );
				?>
				
				
				<div id="tabs" class="azrcrv-ui-tabs">
					<ul class="azrcrv-ui-tabs-nav azrcrv-ui-widget-header" role="tablist">
						<li class="azrcrv-ui-state-default azrcrv-ui-state-active" aria-controls="tab-panel-1" aria-labelledby="tab-1" aria-selected="true" aria-expanded="true" role="tab">
							<a id="tab-1" class="azrcrv-ui-tabs-anchor" href="#tab-panel-1"><?php echo $tab_1_label; ?></a>
						</li>
						<li class="azrcrv-ui-state-default" aria-controls="tab-panel-3" aria-labelledby="tab-3" aria-selected="false" aria-expanded="false" role="tab">
							<a id="tab-3" class="azrcrv-ui-tabs-anchor" href="#tab-panel-3"><?php echo $tab_3_label; ?></a>
						</li>
						<li class="azrcrv-ui-state-default" aria-controls="tab-panel-4" aria-labelledby="tab-4" aria-selected="false" aria-expanded="false" role="tab">
							<a id="tab-4" class="azrcrv-ui-tabs-anchor" href="#tab-panel-4"><?php echo $tab_4_label; ?></a>
						</li>
					</ul>
					<div id="tab-panel-1" class="azrcrv-ui-tabs-scroll" role="tabpanel" aria-hidden="false">
						<fieldset>
							<legend class='screen-reader-text'>
								<?php echo $tab_1_label; ?>
							</legend>
							<?php echo $tab_1; ?>
						</fieldset>
					</div>
					<div id="tab-panel-3" class="azrcrv-ui-tabs-scroll azrcrv-ui-tabs-hidden" role="tabpanel" aria-hidden="true">
						<fieldset>
							<legend class='screen-reader-text'>
								<?php echo $tab_3_label; ?>
							</legend>
							<?php echo $tab_3; ?>
						</fieldset>
					</div>
					<div id="tab-panel-4" class="azrcrv-ui-tabs-scroll azrcrv-ui-tabs-hidden" role="tabpanel" aria-hidden="true">
						<fieldset>
							<legend class='screen-reader-text'>
								<?php echo $tab_4_label; ?>
							</legend>
							<?php echo $tab_4; ?>
						</fieldset>
					</div>
				</div>

			<input type="submit" name="btn_save" value="<?php esc_html_e( 'Save Settings', 'azrcrv-lacss' ); ?>" class="button-primary"/>
		</form>
		<div class='azrcrv-lacss-donate'>
			<?php
				esc_html_e( 'Support', 'azrcrv-lacss' );
			?>
			azurecurve | Development
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
				<input type="hidden" name="cmd" value="_s-xclick">
				<input type="hidden" name="hosted_button_id" value="MCJQN9SJZYLWJ">
				<input type="image" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal – The safer, easier way to pay online.">
				<img alt="" border="0" src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" width="1" height="1">
			</form>
			<span>
				<?php
				esc_html_e( 'You can help support the development of our free plugins by donating a small amount of money.', 'azrcrv-lacss' );
				?>
			</span>
		</div>
	</div>
	<?php

}

/**
 * Check if function active (included due to standard function failing due to order of load).
 *
 * @since 1.0.0
 */
function is_azrcrv_plugin_active( $plugin ) {
	return in_array( $plugin, (array) get_option( 'active_plugins', array() ) );
}

/**
 * Save settings.
 *
 * @since 1.0.0
 */
function save_options() {
	// Check that user has proper security level.
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permissions to perform this action', 'azrcrv-lacss' ) );
	}

	// Check that nonce field created in configuration form is present.
	if ( ! empty( $_POST ) && check_admin_referer( 'azrcrv-lacss', 'azrcrv-lacss-nonce' ) ) {

		// Retrieve original plugin options array.
		$options = get_option( 'azrcrv-lacss' );

		/*
			Enabled
		*/
		$option_name = 'enable-internal';
		if ( isset( $_POST[ $option_name ] ) ) {
			$options['enable']['internal'] = 1;
		} else {
			$options['enable']['internal'] = 0;
		}

		/*
			CSS
		*/
		$option_name = 'css-internal';
		if ( isset( $_POST[ $option_name ] ) ) {
			$options['css']['internal'] = sanitize_textarea_field( wp_unslash( $_POST[ $option_name ] ) );
		}

		// Store updated options array to database.
		update_option( 'azrcrv-lacss', $options );

		// Redirect the page to the configuration form that was processed.
		wp_safe_redirect( add_query_arg( 'page', 'azrcrv-lacss&settings-updated', admin_url( 'admin.php' ) ) );
		exit;

	}

}
