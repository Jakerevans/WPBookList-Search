<?php
/**
 * Class WPBookList_Search_Translations - class-wpbooklist-translations.php
 *
 * @author   Jake Evans
 * @category Translations
 * @package  Includes/Classes/Translations
 * @version  0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPBookList_Search_Translations', false ) ) :
	/**
	 * WPBookList_Search_Translations class. This class will house all the translations we may ever need...
	 */
	class WPBookList_Search_Translations {

		/**
		 * Class Constructor - Simply calls the one function to return all Translated strings.
		 */
		public function __construct() {
			$this->trans_strings();
		}

		/**
		 * All the Translations.
		 */
		public function trans_strings() {
			$this->trans_1 = __( 'Enter your License Key Here', 'wpbooklist-textdomain' );
			$this->trans_2 = __( 'Save', 'wpbooklist-textdomain' );
			$this->trans_3 = __( 'Uh-Oh! Looks like you haven\'t entered your License Key for the WPBookList Search Extension yet! Enter your License Key below to being using WPBookList Search.', 'wpbooklist-textdomain' );
			$this->trans_4 = __( 'Save License Key', 'wpbooklist-textdomain' );

			// The array of translation strings.
			$translation_array = array(
				'trans1' => $this->trans_1,
			);

			return $translation_array;
		}
	}
endif;
