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
	 * Class Constructor
	 */
	public function __construct() {

		// Get Translations.
		require_once ROOT_WPBL_TRANSLATIONS_DIR . 'class-wpbooklist-translations.php';
		$this->trans = new WPBookList_Translations();
		$this->trans->trans_strings();


	}

	public function output_search_form(){

		global $wpdb;

		$extension_settings = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'wpbooklist_search_settings' );

		$string1 = '
				<div id="wpbooklist-storefront-container-div">
					<p class="wpbooklist-tab-intro-para">Here you can change some of the default settings for your WPBookList Search Extension, such as how many search results appear on one page, and what values certain Drop-Down boxes contain.</p>
					<p style="margin-top:40px; text-align:center; max-width:600px; font-weight:bold; font-size:15px; margin-left:auto; margin-right:auto;">Set Results Per Page</p>
					<input id="wpbooklist-search-perpage-input" type="number" value="' . $extension_settings->perpage . '"/>
					<p style="margin-top:40px; text-align:center; max-width:600px; font-weight:bold; font-size:15px; margin-left:auto; margin-right:auto;">Set Earliest Publication Date Year </p>
					<input id="wpbooklist-search-earlypubdate-input" type="number" value="' . $extension_settings->earlypubdate . '"/>
					<p style="margin-top:40px; text-align:center; max-width:600px; font-weight:bold; font-size:15px; margin-left:auto; margin-right:auto;">Set the \'Format\' Drop-Down values (seperate each value with a comma)</p>
					<input id="wpbooklist-search-format-input" type="text" value="' . $extension_settings->formatvalues . '"/>
					<p style="margin-top:40px; text-align:center; max-width:600px; font-weight:bold; font-size:15px; margin-left:auto; margin-right:auto;">Set the \'Genre\' Drop-Down values (seperate each value with a comma)</p>
					<input id="wpbooklist-search-genre-input" type="text" value="' . $extension_settings->genrevalues . '"/>
					<p style="margin-top:40px; text-align:center; max-width:600px; font-weight:bold; font-size:15px; margin-left:auto; margin-right:auto;">Set the \'Sub-Genre\' Drop-Down values (seperate each value with a comma)</p>
					<input id="wpbooklist-search-subgenre-input" type="text" value="' . $extension_settings->subgenrevalues . '"/>
					<div id="wpbooklist-search-save-wrapper">
						<button id="wpbooklist-search-save-general-button">Save Search Settings</button>
						<div class="wpbooklist-spinner" id="wpbooklist-spinner-1"></div>
					</div>
					<div id="wpbooklist-storefront-success-div"></div>
				</div>';
		
    	return $string1;
	}
}

endif;