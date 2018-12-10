<?php
/**
 * WPBookList Search Tab
 *
 * @author   Jake Evans
 * @category Extension Ui
 * @package  Includes/UI
 * @version  6.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPBookList_Frontend_Search_UI', false ) ) :
	/**
	 * WPBookList_Admin_Menu Class.
	 */
	class WPBookList_Frontend_Search_UI {

		public $search_extension_settings;
		public $final_search_html;
		public $checkboxes_array;
		public $db_array;
		public $search_in_boxes;
		public $search_by_boxes;

		public $searchby_flag = false;
		public $searchby_term;
		public $filterby_flag = false;
		public $filterby_term;
		public $searchterm_flag = false;
		public $searchterm_term;
		public $offset_flag = false;
		public $offset_term;
		public $querytable_flag = false;
		public $querytable_term;

		public $url_param_string;
		public $final_query;
		public $actual_search_results;




		public $filter_search_results_html;
		public $search_results_actual_html;
		public $pagination_actual_html;

		public $final_html;

		/**
		 * Class Constructor
		 */
		public function __construct() {
			require_once CLASS_DIR . 'class-admin-ui-template.php';
			require_once SEARCH_CLASS_DIR . 'class-search-form.php';

			// Get Translations.
			require_once CLASS_TRANSLATIONS_DIR . 'class-wpbooklist-translations.php';
			$this->trans = new WPBookList_Translations();
			$this->trans->trans_strings();

			// Gets the Gets the Core WPBookList Settings from db.
			$this->get_core_settings();

			// Gets the Search Extension Settings from db.
			$this->get_search_extension_settings();

			// Builds the actual checkboxes of Libraries that are to be displayed for the user to 'Search In...'.
			$this->build_search_in_checkboxes();

			// Builds the actual checkboxes that are to be displayed for the user to 'Search By...'.
			$this->build_search_by_checkboxes();

			// Builds the final 'Search By...' HTML, complete with checkboxes and the Search Text Input and Button.
			$this->build_final_search_html();

			// Builds the final search query to be used and runs it.
			$this->build_and_run_search_query();

			// Builds the 'Filter Search Results' HTML.
			$this->build_filter_search_results_html();

			// Builds the actual search results HTML, complete with the retreived books.
			$this->build_search_results_actual_html();

			// Builds the actual pagination HTML.
			$this->build_pagination_actual_html();

			// Outputs the final HTML the User will see.
			$this->output_final_html();
		}


		/**
		 * Gets the Core WPBookList Settings from db.
		 */
		private function get_core_settings() {

			global $wpdb;
			$this->core_user_options = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'wpbooklist_jre_user_options' );

		}

		/**
		 * Gets the Search Extension Settings from db.
		 */
		private function get_search_extension_settings() {

			global $wpdb;
			$this->search_extension_settings = '';

		}

		/**
		 * Builds the actual checkboxes of Libraries that are to be displayed for the user to 'Search In...'.
		 */
		private function build_search_in_checkboxes() {

			global $wpdb;

			// Getting all user-created libraries.
			$db_row = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'wpbooklist_jre_list_dynamic_db_names' );

			$this->search_in_boxes = '
				<div class="wpbooklist-display-options-indiv-entry-wrapper">
					<div id="wpbooklist-display-options-indiv-entry-title">
						' . $this->trans->trans_590 . '...
					</div>
					<div class="wpbooklist-display-options-indiv-entry wpbooklist-display-options-indiv-entry-checkall">
						<div class="wpbooklist-display-options-label-div">
							<label>' . $this->trans->trans_257 . '</label>
						</div>
						<div class="wpbooklist-margin-right-td">
							<input type="checkbox" name="hide-library-display-form-checkall"></input>
						</div>
					</div>
					<div class="wpbooklist-display-options-indiv-entry">
						<div class="wpbooklist-display-options-label-div">
							<label>' . $this->trans->trans_61 . '</label>
						</div>
						<div class="wpbooklist-margin-right-td">
							<input data-dblibname="' . $wpdb->prefix . 'wpbooklist_jre_saved_book_log" type="checkbox" name="' . $wpdb->prefix . 'wpbooklist_jre_saved_book_log"></input>
						</div>
					</div>';

			foreach ( $db_row as $key => $value ) {

				$this->search_in_boxes = $this->search_in_boxes .
					'<div class="wpbooklist-display-options-indiv-entry">
						<div class="wpbooklist-display-options-label-div">
							<label>' . ucfirst( $value->user_table_name ) . '</label>
						</div>
						<div class="wpbooklist-margin-right-td">
							<input data-dblibname="' . $wpdb->prefix . 'wpbooklist_jre_' . $value->user_table_name . '" type="checkbox" name="' . $wpdb->prefix . 'wpbooklist_jre_' . $value->user_table_name . '"></input>
						</div>
					</div>';

			}

		}

		/**
		 * Builds the actual checkboxes that are to be displayed for the user to 'Search By...'.
		 */
		private function build_search_by_checkboxes() {

			global $wpdb;

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
				$this->trans->trans_281 => 'isbn',
				$this->trans->trans_135 => 'isbn13',
				$this->trans->trans_136 => 'illustrator',
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

			$this->search_by_boxes = '
				<div class="wpbooklist-display-options-indiv-entry-wrapper">
					<div id="wpbooklist-display-options-indiv-entry-title">
						' . $this->trans->trans_12 . '...
					</div>
					<div class="wpbooklist-display-options-indiv-entry wpbooklist-display-options-indiv-entry-checkall">
						<div class="wpbooklist-display-options-label-div">
							<label>' . $this->trans->trans_257 . '</label>
						</div>
						<div class="wpbooklist-margin-right-td">
							<input type="checkbox" name="hide-library-display-form-checkall"></input>
						</div>
					</div>';

			// Sort/Alphabetize arrays.
			sort( $this->checkboxes_array );
			ksort( $this->db_array );

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

				$this->search_by_boxes = $this->search_by_boxes .
					'<div class="wpbooklist-display-options-indiv-entry">
						<div class="wpbooklist-display-options-label-div">
							<label>' . ucfirst( $indiv_entry ) . '</label>
						</div>
						<div class="wpbooklist-margin-right-td">
							<input data-dbfieldname="' . $this->db_array[ $unmodded ] . '" type="checkbox" name="hide-library-display-form-' . $forhtml . '"></input>
						</div>
					</div>';

			}
		}

		/**
		 * Builds the final 'Search By...' HTML, complete with checkboxes and the Search Text Input and Button.
		 */
		private function build_final_search_html() {

			$this->final_search_html = '
				<div id="wpbooklist-search-wrapper">
					<div id="wpbooklist-search-searchterm-input-wrapper">
						<form>
							<input id="wpbooklist-search-searchterm-input" name="wpbooklist-search-searchterm-input" type="text" />
							<button id="wpbooklist-search-searchterm-button">' . $this->trans->trans_1 . '</button>
						</form>
					</div>
					' . $this->search_in_boxes . '</div>
					' . $this->search_by_boxes . '
				</div>';

		}

		/**
		 * Builds the final search query to be used and runs it.
		 */
		private function build_and_run_search_query() {

			global $wpdb;

			// Getting all URL parameters.
			$this->url_param_string = urldecode( http_build_query( array_merge( $_GET ) ) );

			if ( '' !== $this->url_param_string ) {

				// Let's set some flags indicating what params showed up in the URL.
				if ( false !== stripos( $this->url_param_string, 'searchby' ) ) {
					$this->searchby_flag = true;

					if ( isset( $_GET['searchby'] ) ) {
						$this->searchby_term = filter_var( wp_unslash( $_GET['searchby'] ), FILTER_SANITIZE_STRING );
					}
				}
				if ( false !== stripos( $this->url_param_string, 'filterby' ) ) {
					$this->filterby_flag = true;
					if ( isset( $_GET['filterby'] ) ) {
						$this->filterby_term = filter_var( wp_unslash( $_GET['filterby'] ), FILTER_SANITIZE_STRING );
					}
				}
				if ( false !== stripos( $this->url_param_string, 'searchterm' ) ) {
					$this->searchterm_flag = true;
					if ( isset( $_GET['searchterm'] ) ) {
						$this->searchterm_term = filter_var( wp_unslash( $_GET['searchterm'] ), FILTER_SANITIZE_STRING );
					}
				}
				if ( false !== stripos( $this->url_param_string, 'offset' ) ) {
					$this->offset_flag = true;
					if ( isset( $_GET['offset'] ) ) {
						$this->offset_term = filter_var( wp_unslash( $_GET['offset'] ), FILTER_SANITIZE_STRING );
					}
				}
				if ( false !== stripos( $this->url_param_string, 'querytable' ) ) {
					$this->querytable_flag = true;
					if ( isset( $_GET['querytable'] ) ) {
						$this->querytable_term = filter_var( wp_unslash( $_GET['querytable'] ), FILTER_SANITIZE_STRING );
					}
				}

				echo $this->url_param_string;
			}



			$this->final_query = '';
			$this->actual_search_results = '';

		}




		/**
		 * Builds the 'Filter Search Results' HTML.
		 */
		private function build_filter_search_results_html() {
			$this->filter_search_results_html = '';
		}

		/**
		 * Builds the actual search results HTML, complete with the retreived books.
		 */
		private function build_search_results_actual_html() {
			$this->search_results_actual_html = '';
		}

		/**
		 * Builds the actual pagination HTML.
		 */
		private function build_pagination_actual_html() {
			$this->pagination_actual_html = '';
		}

		/**
		 * Outputs the final HTML the User will see.
		 */
		private function output_final_html() {

			$this->final_html = $this->final_search_html . $this->filter_search_results_html . $this->search_results_actual_html . $this->pagination_actual_html;
			echo $this->final_html;
		}

	}
endif;

