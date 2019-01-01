<?php
/**
 * Class Search_Ajax_Functions - class-wpbooklist-ajax-functions.php
 *
 * @author   Jake Evans
 * @category Admin
 * @package  Includes
 * @version  6.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Search_Ajax_Functions', false ) ) :
	/**
	 * Search_Ajax_Functions class. Here we'll do things like enqueue scripts/css, set up menus, etc.
	 */
	class Search_Ajax_Functions {

		/**
		 * Class Constructor - Simply calls the Translations
		 */
		public function __construct() {


		}

		/**
		 * Callback function for handling the saving of the user's License Key.
		 */
		public function wpbooklist_search_save_license_key_action_callback() {

			global $wpdb;

			check_ajax_referer( 'wpbooklist_search_save_license_key_action_callback', 'security' );

			if ( isset( $_POST['license'] ) ) {
				$license = filter_var( wp_unslash( $_POST['license'] ), FILTER_SANITIZE_STRING );
			}

			$data         = array(
				'license' => $license,
			);
			$format       = array( '%s' );
			$where        = array( 'ID' => 1 );
			$where_format = array( '%d' );
			$save_result = $wpdb->update( $wpdb->prefix . 'wpbooklist_search_options', $data, $where, $format, $where_format );

			wp_die( $save_result );

		}

		/**
		 * Callback function for saving the 'Search Field' Settings.
		 */
		public function wpbooklist_search_save_search_field_settings_action_callback() {

			global $wpdb;

			check_ajax_referer( 'wpbooklist_search_save_search_field_settings_action_callback', 'security' );

			if ( isset( $_POST['hidesearchby'] ) ) {
				$hidesearchby = filter_var( wp_unslash( $_POST['hidesearchby'] ), FILTER_SANITIZE_STRING );
			}

			if ( isset( $_POST['hidesearchin'] ) ) {
				$hidesearchin = filter_var( wp_unslash( $_POST['hidesearchin'] ), FILTER_SANITIZE_STRING );
			}

			$hidesearchby = rtrim( $hidesearchby, ',' );
			$hidesearchin = rtrim( $hidesearchin, ',' );

			$data         = array(
				'hidesearchby' => $hidesearchby,
				'hidesearchin' => $hidesearchin,
			);
			$format       = array( '%s' );
			$where        = array( 'ID' => 1 );
			$where_format = array( '%d' );
			$save_result = $wpdb->update( $wpdb->prefix . 'wpbooklist_search_options', $data, $where, $format, $where_format );

			wp_die( $save_result );

		}

		/**
		 * Callback function for saving the 'Search General Settings' Settings.
		 */
		public function wpbooklist_search_save_search_general_settings_action_callback() {

			global $wpdb;

			check_ajax_referer( 'wpbooklist_search_save_search_general_settings_action_callback', 'security' );

			if ( isset( $_POST['perpage'] ) ) {
				$perpage = filter_var( wp_unslash( $_POST['perpage'] ), FILTER_SANITIZE_STRING );
			}

			if ( isset( $_POST['searchmode'] ) ) {
				$searchmode = filter_var( wp_unslash( $_POST['searchmode'] ), FILTER_SANITIZE_STRING );
			}

			$data         = array(
				'perpage' => $perpage,
				'searchmode' => $searchmode,
			);
			$format       = array( '%s' );
			$where        = array( 'ID' => 1 );
			$where_format = array( '%d' );
			$save_result = $wpdb->update( $wpdb->prefix . 'wpbooklist_search_options', $data, $where, $format, $where_format );

			wp_die( $save_result );

		}

	}
endif;
