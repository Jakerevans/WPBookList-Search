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

		public $searchbyfields        = '';
		public $searchbyfieldsarray   = array();
		public $searchvalues          = '';
		public $searchvaluesarray     = array();
		public $finalquery            = '';
		public $alldynamictablesarray = array();
		public $final_search_result   = array();
		public $customfieldstext      = '';
		public $customfieldsdropdown  = '';
		public $output_search_stats   = '';
		public $offset_term           = 0;
		public $perpage               = 20;
		public $total_search_results  = 0;
		public $sortby                = 'title';
		public $formatvalues          = '';
		public $genrevalues           = '';
		public $subgenrevalues        = '';



		/**
		 * Class Constructor
		 */
		public function __construct( $action ) {
			require_once CLASS_DIR . 'class-admin-ui-template.php';
			require_once SEARCH_CLASS_DIR . 'class-wpbooklist-search-form.php';

			// Get Translations.
			require_once CLASS_TRANSLATIONS_DIR . 'class-wpbooklist-translations.php';
			$this->trans = new WPBookList_Translations();
			$this->trans->trans_strings();

			$this->action = $action;

			$this->get_search_options();
			$this->get_url_params();
			$this->build_db_query();
			$this->output_top_container();
			$this->build_custom_fields();
			$this->output_text_fields();
			$this->build_years_dropdown();
			$this->output_right_fields();
			$this->output_submit();
			$this->output_search_results_open();
			$this->output_search_results_stats();
			$this->build_pagination_actual_html();
			$this->output_search_results_actual_content();
			$this->output_search_results_pagination();
			$this->output_search_results_close();
			$this->output_ending_container();
			$this->stitch_together_output();
		}


		/**
		 * Gets the Saved Search Options.
		 */
		public function get_search_options() {

			// Build the drop-down values.
			global $wpdb;
			$options              = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'wpbooklist_search_settings' );
			$this->perpage        = $options->perpage;
			$this->formatvalues   = $options->formatvalues;
			$this->genrevalues    = $options->genrevalues;
			$this->subgenrevalues = $options->subgenrevalues;
			$this->earlypubdate   = $options->earlypubdate;

		}

		/**
		 * Gets the URL params
		 */
		public function get_url_params() {

			// Getting all URL parameters.
			$this->url_param_string = urldecode( http_build_query( array_merge( $_GET ) ) );

			// Seeing if the searchby parameter exists.
			if ( false !== strpos( $this->url_param_string, 'searchby' ) ) {
				if ( isset( $_GET['searchby'] ) ) {
					$this->searchbyfields = filter_var( wp_unslash( $_GET['searchby'] ), FILTER_SANITIZE_STRING );

					if ( false !== stripos( $this->searchbyfields, ',' ) ) {
						$this->searchbyfieldsarray = explode( ',', $this->searchbyfields );
					} else {
						array_push( $this->searchbyfieldsarray, $this->searchbyfields );
					}
				}
			}

			// Seeing if the searchvalues parameter exists.
			if ( false !== strpos( $this->url_param_string, 'searchvalues' ) ) {
				if ( isset( $_GET['searchvalues'] ) ) {
					$this->searchvalues = filter_var( wp_unslash( $_GET['searchvalues'] ), FILTER_SANITIZE_STRING );

					if ( false !== stripos( $this->searchvalues, ',' ) ) {
						$this->searchvaluesarray = explode( ',', $this->searchvalues );
					} else {
						array_push( $this->searchvaluesarray, $this->searchvalues );
					}
				}
			}

			if ( isset( $_GET['offset'] ) ) {
				$this->offset_term = filter_var( wp_unslash( $_GET['offset'] ), FILTER_SANITIZE_STRING );
			}

			if ( isset( $_GET['sortby'] ) ) {
				$this->sortby = filter_var( wp_unslash( $_GET['sortby'] ), FILTER_SANITIZE_STRING );
			}
		}

		/**
		 * Builds the DB Query.
		 */
		public function build_db_query() {

			if ( '' !== $this->searchvalues ) {

				global $wpdb;
				/* HOW TO GET THE COLUMN NAMES FROM A WORDPRESS TABLE!
				$existing_columns = $wpdb->get_col("DESC {$wpdb->prefix}wpbooklist_jre_saved_book_log", 0);
				error_log(print_r($existing_columns,true));
				*/

				$this->finalquery = "(SELECT title, image, author, author2, author3, ID, pub_year, page_yes, post_yes, rating, '" . $wpdb->prefix . "wpbooklist_jre_saved_book_log' as source FROM " . $wpdb->prefix . 'wpbooklist_jre_saved_book_log WHERE ';

				$beforepubdate  = false;
				$afterpubdate   = false;
				$exactlypubdate = false;
				$greatreview    = false;
				$lessreview     = false;
				$lastclause     = null;

				$likes = '';
				if ( 1 <= count( $this->searchbyfieldsarray ) ) {

					foreach ( $this->searchvaluesarray as $key => $value ) {

						$value = rtrim( $value );
						$value = ltrim( $value );

						if ( ( false !== stripos( $value, 'Before-' ) ) || ( false !== stripos( $value, 'Exactly-' ) ) || ( false !== stripos( $value, 'After-' ) ) || ( false !== stripos( $value, 'Greater-' ) ) || ( false !== stripos( $value, 'Less-' ) ) ) {
							$temp  = explode( '-', $value );
							$value = $temp[1];

							// Consideration for if the Publication year is in play.
							if ( 'Before' === $temp[0] || 'Exactly' === $temp[0] || 'After' === $temp[0] ) {
								$this->searchvaluesarray[ $key ] = 'pub_year';

								if ( 'Before' === $temp[0] ) {
									$beforepubdate = true;
								}

								if ( 'Exactly' === $temp[0] ) {
									$exactlypubdate = true;
								}

								if ( 'After' === $temp[0] ) {
									$afterpubdate = true;
								}
							}

							// Consideration for if the Rating is in play.
							if ( 'Greater' === $temp[0] || 'Less' === $temp[0]) {
								$this->searchvaluesarray[ $key ] = 'rating';

								if ( 'Greater' === $temp[0] ) {
									$greatreview = true;
								}

								if ( 'Less' === $temp[0] ) {
									$lessreview = true;
								}
							}
						}

						$temp = array();
						if ( false !== stripos( $value, '|' ) ) {

							$temp = explode( '|', $value );
							foreach ( $temp as $key2 => $value2 ) {

								echo $value2;

								$value2 = rtrim( $value2 );
								$value2 = ltrim( $value2 );

								if ( ( count( $temp ) - 1 ) !== $key2 ) {
									$likes = $likes . $this->searchbyfieldsarray[ $key ] . " LIKE '%" . $value2 . "%' OR ";
									$lastclause = ' OR ';
								} else {

									if ( 'keywords' === $this->searchbyfieldsarray[ $key ] ) {
										$likes = $likes . $this->searchbyfieldsarray[ $key ] . " LIKE '%" . $value2 . "%' OR ";
										$lastclause = ' OR ';
									} else {
										$likes = $likes . $this->searchbyfieldsarray[ $key ] . " LIKE '%" . $value2 . "%' OR ";
										$lastclause = ' OR ';
									}
								}
							}
						} else {
							if ( 'keywords' === $this->searchbyfieldsarray[ $key ] ) {
								$likes = $likes . $this->searchbyfieldsarray[ $key ] . " LIKE '%" . $value . "%' OR ";
								$lastclause = ' OR ';
							} else {
								$likes = $likes . $this->searchbyfieldsarray[ $key ] . " LIKE '%" . $value . "%' AND ";
								$lastclause = ' AND ';
							}
						}

						// Consideration for if the Keywords is in play.
						if ( false !== stripos( $this->searchbyfieldsarray[ $key ], 'keywords' ) ) {

							$temp_final_query_explode = explode( "rating, '", $this->finalquery );

							if ( false !== stripos( $value, '|' ) ) {
								$temp_explode = explode( '|', $value );

								foreach ( $temp_explode as $key3 => $value3 ) {
									$likes = $likes . "title LIKE '%" . $value3 . "%' OR originaltitle LIKE '%" . $value3 . "%' OR publisher LIKE '%" . $value3 . "%' OR author LIKE '%" . $value3 . "%' OR author2 LIKE '%" . $value3 . "%' OR author3 LIKE '%" . $value3 . "%' OR authorfirst LIKE '%" . $value3 . "%' OR authorfirst2 LIKE '%" . $value3 . "%' OR authorfirst3 LIKE '%" . $value3 . "%' OR authorlast LIKE '%" . $value3 . "%' OR authorlast2 LIKE '%" . $value3 . "%' OR authorlast3 LIKE '%" . $value3 . "%' OR series LIKE '%" . $value3 . "%' OR genres LIKE '%" . $value3 . "%' OR subgenre LIKE '%" . $value3 . "%' OR keywords LIKE '%" . $value3 . "%' OR shortdescription LIKE '%" . $value3 . "%' OR description LIKE '%" . $value3 . "%' " . $lastclause;
								}
							} else {
								$likes = $likes . "title LIKE '%" . $value . "%' OR originaltitle LIKE '%" . $value . "%' OR publisher LIKE '%" . $value . "%' OR author LIKE '%" . $value . "%' OR author2 LIKE '%" . $value . "%' OR author3 LIKE '%" . $value . "%' OR authorfirst LIKE '%" . $value . "%' OR authorfirst2 LIKE '%" . $value . "%' OR authorfirst3 LIKE '%" . $value . "%' OR authorlast LIKE '%" . $value . "%' OR authorlast2 LIKE '%" . $value . "%' OR authorlast3 LIKE '%" . $value . "%' OR series LIKE '%" . $value . "%' OR genres LIKE '%" . $value . "%' OR subgenre LIKE '%" . $value . "%' OR keywords LIKE '%" . $value . "%' OR shortdescription LIKE '%" . $value . "%' OR description LIKE '%" . $value . "%' " . $lastclause;
							}

							$temp_final_query_explode[0] = "(SELECT title, image, author, author2, author3, ID, pub_year, page_yes, post_yes, rating, originaltitle, publisher, author, author2, author3, authorfirst, authorfirst2, authorfirst3, series, genres, subgenre, keywords, shortdescription, description, '";
							$this->finalquery = $temp_final_query_explode[0] . $temp_final_query_explode[1];


						}
					}
				}

				$likes_for_count  = rtrim( $likes, 'AND ' );
				$likes_for_count  = $likes_for_count . ' UNION ALL ';
				$this->finalquery = $this->finalquery . $likes;
				$count_query      = '(SELECT * FROM '.$wpdb->prefix . 'wpbooklist_jre_saved_book_log WHERE ' . $likes_for_count;

				if ( false !== stripos( $this->finalquery, 'pub_year LIKE' ) || false !== stripos( $this->finalquery, 'rating LIKE' ) ) {

					if ( $beforepubdate ) {
						$this->finalquery = str_replace( "pub_year LIKE '%", 'pub_year <= ', $this->finalquery );
						$pos = strpos( $this->finalquery, '<= ' );
						$this->finalquery = substr_replace( $this->finalquery, ' <= ', ( $pos - 1 ), 4);
						$this->finalquery = rtrim( $this->finalquery, "%' AND" );
						$this->finalquery = rtrim( $this->finalquery, "%' OR" );

					}

					if ( $exactlypubdate ) {
						$this->finalquery = str_replace( "pub_year LIKE '%", 'pub_year = ', $this->finalquery );
						$pos = strpos( $this->finalquery, '= ' );
						$this->finalquery = substr_replace( $this->finalquery, ' = ', ( $pos - 1 ), 3);
						$this->finalquery = rtrim( $this->finalquery, "%' AND" );
						$this->finalquery = rtrim( $this->finalquery, "%' OR" );
					}

					if ( $afterpubdate ) {
						$this->finalquery = str_replace( "pub_year LIKE '%", 'pub_year >= ', $this->finalquery );
						$pos = strpos( $this->finalquery, '>= ' );
						$this->finalquery = substr_replace( $this->finalquery, ' >= ', ( $pos - 1 ), 4);
						$this->finalquery = rtrim( $this->finalquery, "%' AND" );
						$this->finalquery = rtrim( $this->finalquery, "%' OR" );
					}

					if ( $lessreview ) {
						$this->finalquery = str_replace( "rating LIKE '%", 'rating < ', $this->finalquery );
						$pos = strpos( $this->finalquery, '< ' );
						$this->finalquery = substr_replace( $this->finalquery, ' < ', ( $pos - 1 ), 2);
						$temp_array = explode( 'rating', $this->finalquery );
						$pos = strpos( $temp_array[1], "%'" );
						if ( false !== $pos ) {
							$temp_array[1] = substr_replace( $temp_array[1], '', $pos, strlen( "%'" ) );
						}
						$this->finalquery = $temp_array[0] . ' rating ' . $temp_array[1];

					}

					if ( $greatreview ) {
						$this->finalquery = str_replace( "rating LIKE '%", 'rating > ', $this->finalquery );
						$pos = strpos( $this->finalquery, '> ' );
						$this->finalquery = substr_replace( $this->finalquery, ' > ', ( $pos - 1 ), 2);
						$temp_array = explode( 'rating', $this->finalquery );
						$pos = strpos( $temp_array[1], "%'" );
						if ( false !== $pos ) {
							$temp_array[1] = substr_replace( $temp_array[1], '', $pos, strlen( "%'" ) );
						}
						$this->finalquery = $temp_array[0] . ' rating ' . $temp_array[1];
					}
				}

				if ( false !== stripos( $count_query, 'pub_year LIKE' ) || false !== stripos( $count_query, 'rating LIKE' ) ) {

					if ( $beforepubdate ) {
						$count_query = str_replace( "pub_year LIKE '%", 'pub_year <= ', $count_query );
						$pos         = strpos( $count_query, '<= ' );
						$count_query = substr_replace( $count_query, ' <= ', ( $pos - 1 ), 4 );
						$count_query = str_replace( "%'UNION", ' UNION', $count_query );
					}

					if ( $exactlypubdate ) {
						$count_query = str_replace( "pub_year LIKE '%", 'pub_year = ', $count_query );
						$pos         = strpos( $count_query, '= ' );
						$count_query = substr_replace( $count_query, ' = ', ( $pos - 1 ), 3 );
						$count_query = str_replace( "%'UNION", ' UNION', $count_query );
					}

					if ( $afterpubdate ) {
						$count_query = str_replace( "pub_year LIKE '%", 'pub_year >= ', $count_query );
						$pos         = strpos( $count_query, '>= ' );
						$count_query = substr_replace( $count_query, ' >= ', ( $pos - 1 ), 4 );
						$count_query = str_replace( "%'UNION", ' UNION', $count_query );
						$count_query = str_replace( "%' UNION", ' UNION', $count_query );
					}

					if ( $lessreview ) {
						$count_query = str_replace( "rating LIKE '%", 'rating < ', $count_query );
						$pos         = strpos( $count_query, '< ' );
						$count_query = substr_replace( $count_query, ' < ', ( $pos - 1 ), 2 );
						$count_query = str_replace( "%'UNION", ' UNION', $count_query );
						$count_query = str_replace( "%' UNION", ' UNION', $count_query );

						$temp_array = explode( 'rating', $count_query );
						$pos        = strpos( $temp_array[1], "%'" );
						if ( false !== $pos ) {
							$temp_array[1] = substr_replace( $temp_array[1], '', $pos, strlen( "%'" ) );
						}
						$count_query = $temp_array[0] . ' rating ' . $temp_array[1];
					}

					if ( $greatreview ) {
						$count_query = str_replace( "rating LIKE '%", 'rating > ', $count_query );
						$pos         = strpos( $count_query, '> ' );
						$count_query = substr_replace( $count_query, ' > ', ( $pos - 1 ), 2 );
						$count_query = str_replace( "%'UNION", ' UNION', $count_query );
						$count_query = str_replace( "%' UNION", ' UNION', $count_query );

						$temp_array = explode( 'rating', $count_query );
						$pos        = strpos( $temp_array[1], "%'" );
						if ( false !== $pos ) {
							$temp_array[1] = substr_replace( $temp_array[1], '', $pos, strlen( "%'" ) );
						}
						$count_query = $temp_array[0] . ' rating ' . $temp_array[1];
					}
				}

				$this->finalquery = rtrim( $this->finalquery, 'AND' );
				$this->finalquery = rtrim( $this->finalquery, 'OR' );
				$this->finalquery = rtrim( $this->finalquery, 'AND ' );
				$this->finalquery = rtrim( $this->finalquery, 'OR ' );
				$this->finalquery = $this->finalquery . ')';

				if ( 0 < count( $this->final_search_result ) ) {
					foreach ( $this->final_search_result as $key => $value3 ) {
						$value3->table = $wpdb->prefix . 'wpbooklist_jre_saved_book_log';
					}
				}

				// First build an array of all the custom db tables.
				$table_name2 = $wpdb->prefix . 'wpbooklist_jre_list_dynamic_db_names';
				$db_row = $wpdb->get_results( "SELECT * FROM $table_name2" );
				$dyna_query = '';
				foreach ( $db_row as $table ) {
					$dyna_query = $dyna_query . str_replace( 'saved_book_log', $table->user_table_name, $this->finalquery ) . ' UNION ALL ';
					$count_query = $count_query . str_replace( "title, image, author, author2, author3, ID, pub_year, page_yes, post_yes, rating, '".$wpdb->prefix."wpbooklist_jre_".$table->user_table_name."' as source", '* ', str_replace('(','',$dyna_query) );
					$count_query = str_replace( ")", '', $count_query );
					$count_query = $count_query . ' UNION ALL';	
				}

				$count_query = substr($count_query, 0, -10);
				$count_query = 'SELECT count(*) FROM '.$count_query . ')';

				if ( '' !== $dyna_query ) {
					$dyna_query = rtrim( $dyna_query, 'UNION ALL ' );
					$dyna_query = $this->finalquery . ' UNION ALL ' . $dyna_query;
				} else {
					$dyna_query = $this->finalquery;
				}

				// Now we'll do the Sort by stuff.
				if ( 'title' !== $this->sortby ) {

					if ( 'pubyearold' === $this->sortby ) {
						$this->sortby = 'pub_year ASC';
					}

					if ( 'pubyearnew' === $this->sortby ) {
						$this->sortby = 'pub_year DESC';
					}

					if ( 'ratinghigh' === $this->sortby ) {
						$this->sortby = 'rating DESC';
					}

				}

				$this->finalquery = $dyna_query . 'ORDER BY ' . $this->sortby . ' LIMIT ' . $this->perpage . ' OFFSET ' . $this->offset_term;

				$count_query = str_replace( ' group by title)', ')', $count_query );
				$count_query = str_replace( 'OR UNION ALL SELECT', 'UNION ALL SELECT', $count_query );
				$count_query = rtrim( $count_query, ' UNION ALL)' );
				$count_query = $count_query . ') as tem';

				foreach ( $db_row as $table ) {
					$count_query = str_replace( "SELECT title, image, author, author2, author3, ID, pub_year, page_yes, post_yes, rating, originaltitle, publisher, author, author2, author3, authorfirst, authorfirst2, authorfirst3, series, genres, subgenre, keywords, shortdescription, description, '" . $wpdb->prefix . "wpbooklist_jre_" . $table->user_table_name . "' as source FROM " . $wpdb->prefix . "wpbooklist_jre_" . $table->user_table_name, 'SELECT * FROM ' . $wpdb->prefix . "wpbooklist_jre_" . $table->user_table_name, $count_query );
				}

				$count_query;
				//echo $this->finalquery;

				$this->total_search_results = $wpdb->get_var( $count_query );
				$this->final_search_result  = $wpdb->get_results( $this->finalquery );

			}
		}

		/**
		 * Outputs the opening of the very top container.
		 */
		public function output_top_container() {

			$styling          = '';
			$show_search_html = '';
			if ( 0 < $this->total_search_results ) {
				$styling = 'style="opacity:0; height:0px; overflow:hidden;"';
				$show_search_html = '<button id="wpbooklist-search-show-form-button" type="button">Show Search Form</button>';
			}



			$this->top_container_output = '
				<div id="wpbooklist_search_top_container"><form id="wpbooklist-search-searchterm-form">' . $show_search_html . '<div ' . $styling . ' id="wpbooklist-search-controls-wrapper">';

		}

		/**
		 * Builds the drop-down for the Publication Years.
		 */
		public function build_years_dropdown() {

			$current_year            = date( 'Y' );
			$this->options_year_html = '';

			for ( $i = $current_year; $i >= $this->earlypubdate; $i-- ) {
				$this->options_year_html = $this->options_year_html . '<option>' . $i . '</option>';
			}
		}

		/**
		 * Build the Custom Fields options.
		 */
		public function build_custom_fields() {

			global $wpdb;
			$customfields = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'wpbooklist_jre_user_options' );

			if ( false !== stripos( $customfields->customfields, '--' ) ) {

				$customfieldsarray = explode( '--', $customfields->customfields );

				foreach ( $customfieldsarray as $key => $value ) {

					if ( false !== stripos( $value, ';' ) ) {

						$temparray = explode( ';', $value );
						$tempname = str_replace( '_', ' ', $temparray[0] );

						if ( 'Plain Text Entry' === $temparray[1] ) {

							$this->customfieldstext = $this->customfieldstext . '
								<div class="wpbooklist-search-left-textfields-row"> 
									<label>' . $tempname . '</label>
									<input class="wpbooklist-search-customfield-text" data-dbfieldname="' . $temparray[0] . '" type="text" />
								</div>';
						}

						if ( 'Drop-Down' === $temparray[1] ) {

							if ( false !== stripos( $temparray[4], '/' ) ) {

								$optionsstring = '<option value="default" selected default>Select an Option...</option>';
								$options = explode( '/', $temparray[4] );

								foreach ( $options as $key => $value ) {
									$optionsstring = $optionsstring . '<option>' . $value . '</option>';
								}

								$this->customfieldsdropdown = $this->customfieldsdropdown . '
								<div class="wpbooklist-search-right-fields-row"> 
									<label>' . $tempname . '</label>
									<select class="wpbooklist-search-customfield-dropdown" data-dbfieldname="' . $temparray[0] . '">
										' . $optionsstring . '
									</select>
								</div>';

							}
						}
					}
				}
			}
		}


		/**
		 * Outputs the left-hand text fields.
		 */
		public function output_text_fields() {

			$this->text_fields_output = '
				<div id="wpbooklist_search_left_textfields_container">
					<div class="wpbooklist-search-left-textfields-row"> 
						<label>Title</label>
						<input id="wpbooklist-search-title" data-dbfieldname="title" type="text" />
					</div>
					<div class="wpbooklist-search-left-textfields-row"> 
						<label>Author</label>
						<input id="wpbooklist-search-author" data-dbfieldname="author" type="text" />
					</div>
					<div class="wpbooklist-search-left-textfields-row"> 
						<label>Keywords</label>
						<input id="wpbooklist-search-keywords" data-dbfieldname="keywords" type="text" />
					</div>
					<div class="wpbooklist-search-left-textfields-row"> 
						<label>ISBN(s)</label>
						<input id="wpbooklist-search-isbn" data-dbfieldname="isbn" type="text" />
					</div>
					<div class="wpbooklist-search-left-textfields-row"> 
						<label>Publisher</label>
						<input id="wpbooklist-search-publisher" data-dbfieldname="publisher" type="text" />
					</div>
					<div class="wpbooklist-search-left-textfields-row"> 
						<label>Series</label>
						<input id="wpbooklist-search-series" data-dbfieldname="series" type="text" />
					</div>
					<div class="wpbooklist-search-left-textfields-row"> 
						<label>Language</label>
						<input id="wpbooklist-search-language" data-dbfieldname="language" type="text" />
					</div>
					' . $this->customfieldstext . '
					<div class="wpbooklist-search-right-fields-row"> 
						<label>Sort By</label>
						<select id="wpbooklist-search-sortby" data-dbfieldname="sortby">
							<option value="default" selected default>Select a Sort By Option...</option>
							<option value="title">Title</option>
							<option value="author">Author</option>
							<option value="pubyearnew">Publication Year (Descending)</option>
							<option value="pubyearold">Publication Year (Ascending)</option>
							<option value="ratinghigh">Highest Rated</option>
						</select>
					</div>
				</div>
			';

		}

		/**
		 * Outputs the left-hand text fields.
		 */
		public function output_right_fields() {

			// Build the drop-down values.
			$format_options   = '<option value="default" selected default>Select a Format...</option>';
			$genre_options    = '<option value="default" selected default>Select a Genre...</option>';
			$subgenre_options = '<option value="default" selected default>Select a Sub-Genre...</option>';

			if ( '' !== $this->formatvalues && false !== stripos( $this->formatvalues, ',' ) ) {
				$temp = explode( ',', $this->formatvalues );

				foreach ( $temp as $key => $value ) {
					if ( '' !== $value ) {
						$format_options = $format_options . '<option value="' . $value . '">' . $value . '</option>';
					}
				}
			}

			if ( '' !== $this->genrevalues && false !== stripos( $this->genrevalues, ',' ) ) {
				$temp = explode( ',', $this->genrevalues );

				foreach ( $temp as $key => $value ) {
					if ( '' !== $value ) {
						$genre_options = $genre_options . '<option value="' . $value . '">' . $value . '</option>';
					}
				}
			}

			if ( '' !== $this->subgenrevalues && false !== stripos( $this->subgenrevalues, ',' ) ) {
				$temp = explode( ',', $this->subgenrevalues );

				foreach ( $temp as $key => $value ) {
					if ( '' !== $value ) {
						$subgenre_options = $subgenre_options . '<option value="' . $value . '">' . $value . '</option>';
					}
				}
			}

			$this->right_fields_output = '
				<div id="wpbooklist_search_right_fields_container">
					<div class="wpbooklist-search-right-fields-row"> 
						<label>Format</label>
						<select id="wpbooklist-search-format" data-dbfieldname="format">
							' . $format_options . '
						</select>
					</div>
					<div class="wpbooklist-search-right-fields-row"> 
						<label>Genre</label>
						<select id="wpbooklist-search-genres" data-dbfieldname="genres">
							' . $genre_options . '
						</select>
					</div>
					<div class="wpbooklist-search-right-fields-row"> 
						<label>Sub-Genre</label>
						<select id="wpbooklist-search-subgenre" data-dbfieldname="subgenre">
							' . $subgenre_options . '
						</select>
					</div>
					' . $this->customfieldsdropdown . '
					<div class="wpbooklist-search-right-fields-row"> 
						<label>Rating</label>
						<select class="wpbooklist-search-dropdown wpbooklist-search-dropdown-ratingyear" id="wpbooklist-search-rating-year-term">
							<option value="Greater">Greater Than</option>
							<option value="Less">Less Than</option>
						</select>
						<select class="wpbooklist-search-dropdown wpbooklist-search-dropdown-ratingyear" id="wpbooklist-search-rating" data-dbfieldname="rating">
							<option value="default" selected default>Select a Rating...</option>
							<option value="5">5 Stars</option>
							<option value="4.5">4 1/2 Stars</option>
							<option value="4">4 Stars</option>
							<option value="3.5">3 1/2 Stars</option>
							<option value="3">3 Stars</option>
							<option value="2.5">2 1/2 Stars</option>
							<option value="2">2 Stars</option>
							<option value="1.5">1 1/2 Stars</option>
							<option value="1">1 Stars</option>
						</select>
					</div>
					<div class="wpbooklist-search-right-fields-row"> 
						<label>Publication Date</label>
						<select class="wpbooklist-search-dropdown wpbooklist-search-dropdown-pubyear" id="wpbooklist-search-pub-year-term">
							<option>Before</option>
							<option>Exactly</option>
							<option>After</option>
						</select>
						<select class="wpbooklist-search-dropdown wpbooklist-search-dropdown-pubyear" id="wpbooklist-search-pub_year" data-dbfieldname="pub_year">
							<option value="default" selected default>Select a Year...</option>
							' . $this->options_year_html . '
						</select>
					</div>
				</div>
			';

		}

		/**
		 * Closes the very top container.
		 */
		public function output_submit() {

			$this->output_submit = '
					<div id="wpbooklist-search-submit-search-div">
						<button>Search</button>
						<button type="button" id="wpbooklist-search-submit-search-div">Reset</button>
					</div>
				</div>';

		}

		/**
		 * Outputs the opening of the search results area.
		 */
		public function output_search_results_open() {

			$this->output_search_results_open = '
				<div id="wpbooklist-search-results-main-wrapper">
					';

		}

		/**
		 * Outputs the search stats area
		 */
		public function output_search_results_stats() {

			$end_display = 0;
			if ( $this->total_search_results < ( $this->perpage + $this->offset_term ) ) {
				$end_display = $this->total_search_results;
			} else {
				$end_display = $this->perpage + $this->offset_term;
			}

			$displaying_string = '';
			if ( '1' === $this->total_search_results ) {
				$displaying_string = 'Displaying Result 1 of 1';
			} elseif( 0 === $this->total_search_results ) {
				$displaying_string = 'Displaying Results ' . ( $this->offset_term + 1 ) . ' -  ' . $end_display;
			} else {
				$displaying_string = 'Displaying Results ' . ( $this->offset_term + 1 ) . ' -  ' . $end_display;
			}

			if ( '' !== $this->searchvalues ) {

				if ( '0' === $this->total_search_results ) {

					$this->output_search_stats = '
					<p id="wpbooklist-search-no-results-found"><img class="wpbooklist-storytime-shocked-img-front" src="' . ROOT_IMG_ICONS_URL . 'shocked.svg">Uh-Oh! No books were found!<br>Try another search!</p>';

				} else {

					$this->output_search_stats = '
					<div id="wpbooklist-search-results-stats-wrapper">
						<div>' . $this->total_search_results . ' Total Search Results</div>
						<div>' . $displaying_string . '</div>
					</div>';

				}
			}
		}

		/**
		 * Builds the actual pagination HTML.
		 */
		private function build_pagination_actual_html() {
			$string1 = '';
			if ( $this->perpage < $this->total_search_results ) {
				$pagination_options_string = '';
				// Setting up variables to determine the previous offset to go back to, or to disable that ability if on Page 1.
				if ( '0' !== $this->offset_term && null !== $this->offset_term ) {
					$prevnum          = $this->offset_term - $this->perpage;
					$styledisableleft = '';
				} else {
					$prevnum          = 0;
					$styledisableleft = 'style="pointer-events:none;opacity:0.5;"';
				}
				// Setting up variables to determine the next offset to go to, or to disable that ability if on last Page.
				if ( $this->offset_term < ( $this->total_search_results - $this->perpage ) ) {
					$nextnum           = $this->offset_term + $this->perpage;
					$styledisableright = '';
				} else {
					$nextnum           = $this->offset_term;
					$styledisableright = 'style="pointer-events:none;opacity:0.5;"';
				}
				// Getting total number of full pages and/or if there's only a partial/remainder page.
				if ( $this->total_search_results > 0 && $this->perpage > 0 ) {
					// Getting whole pages. Can be zero if total number of books is less that amount set to be displayed per page in the backend settings.
					$whole_pages = floor( $this->total_search_results / $this->perpage );
					// Determing whether there is a partial page, whose contents contains less books than amount set to be displayed per page in the backend settings. Will only be 0 if total number of books is evenly divisible by $this->perpage.
					$remainder_pages = $this->total_search_results % $this->perpage;
					if ( 0 !== $remainder_pages ) {
						$remainder_pages = 1;
					}
					// If there's only one page, don't show pagination.
					if ( ( 1 === $whole_pages && 0 === $remainder_pages ) || ( 0 === $whole_pages && 1 === $remainder_pages ) ) {
						return;
					}
					// The loop that will create the <option> html for the <select> for the whole pages.
					for ( $i = 1; $i <= $whole_pages + $remainder_pages; $i++ ) {
						$pagination_options_string = $pagination_options_string . '<option value="' . ( ( $i - 1 ) * $this->perpage ) . '">' . $this->trans->trans_600 . ' ' . $i . '</option>';
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
			}
			$this->pagination_actual_html = $string1;
		}

		/**
		 * Outputs the actual search results themselves.
		 */
		public function output_search_results_actual_content() {

			$this->output_search_results_actual = '
				<div id="wpbooklist-search-results-actual-wrapper">';

			foreach ( $this->final_search_result as $key => $book ) {

				$author = $book->author;

				if ( null !== $book->author2 && '' !== $book->author2 ) {

					$author = $author . ', ' . $book->author2;

				}

				if ( null !== $book->author3 && '' !== $book->author3 ) {

					$author = $author . ', ' . $book->author3;

				}

				if ( 'page' === $this->action ) {
					if ( ( null !== $book->page_yes && 'false' !== $book->page_yes && 'N/A' !== $book->page_yes && 'no' !== $book->page_yes && 'No' !== $book->page_yes ) && '' !== get_permalink( $book->page_yes ) && null !== get_permalink( $book->page_yes )   ) {

						$this->output_search_results_actual = $this->output_search_results_actual . '
							<div class="wpbooklist-search-indiv-book-row">
								<div class="wpbooklist-search-indiv-book-img-wrapper">
								 	<a href="' . get_permalink( $book->page_yes ) . '"><img class="wpbooklist_cover_image_class" data-bookid="' . $book->ID . '" data-booktable="' . $book->source . '" src="' . $book->image . '" style="opacity: 1;"></a>
								 </div>
								<div class="wpbooklist-search-indiv-book-title-details-wrapper">
									<div class="wpbooklist-search-indiv-book-title-wrapper">
										 <a href="' . get_permalink( $book->page_yes ) . '">
										 	<p class="wpbooklist-search-indiv-book-title" data-bookid="' . $book->ID . '" data-booktable="' . $book->source . '">' . $book->title . '</p>
						    			</a>
									</div>
									<div class="wpbooklist-search-indiv-book-details-wrapper">
										<p>' . $author . ' - ' . $book->pub_year . '</p>
									</div>
								</div>
							</div>';
					} else {
						$this->output_search_results_actual = $this->output_search_results_actual . '

						<div class="wpbooklist-search-indiv-book-row">
							<div class="wpbooklist-search-indiv-book-img-wrapper">
								<img class="wpbooklist-show-book-colorbox" data-bookid="' . $book->ID . '" data-booktable="' . $book->source . '" src="' . $book->image . '" />
							</div>
							<div class="wpbooklist-search-indiv-book-title-details-wrapper">
								<div class="wpbooklist-search-indiv-book-title-wrapper">
									<p class="wpbooklist-search-indiv-book-title wpbooklist-show-book-colorbox" data-bookid="' . $book->ID . '" data-booktable="' . $book->source . '">' . $book->title . '</p>
								</div>
								<div class="wpbooklist-search-indiv-book-details-wrapper">
									<p>' . $author . ' - ' . $book->pub_year . '</p>
								</div>
							</div>
						</div>';
					}
				} else if ( 'post' === $this->action ) {
					if ( ( null !== $book->post_yes && 'false' !== $book->post_yes && 'N/A' !== $book->post_yes && 'no' !== $book->post_yes && 'No' !== $book->post_yes ) && '' !== get_permalink( $book->post_yes ) && null !== get_permalink( $book->post_yes )   ) {
						$this->output_search_results_actual = $this->output_search_results_actual . '
							<div class="wpbooklist-search-indiv-book-row">
								<div class="wpbooklist-search-indiv-book-img-wrapper">
								 	<a href="' . get_permalink( $book->post_yes ) . '"><img class="wpbooklist_cover_image_class" data-bookid="' . $book->ID . '" data-booktable="' . $book->source . '" src="' . $book->image . '" style="opacity: 1;"></a>
								 </div>
								<div class="wpbooklist-search-indiv-book-title-details-wrapper">
									<div class="wpbooklist-search-indiv-book-title-wrapper">
										 <a href="' . get_permalink( $book->post_yes ) . '">
										 	<p class="wpbooklist-search-indiv-book-title" data-bookid="' . $book->ID . '" data-booktable="' . $book->source . '">' . $book->title . '</p>
						    			</a>
									</div>
									<div class="wpbooklist-search-indiv-book-details-wrapper">
										<p>' . $author . ' - ' . $book->pub_year . '</p>
									</div>
								</div>
							</div>';
					} else {
						$this->output_search_results_actual = $this->output_search_results_actual . '

							<div class="wpbooklist-search-indiv-book-row">
								<div class="wpbooklist-search-indiv-book-img-wrapper">
									<img class="wpbooklist-show-book-colorbox" data-bookid="' . $book->ID . '" data-booktable="' . $book->source . '" src="' . $book->image . '" />
								</div>
								<div class="wpbooklist-search-indiv-book-title-details-wrapper">
									<div class="wpbooklist-search-indiv-book-title-wrapper">
										<p class="wpbooklist-search-indiv-book-title wpbooklist-show-book-colorbox" data-bookid="' . $book->ID . '" data-booktable="' . $book->source . '">' . $book->title . '</p>
									</div>
									<div class="wpbooklist-search-indiv-book-details-wrapper">
										<p>' . $author . ' - ' . $book->pub_year . '</p>
									</div>
								</div>
							</div>';
					}
				} else {
					$this->output_search_results_actual = $this->output_search_results_actual . '

					<div class="wpbooklist-search-indiv-book-row">
						<div class="wpbooklist-search-indiv-book-img-wrapper">
							<img class="wpbooklist-show-book-colorbox" data-bookid="' . $book->ID . '" data-booktable="' . $book->source . '" src="' . $book->image . '" />
						</div>
						<div class="wpbooklist-search-indiv-book-title-details-wrapper">
							<div class="wpbooklist-search-indiv-book-title-wrapper">
								<p class="wpbooklist-search-indiv-book-title wpbooklist-show-book-colorbox" data-bookid="' . $book->ID . '" data-booktable="' . $book->source . '">' . $book->title . '</p>
							</div>
							<div class="wpbooklist-search-indiv-book-details-wrapper">
								<p>' . $author . ' - ' . $book->pub_year . '</p>
							</div>
						</div>
					</div>';
				}
			}

			$this->output_search_results_actual = $this->output_search_results_actual . '</div>';

		}

		/**
		 * Outputs the pagination area.
		 */
		public function output_search_results_pagination() {

			$this->output_search_results_pagination = '
				<div id="wpbooklist-search-results-pagination-wrapper"></div>';
		}

		/**
		 * Outputs the closing of the search results area.
		 */
		public function output_search_results_close() {

			$this->output_search_results_close = '</div>';

		}


		/**
		 * Closes the very top container.
		 */
		public function output_ending_container() {

			$this->ending_container_output = '
				</form></div>';

		}

		/**
		 * Outputs all the final output together and displays it.
		 */
		public function stitch_together_output() {

			echo $this->top_container_output . $this->text_fields_output . $this->right_fields_output . $this->output_submit . $this->output_search_stats . $this->output_search_results_open . $this->output_search_results_actual . $this->output_search_results_pagination . $this->output_search_results_close . $this->pagination_actual_html . $this->ending_container_output;
		}


	}
endif;
