<?php
/**
 * @package     YoastSEO_AMP_Glue\CSS_Builder
 * @author      Jip Moors
 * @copyright   2016 Yoast BV
 * @license     GPL-2.0+
 */

if ( ! class_exists( 'YoastSEO_AMP_CSS_Builder', false ) ) {

	class YoastSEO_AMP_CSS_Builder {

		/**
		 * @var array Option to CSS lookup map
		 */
		private $items = array();

		/**
		 * @var array
		 */
		private $options = array();

		/**
		 * Add option to CSS map
		 *
		 * @param string $option_key Option key.
		 * @param string $selector CSS Selector.
		 * @param string $property CSS Property that will hold the value of the option.
		 */
		public function add_option( $option_key, $selector, $property ) {
			$this->items[ $option_key ] = array( 'selector' => $selector, 'property' => $property );
		}

		/**
		 * @return string Output CSS
		 */
		public function build() {
			$options = YoastSEO_AMP_Options::get();

			$this->options = array_filter( $options );
			$apply         = array_intersect_key( $this->items, $this->options );

			$css = $this->build_css_array( $apply );

			return $this->build_output( $css );
		}

		/**
		 * Builds CSS array based on settings
		 *
		 * @param array $apply
		 *
		 * @return array
		 */
		private function build_css_array( $apply ) {
			$css = array();

			if ( is_array( $apply ) ) {
				foreach ( $apply as $key => $placement ) {

					if ( ! isset( $css[ $placement['selector'] ] ) ) {
						$css[ $placement['selector'] ] = array();
					}

					$css[ $placement['selector'] ][ $placement['property'] ] = $this->options[ $key ];
				}
			}

			return $css;
		}

		/**
		 * Builds output string based on CSS array
		 *
		 * @param array $css
		 *
		 * @return string
		 */
		private function build_output( $css ) {
			$output = "\n";

			if ( ! empty( $css ) ) {
				foreach ( $css as $selector => $properties ) {

					$inner = '';
					foreach ( $properties as $property => $value ) {
						$inner .= sprintf( "%s: %s;\n", $property, $value );
					}

					$output .= sprintf( "%s {\n%s}\n", $selector, $inner );
				}
			}

			return $output;
		}
	}
}
