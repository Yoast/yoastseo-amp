<?php
/**
 * @package     YoastSEO_AMP_Glue\Frontend
 * @author      Joost de Valk
 * @copyright   2016 Yoast BV
 * @license     GPL-2.0+
 */

if ( ! class_exists( 'YoastSEO_AMP_Design' ) ) {
	/**
	 * This class improves the design of the AMP output.
	 */
	class YoastSEO_AMP_Design {

		/**
		 * @var array
		 */
		private $options;

		/**
		 * YoastSEO_AMP_Frontend constructor.
		 */
		public function __construct() {
			$this->options = array_merge( WPSEO_Options::get_options( array( 'wpseo_social' ) ), YoastSEO_AMP_Options::get() );

			add_action( 'amp_post_template_css', array( $this, 'additional_css' ) );
			add_action( 'amp_post_template_head', array( $this, 'extra_head' ) );
		}

		/**
		 * Add additional CSS to the AMP output
		 */
		public function additional_css() {
			require 'views/additional-css.php';

			$this->css_builder();
			$this->extra_css();
		}

		/**
		 * Outputs extra code in the head, if set
		 */
		public function extra_head() {
			if ( $this->options['twitter'] === true ) {
				WPSEO_Twitter::get_instance();
			}

			if ( $this->options['opengraph'] === true ) {
				new WPSEO_OpenGraph;
			}

			do_action( 'wpseo_opengraph' );

			echo strip_tags( $this->options['extra-head'], '<link><meta>' );
		}

		/**
		 * Build the CSS
		 */
		private function css_builder() {
			$css_builder = new YoastSEO_AMP_CSS_Builder();
			$css_builder->add_option( 'header-color', 'nav.amp-wp-title-bar', 'background' );
			$css_builder->add_option( 'headings-color', '.amp-wp-title, h2, h3, h4', 'color' );
			$css_builder->add_option( 'text-color', '.amp-wp-content', 'color' );

			$css_builder->add_option( 'blockquote-bg-color', '.amp-wp-content blockquote', 'background-color' );
			$css_builder->add_option( 'blockquote-border-color', '.amp-wp-content blockquote', 'border-color' );
			$css_builder->add_option( 'blockquote-text-color', '.amp-wp-content blockquote', 'color' );

			$css_builder->add_option( 'link-color', 'a, a:active, a:visited', 'color' );
			$css_builder->add_option( 'link-color-hover', 'a:hover, a:focus', 'color' );

			$css_builder->add_option( 'meta-color', '.amp-wp-meta li, .amp-wp-meta li a', 'color' );

			echo $css_builder->build();
		}

		/**
		 * Outputs the extra-css option after some sanitation
		 */
		private function extra_css() {
			if ( ! empty( $this->options['extra-css'] ) ) {
				$safe_text = strip_tags($this->options['extra-css']);
				$safe_text = wp_check_invalid_utf8( $safe_text );
				$safe_text = _wp_specialchars( $safe_text, ENT_NOQUOTES );
				echo $safe_text;
			}
		}
	}
}
