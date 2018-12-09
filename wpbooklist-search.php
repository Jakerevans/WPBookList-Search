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
 * Description: A Boilerplate Extension for WPBookList that creates a menu page and has it's own tabs.
 * Version: 6.0.0
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

	// Extension version number.
	define( 'SEARCH_VERSION_NUM', '6.0.0' );

	// Root plugin folder directory.
	define( 'SEARCH_ROOT_DIR', plugin_dir_path( __FILE__ ) );

	// Root WordPress Plugin Directory.
	define( 'SEARCH_ROOT_WP_PLUGINS_DIR', str_replace( '/wpbooklist-search', '', plugin_dir_path( __FILE__ ) ) );

	// Root WPBL Dir.
	define( 'ROOT_WPBL_DIR', SEARCH_ROOT_WP_PLUGINS_DIR . 'wpbooklist/' );

	// Root WPBL Classes Dir.
	define( 'ROOT_WPBL_CLASSES_DIR', ROOT_WPBL_DIR . 'includes/classes/' );

	// Root WPBL Transients Dir.
	define( 'ROOT_WPBL_TRANSIENTS_DIR', ROOT_WPBL_CLASSES_DIR . 'transients/' );

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
			'adminnonce1' => 'wpbooklist_search_functionname_action_callback',
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

	// Function that loads up the menu page entry for this Extension.
	add_filter( 'wpbooklist_add_sub_menu', array( $search_general_functions, 'wpbooklist_search_submenu' ) );

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

	// Creates tables upon activation.
	register_activation_hook( __FILE__, array( $search_general_functions, 'wpbooklist_search_create_tables' ) );

	// Runs once upon extension activation and adds it's version number to the 'extensionversions' column in the 'wpbooklist_jre_user_options' table of the core plugin.
	register_activation_hook( __FILE__, array( $search_general_functions, 'wpbooklist_search_record_extension_version' ) );



/* END OF FUNCTIONS FOUND IN CLASS-WPBOOKLIST-GENERAL-FUNCTIONS.PHP THAT APPLY PLUGIN-WIDE */

/* FUNCTIONS FOUND IN CLASS-WPBOOKLIST-AJAX-FUNCTIONS.PHP THAT APPLY PLUGIN-WIDE */

	// For receiving user feedback upon deactivation & deletion.
	add_action( 'wp_ajax_search_exit_results_action', array( $search_ajax_functions, 'search_exit_results_action_callback' ) );

/* END OF FUNCTIONS FOUND IN CLASS-WPBOOKLIST-AJAX-FUNCTIONS.PHP THAT APPLY PLUGIN-WIDE */






















