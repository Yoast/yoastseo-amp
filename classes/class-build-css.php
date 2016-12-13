<?php
/**
 * @package     YoastSEO_AMP_Glue\CSS_Builder
 * @author      Jip Moors
 * @copyright   2016 Yoast BV
 * @license     GPL-2.0+
 */

if ( ! class_exists( 'YoastSEO_AMP_CSS_Builder', false ) ) {

	class YoastSEO_AMP_CSS_Builder {

		/** @var array Option to CSS lookup map */
		private $items = array();

		/**
		 * Add option to CSS map
		 *
		 * @param string $option_key Option key.
		 * @param string $selector   CSS Selector.
		 * @param string $property   CSS Property that will hold the value of the option.
		 */
		public function add_option( $option_key, $selector, $property ) {
			$this->items[ $option_key ] = array( 'selector' => $selector, 'property' => $property );
		}

		/**
		 * @return string Output CSS
		 */
		public function build() {
			$options = YoastSEO_AMP_Options::get();

			$output = "\n";
			$css    = array();

			$options = array_filter( $options );
			$apply   = array_intersect_key( $this->items, $options );

			if ( is_array( $apply ) ) {
				foreach ( $apply as $key => $placement ) {

					if ( ! isset( $css[ $placement['selector'] ] ) ) {
						$css[ $placement['selector'] ] = array();
					}

					$css[ $placement['selector'] ][ $placement['property'] ] = $options[ $key ];
				}
			}

			if ( ! empty( $css ) ) {

			    // able to filter css properties
			    $css = apply_filters('yoast_seo_amp_css_build', $css);

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
