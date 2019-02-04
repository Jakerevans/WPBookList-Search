<?php
/**
 * WPBookList WPBookList_Search_Form Submenu Class
 *
 * @author   Jake Evans
 * @category ??????
 * @package  ??????
 * @version  1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPBookList_Search_Form', false ) ) :
	/**
	 * WPBookList_Search_Form Class.
	 */
	class WPBookList_Search_Form {

		/**
		 * Class Constructor - Simply calls the one function to return all Translated strings.
		 */
		public function __construct() {

			// Get Translations.
			require_once ROOT_WPBL_TRANSLATIONS_DIR . 'class-wpbooklist-translations.php';
			$this->trans = new WPBookList_Translations();
			$this->trans->trans_strings();

		}

		/**
		 * Outputs the actual html.
		 */
		public function output_search_form() {

			global $wpdb;

			// Get the saved search options to populate the checkboxes.
			$searchopts = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'wpbooklist_search_settings' );

			$options = '';
			if ( 'inclusive' === $searchopts->searchmode ) {
				$options = '<option disabled>' . $this->trans->trans_656 . '</option>
					<option value="inclusive" selected>' . $this->trans->trans_654 . '</option>
					<option value="exclusive">' . $this->trans->trans_655 . '</option>';
			} elseif ( 'exclusive' === $searchopts->searchmode ) {
				$options = '<option disabled>' . $this->trans->trans_656 . '</option>
					<option value="inclusive">' . $this->trans->trans_654 . '</option>
					<option value="exclusive" selected>' . $this->trans->trans_655 . '</option>';
			} else {
				$options = '<option selected default disabled>' . $this->trans->trans_656 . '</option>
					<option value="inclusive">' . $this->trans->trans_654 . '</option>
					<option value="exclusive">' . $this->trans->trans_655 . '</option>';
			}

			$string1 = '
				<p class="wpbooklist-tab-intro-para">' . $this->trans->trans_650 . '</p>
				<p style="margin-top:40px; text-align:center; max-width:600px; font-weight:bold; font-size:15px; margin-left:auto; margin-right:auto;">' . $this->trans->trans_651 . '</p>
				<input id="wpbooklist-search-perpage-input" type="number" step="1" value="' . $searchopts->perpage . '" />
				<p style="margin-top:40px; text-align:center; max-width:600px; font-weight:bold; font-size:15px; margin-left:auto; margin-right:auto;">' . $this->trans->trans_652 . '</p>
				<p class="wpbooklist-tab-intro-para">' . $this->trans->trans_653 . '</p>
				<select id="wpbooklist-search-searchmode-select">
					' . $options . '
				</select>
				<div id="wpbooklist-search-save-wrapper">
					<button id="wpbooklist-search-save-general-button">' . $this->trans->trans_645 . '</button>
					<div class="wpbooklist-spinner" id="wpbooklist-spinner-1"></div>
				</div>';

			return $string1;
		}
	}

endif;
