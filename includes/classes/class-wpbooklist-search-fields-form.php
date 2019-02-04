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

			// Get the saved search options to populate the checkboxes.
			$hidesearchin = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'wpbooklist_search_settings' );

			// Determining if the default library needs to be checked in any way.
			$saved_checked_1 = '';
			$saved_checked_2 = '';
			$saved_checked_3 = '';
			if ( false !== stripos( $hidesearchin->hidesearchin, 'defaultwpbllibrary-' ) ) {

				$temp = explode( 'defaultwpbllibrary-' , $hidesearchin->hidesearchin );

				if ( '2' === mb_substr( $temp[1], 0, 1, 'utf-8' ) ) {
					$saved_checked_1 = 'checked';
				}

				if ( '1' === mb_substr( $temp[1], 0, 1, 'utf-8' ) ) {
					$saved_checked_2 = 'checked';
				}

				if ( '0' === mb_substr( $temp[1], 0, 1, 'utf-8' ) ) {
					$saved_checked_3 = 'checked';
				}
			}

			$string1 = '
				<p class="wpbooklist-tab-intro-para">' . $this->trans->trans_639 . '</p>
				<div id="wpbooklist-search-checkall-wrapper">
					<button id="wpbooklist-search-checkall-default">' . $this->trans->trans_647 . '</button>
					<button id="wpbooklist-search-checkall-defaulthide">' . $this->trans->trans_648 . '</button>
					<button id="wpbooklist-search-checkall-remove">' . $this->trans->trans_649 . '</button>
					<button id="wpbooklist-search-checkall-uncheckall">' . $this->trans->trans_258 . '</button>
				</div>
				<p style="margin-top:40px; text-align:center; max-width:600px; font-weight:bold; font-size:15px; margin-left:auto; margin-right:auto;">' . $this->trans->trans_640 . '</p>';

			$searchin_string = '<div>';
			$db_string       = '<div class="wpbooklist-edit-book-indiv-div-class wpbooklist-searchin-indiv-class">
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
									<input ' . $saved_checked_1 . ' class="wpbooklist-search-searchin-checkbox wpbooklist-search-searchin-checkbox-1" data-dblibname="defaultwpbllibrary" type="checkbox" name="wp_wpbooklist_jre_saved_book_log">
								</div>
							</div>
							<div class="wpbooklist-search-display-options-indiv-entry">
								<div class="wpbooklist-search-display-options-label-div">
									<label>' . $this->trans->trans_642 . '</label>
								</div>
								<div class="wpbooklist-search-checkbox-actual-wrapper">
									<input ' . $saved_checked_2 . ' class="wpbooklist-search-searchin-checkbox wpbooklist-search-searchin-checkbox-2" data-dblibname="defaultwpbllibrary" type="checkbox" name="wp_wpbooklist_jre_saved_book_log">
								</div>
							</div>
							<div class="wpbooklist-search-display-options-indiv-entry">
								<div class="wpbooklist-search-display-options-label-div">
									<label>' . $this->trans->trans_643 . '</label>
								</div>
								<div class="wpbooklist-search-checkbox-actual-wrapper">
									<input ' . $saved_checked_3 . ' class="wpbooklist-search-searchin-checkbox wpbooklist-search-searchin-checkbox-3" data-dblibname="defaultwpbllibrary" type="checkbox" name="wp_wpbooklist_jre_saved_book_log">
								</div>
							</div>

						</div>
					</div>';

			// Getting all user-created libraries.
			$db_row = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'wpbooklist_jre_list_dynamic_db_names' );
			foreach ( $db_row as $key => $table ) {

				// Seeing if the checkbox should be checked based on saved settings...
				$saved_checked_1 = '';
				$saved_checked_2 = '';
				$saved_checked_3 = '';
				if ( false !== stripos( $hidesearchin->hidesearchin, $table->user_table_name . '-' )  ) {

					$temp = explode( $table->user_table_name . '-', $hidesearchin->hidesearchin );

					if ( '2' === mb_substr( $temp[1], 0, 1, 'utf-8' ) ) {
						$saved_checked_1 = 'checked';
					}

					if ( '1' === mb_substr( $temp[1], 0, 1, 'utf-8' ) ) {
						$saved_checked_2 = 'checked';
					}

					if ( '0' === mb_substr( $temp[1], 0, 1, 'utf-8' ) ) {
						$saved_checked_3 = 'checked';
					}
				}

				$db_string = $db_string . '
					<div class="wpbooklist-edit-book-indiv-div-class wpbooklist-searchin-indiv-class">
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
									<input ' . $saved_checked_1 . ' class="wpbooklist-search-searchin-checkbox wpbooklist-search-searchin-checkbox-1" data-dblibname="' . $table->user_table_name . '" type="checkbox">
								</div>
							</div>
							<div class="wpbooklist-search-display-options-indiv-entry">
								<div class="wpbooklist-search-display-options-label-div">
									<label>' . $this->trans->trans_642 . '</label>
								</div>
								<div class="wpbooklist-search-checkbox-actual-wrapper">
									<input ' . $saved_checked_2 . ' class="wpbooklist-search-searchin-checkbox wpbooklist-search-searchin-checkbox-2" data-dblibname="' . $table->user_table_name . '" type="checkbox">
								</div>
							</div>
							<div class="wpbooklist-search-display-options-indiv-entry">
								<div class="wpbooklist-search-display-options-label-div">
									<label>' . $this->trans->trans_643 . '</label>
								</div>
								<div class="wpbooklist-search-checkbox-actual-wrapper">
									<input ' . $saved_checked_3 . ' class="wpbooklist-search-searchin-checkbox wpbooklist-search-searchin-checkbox-3" data-dblibname="' . $table->user_table_name . '" type="checkbox">
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

			// Get the saved search options to populate the checkboxes.
			$hidesearchby = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'wpbooklist_search_settings' );

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

				// Seeing if the checkbox should be checked based on saved settings...
				$saved_checked_1 = '';
				$saved_checked_2 = '';
				$saved_checked_3 = '';
				if ( false !== stripos( $hidesearchby->hidesearchby, $this->db_array[ $unmodded ] . '-' )  ) {

					$temp = explode( $this->db_array[ $unmodded ] . '-', $hidesearchby->hidesearchby );

					if ( '2' === mb_substr( $temp[1], 0, 1, 'utf-8' ) ) {
						$saved_checked_1 = 'checked';
					}

					if ( '1' === mb_substr( $temp[1], 0, 1, 'utf-8' ) ) {
						$saved_checked_2 = 'checked';
					}

					if ( '0' === mb_substr( $temp[1], 0, 1, 'utf-8' ) ) {
						$saved_checked_3 = 'checked';
					}
				}

				// Modify text for use as a human-readable label.
				$indiv_entry = str_replace( '_', ' ', $indiv_entry );

				$fields_string = $fields_string . '
					<div class="wpbooklist-edit-book-indiv-div-class wpbooklist-searchby-indiv-class">
						<div class="wpbooklist-search-libname-wrapper">
							<div class="wpbooklist-search-libname-div">
								<img class="wpbooklist-search-searchin-img" src="' . SEARCH_ROOT_IMG_URL . 'optimization.svg">
								<p class="wpbooklist-search-searchin-title">' . ucfirst( $indiv_entry ) . '</p>
							</div>
						</div>
						<div class="wpbooklist-search-checkboxes-wrapper">
							<div class="wpbooklist-search-display-options-indiv-entry">
								<div class="wpbooklist-search-display-options-label-div">
									<label>' . $this->trans->trans_641 . '</label>
								</div>
								<div class="wpbooklist-search-checkbox-actual-wrapper">
									<input ' . $saved_checked_1 . ' class="wpbooklist-search-searchby-checkbox wpbooklist-search-searchby-checkbox-1" data-dbfieldname="' . $this->db_array[ $unmodded ] . '" type="checkbox">
								</div>
							</div>
							<div class="wpbooklist-search-display-options-indiv-entry">
								<div class="wpbooklist-search-display-options-label-div">
									<label>' . $this->trans->trans_642 . '</label>
								</div>
								<div class="wpbooklist-search-checkbox-actual-wrapper">
									<input ' . $saved_checked_2 . ' class="wpbooklist-search-searchby-checkbox wpbooklist-search-searchby-checkbox-2" data-dbfieldname="' . $this->db_array[ $unmodded ] . '" type="checkbox">
								</div>
							</div>
							<div class="wpbooklist-search-display-options-indiv-entry">
								<div class="wpbooklist-search-display-options-label-div">
									<label>' . $this->trans->trans_646 . '</label>
								</div>
								<div class="wpbooklist-search-checkbox-actual-wrapper">
									<input ' . $saved_checked_3 . ' class="wpbooklist-search-searchby-checkbox wpbooklist-search-searchby-checkbox-3" data-dbfieldname="' . $this->db_array[ $unmodded ] . '" type="checkbox">
								</div>
							</div>

						</div>
					</div>';

			}

			$save_string = '<div id="wpbooklist-search-save-wrapper">
								<button id="wpbooklist-search-save-button">' . $this->trans->trans_645 . '</button>
								<div class="wpbooklist-spinner" id="wpbooklist-spinner-1"></div>
							</div>';


			$string1 = $string1 . $searchin_string . $string2 . $fields_string . $save_string;
			return $string1;
		}
	}

endif;