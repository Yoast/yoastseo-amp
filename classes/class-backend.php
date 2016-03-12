<?php
/**
 * @package     YoastSEO_AMP_Glue\Admin
 * @author      Joost de Valk
 * @copyright   2016 Yoast BV
 * @license     GPL-2.0+
 */

if ( ! class_exists( 'YoastSEO_AMP_Backend', false ) ) {
	/**
	 * This class improves upon the AMP output by the default WordPress AMP plugin using Yoast SEO metadata.
	 */
	class YoastSEO_AMP_Backend {

		/**
		 * @var array
		 */
		public $options;

		/**
		 * YoastSEO_AMP_Backend constructor.
		 */
		public function __construct() {
			$this->options = YoastSEO_AMP_Options::get();

			// Add subitem to menu
			add_filter( 'wpseo_submenu_pages', array( $this, 'add_submenu_page' ) );

			// Register AMP admin page as a Yoast SEO admin page
			add_filter( 'wpseo_admin_pages', array( $this, 'add_admin_pages' ) );
		}

		/**
		 * Add submenu item
		 *
		 * @param array $sub_menu_pages
		 *
		 * @return array
		 */
		public function add_submenu_page( $sub_menu_pages ) {

			$sub_menu_pages[] = array(
				'wpseo_dashboard',
				__( 'AMP', 'wordpress-seo' ),
				__( 'AMP', 'wordpress-seo' ),
				'manage_options',
				'wpseo_amp',
				array( $this, 'display' ),
				array( array( $this, 'enqueue_admin_page' ) ),
			);

			return $sub_menu_pages;
		}

		/**
		 * Displays the admin page
		 */
		public function display() {
			require 'views/admin-page.php';
		}

		/**
		 * Enqueue admin page JS
		 */
		public function enqueue_admin_page() {
			wp_enqueue_style( 'yoast_amp_css', plugin_dir_url( __FILE__ ) . 'assets/amp-admin-page.css', array( 'wp-color-picker' ), false );

			wp_enqueue_media(); // enqueue files needed for upload functionality
			wp_enqueue_script( 'wpseo-admin-media', plugin_dir_url( __FILE__ ) . 'assets/wp-seo-admin-media.js', array(
				'jquery',
				'jquery-ui-core',
			), false, true );
			wp_localize_script( 'wpseo-admin-media', 'wpseoMediaL10n', $this->localize_media_script() );

			wp_enqueue_script( 'yoast_amp_js', plugin_dir_url( __FILE__ ) . 'assets/amp-admin-page.js', array(
				'jquery',
				'wp-color-picker'
			), false, true );
		}

		/**
		 * Pass some variables to js for upload module.
		 *
		 * @return  array
		 */
		public function localize_media_script() {
			return array(
				'choose_image' => __( 'Use Logo', 'wordpress-seo' ),
			);
		}

		/**
		 * Add admin page to admin_pages so the correct assets are loaded by WPSEO
		 *
		 * @param $admin_pages
		 *
		 * @return array
		 */
		public function add_admin_pages( $admin_pages ) {
			$admin_pages[] = 'wpseo_amp';

			return $admin_pages;
		}

		/**
		 * Render a color picker
		 *
		 * @param string $var
		 * @param string $label
		 *
		 * @SuppressWarnings("PMD.UnusedPrivateMethod") // As this is used in a view
		 */
		private function color_picker( $var, $label ) {
			echo '<label class="checkbox" for="', esc_attr( $var ), '">', esc_html( $label ), '</label>';
			echo '<input type="text" name="wpseo_amp[', esc_attr( $var ), ']"';
			if ( isset( $this->options[ $var ] ) ) {
				echo ' value="' . esc_attr( $this->options[ $var ] ) . '"';
			}
			echo ' class="yst_colorpicker" id="', esc_attr( $var ), '"/>';
			echo '<br/>';
		}
	}
}
