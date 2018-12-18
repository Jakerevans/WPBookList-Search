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
				<p style="text-align:center; max-width:600px; font-weight:bold; font-size:15px; margin-left:auto; margin-right:auto;">' . $this->trans->trans_640 . '</p>';

			$searchin_string = '<div>';
			$db_string       = '';

			// Getting all user-created libraries.
			$db_row = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'wpbooklist_jre_list_dynamic_db_names' );
			foreach ( $db_row as $key => $table ) {
				$db_string = $db_string . '
					<div class="wpbooklist-edit-book-indiv-div-class" id="wpbooklist-searchin-indiv-div-id-' . $key . '">
							<div class="wpbooklist-edit-title-div">
								<div class="wpbooklist-bulk-delete-checkbox-div">
									<input data-key="0" data-table="wp_wpbooklist_jre_saved_book_log" data-book-id="1" class="wpbooklist-bulk-delete-checkbox" type="checkbox"><label>Delete Title</label>
								</div>
								<div class="wpbooklist-edit-img-author-div">
									<img data-bookid="1" data-bookuid="5c190f42c75d1" data-booktable="wp_wpbooklist_jre_saved_book_log" class="wpbooklist-edit-book-cover-img wpbooklist-show-book-colorbox" src="https://images-na.ssl-images-amazon.com/images/I/61duRkxH5TL.jpg">
									<p class="wpbooklist-edit-book-title wpbooklist-show-book-colorbox" data-booktable="wp_wpbooklist_jre_saved_book_log" data-bookid="1">Fantastic Beasts: The Crimes of Grindelwald - The Original Screenplay (Harry Potter)</p><br>
									<img class="wpbooklist-edit-book-icon wpbooklist-book-icon-author " src="http://localhost/local/wp-content/plugins/wpbooklist/assets/img/icons/author.svg"><p class="wpbooklist-edit-book-author">J.K. Rowling</p>
								</div>
							</div>
							<div class="wpbooklist-edit-actions-div">
									<div class="wpbooklist-edit-actions-edit-button" data-key="0" data-table="wp_wpbooklist_jre_saved_book_log" data-book-id="1">
										<p>Edit
											<img class="wpbooklist-edit-book-icon wpbooklist-edit-book-icon-button" src="http://localhost/local/wp-content/plugins/wpbooklist/assets/img/icons/pencil.svg"> 
										</p>
									</div>
									<div class="wpbooklist-edit-actions-delete-button" data-key="0" data-table="wp_wpbooklist_jre_saved_book_log" data-book-id="1"> 
										<p>Delete
											<img class="wpbooklist-edit-book-icon wpbooklist-edit-book-icon-button" src="http://localhost/local/wp-content/plugins/wpbooklist/assets/img/icons/garbage-bin.svg">
										</p>
									</div>
									<div class="wpbooklist-edit-book-delete-page-post-div"><input data-id="No" id="wpbooklist-delete-page-input" type="checkbox"><label for="wpbooklist-edit-delete-page">Delete Page</label><br><input data-id="No" id="wpbooklist-delete-post-input" type="checkbox"><label for="wpbooklist-edit-delete-post">Delete Post</label></div>
							</div>
							<div class="wpbooklist-spinner" id="wpbooklist-spinner-0"></div>
							<div class="wpbooklist-delete-result" id="wpbooklist-delete-result-0"></div>
							<div class="wpbooklist-edit-form-div" id="wpbooklist-edit-form-div-0">
								
							</div>
						</div>';
			}

			$searchin_string = $searchin_string . $db_string . '</div>';

			$string1 = $string1 . $searchin_string;
			return $string1;
		}
	}

endif;