<?php
/**
 * Class Search_General_Functions - class-search-general-functions.php
 *
 * @author   Jake Evans
 * @category Admin
 * @package  Includes
 * @version  6.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Search_General_Functions', false ) ) :
	/**
	 * Search_General_Functions class. Here we'll do things like enqueue scripts/css, set up menus, etc.
	 */
	class Search_General_Functions {

		/**
		 * Verifies that the core WPBookList plugin is installed and activated - otherwise, the Extension doesn't load and a message is displayed to the user.
		 */
		public function wpbooklist_core_plugin_required() {

			// Require core WPBookList Plugin.
			if ( ! is_plugin_active( 'wpbooklist/wpbooklist.php' ) && current_user_can( 'activate_plugins' ) ) {

				// Stop activation redirect and show error.
				wp_die( 'Whoops! This WPBookList Extension requires the Core WPBookList Plugin to be installed and activated! <br><a target="_blank" href="https://wordpress.org/plugins/wpbooklist/">Download WPBookList Here!</a><br><br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Return to Plugins</a>');
			}
		}

		


		/**
		 * Verifies the crown of the rose.
		 *
		 * @param  array $plugins List of plugins to activate & load.
		 */
		public function wpbooklist_search_smell_rose() {

			global $wpdb;

			// Get license key from plugin options, if it's already been saved. If it has, don't display anything.
			$this->extension_settings = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'wpbooklist_search_settings' );

			// If the License Key just hasn't been entered yet...
			if ( null === $this->extension_settings->freg || '' === $this->extension_settings->freg ) {
				return;
			} else {

				if ( false !== stripos( $this->extension_settings->freg, '---' ) ) {

					$temp = explode( '---', $this->extension_settings->freg );

					if ( 'aod' === $temp[1] ) {

						// Get the date.
						require_once ROOT_WPBL_UTILITIES_DIR . 'class-wpbooklist-utilities-date.php';
						$utilities_date = new WPBookList_Utilities_Date();
						$this->date     = $utilities_date->wpbooklist_get_date_via_current_time( 'timestamp' );

						if ( 604800 < ( $this->date - (int) $temp[2] ) ) {

							$checker_good_flag = false;

							$san_check = wp_remote_get( 'https://wpbooklist.com/?edd_action=activate_license&item_id=' . EDD_SL_ITEM_ID_SEARCH . '&license=' . $temp[0] . '&url=' . get_site_url() );

							// Check the response code.
							$response_code    = wp_remote_retrieve_response_code( $san_check );
							$response_message = wp_remote_retrieve_response_message( $san_check );

							if ( 200 !== $response_code && ! empty( $response_message ) ) {
								return new WP_Error( $response_code, $response_message );
							} elseif ( 200 !== $response_code ) {
								$this->apireport = $this->apireport . 'Unknown error occurred with wp_remote_get() trying to build Books-a-Million link in the create_buy_links() function ';
								return new WP_Error( $response_code, 'Unknown error occurred with wp_remote_get() trying to build Books-a-Million link in the create_buy_links() function' );
							} else {
								$san_check = wp_remote_retrieve_body( $san_check );
								$san_check = json_decode( $san_check, true );

								if ( 'valid' === $san_check['license'] && $san_check['success'] ) {

									$this->date = $utilities_date->wpbooklist_get_date_via_current_time( 'timestamp' );

									$data         = array(
										'freg' => $temp[0] . '---aod---' . $this->date,
									);
									$format       = array( '%s' );
									$where        = array( 'ID' => 1 );
									$where_format = array( '%d' );
									$save_result = $wpdb->update( $wpdb->prefix . 'wpbooklist_search_settings', $data, $where, $format, $where_format );

									$checker_good_flag = true;
								} else {
									$data         = array(
										'freg' => '',
									);
									$format       = array( '%s' );
									$where        = array( 'ID' => 1 );
									$where_format = array( '%d' );
									$save_result = $wpdb->update( $wpdb->prefix . 'wpbooklist_search_settings', $data, $where, $format, $where_format );
								}
							}

							if ( ! $checker_good_flag ) {
								deactivate_plugins( SEARCH_ROOT_DIR . 'wpbooklist-search.php' );
								return;
							}
						} else {
							return;
						}
					} else {

						$checker_good_flag = false;

						$san_check = wp_remote_get( 'https://wpbooklist.com/?edd_action=activate_license&item_id=' . EDD_SL_ITEM_ID_SEARCH . '&license=' . $this->extension_settings->freg . '&url=' . get_site_url() );

						// Check the response code.
						$response_code    = wp_remote_retrieve_response_code( $san_check );
						$response_message = wp_remote_retrieve_response_message( $san_check );

						if ( 200 !== $response_code && ! empty( $response_message ) ) {
							return new WP_Error( $response_code, $response_message );
						} elseif ( 200 !== $response_code ) {
							$this->apireport = $this->apireport . 'Unknown error occurred with wp_remote_get() trying to build Books-a-Million link in the create_buy_links() function ';
							return new WP_Error( $response_code, 'Unknown error occurred with wp_remote_get() trying to build Books-a-Million link in the create_buy_links() function' );
						} else {
							$san_check = wp_remote_retrieve_body( $san_check );
							$san_check = json_decode( $san_check, true );

							if ( 'valid' === $san_check['license'] && $san_check['success'] ) {

								// Get the date.
								require_once ROOT_WPBL_UTILITIES_DIR . 'class-wpbooklist-utilities-date.php';
								$utilities_date = new WPBookList_Utilities_Date();
								$this->date     = $utilities_date->wpbooklist_get_date_via_current_time( 'timestamp' );

								$data         = array(
									'freg' => $this->extension_settings->freg . '---aod---' . $this->date,
								);
								$format       = array( '%s' );
								$where        = array( 'ID' => 1 );
								$where_format = array( '%d' );
								$save_result = $wpdb->update( $wpdb->prefix . 'wpbooklist_search_settings', $data, $where, $format, $where_format );

								$checker_good_flag = true;

							} else {
								$data         = array(
									'freg' => '',
								);
								$format       = array( '%s' );
								$where        = array( 'ID' => 1 );
								$where_format = array( '%d' );
								$save_result = $wpdb->update( $wpdb->prefix . 'wpbooklist_search_settings', $data, $where, $format, $where_format );
							}
						}

						if ( ! $checker_good_flag ) {
							deactivate_plugins( SEARCH_ROOT_DIR . 'wpbooklist-search.php' );
							return;
						}
					}
				} else {

					$checker_good_flag = false;

					$san_check = wp_remote_get( 'https://wpbooklist.com/?edd_action=activate_license&item_id=' . EDD_SL_ITEM_ID_SEARCH . '&license=' . $this->extension_settings->freg . '&url=' . get_site_url() );

					// Check the response code.
					$response_code    = wp_remote_retrieve_response_code( $san_check );
					$response_message = wp_remote_retrieve_response_message( $san_check );

					if ( 200 !== $response_code && ! empty( $response_message ) ) {
						return new WP_Error( $response_code, $response_message );
					} elseif ( 200 !== $response_code ) {
						$this->apireport = $this->apireport . 'Unknown error occurred with wp_remote_get() trying to build Books-a-Million link in the create_buy_links() function ';
						return new WP_Error( $response_code, 'Unknown error occurred with wp_remote_get() trying to build Books-a-Million link in the create_buy_links() function' );
					} else {
						$san_check = wp_remote_retrieve_body( $san_check );
						$san_check = json_decode( $san_check, true );

						if ( 'valid' === $san_check['license'] && $san_check['success'] ) {

							// Get the date.
							require_once ROOT_WPBL_UTILITIES_DIR . 'class-wpbooklist-utilities-date.php';
							$utilities_date = new WPBookList_Utilities_Date();
							$this->date     = $utilities_date->wpbooklist_get_date_via_current_time( 'timestamp' );

							$data         = array(
								'freg' => $this->extension_settings->freg . '---aod---' . $this->date,
							);
							$format       = array( '%s' );
							$where        = array( 'ID' => 1 );
							$where_format = array( '%d' );
							$save_result = $wpdb->update( $wpdb->prefix . 'wpbooklist_search_settings', $data, $where, $format, $where_format );

							$checker_good_flag = true;

						} else {
							$data         = array(
								'freg' => '',
							);
							$format       = array( '%s' );
							$where        = array( 'ID' => 1 );
							$where_format = array( '%d' );
							$save_result = $wpdb->update( $wpdb->prefix . 'wpbooklist_search_settings', $data, $where, $format, $where_format );
						}
					}

					if ( ! $checker_good_flag ) {
						deactivate_plugins( SEARCH_ROOT_DIR . 'wpbooklist-search.php' );

						if ( isset( $_SERVER['REQUEST_URI'] ) ) {
							//header( 'Location: ' . filter_var( wp_unslash( $_SERVER['REQUEST_URI'] ), FILTER_SANITIZE_STRING ) );
						}

						return;
					}
				}
			}
		}

		/**
		 * Adds in the 'Enter License Key' text input and submit button.
		 *
		 * @param  array $links List of existing plugin action links.
		 * @return array List of modified plugin action links.
		 */
		public function wpbooklist_search_pluginspage_license_entry( $links ) {

			global $wpdb;

			require_once CLASS_TRANSLATIONS_DIR . 'class-wpbooklist-translations.php';
			$trans = new WPBookList_Translations();

			// Get license key from plugin options, if it's already been saved. If it has, don't display anything.
			$this->extension_settings = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'wpbooklist_search_settings' );

			if ( null === $this->extension_settings->freg || '' === $this->extension_settings->freg ) {
				$value = $trans->trans_613;
			} else {
				$value = $this->extension_settings->freg;
			}

			$license_html = '
				<form>
					<input id="wpbooklist-extension-genreric-key-plugins-page-button-search" class="wpbooklist-extension-licence-key-plugins-page-input" type="text" placeholder="' . $trans->trans_613 . '" value="' . $value . '"></input>
					<button id="wpbooklist-extension-licence-key-plugins-page-button-search" class="wpbooklist-extension-licence-key-plugins-page-button">' . $trans->trans_614 . '</button>
				</form>';

			array_push( $links, $license_html );

			return $links;

		}

		/**
		 * Displays the 'Enter Your License Key' message at the top of the dashboard if the user hasn't done so already.
		 */
		public function wpbooklist_search_top_dashboard_license_notification() {

			global $wpdb;

			// Get license key from plugin options, if it's already been saved. If it has, don't display anything.
			$this->extension_settings = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'wpbooklist_search_settings' );

			if ( null === $this->extension_settings->freg || '' === $this->extension_settings->freg ) {

				require_once CLASS_TRANSLATIONS_DIR . 'class-wpbooklist-translations.php';
				$trans = new WPBookList_Translations();

				echo '
				<div class="notice notice-success is-dismissible">
					<form class="wpbooklist-extension-licence-key-dashboard-form" id="wpbooklist-extension-licence-key-dashboard-form-search">
						<p class="wpbooklist-extension-licence-key-dashboard-title">' . $trans->trans_615 . '</p>
						<input id="wpbooklist-extension-licence-key-dashboard-input-search" class="wpbooklist-extension-licence-key-dashboard-input" type="text" placeholder="' . $trans->trans_613 . '" value="' . $trans->trans_613 . '"></input>
						<button data-ext="search" id="wpbooklist-extension-licence-key-dashboard-button-search" class="wpbooklist-extension-licence-key-dashboard-button">' . $trans->trans_614 . '</button>
					</form>
				</div>';
			}
		}


		/**
		 * Functions that loads up the menu page entry for this Extension.
		 *
		 * @param array $submenu_array The array that contains submenu entries to add to.
		 */
		public function wpbooklist_search_submenu( $submenu_array ) {

			global $wpdb;

			// Get license key from plugin options, if it's already been saved. If it has, don't display anything.
			$this->extension_settings = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'wpbooklist_search_settings' );

			// If the License Key just hasn't been entered yet...
			if ( null === $this->extension_settings->freg || '' === $this->extension_settings->freg ) {

				return $submenu_array;

			} else {

				// If a License key has been saved, let's verify it, and if it's not good, don't load the plugin.
				$license_good_flag = true;

				if ( $license_good_flag ) {

					$extra_submenu = array(
						'Search',
					);

					// Combine the two arrays.
					$submenu_array = array_merge( $submenu_array, $extra_submenu );
					return $submenu_array;

				} else {

					return $submenu_array;

				}
			}
		}

		/**
		 *  Here we take the Constant defined in wpbooklist.php that holds the values that all our nonces will be created from, we create the actual nonces using wp_create_nonce, and the we define our new, final nonces Constant, called WPBOOKLIST_FINAL_NONCES_ARRAY.
		 */
		public function wpbooklist_search_create_nonces() {

			$temp_array = array();
			foreach ( json_decode( SEARCH_NONCES_ARRAY ) as $key => $noncetext ) {
				$nonce              = wp_create_nonce( $noncetext );
				$temp_array[ $key ] = $nonce;
			}

			// Defining our final nonce array.
			define( 'SEARCH_FINAL_NONCES_ARRAY', wp_json_encode( $temp_array ) );

		}

		/**
		 *  Runs once upon extension activation and adds it's version number to the 'extensionversions' column in the 'wpbooklist_jre_user_options' table of the core plugin.
		 */
		public function wpbooklist_search_record_extension_version() {
			global $wpdb;
			$existing_string = $wpdb->get_row( 'SELECT * from ' . $wpdb->prefix . 'wpbooklist_jre_user_options' );

			// Check to see if Extension is already registered.
			if ( false !== strpos( $existing_string->extensionversions, 'search' ) ) {
				$split_string = explode( 'search', $existing_string->extensionversions );
				$first_part   = $split_string[0];
				$last_part    = substr( $split_string[1], 5 );
				$new_string   = $first_part . 'search' . WPBOOKLIST_SEARCH_VERSION_NUM . $last_part;
			} else {
				$new_string = $existing_string->extensionversions . 'search' . WPBOOKLIST_SEARCH_VERSION_NUM;
			}

			$data         = array(
				'extensionversions' => $new_string,
			);
			$format       = array( '%s' );
			$where        = array( 'ID' => 1 );
			$where_format = array( '%d' );
			$wpdb->update( $wpdb->prefix . 'wpbooklist_jre_user_options', $data, $where, $format, $where_format );

		}

		/**
		 *  Function to run the compatability code in the Compat class for upgrades/updates, if stored version number doesn't match the defined global in wpbooklist-search.php
		 */
		public function wpbooklist_search_update_upgrade_function() {

			// Get current version #.
			global $wpdb;
			$existing_string = $wpdb->get_row( 'SELECT * from ' . $wpdb->prefix . 'wpbooklist_jre_user_options' );

			// Check to see if Extension is already registered and matches this version.
			if ( false !== strpos( $existing_string->extensionversions, 'search' ) ) {
				$split_string = explode( 'search', $existing_string->extensionversions );
				$version      = substr( $split_string[1], 0, 5 );

				// If version number does not match the current version number found in wpbooklist.php, call the Compat class and run upgrade functions.
				if ( WPBOOKLIST_SEARCH_VERSION_NUM !== $version ) {
					require_once SEARCH_CLASS_COMPAT_DIR . 'class-search-compat-functions.php';
					$compat_class = new Search_Compat_Functions();
				}
			}
		}

		/**
		 * Adding the admin js file
		 */
		public function wpbooklist_search_admin_js() {

			global $wpdb;

			wp_register_script( 'wpbooklist_search_adminjs', SEARCH_JS_URL . 'wpbooklist_search_admin.min.js', array( 'jquery' ), WPBOOKLIST_VERSION_NUM, true );

			// Next 4-5 lines are required to allow translations of strings that would otherwise live in the wpbooklist-admin-js.js JavaScript File.
			require_once CLASS_TRANSLATIONS_DIR . 'class-wpbooklist-translations.php';
			$trans = new WPBookList_Translations();

			// Localize the script with the appropriate translation array from the Translations class.
			$translation_array1 = $trans->trans_strings();

			// Now grab all of our Nonces to pass to the JavaScript for the Ajax functions and merge with the Translations array.
			$final_array_of_php_values = array_merge( $translation_array1, json_decode( SEARCH_FINAL_NONCES_ARRAY, true ) );

			// Adding some other individual values we may need.
			$final_array_of_php_values['SEARCH_ROOT_IMG_ICONS_URL'] = SEARCH_ROOT_IMG_ICONS_URL;
			$final_array_of_php_values['SEARCH_ROOT_IMG_URL']       = SEARCH_ROOT_IMG_URL;
			$final_array_of_php_values['SEARCH_SPECIAL_PREFIX']     = $wpdb->prefix;
			$final_array_of_php_values['FOR_TAB_HIGHLIGHT']         = admin_url() . 'admin.php';
			$final_array_of_php_values['SAVED_ATTACHEMENT_ID']      = get_option( 'media_selector_attachment_id', 0 );

			// Now registering/localizing our JavaScript file, passing all the PHP variables we'll need in our $final_array_of_php_values array, to be accessed from 'wphealthtracker_php_variables' object (like wphealthtracker_php_variables.nameofkey, like any other JavaScript object).
			wp_localize_script( 'wpbooklist_search_adminjs', 'wpbooklistSearchPhpVariables', $final_array_of_php_values );

			wp_enqueue_script( 'wpbooklist_search_adminjs' );

			return $final_array_of_php_values;

		}

		/**
		 * Adding the frontend js file
		 */
		public function wpbooklist_search_frontend_js() {

			global $wpdb;

			wp_register_script( 'wpbooklist_search_frontendjs', SEARCH_JS_URL . 'wpbooklist_search_frontend.min.js', array( 'jquery' ), WPBOOKLIST_SEARCH_VERSION_NUM, true );

			// Next 4-5 lines are required to allow translations of strings that would otherwise live in the wpbooklist-admin-js.js JavaScript File.
			require_once CLASS_TRANSLATIONS_DIR . 'class-wpbooklist-translations.php';
			$trans = new WPBookList_Translations();

			// Localize the script with the appropriate translation array from the Translations class.
			$translation_array1 = $trans->trans_strings();

			// Now grab all of our Nonces to pass to the JavaScript for the Ajax functions and merge with the Translations array.
			$final_array_of_php_values = array_merge( $translation_array1, json_decode( SEARCH_FINAL_NONCES_ARRAY, true ) );

			// Adding some other individual values we may need.
			$final_array_of_php_values['SEARCH_ROOT_IMG_ICONS_URL'] = SEARCH_ROOT_IMG_ICONS_URL;
			$final_array_of_php_values['SEARCH_ROOT_IMG_URL']       = SEARCH_ROOT_IMG_URL;
			$final_array_of_php_values['SEARCH_SPECIAL_PREFIX']     = $wpdb->prefix;

			// Now registering/localizing our JavaScript file, passing all the PHP variables we'll need in our $final_array_of_php_values array, to be accessed from 'wphealthtracker_php_variables' object (like wphealthtracker_php_variables.nameofkey, like any other JavaScript object).
			wp_localize_script( 'wpbooklist_search_frontendjs', 'wpbooklistSearchPhpVariables', $final_array_of_php_values );

			wp_enqueue_script( 'wpbooklist_search_frontendjs' );

			return $final_array_of_php_values;

		}

		/**
		 * Adding the admin css file
		 */
		public function wpbooklist_search_admin_style() {

			wp_register_style( 'wpbooklist_search_adminui', SEARCH_CSS_URL . 'wpbooklist-search-main-admin.css', null, WPBOOKLIST_SEARCH_VERSION_NUM );
			wp_enqueue_style( 'wpbooklist_search_adminui' );

		}

		/**
		 * Adding the frontend css file
		 */
		public function wpbooklist_search_frontend_style() {

			wp_register_style( 'wpbooklist_search_frontendui', SEARCH_CSS_URL . 'wpbooklist-search-main-frontend.css', null, WPBOOKLIST_SEARCH_VERSION_NUM );
			wp_enqueue_style( 'wpbooklist_search_frontendui' );

		}

		/**
		 *  Function to add table names to the global $wpdb.
		 */
		public function wpbooklist_search_register_table_name() {
			global $wpdb;
			$wpdb->wpbooklist_search_settings = "{$wpdb->prefix}wpbooklist_search_settings";
		}

		/**
		 *  Function that calls the Style and Scripts needed for displaying of admin pointer messages.
		 */
		public function wpbooklist_search_admin_pointers_javascript() {
			wp_enqueue_style( 'wp-pointer' );
			wp_enqueue_script( 'wp-pointer' );
			wp_enqueue_script( 'utils' );
		}

		/**
		 *  Runs once upon plugin activation and creates the table that holds info on WPBookList Pages & Posts.
		 */
		public function wpbooklist_search_create_tables() {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			global $wpdb;
			global $charset_collate;

			// Call this manually as we may have missed the init hook.
			$this->wpbooklist_search_register_table_name();

			$sql_create_table1 = "CREATE TABLE {$wpdb->wpbooklist_search_settings}
			(
				ID bigint(190) auto_increment,
				perpage bigint(255) NOT NULL DEFAULT 20,
				earlypubdate bigint(255) NOT NULL DEFAULT 1800,
				formatvalues varchar(255) NOT NULL DEFAULT 'Paperback,Hardbound,Kindle,Audiobook,Other',
				genrevalues varchar(255) NOT NULL DEFAULT 'Fiction,Non-Fiction',
				subgenrevalues varchar(255) NOT NULL DEFAULT 'Fiction,Non-Fiction',
				freg varchar(255),
				PRIMARY KEY  (ID),
				KEY perpage (perpage)
			) $charset_collate; ";

			// If table doesn't exist, create table and add initial data to it.
			$test_name = $wpdb->prefix . 'wpbooklist_search_settings';
			if ( $test_name !== $wpdb->get_var( "SHOW TABLES LIKE '$test_name'" ) ) {
				dbDelta( $sql_create_table1 );
				$table_name = $wpdb->prefix . 'wpbooklist_search_settings';
				$wpdb->insert( $table_name, array( 'ID' => 1, 'perpage' => 20 ) );
			}
		}

		/** Function to allow users to output the search HTML
		 *
		 *  @param array $atts - The array that contains the shortcode attributes/arguments.
		 */
		public function wpbooklist_search_plugin_dynamic_shortcode_function( $atts ) {
			global $wpdb;

			extract(
				shortcode_atts(
					array(
						'action' => 'colorbox',
					),
				$atts )
			);

			// Set up the action taken when cover image is clicked on.
			if ( isset( $atts['action'] ) ) {
				$action = $atts['action'];
			} else {
				$action = 'colorbox';
			}

			if ( null === $atts ) {
				$action      = 'colorbox';
			}

			ob_start();
			include_once SEARCH_CLASS_DIR . 'class-wpbooklist-frontend-search-ui.php';
			$front_end_library_ui = new WPBookList_Frontend_Search_UI( $action );
			return ob_get_clean();
		}

	}
endif;
