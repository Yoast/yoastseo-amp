<?php
/**
 * @package     YoastSEO_AMP_Glue\Options
 * @author      Jip Moors
 * @copyright   2016 Yoast BV
 * @license     GPL-2.0+
 */

if ( ! class_exists( 'YoastSEO_AMP_Options' ) ) {

	class YoastSEO_AMP_Options {

		/** @var string Name of the option in the database */
		private $option_name = 'wpseo_amp';

		/** @var array Current options */
		private $options;

		/** @var array Option defaults */
		private $defaults = array(
			'version'                 => 1,
			'analytics-extra'         => '',
		);

		/** @var self Class instance */
		private static $instance;

		private function __construct() {
			// Register settings
			add_action( 'admin_init', array( $this, 'register_settings' ) );
		}

		/**
		 * Register the premium settings
		 */
		public function register_settings() {
			register_setting( 'wpseo_amp_settings', $this->option_name, array( $this, 'sanitize_options' ) );
		}

		/**
		 * Sanitize options
		 *
		 * @param $options
		 *
		 * @return mixed
		 */
		public function sanitize_options( $options ) {
			$options['version'] = 1;

			$options['analytics-extra'] = $this->sanitize_analytics_code( $options['analytics-extra'] );

			return $options;
		}

		/**
		 * @param string $source Raw input.
		 *
		 * @return string Sanitized code.
		 */
		private function sanitize_analytics_code( $source ) {

			$source = trim( $source );

			if ( empty( $source ) ) {
				return $source;
			}

			$code = $source;

			// Strip all tags, to verify JSON input.
			$json        = strip_tags( $code );
			$parsed_json = json_decode( $json, true );

			// Non-parsable JSON is always bad.
			if ( is_null( $parsed_json ) ) {
				return '';
			}

			$allowed_tags = strip_tags( $code, '<amp-analytics>' );

			// Strip JSON content so we can apply verified script tag.
			$tag = str_replace( $json, '', $allowed_tags );

			// If the tag doesn't occur in the code, the code is invalid.
			if ( false === strpos( $allowed_tags, '<amp-analytics' ) ) {
				return '';
			}

			$parts = explode( '><', $tag );
			$parts[0] .= '>';
			$parts[1] = '<' . $parts[1];

			// Rebuild with script tag and json content.
			array_splice( $parts, 1, null, array(
				'<script type="application/json">',
				trim( $json ),
				'</script>'
			) );

			return implode( "\n", $parts );
		}

		/**
		 * Get the options
		 *
		 * @return array
		 */
		public static function get() {

			$me = self::get_instance();
			$me->fetch_options();

			return $me->options;
		}

		/**
		 * @return YoastSEO_AMP_Options
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Collect options
		 *
		 * @SuppressWarnings("PMD.UnusedPrivateMethod")
		 */
		private function fetch_options() {
			$saved_options = $this->options;
			if ( ! is_array( $this->options ) ) {
				$saved_options = get_option( 'wpseo_amp' );

				// Apply defaults.
				$this->options = wp_parse_args( $saved_options, $this->defaults );
			}

			// Make sure all post types are present.
			$this->update_post_type_settings();

			// Save changes to database.
			if ( $this->options !== $saved_options ) {
				update_option( $this->option_name, $this->options );
			}
		}

		/**
		 * Get post types
		 */
		private function update_post_type_settings() {
			$post_type_names = array();
			$post_types      = get_post_types( array( 'public' => true ), 'objects' );

			if ( is_array( $post_types ) && $post_types !== array() ) {
				foreach ( $post_types as $post_type ) {
					if ( ! isset( $this->options[ 'post_types-' . $post_type->name . '-amp' ] ) ) {
						$this->options[ 'post_types-' . $post_type->name . '-amp' ] = 'off';
						if ( 'post' === $post_type->name ) {
							$this->options[ 'post_types-' . $post_type->name . '-amp' ] = 'on';
						}
					}

					$post_type_names[] = $post_type->name;
				}
			}
		}
	}
}