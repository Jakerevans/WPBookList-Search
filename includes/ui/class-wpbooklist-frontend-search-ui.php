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

		public $dynamic_libs_array = array();
		public $dynamic_libs_for_display_array = array();
		public $search_extension_settings;
		public $final_search_html;
		public $checkboxes_array;
		public $db_array;
		public $search_in_boxes;
		public $search_by_boxes;

		public $searchby_flag = false;
		public $searchby_term;
		public $searchterm_flag = false;
		public $searchterm_term;
		public $offset_flag = false;
		public $offset_term;
		public $querytable_flag = false;
		public $querytable_term;
		public $filter_flag = false;
		public $filter_terms;
		public $filter_values;

		public $url_param_string;
		public $final_query;
		public $actual_search_results = array();
		public $total_search_results;




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

			// Builds array of all custom libraries.
			$this->get_all_libraries();

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
		 * Builds array of all custom libraries.
		 */
		private function get_all_libraries() {

			global $wpdb;
			// Getting all user-created libraries.
			$db_row = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'wpbooklist_jre_list_dynamic_db_names' );

			foreach ( $db_row as $key => $value ) {
				array_push( $this->dynamic_libs_for_display_array, $value->user_table_name );
				array_push( $this->dynamic_libs_array, $wpdb->prefix . 'wpbooklist_jre_' . $value->user_table_name );
			}

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
			$this->search_extension_settings = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'wpbooklist_search_options' );

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
							<input id="wpbooklist-search-searchin-checkbox-checkall" type="checkbox" name="hide-library-display-form-checkall"></input>
						</div>
					</div>
					<div class="wpbooklist-display-options-indiv-entry">
						<div class="wpbooklist-display-options-label-div">
							<label>' . $this->trans->trans_61 . '</label>
						</div>
						<div class="wpbooklist-margin-right-td">
							<input class="wpbooklist-search-searchin-checkbox" data-dblibname="' . $wpdb->prefix . 'wpbooklist_jre_saved_book_log" type="checkbox" name="' . $wpdb->prefix . 'wpbooklist_jre_saved_book_log"></input>
						</div>
					</div>';

			foreach ( $this->dynamic_libs_array as $key => $value ) {

				$this->search_in_boxes = $this->search_in_boxes .
					'<div class="wpbooklist-display-options-indiv-entry">
						<div class="wpbooklist-display-options-label-div">
							<label>' . ucfirst( $this->dynamic_libs_for_display_array[ $key ] ) . '</label>
						</div>
						<div class="wpbooklist-margin-right-td">
							<input class="wpbooklist-search-searchin-checkbox" data-dblibname="' . $wpdb->prefix . 'wpbooklist_jre_' . $this->dynamic_libs_for_display_array[ $key ] . '" type="checkbox" name="' . $value . '"></input>
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
							<input id="wpbooklist-search-searchby-checkbox-checkall" type="checkbox" name="hide-library-display-form-checkall"></input>
						</div>
					</div>';

			// Sort/Alphabetize arrays.
			sort( $this->checkboxes_array );
			ksort( $this->db_array );

			error_log(print_r($this->checkboxes_array,true));
			error_log(print_r($this->db_array,true));

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
							<input class="wpbooklist-search-searchby-checkbox" data-dbfieldname="' . $this->db_array[ $unmodded ] . '" type="checkbox" name="hide-library-display-form-' . $this->db_array[ $unmodded ] . '"></input>
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
						<form id="wpbooklist-search-searchterm-form">
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
				if ( false !== stripos( $this->url_param_string, 'filterterms' ) ) {
					$this->filter_flag = true;
					if ( isset( $_GET['filterterms'] ) ) {
						$this->filter_terms = filter_var( wp_unslash( $_GET['filterterms'] ), FILTER_SANITIZE_STRING );
					}
				}
				if ( false !== stripos( $this->url_param_string, 'filtervalues' ) ) {
					$this->filter_flag = true;
					if ( isset( $_GET['filtervalues'] ) ) {
						$this->filter_values = filter_var( wp_unslash( $_GET['filtervalues'] ), FILTER_SANITIZE_STRING );
					}
				}
			}

			// Building an array that will house all of the Libraries we need to search in, whether that's just one Library of multiple.
			$lib_array = array();
			if ( $this->querytable_flag ) {

				if ( false !== stripos( $this->querytable_term, ',' ) || false !== stripos( $this->querytable_term, 'alllibraries' ) ) {

					if ( false !== stripos( $this->querytable_term, ',' ) ) {

						$temp = explode( ',', $this->querytable_term );

						foreach ( $temp as $key => $indiv_lib ) {
							array_push( $lib_array, $indiv_lib );
						}
					} else {

						foreach ( $this->dynamic_libs_array as $key => $value ) {
							array_push( $lib_array, $value );
						}

						array_push( $lib_array, $wpdb->prefix . 'wpbooklist_jre_saved_book_log' );

					}
				} else {
					array_push( $lib_array, $this->querytable_term );
				}
			}

			// Now build the Checkboxes portion of the initial search.
			$searchby_array = array();
			if ( $this->searchby_flag ) {

				// If there are multiple search checkboxes checked...
				if ( false !== stripos( $this->searchby_term, ',' ) ) {

					$temp = explode( ',', $this->searchby_term );

					foreach ( $temp as $key => $value ) {
						array_push( $searchby_array, $value );
					}
				} else {
					if ( 'searchbyall' === $this->searchby_term ) {
						foreach ( $this->checkboxes_array as $key => $indiv_entry ) {
							array_push( $searchby_array, $this->db_array[ $indiv_entry ] );
						}
					} else {
						array_push( $searchby_array, $this->searchby_term );
					}
				}
			}

			// Now loop through our array of Libraries to search in, build a query, run it, and add results to a final array.
			foreach ( $lib_array as $key => $library ) {

				// If there's only one 'Search By...' term selected, meaning we don't need to try and append any AND clauses...
				if ( 1 === count( $searchby_array ) ) {

					// Query for getting the actual search results, accounting for pagination, serach term, etc.
					$this->final_query = 'SELECT * FROM ' . $library . ' WHERE (' . $searchby_array[0] . ' LIKE "%' . $this->searchterm_term . '%")';

					// The Final Total Search Results Query.
					$this->final_count_query = 'SELECT COUNT(*) FROM ' . $library . ' WHERE (' . $searchby_array[0] . ' LIKE "%' . $this->searchterm_term . '%")';

				} elseif ( 1 < count( $searchby_array ) ) {

					// Query for getting the actual search results, accounting for pagination, serach term, etc.
					$this->final_query = 'SELECT * FROM ' . $library . ' WHERE (' . $searchby_array[0] . ' LIKE "%' . $this->searchterm_term . '%"';

					// The Final Total Search Results Query.
					$this->final_count_query = 'SELECT COUNT(*) FROM ' . $library . ' WHERE (' . $searchby_array[0] . ' LIKE "%' . $this->searchterm_term . '%"';

					foreach ( $searchby_array as $key => $searchby_value ) {

						// Exclude the first 'Search By...' value, as we've manually included it above this foreach, to give it the initial 'WHERE' clause.
						if ( 0 < $key ) {

							// Query for getting the actual search results, accounting for pagination, serach term, etc.
							$this->final_query = $this->final_query . ' OR ' . $searchby_value . ' LIKE "%' . $this->searchterm_term . '%"';

							// The Final Total Search Results Query.
							$this->final_count_query = $this->final_count_query . ' OR ' . $searchby_value . ' LIKE "%' . $this->searchterm_term . '%"';
						}
					}

					// Query for getting the actual search results, accounting for pagination, serach term, etc.
					$this->final_query = $this->final_query . ')';

					// The Final Total Search Results Query.
					$this->final_count_query = $this->final_count_query . ')';
				}

				// Now we need to take Filtering into account.
				if ( $this->filter_flag ) {

					// Here we'll modify the actual filter values to match what is saved in the db, based on which filter term is set.
					$filter_term_array = array();
					$filter_values_array = array();

					// First build two arrays - one for filter terms, on for filter values. The array keys from one array to the other should match, i.e., $filter_term_array[0] is the db column name, and $this->filter_values[0] is it's corresponding value in the db to search for.
					if ( false !== stripos( $this->filter_terms, ',' ) ) {
						$filter_term_array = explode( ',', $this->filter_terms );
					} else {
						array_push( $filter_term_array, $this->filter_terms );
					}
					if ( false !== stripos( $this->filter_values, ',' ) ) {
						$filter_values_array = explode( ',', $this->filter_values );
					} else {
						array_push( $filter_values_array, $this->filter_values );
					}

					foreach ( $filter_term_array as $key => $dbcolumn ) {

						if ( '1' === $filter_values_array[ $key ] ) {

							// This switch will modify the filter value to match what should be saved in the db.
							switch ( $dbcolumn ) {
								case 'signed':
									$filter_values_array[ $key ] = $this->trans->trans_131;
									break;

								default:

									break;
							}
						}
					}

					foreach ( $filter_term_array as $key => $indiv_filterterm ) {

						$this->final_query = $this->final_query . ' AND (' . $indiv_filterterm . ' = "' . $filter_values_array[ $key ] . '")';

						$this->final_count_query = $this->final_count_query . ' AND (' . $indiv_filterterm . ' = "' . $filter_values_array[ $key ] . '")';
					}
				}

				// Now add the total number of search results (before LIMIT & OFFSET) to get a final Total Results across all libraries to build the pagination.
				$this->total_search_results = $this->total_search_results + $wpdb->get_var( $this->final_count_query );

				$search_results = $wpdb->get_results( $this->final_query );

				// Now we're adding in the search results to the final search results array as long as we're under the 'Per Page' limit.
				foreach ( $search_results as $key => $value ) {

					// Add the Library in for outputting the results.
					$value->table = $library;

					array_push( $this->actual_search_results, $value );
				}
			}

			if ( 0 < (int) $this->offset_term ) {
				foreach ( $this->actual_search_results as $key => $result ) {
					if ( ( $key < (int) $this->offset_term ) || ( $key > ( (int) $this->offset_term + $this->search_extension_settings->perpage - 1 ) ) ) {
						unset( $this->actual_search_results[ $key ] );
					}
				}
			} else {
				foreach ( $this->actual_search_results as $key => $result ) {
					if ( $key > ( $this->search_extension_settings->perpage - 1 ) ) {
						unset( $this->actual_search_results[ $key ] );
					}
				}
			}
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

			// If a search has been performed.
			if ( $this->searchterm_flag ) {

				// If search results were found...
				if ( 0 < $this->total_search_results ) {

					$this->search_results_actual_html = '
						<div id="wpbooklist-search-results-top-wrapper">
							<div id="wpbooklist-display-options-indiv-entry-title">
							' . $this->trans->trans_591 . '...
							</div>';

					foreach ( $this->actual_search_results as $key => $value ) {

						// Build the Author for this title.
						$author_for_display = '';
						if ( '' !== $value->author && null !== $value->author ) {
							$author_for_display = $value->author;
						}

						$this->search_results_actual_html = $this->search_results_actual_html .
							'<div class="wpbooklist-search-results-listing">
								<div class="wpbooklist-search-results-listing-img-wrapper">
									<img class="wpbooklist_cover_image_class wpbooklist-show-book-colorbox wpbooklist-search-book-image" data-bookid="' . $value->ID . '" data-booktable="' . $value->table . '" id="wpbooklist_cover_image" src="' . $value->image . '" style="opacity: 1;">
								</div>
								<div class="wpbooklist-search-results-listing-details-wrapper">
									<div class="wpbooklist-search-results-listing-title-wrapper">
										<p class="wpbooklist_saved_title_link wpbooklist-show-book-colorbox" data-bookid="' . $value->ID . '" data-booktable="' . $value->table . '" id="wpbooklist_saved_title_link">' . $value->title . '<span class="hidden_id_title">' . $value->ID . '</span>
					    				</p>
									</div>
									<div class="wpbooklist-search-results-listing-author-wrapper">
										<p>' . $this->trans->trans_592 . ' ' . $author_for_display . '</p>
									</div>
									<div class="wpbooklist-search-results-listing-pubdate-wrapper">
										<p>' . $this->trans->trans_597 . ' ' . $value->pub_year . '</p>
									</div>


								</div>

							</div>';
					}

					$this->search_results_actual_html = $this->search_results_actual_html . '</div>';

				} else {
					// Display a 'no results found' message.
					$this->search_results_actual_html = '
						<div id="wpbooklist-search-results-top-wrapper">
							<div id="wpbooklist-display-options-indiv-entry-title">
							' . $this->trans->trans_591 . '...
							</div>
						</div>';
				}
			} else {
				// No search has been performed on this page yet - don't output anything.
				$this->search_results_actual_html = '';
			}
		}

		/**
		 * Builds the actual pagination HTML.
		 */
		private function build_pagination_actual_html() {

			//echo $this->total_search_results;
			//echo $this->search_extension_settings->perpage;

			$pagination_options_string = '';

			// Setting up variables to determine the previous offset to go back to, or to disable that ability if on Page 1.
			if ( '0' !== $this->offset_term && null !== $this->offset_term ) {
				$prevnum          = $this->offset_term - $this->search_extension_settings->perpage;
				$styledisableleft = '';
			} else {
				$prevnum          = 0;
				$styledisableleft = 'style="pointer-events:none;opacity:0.5;"';
			}

			// Setting up variables to determine the next offset to go to, or to disable that ability if on last Page.
			if ( $this->offset_term < ( $this->total_search_results - $this->search_extension_settings->perpage ) ) {
				$nextnum           = $this->offset_term + $this->search_extension_settings->perpage;
				$styledisableright = '';
			} else {
				$nextnum           = $this->offset_term;
				$styledisableright = 'style="pointer-events:none;opacity:0.5;"';
			}

			// Getting total number of full pages and/or if there's only a partial/remainder page.
			if ( $this->total_search_results > 0 && $this->search_extension_settings->perpage > 0 ) {

				// Getting whole pages. Can be zero if total number of books is less that amount set to be displayed per page in the backend settings.
				$whole_pages = floor( $this->total_search_results / $this->search_extension_settings->perpage );

				// Determing whether there is a partial page, whose contents contains less books than amount set to be displayed per page in the backend settings. Will only be 0 if total number of books is evenly divisible by $this->search_extension_settings->perpage.
				$remainder_pages = $this->total_search_results % $this->search_extension_settings->perpage;
				if ( 0 !== $remainder_pages ) {
					$remainder_pages = 1;
				}

				// If there's only one page, don't show pagination.
				if ( ( 1 === $whole_pages && 0 === $remainder_pages ) || ( 0 === $whole_pages && 1 === $remainder_pages ) ) {
					return;
				}

				// The loop that will create the <option> html for the <select> for the whole pages.
				for ( $i = 1; $i <= $whole_pages + $remainder_pages; $i++ ) {

					$pagination_options_string = $pagination_options_string . '<option value="' . ( ( $i - 1 ) * $this->search_extension_settings->perpage ) . '">' . $this->trans->trans_600 . ' ' . $i . '</option>';

				}
			}

			// Actual Pagination HTML.
			if ( '' !== $pagination_options_string ) {
				$string1 = '
				<div class="wpbooklist-pagination-div">
					<div class="wpbooklist-pagination-div-inner">
						<div class="wpbooklist-pagination-left-div" ' . $styledisableleft . ' data-offset="' . $prevnum . '">
							<p><img class="wpbooklist-pagination-prev-img" src="' . ROOT_IMG_URL . 'next-left.png" />' . $this->trans->trans_36 . '</p>
						</div>
						<div class="wpbooklist-pagination-middle-div">
							<select class="wpbooklist-pagination-middle-div-select" id="wpbooklist-search-pagination-middle-div-select">
								' . $pagination_options_string . '
							</select>
						</div>
						<div class="wpbooklist-pagination-right-div" ' . $styledisableright . ' data-offset="' . $nextnum . '" >
							<p>' . $this->trans->trans_37 . '<img class="wpbooklist-pagination-prev-img" src="' . ROOT_IMG_URL . 'next-right.png" /></p>
						</div>
					</div>
				</div>';
			} else {
				$string1 = '';
			}

			$this->pagination_actual_html = $string1;
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

