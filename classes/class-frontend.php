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
		 * @var WPSEO_Frontend
		 */
		private $front;

		/**
		 * @var array
		 */
		private $options;

		/**
		 * @var array
		 */
		private $wpseo_options;

		/**
		 * YoastSEO_AMP_Frontend constructor.
		 */
		public function __construct() {
			$this->set_options();

			add_action( 'amp_init', array( $this, 'post_types' ) );

			add_action( 'amp_post_template_css', array( $this, 'additional_css' ) );
			add_action( 'amp_post_template_head', array( $this, 'extra_head' ) );
			add_action( 'amp_post_template_footer', array( $this, 'extra_footer' ) );

			add_filter( 'amp_post_template_data', array( $this, 'fix_amp_post_data' ) );
			add_filter( 'amp_post_template_metadata', array( $this, 'fix_amp_post_metadata' ), 10, 2 );
			add_filter( 'amp_post_template_analytics', array( $this, 'analytics' ) );

			add_filter( 'amp_content_sanitizers', array( $this, 'add_sanitizer' ) );
		}

		private function set_options() {
			$this->wpseo_options = WPSEO_Options::get_all();
			$this->options       = YoastSEO_AMP_Options::get();
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
		 * If analytics tracking has been set, output it now.
		 *
		 * @param array $analytics
		 *
		 * @return array
		 */
		public function analytics( $analytics ) {
			if ( isset( $this->options['analytics-extra'] ) && ! empty( $this->options['analytics-extra'] ) ) {
				return $analytics;
			}

			if ( ! class_exists( 'Yoast_GA_Options' ) || Yoast_GA_Options::instance()->get_tracking_code() === null ) {
				return $analytics;
			}
			$UA = Yoast_GA_Options::instance()->get_tracking_code();

			$analytics['yst-googleanalytics'] = array(
				'type'        => 'googleanalytics',
				'attributes'  => array(),
				'config_data' => array(
					'vars'     => array(
						'account' => $UA
					),
					'triggers' => array(
						'trackPageview' => array(
							'on'      => 'visible',
							'request' => 'pageview',
						),
					),
				),
			);

			return $analytics;
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

		/**
		 * Fix the basic AMP post data
		 *
		 * @param array $data
		 *
		 * @return array
		 */
		public function fix_amp_post_data( $data ) {
			$data['canonical_url'] = $this->front->canonical( false );
			if ( ! empty( $this->options['amp_site_icon'] ) ) {
				$data['site_icon_url'] = $this->options['amp_site_icon'];
			}

			// If we are loading extra analytics, we need to load the module too.
			if ( ! empty( $this->options['analytics-extra'] ) ) {
				$data['amp_component_scripts']['amp-analytics'] = 'https://cdn.ampproject.org/v0/amp-analytics-0.1.js';
			}

			return $data;
		}

		/**
		 * Fix the AMP metadata for a post
		 *
		 * @param array $metadata
		 * @param WP_Post $post
		 *
		 * @return array
		 */
		public function fix_amp_post_metadata( $metadata, $post ) {
			$this->front = WPSEO_Frontend::get_instance();

			$this->build_organization_object( $metadata );

			$desc = $this->front->metadesc( false );
			if ( $desc ) {
				$metadata['description'] = $desc;
			}

			$image = isset( $metadata['image'] ) ? $metadata['image'] : null;

			$metadata['image'] = $this->get_image( $post, $image );
			$metadata['@type'] = $this->get_post_schema_type( $post );

			return $metadata;
		}

		/**
		 * Add additional CSS to the AMP output
		 */
		public function additional_css() {

			require 'views/additional-css.php';

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

			if ( ! empty( $this->options['extra-css'] ) ) {
				$safe_text = strip_tags($this->options['extra-css']);
				$safe_text = wp_check_invalid_utf8( $safe_text );
				$safe_text = _wp_specialchars( $safe_text, ENT_NOQUOTES );
				echo $safe_text;
			}
		}

		/**
		 * Outputs extra code in the head, if set
		 */
		public function extra_head() {
			$options = WPSEO_Options::get_option( 'wpseo_social' );

			if ( $options['twitter'] === true ) {
				WPSEO_Twitter::get_instance();
			}

			if ( $options['opengraph'] === true ) {
				$GLOBALS['wpseo_og'] = new WPSEO_OpenGraph;
			}

			do_action( 'wpseo_opengraph' );

			echo strip_tags( $this->options['extra-head'], '<link><meta>' );
		}

		/**
		 * Outputs analytics code in the footer, if set
		 */
		public function extra_footer() {
			echo $this->options['analytics-extra'];
		}

		/**
		 * Builds the organization object if needed.
		 *
		 * @param array $metadata
		 */
		private function build_organization_object( &$metadata ) {
			// While it's using the blog name, it's actually outputting the company name.
			if ( ! empty( $this->wpseo_options['company_name'] ) ) {
				$metadata['publisher']['name'] = $this->wpseo_options['company_name'];
			}

			// The logo needs to be 600px wide max, 60px high max.
			$logo = $this->get_image_object( $this->wpseo_options['company_logo'], array( 600, 60 ) );
			if ( is_array( $logo ) ) {
				$metadata['publisher']['logo'] = $logo;
			}
		}

		/**
		 * Builds an image object array from an image URL
		 *
		 * @param string $image_url
		 * @param string|array $size Optional. Image size. Accepts any valid image size, or an array of width
		 *                                     and height values in pixels (in that order). Default 'full'.
		 *
		 * @return array|false
		 */
		private function get_image_object( $image_url, $size = 'full' ) {
			if ( empty( $image_url ) ) {
				return false;
			}

			$image_id  = attachment_url_to_postid( $image_url );
			$image_src = wp_get_attachment_image_src( $image_id, $size );

			if ( is_array( $image_src ) ) {
				return array(
					'@type'  => 'ImageObject',
					'url'    => $image_src[0],
					'width'  => $image_src[1],
					'height' => $image_src[2]
				);
			}

			return false;
		}

		/**
		 * Retrieve the Schema.org image for the post
		 *
		 * @param WP_Post    $post
		 * @param array|null $image The currently set post image
		 *
		 * @return array
		 */
		private function get_image( $post, $image ) {
			$og_image = $this->get_image_object( WPSEO_Meta::get_value( 'opengraph-image', $post->ID ) );
			if ( is_array( $og_image ) ) {
				return $og_image;
			}

			// Posts without an image fail validation in Google, leading to Search Console errors
			if ( ! is_array( $image ) && isset( $this->options['default_image'] ) ) {
				return $this->get_image_object( $this->options['default_image'] );
			}

			return $image;
		}

		/**
		 * Gets the Schema.org type for the post, based on the post type.
		 *
		 * @param WP_Post $post
		 *
		 * @return string
		 */
		private function get_post_schema_type( $post ) {
			$type = 'WebPage';
			if ( 'post' === $post->post_type ) {
				$type = 'Article';
			}

			/**
			 * Filter: 'yoastseo_amp_schema_type' - Allow changing the Schema.org type for the post
			 *
			 * @api string $type The Schema.org type for the $post
			 *
			 * @param WP_Post $post
			 */
			$type = apply_filters( 'yoastseo_amp_schema_type', $type, $post );

			return $type;
		}
	}
}
