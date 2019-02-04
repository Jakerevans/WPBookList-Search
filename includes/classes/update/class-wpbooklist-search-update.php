<?php
/**
 * WPBookList WPBookList_Search_Update Class
 *
 * @author   Jake Evans
 * @category admin
 * @package  classes/update
 * @version  1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPBookList_Search_Update', false ) ) :
	/**
	 * WPBookList_Search_Update Class.
	 */
	class WPBookList_Search_Update {

		/**
		 * Class Constructor
		 */
		public function __construct() {

			$this->wpbooklist_search_update_kickoff();

		}


		/**
		 * Outputs the actual HTML for the tab.
		 */
		public function wpbooklist_search_update_kickoff() {

			if ( ! class_exists( 'WPBookList_Search_Update_Actual' ) ) {

				// Load our custom updater if it doesn't already exist.
				require_once( SEARCH_UPDATE_DIR . 'class-wpbooklist-search-update-actual.php' );
			}

			global $wpdb;

			// Checking if table exists.
			$test_name = $wpdb->prefix . 'wpbooklist_search_settings';
			if ( $test_name === $wpdb->get_var( "SHOW TABLES LIKE '$test_name'" ) ) {

				// Get license key from plugin options, if it's already been saved. If it has, don't display anything.
				$extension_settings = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'wpbooklist_search_settings' );
				$extension_settings = explode( '---', $extension_settings->freg);

				// Retrieve our license key from the DB.
				$license_key = $extension_settings[0];

				// Setup the updater.
				$edd_updater = new WPBookList_Search_Update_Actual( EDD_SL_STORE_URL_SEARCH, SEARCH_ROOT_DIR . 'wpbooklist-search.php', array(
					'version' => WPBOOKLIST_SEARCH_VERSION_NUM,
					'license' => $license_key,
					'item_id' => EDD_SL_ITEM_ID_SEARCH,
					'author'  => 'Jake Evans',
					'url'     => home_url(),
					'beta'    => false,
				) );

			}
		}
	}

endif;
