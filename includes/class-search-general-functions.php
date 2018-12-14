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

		/** Functions that loads up the menu page entry for this Extension.
		 *
		 *  @param array $submenu_array - The array that contains submenu entries to add to.
		 */
		public function wpbooklist_search_submenu( $submenu_array ) {
			$extra_submenu = array(
				'Search',
			);

			// Combine the two arrays.
			$submenu_array = array_merge( $submenu_array, $extra_submenu );
			return $submenu_array;
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
				$new_string   = $first_part . 'search' . SEARCH_VERSION_NUM . $last_part;
			} else {
				$new_string = $existing_string->extensionversions . 'search' . SEARCH_VERSION_NUM;
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
				if ( SEARCH_VERSION_NUM !== $version ) {
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
			require_once SEARCH_CLASS_TRANSLATIONS_DIR . 'class-wpbooklist-search-translations.php';
			$trans = new WPBookList_Search_Translations();

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

			wp_register_script( 'wpbooklist_search_frontendjs', SEARCH_JS_URL . 'wpbooklist_search_frontend.min.js', array( 'jquery' ), SEARCH_VERSION_NUM, true );

			// Next 4-5 lines are required to allow translations of strings that would otherwise live in the wpbooklist-admin-js.js JavaScript File.
			require_once SEARCH_CLASS_TRANSLATIONS_DIR . 'class-wpbooklist-search-translations.php';
			$trans = new WPBookList_Search_Translations();

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

			wp_register_style( 'wpbooklist_search_adminui', SEARCH_CSS_URL . 'wpbooklist-search-main-admin.css', null, SEARCH_VERSION_NUM );
			wp_enqueue_style( 'wpbooklist_search_adminui' );

		}

		/**
		 * Adding the frontend css file
		 */
		public function wpbooklist_search_frontend_style() {

			wp_register_style( 'wpbooklist_search_frontendui', SEARCH_CSS_URL . 'wpbooklist-search-main-frontend.css', null, SEARCH_VERSION_NUM );
			wp_enqueue_style( 'wpbooklist_search_frontendui' );

		}

		/**
		 *  Function to add table names to the global $wpdb.
		 */
		public function wpbooklist_search_register_table_name() {
			global $wpdb;
			$wpdb->wpbooklist_search_options = "{$wpdb->prefix}wpbooklist_search_options";
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

			$sql_create_table1 = "CREATE TABLE {$wpdb->wpbooklist_search_options}
			(
				ID bigint(190) auto_increment,
				perpage bigint(255) NOT NULL DEFAULT 20,
				hidesearchby varchar(255),
				defaultsearchby varchar(255),
				hidesearchin varchar(255),
				defaultsearchin varchar(255),
				hidesearchfilter varchar(255),
				defaultsearchfilter varchar(255),
				searchmode varchar(255) NOT NULL DEFAULT 'inclusive',
				PRIMARY KEY  (ID),
				KEY perpage (perpage)
			) $charset_collate; ";
//pages-2,author-2,title-2,
			// If table doesn't exist, create table and add initial data to it.
			$test_name = $wpdb->prefix . 'wpbooklist_search_options';
			if ( $test_name !== $wpdb->get_var( "SHOW TABLES LIKE '$test_name'" ) ) {
				dbDelta( $sql_create_table1 );
				$table_name = $wpdb->prefix . 'wpbooklist_search_options';
				$wpdb->insert( $table_name, array( 'ID' => 1, 'perpage' => 20, 'searchmode' => 'inclusive', ) );
			}
		}

		/** Function to allow users to output the search HTML
		 *
		 *  @param array $atts - The array that contains the shortcode attributes/arguments.
		 */
		public function wpbooklist_search_plugin_dynamic_shortcode_function( $atts ) {
			global $wpdb;
			/*
			extract(
				shortcode_atts(
					array(
						'table'  => $wpdb->prefix . "wpbooklist_jre_saved_book_log",
						'action' => 'colorbox',
					),
				$atts )
			);

			// Set up the table.
			if ( isset( $atts['table'] ) ) {
				$which_table = $wpdb->prefix . 'wpbooklist_jre_' . $table;
			} else {
				$which_table = $wpdb->prefix . 'wpbooklist_jre_saved_book_log';
			}

			// Set up the action taken when cover image is clicked on.
			if ( isset( $atts['action'] ) ) {
				$action = $atts['action'];
			} else {
				$action = 'colorbox';
			}

			if ( null === $atts ) {
				$which_table = $wpdb->prefix . 'wpbooklist_jre_saved_book_log';
				$action      = 'colorbox';
			}

			$offset = 0;
			*/
			ob_start();
			include_once SEARCH_ROOT_INCLUDES_UI . 'class-wpbooklist-frontend-search-ui.php';
			$front_end_library_ui = new WPBookList_Frontend_Search_UI( );
			return ob_get_clean();
		}

	}
endif;
