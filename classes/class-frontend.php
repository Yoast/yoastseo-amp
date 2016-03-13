<?php
/**
 * @package     YoastSEO_AMP_Glue\Frontend
 * @author      Joost de Valk
 * @copyright   2016 Yoast BV
 * @license     GPL-2.0+
 */

if ( ! class_exists( 'YoastSEO_AMP_Frontend' ) ) {
	/**
	 * This class improves upon the AMP output by the default WordPress AMP plugin using Yoast SEO metadata.
	 */
	class YoastSEO_AMP_Frontend {

		/**
		 * @var array
		 */
		private $options;

		/**
		 * YoastSEO_AMP_Frontend constructor.
		 */
		public function __construct() {
			$this->options = array_merge( WPSEO_Options::get_all(), YoastSEO_AMP_Options::get() );

			add_action( 'amp_init', array( $this, 'post_types' ) );
			add_action( 'wp', array( $this, 'boot' ) );
		}

		/**
		 * Start all the AMP filters and design classes
		 */
		public function boot() {
			if ( ! is_amp_endpoint() ) {
				return;
			}

			add_filter( 'amp_content_sanitizers', array( $this, 'add_sanitizer' ) );

			require_once 'class-post-data.php';
			new YoastSEO_AMP_Postdata();

			require_once 'class-analytics.php';
			new YoastSEO_AMP_Analytics();

			require_once 'class-design.php';
			new YoastSEO_AMP_Design();
		}

		/**
		 * Add our own sanitizer to the array of sanitizers
		 *
		 * @param array $sanitizers
		 *
		 * @return array
		 */
		public function add_sanitizer( $sanitizers ) {
			require_once 'class-sanitizer.php';

			$sanitizers['Yoast_AMP_Blacklist_Sanitizer'] = array();

			return $sanitizers;
		}

		/**
		 * Make AMP work for all the post types we want it for
		 */
		public function post_types() {
			$post_types = get_post_types( array( 'public' => true ), 'objects' );
			if ( is_array( $post_types ) && $post_types !== array() ) {
				foreach ( $post_types as $pt ) {
					if ( ! isset( $this->options[ 'post_types-' . $pt->name . '-amp' ] ) ) {
						continue;
					}
					if ( $this->options[ 'post_types-' . $pt->name . '-amp' ] === 'on' ) {
						add_post_type_support( $pt->name, AMP_QUERY_VAR );
						return;
					}
					if ( 'post' === $pt->name ) {
						add_action( 'wp', array( $this, 'disable_amp_for_posts' ) );
						return;
					}
					remove_post_type_support( $pt->name, AMP_QUERY_VAR );
				}
			}
		}

		/**
		 * Disables AMP for posts specifically, run later because of AMP plugin internals
		 */
		public function disable_amp_for_posts() {
			remove_post_type_support( 'post', AMP_QUERY_VAR );
		}
	}
}
