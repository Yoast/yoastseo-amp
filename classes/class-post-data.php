<?php
/**
 * @package     YoastSEO_AMP_Glue\Frontend
 * @author      Joost de Valk
 * @copyright   2016 Yoast BV
 * @license     GPL-2.0+
 */

if ( ! class_exists( 'YoastSEO_AMP_Postdata' ) ) {
	/**
	 * This class improves upon the AMP output by the default WordPress AMP plugin using Yoast SEO metadata.
	 */
	class YoastSEO_AMP_Postdata {

		/**
		 * @var WPSEO_Frontend
		 */
		private $front;

		/**
		 * @var array
		 */
		private $options;

		/**
		 * YoastSEO_AMP_Frontend constructor.
		 */
		public function __construct() {
			$this->front = WPSEO_Frontend::get_instance();

			add_filter( 'amp_post_template_data', array( $this, 'fix_amp_post_data' ) );
			add_filter( 'amp_post_template_metadata', array( $this, 'fix_amp_post_metadata' ), 10, 2 );
			$this->options = array_merge( WPSEO_Options::get_all(), YoastSEO_AMP_Options::get() );
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
		 * Builds the organization object if needed.
		 *
		 * @param array $metadata
		 */
		private function build_organization_object( &$metadata ) {
			// While it's using the blog name, it's actually outputting the company name.
			if ( ! empty( $this->options['company_name'] ) ) {
				$metadata['publisher']['name'] = $this->options['company_name'];
			}

			// The logo needs to be 600px wide max, 60px high max.
			$logo = $this->get_image_object( $this->options['company_logo'], array( 600, 60 ) );
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
