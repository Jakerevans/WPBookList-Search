<?php
/**
 * WordPress Book List Search Extension
 *
 * @package     WordPress Book List Search Extension
 * @author      Jake Evans
 * @copyright   2018 Jake Evans
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: WPBookList Search Extension
 * Plugin URI: https://www.jakerevans.com
 * Description: A WPBookList Extension that provides Advanced Search functions to search through a website's WPBookList books.
 * Version: 1.0.0
 * Author: Jake Evans
 * Text Domain: wpbooklist
 * Author URI: https://www.jakerevans.com
 */

/*
 * SETUP NOTES:
 *
 * Change all filename instances from search to desired plugin name
 *
 * Modify Plugin Name
 *
 * Modify Description
 *
 * Modify Version Number in Block comment and in Constant
 *
 * Find & Replace these 3 strings:
 * search
 * Search
 * SEARCH
 *
 * Install Gulp & all Plugins listed in gulpfile.js
 *
 *
 *
 *
 *
 */




// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

/* REQUIRE STATEMENTS */
	require_once 'includes/class-search-general-functions.php';
	require_once 'includes/class-search-ajax-functions.php';
/* END REQUIRE STATEMENTS */

/* CONSTANT DEFINITIONS */

	// Root plugin folder directory.
	if ( ! defined('WPBOOKLIST_VERSION_NUM' ) ) {
		define( 'WPBOOKLIST_VERSION_NUM', '6.1.6' );
	}

	// This Extension's Version Number.
	define( 'WPBOOKLIST_SEARCH_VERSION_NUM', '1.0.0' );

	// This is the URL our updater / license checker pings. This should be the URL of the site with EDD installed.
	define('EDD_SL_STORE_URL_SEARCH', 'https://wpbooklist.com');

	// The id of your product in EDD.
	define( 'EDD_SL_ITEM_ID_SEARCH', 14909 );

	// Root plugin folder directory.
	define( 'SEARCH_ROOT_DIR', plugin_dir_path( __FILE__ ) );

	// Root WordPress Plugin Directory.
	define( 'SEARCH_ROOT_WP_PLUGINS_DIR', str_replace( '/wpbooklist-search', '', plugin_dir_path( __FILE__ ) ) );

	// Root WPBL Dir.
	if ( ! defined('ROOT_WPBL_DIR' ) ) {
		define( 'ROOT_WPBL_DIR', SEARCH_ROOT_WP_PLUGINS_DIR . 'wpbooklist/' );
	}

	// Root WPBL Url.
	if ( ! defined('ROOT_WPBL_URL' ) ) {
		define( 'ROOT_WPBL_URL', plugins_url() . '/wpbooklist/' );
	}

	// Root WPBL Classes Dir.
	if ( ! defined('ROOT_WPBL_CLASSES_DIR' ) ) {
		define( 'ROOT_WPBL_CLASSES_DIR', ROOT_WPBL_DIR . 'includes/classes/' );
	}

	// Root WPBL Transients Dir.
	if ( ! defined('ROOT_WPBL_TRANSIENTS_DIR' ) ) {
		define( 'ROOT_WPBL_TRANSIENTS_DIR', ROOT_WPBL_CLASSES_DIR . 'transients/' );
	}

	// Root WPBL Translations Dir.
	if ( ! defined('ROOT_WPBL_TRANSLATIONS_DIR' ) ) {
		define( 'ROOT_WPBL_TRANSLATIONS_DIR', ROOT_WPBL_CLASSES_DIR . 'translations/' );
	}

	// Root WPBL Root Img Icons Dir.
	if ( ! defined('ROOT_WPBL_IMG_ICONS_URL' ) ) {
		define( 'ROOT_WPBL_IMG_ICONS_URL', ROOT_WPBL_URL . 'assets/img/icons/' );
	}

	// Root WPBL Root Utilities Dir.
	if ( ! defined('ROOT_WPBL_UTILITIES_DIR' ) ) {
		define( 'ROOT_WPBL_UTILITIES_DIR', ROOT_WPBL_CLASSES_DIR . 'utilities/' );
	}

	// Root plugin folder URL .
	define( 'SEARCH_ROOT_URL', plugins_url() . '/wpbooklist-search/' );

	// Root Classes Directory.
	define( 'SEARCH_CLASS_DIR', SEARCH_ROOT_DIR . 'includes/classes/' );

	// Root REST Classes Directory.
	define( 'SEARCH_CLASS_REST_DIR', SEARCH_ROOT_DIR . 'includes/classes/rest/' );

	// Root Compatability Classes Directory.
	define( 'SEARCH_CLASS_COMPAT_DIR', SEARCH_ROOT_DIR . 'includes/classes/compat/' );

	// Root Translations Directory.
	define( 'SEARCH_CLASS_TRANSLATIONS_DIR', SEARCH_ROOT_DIR . 'includes/classes/translations/' );

	// Root Transients Directory.
	define( 'SEARCH_CLASS_TRANSIENTS_DIR', SEARCH_ROOT_DIR . 'includes/classes/transients/' );

	// Root Image URL.
	define( 'SEARCH_ROOT_IMG_URL', SEARCH_ROOT_URL . 'assets/img/' );

	// Root Image Icons URL.
	define( 'SEARCH_ROOT_IMG_ICONS_URL', SEARCH_ROOT_URL . 'assets/img/icons/' );

	// Root CSS URL.
	define( 'SEARCH_CSS_URL', SEARCH_ROOT_URL . 'assets/css/' );

	// Root JS URL.
	define( 'SEARCH_JS_URL', SEARCH_ROOT_URL . 'assets/js/' );

	// Root UI directory.
	define( 'SEARCH_ROOT_INCLUDES_UI', SEARCH_ROOT_DIR . 'includes/ui/' );

	// Root UI Admin directory.
	define( 'SEARCH_ROOT_INCLUDES_UI_ADMIN_DIR', SEARCH_ROOT_DIR . 'includes/ui/admin/' );

	// Define the Uploads base directory.
	$uploads     = wp_upload_dir();
	$upload_path = $uploads['basedir'];
	define( 'SEARCH_UPLOADS_BASE_DIR', $upload_path . '/' );

	// Define the Uploads base URL.
	$upload_url = $uploads['baseurl'];
	define( 'SEARCH_UPLOADS_BASE_URL', $upload_url . '/' );

	// Nonces array.
	define( 'SEARCH_NONCES_ARRAY',
		wp_json_encode(array(
			'adminnonce1'  => 'wpbooklist_search_save_license_key_action_callback',
			'adminnonce2'  => 'wpbooklist_search_save_options_action_callback',
		))
	);

/* END OF CONSTANT DEFINITIONS */

/* MISC. INCLUSIONS & DEFINITIONS */

	// Loading textdomain.
	load_plugin_textdomain( 'wpbooklist', false, SEARCH_ROOT_DIR . 'languages' );

/* END MISC. INCLUSIONS & DEFINITIONS */

/* CLASS INSTANTIATIONS */

	// Call the class found in wpbooklist-functions.php.
	$search_general_functions = new Search_General_Functions();

	// Call the class found in wpbooklist-functions.php.
	$search_ajax_functions = new Search_Ajax_Functions();


/* END CLASS INSTANTIATIONS */


/* FUNCTIONS FOUND IN CLASS-WPBOOKLIST-GENERAL-FUNCTIONS.PHP THAT APPLY PLUGIN-WIDE */

	// Displays the 'Enter Your License Key' message at the top of the dashboard if the user hasn't done so already.
	add_action( 'admin_notices', array( $search_general_functions, 'wpbooklist_search_top_dashboard_license_notification' ) );

	// Function that adds in the License Key Submission form on this Extension's entry on the plugins page.
	add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $search_general_functions, 'wpbooklist_search_pluginspage_license_entry' ) );

	// Adding the function that will take our SEARCH_NONCES_ARRAY Constant from above and create actual nonces to be passed to Javascript functions.
	add_action( 'init', array( $search_general_functions, 'wpbooklist_search_create_nonces' ) );

	// Function to run any code that is needed to modify the plugin between different versions.
	add_action( 'plugins_loaded', array( $search_general_functions, 'wpbooklist_search_update_upgrade_function' ) );

	// Adding the admin js file.
	add_action( 'admin_enqueue_scripts', array( $search_general_functions, 'wpbooklist_search_admin_js' ) );

	// Adding the frontend js file.
	add_action( 'wp_enqueue_scripts', array( $search_general_functions, 'wpbooklist_search_frontend_js' ) );

	// Adding the admin css file for this extension.
	add_action( 'admin_enqueue_scripts', array( $search_general_functions, 'wpbooklist_search_admin_style' ) );

	// Adding the Front-End css file for this extension.
	add_action( 'wp_enqueue_scripts', array( $search_general_functions, 'wpbooklist_search_frontend_style' ) );

	// Function to add table names to the global $wpdb.
	add_action( 'admin_footer', array( $search_general_functions, 'wpbooklist_search_register_table_name' ) );

	// Function to run any code that is needed to modify the plugin between different versions.
	add_action( 'admin_footer', array( $search_general_functions, 'wpbooklist_search_admin_pointers_javascript' ) );

	// Verifies that the core WPBookList plugin is installed and activated - otherwise, the Extension doesn't load and a message is displayed to the user.
	register_activation_hook( __FILE__, array( $search_general_functions, 'wpbooklist_core_plugin_required' ) );

	// Creates tables upon activation.
	register_activation_hook( __FILE__, array( $search_general_functions, 'wpbooklist_search_create_tables' ) );

	// Runs once upon extension activation and adds it's version number to the 'extensionversions' column in the 'wpbooklist_jre_user_options' table of the core plugin.
	register_activation_hook( __FILE__, array( $search_general_functions, 'wpbooklist_search_record_extension_version' ) );

	// And in the darkness bind them.
	add_filter('admin_footer', array( $search_general_functions, 'wpbooklist_search_smell_rose' ));

	global $wpdb;
	$test_name = $wpdb->prefix . 'wpbooklist_search_settings';
	if ( $test_name === $wpdb->get_var( "SHOW TABLES LIKE '$test_name'" ) ) {
		$extension_settings = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'wpbooklist_search_settings' );
		if ( false !== stripos( $extension_settings->freg, 'aod' ) ) {
			add_shortcode( 'wpbooklist_search', array( $search_general_functions, 'wpbooklist_search_plugin_dynamic_shortcode_function' ) );
			add_filter( 'wpbooklist_add_sub_menu', array( $search_general_functions, 'wpbooklist_search_submenu' ) );
		}
	}


/* END OF FUNCTIONS FOUND IN CLASS-WPBOOKLIST-GENERAL-FUNCTIONS.PHP THAT APPLY PLUGIN-WIDE */

/* FUNCTIONS FOUND IN CLASS-WPBOOKLIST-AJAX-FUNCTIONS.PHP THAT APPLY PLUGIN-WIDE */

	// Callback function for handling the saving of the user's License Key.
	add_action( 'wp_ajax_wpbooklist_search_save_license_key_action', array( $search_ajax_functions, 'wpbooklist_search_save_license_key_action_callback' ) );

	// Callback function for handling the saving of the user's Options.
	add_action( 'wp_ajax_wpbooklist_search_save_options_action', array( $search_ajax_functions, 'wpbooklist_search_save_options_action_callback' ) );

	// For receiving user feedback upon deactivation & deletion.
	add_action( 'wp_ajax_search_exit_results_action', array( $search_ajax_functions, 'search_exit_results_action_callback' ) );

	// For saving the 'Search Field' Settings.
	add_action( 'wp_ajax_wpbooklist_search_save_search_field_settings_action', array( $search_ajax_functions, 'wpbooklist_search_save_search_field_settings_action_callback' ) );

	// For saving the 'Search General Settings' Settings.
	add_action( 'wp_ajax_wpbooklist_search_save_search_general_settings_action', array( $search_ajax_functions, 'wpbooklist_search_save_search_general_settings_action_callback' ) );





/* END OF FUNCTIONS FOUND IN CLASS-WPBOOKLIST-AJAX-FUNCTIONS.PHP THAT APPLY PLUGIN-WIDE */






















