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
	class WPBookList_Search_Fields_Form {

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

			$string1 = '
				<p class="wpbooklist-tab-intro-para">' . $this->trans->trans_639 . '</p>
				<p style="margin-top:40px; text-align:center; max-width:600px; font-weight:bold; font-size:15px; margin-left:auto; margin-right:auto;">' . $this->trans->trans_640 . '</p>';

			$searchin_string = '<div>';
			$db_string       = '<div class="wpbooklist-edit-book-indiv-div-class wpbooklist-search-indiv-class" id="wpbooklist-searchin-indiv-div-id-0">
						<div class="wpbooklist-search-libname-wrapper">
							<div class="wpbooklist-search-libname-div">
								<img class="wpbooklist-search-searchin-img" src="' . SEARCH_ROOT_IMG_URL .  'library.svg">
								<p class="wpbooklist-search-searchin-title">' . $this->trans->trans_61 . '</p>
							</div>
						</div>
						<div class="wpbooklist-search-checkboxes-wrapper">
							<div class="wpbooklist-search-display-options-indiv-entry">
								<div class="wpbooklist-search-display-options-label-div">
									<label>' . $this->trans->trans_641 . '</label>
								</div>
								<div class="wpbooklist-search-checkbox-actual-wrapper">
									<input class="wpbooklist-search-searchin-checkbox wpbooklist-search-searchin-checkbox-1" data-dblibname="wp_wpbooklist_jre_saved_book_log" type="checkbox" name="wp_wpbooklist_jre_saved_book_log">
								</div>
							</div>
							<div class="wpbooklist-search-display-options-indiv-entry">
								<div class="wpbooklist-search-display-options-label-div">
									<label>' . $this->trans->trans_642 . '</label>
								</div>
								<div class="wpbooklist-search-checkbox-actual-wrapper">
									<input class="wpbooklist-search-searchin-checkbox wpbooklist-search-searchin-checkbox-2" data-dblibname="wp_wpbooklist_jre_saved_book_log" type="checkbox" name="wp_wpbooklist_jre_saved_book_log">
								</div>
							</div>
							<div class="wpbooklist-search-display-options-indiv-entry">
								<div class="wpbooklist-search-display-options-label-div">
									<label>' . $this->trans->trans_643 . '</label>
								</div>
								<div class="wpbooklist-search-checkbox-actual-wrapper">
									<input class="wpbooklist-search-searchin-checkbox wpbooklist-search-searchin-checkbox-3" data-dblibname="wp_wpbooklist_jre_saved_book_log" type="checkbox" name="wp_wpbooklist_jre_saved_book_log">
								</div>
							</div>

						</div>
					</div>';

			// Getting all user-created libraries.
			$db_row = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'wpbooklist_jre_list_dynamic_db_names' );
			foreach ( $db_row as $key => $table ) {
				$db_string = $db_string . '
					<div class="wpbooklist-edit-book-indiv-div-class wpbooklist-search-indiv-class" id="wpbooklist-searchin-indiv-div-id-' . $key . '">
						<div class="wpbooklist-search-libname-wrapper">
							<div class="wpbooklist-search-libname-div">
								<img class="wpbooklist-search-searchin-img" src="' . SEARCH_ROOT_IMG_URL .  'library.svg">
								<p class="wpbooklist-search-searchin-title">' . ucfirst( $table->user_table_name ) . '</p>
							</div>
						</div>
						<div class="wpbooklist-search-checkboxes-wrapper">
							<div class="wpbooklist-search-display-options-indiv-entry">
								<div class="wpbooklist-search-display-options-label-div">
									<label>' . $this->trans->trans_641 . '</label>
								</div>
								<div class="wpbooklist-search-checkbox-actual-wrapper">
									<input class="wpbooklist-search-searchin-checkbox wpbooklist-search-searchin-checkbox-1" data-dblibname="wp_wpbooklist_jre_' . $table->user_table_name . '" type="checkbox" name="wp_wpbooklist_jre_' . $table->user_table_name . '">
								</div>
							</div>
							<div class="wpbooklist-search-display-options-indiv-entry">
								<div class="wpbooklist-search-display-options-label-div">
									<label>' . $this->trans->trans_642 . '</label>
								</div>
								<div class="wpbooklist-search-checkbox-actual-wrapper">
									<input class="wpbooklist-search-searchin-checkbox wpbooklist-search-searchin-checkbox-2" data-dblibname="wp_wpbooklist_jre_' . $table->user_table_name . '" type="checkbox" name="wp_wpbooklist_jre_' . $table->user_table_name . '">
								</div>
							</div>
							<div class="wpbooklist-search-display-options-indiv-entry">
								<div class="wpbooklist-search-display-options-label-div">
									<label>' . $this->trans->trans_643 . '</label>
								</div>
								<div class="wpbooklist-search-checkbox-actual-wrapper">
									<input class="wpbooklist-search-searchin-checkbox wpbooklist-search-searchin-checkbox-3" data-dblibname="wp_wpbooklist_jre_' . $table->user_table_name . '" type="checkbox" name="wp_wpbooklist_jre_' . $table->user_table_name . '">
								</div>
							</div>

						</div>
					</div>';
			}

			$searchin_string = $searchin_string . $db_string . '</div>';

			// Now we'll start building the Search By Fields...
			$this->core_user_options = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'wpbooklist_jre_user_options' );

			// Our hard-coded values based on the core WPBookList fields for each book.
			$this->checkboxes_array = array(
				$this->trans->trans_137,
				$this->trans->trans_587,
				$this->trans->trans_588,
				$this->trans->trans_589,
				$this->trans->trans_271,
				$this->trans->trans_144,
				$this->trans->trans_155,
				$this->trans->trans_158,
				$this->trans->trans_152,
				$this->trans->trans_146,
				$this->trans->trans_281,
				$this->trans->trans_135,
				$this->trans->trans_136,
				$this->trans->trans_149,
				$this->trans->trans_154,
				$this->trans->trans_153,
				$this->trans->trans_145,
				$this->trans->trans_139,
				$this->trans->trans_142,
				$this->trans->trans_143,
				$this->trans->trans_141,
				$this->trans->trans_156,
				$this->trans->trans_151,
				$this->trans->trans_147,
			);

			// The corresponding Database Field names, to be used as a data-attribute for each checkbox.
			$this->db_array = array(
				$this->trans->trans_137 => 'asin',
				$this->trans->trans_587 => 'author',
				$this->trans->trans_588 => 'author2',
				$this->trans->trans_589 => 'author3',
				$this->trans->trans_271 => 'title',
				$this->trans->trans_144 => 'callnumber',
				$this->trans->trans_155 => 'edition',
				$this->trans->trans_158 => 'format',
				$this->trans->trans_152 => 'description',
				$this->trans->trans_146 => 'genres',
				$this->trans->trans_281 => 'illustrator',
				$this->trans->trans_135 => 'isbn',
				$this->trans->trans_136 => 'isbn13',
				$this->trans->trans_149 => 'keywords',
				$this->trans->trans_154 => 'language',
				$this->trans->trans_153 => 'notes',
				$this->trans->trans_145 => 'originalpubyear',
				$this->trans->trans_139 => 'originaltitle',
				$this->trans->trans_142 => 'pages',
				$this->trans->trans_143 => 'pub_year',
				$this->trans->trans_141 => 'publisher',
				$this->trans->trans_156 => 'series',
				$this->trans->trans_151 => 'shortdescription',
				$this->trans->trans_147 => 'subgenre',
			);

			// Loop through the Custom Fields.
			if ( false !== stripos( $this->core_user_options->customfields, '--' ) ) {
				$fields = explode( '--', $this->core_user_options->customfields );

				// Loop through each custom field entry.
				foreach ( $fields as $key => $entry ) {

					if ( false !== stripos( $entry, ';' ) ) {
						$entry_details = explode( ';', $entry );

						// All kinds of checks to make sure good value exists.
						if ( array_key_exists( 0, $entry_details ) && isset( $entry_details[0] ) && '' !== $entry_details[0] && null !== $entry_details[0] ) {

							array_push( $this->checkboxes_array, $entry_details[0] );
							$this->db_array[ $entry_details[0] ] = $entry_details[0];

						}
					}
				}
			}

			$string2 = '
				<p style="margin-top:40px; text-align:center; max-width:600px; font-weight:bold; font-size:15px; margin-left:auto; margin-right:auto;">' . $this->trans->trans_644 . '</p>';

			// Sort/Alphabetize arrays.
			sort( $this->checkboxes_array );
			ksort( $this->db_array );

			$fields_string = '';
			foreach ( $this->checkboxes_array as $key => $indiv_entry ) {

				// Modify text for use in name attribute.
				$unmodded = $indiv_entry;
				$forhtml  = $indiv_entry;
				$forhtml  = strtolower( $forhtml );
				$forhtml  = str_replace( ' ', '', $forhtml );
				$forhtml  = str_replace( '#', '', $forhtml );
				$forhtml  = str_replace( '-', '', $forhtml );
				$forhtml  = str_replace( '_', '', $forhtml );
				$forhtml  = str_replace( '(', '', $forhtml );
				$forhtml  = str_replace( ')', '', $forhtml );
				$forhtml  = str_replace( ':', '', $forhtml );

				// Modify text for use as a human-readable label.
				$indiv_entry = str_replace( '_', ' ', $indiv_entry );

				$fields_string = $fields_string . '
					<div class="wpbooklist-edit-book-indiv-div-class wpbooklist-search-indiv-class" id="wpbooklist-searchin-indiv-div-id-' . $key . '">
						<div class="wpbooklist-search-libname-wrapper">
							<div class="wpbooklist-search-libname-div">
								<img class="wpbooklist-search-searchin-img" src="' . SEARCH_ROOT_IMG_URL .  'optimization.svg">
								<p class="wpbooklist-search-searchin-title">' . ucfirst( $indiv_entry ) . '</p>
							</div>
						</div>
						<div class="wpbooklist-search-checkboxes-wrapper">
							<div class="wpbooklist-search-display-options-indiv-entry">
								<div class="wpbooklist-search-display-options-label-div">
									<label>' . $this->trans->trans_641 . '</label>
								</div>
								<div class="wpbooklist-search-checkbox-actual-wrapper">
									<input class="wpbooklist-search-searchby-checkbox wpbooklist-search-searchby-checkbox-1" data-dbfieldname="' . $this->db_array[ $unmodded ] . '" type="checkbox">
								</div>
							</div>
							<div class="wpbooklist-search-display-options-indiv-entry">
								<div class="wpbooklist-search-display-options-label-div">
									<label>' . $this->trans->trans_642 . '</label>
								</div>
								<div class="wpbooklist-search-checkbox-actual-wrapper">
									<input class="wpbooklist-search-searchby-checkbox wpbooklist-search-searchby-checkbox-2" data-dbfieldname="' . $this->db_array[ $unmodded ] . '" type="checkbox">
								</div>
							</div>
							<div class="wpbooklist-search-display-options-indiv-entry">
								<div class="wpbooklist-search-display-options-label-div">
									<label>' . $this->trans->trans_643 . '</label>
								</div>
								<div class="wpbooklist-search-checkbox-actual-wrapper">
									<input class="wpbooklist-search-searchby-checkbox wpbooklist-search-searchby-checkbox-3" data-dbfieldname="' . $this->db_array[ $unmodded ] . '" type="checkbox">
								</div>
							</div>

						</div>
					</div>';

			}

			$save_string = '<div id="wpbooklist-search-save-wrapper">
								<button>' . $this->trans->trans_645 . '</button>
								<div class="wpbooklist-spinner" id="wpbooklist-spinner-1"></div>
								<div id="wpbooklist-search-save-response-div"></div>
							</div>';


			$string1 = $string1 . $searchin_string . $string2 . $fields_string . $save_string;
			return $string1;
		}
	}

endif;