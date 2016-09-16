<?php
/**
 * @package     YoastSEO_AMP_Glue\Options
 * @author      Andrew Taylor
 * @license     GPL-2.0+
 */

if ( ! class_exists( 'YoastSEO_AMP_Customizer' ) ) {

	class YoastSEO_AMP_Customizer {

		/** @var string Name of the option in the database */
		private $option_name = 'wpseo_amp';

		/** @var array Option defaults */
		private $defaults = array(
			'version'                 => 1,
			'amp_site_icon'           => '',
			'default_image'           => '',
			'header-color'            => '',
			'headings-color'          => '',
			'text-color'              => '',
			'meta-color'              => '',
			'link-color'              => '',
			'link-color-hover'        => '',
			'underline'               => 'underline',
			'blockquote-text-color'   => '',
			'blockquote-bg-color'     => '',
			'blockquote-border-color' => '',
			'extra-css'               => '',
			'extra-head'              => '',
		);

		/** @var self Class instance */
		private static $instance;

		/**
		 * @return YoastSEO_AMP_Customizer
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		private function __construct() {
			add_action( 'customize_register', array( $this, 'amp_customizer_settings' ) );
			add_action( 'amp_post_template_footer', array( $this, 'amp_customizer_preview_enqueue' ) );
			add_action( 'customize_controls_enqueue_scripts', array( $this, 'amp_customizer_controls_enqueue' ) );
		}

		/**
		 * Sanitize link underline option
		 *
		 * @param string $input Raw input.
		 *
		 * @return string Sanitized input.
		 */
		public function sanitize_link_underline( $input ) {
			return ( 'underline' === $input ) ? $input : 'none';
		}

		/**
		 * Sanitize extra CSS
		 *
		 * @param string $input Raw input.
		 *
		 * @return string Sanitized input.
		 */
		public function sanitize_css( $input ) {
			$extra_css = strip_tags( $input );
			$extra_css = wp_check_invalid_utf8( $extra_css );
			$extra_css = _wp_specialchars( $extra_css, ENT_NOQUOTES );

			return $extra_css;
		}

		/**
		 * Sanitize extra <head> code
		 *
		 * @param string $input Raw input.
		 *
		 * @return string Sanitized input.
		 */
		public function sanitize_extra_head( $input ) {
			// Only allow meta and link tags in head.
			return strip_tags( $input, '<link><meta>' );
		}

		/**
		 * Validate extra <head> code
		 *
		 * @param object $validity validity object.
		 * @param mixed  $value    user inputted value.
		 *
		 * @return object $validity
		 */
		public function validate_extra_head( $validity, $value ) {
			if ( ! empty( $value ) ) {
				if ( 1 === preg_match( '/<[^link|meta]/', $value ) ){
					$validity->add( 'invalid-code', sprintf( esc_html( __( 'Only %1$s and %2$s tags are allowed', 'wordpress-seo' ) ), '<link>', '<meta>' ) );
				}
			}

			return $validity;
		}

		/**
		 * Validate the Amp site icon
		 *
		 * @param object $validity validity object.
		 * @param mixed  $value    user inputted value.
		 *
		 * @return object $validity
		 */
		function sanitize_amp_site_icon( $validity, $value ) {
			if ( ! empty( $value ) ) {
				$image_atts = wp_get_attachment_metadata( $value );
				if ( false !== $image_atts ) {
					if ( $image_atts['width'] < 32 || $image_atts['height'] < 32 ){
						$validity->add( 'image-size', __( 'The amp icon needs to be at least 32px &times; 32px', 'wordpress-seo' ) );
					}
					if ( $image_atts['width'] !== $image_atts['height'] ){
						$validity->add( 'image-size', __( 'The amp icon needs to have the same width and height', 'wordpress-seo' ) );
					}
				}
			}

			return $validity;
		}

		/**
		 * Validate the Amp default image
		 *
		 * @param object $validity validity object.
		 * @param mixed  $value    user inputted value.
		 *
		 * @return object $validity
		 */
		function sanitize_amp_default_image( $validity, $value ) {
			if ( ! empty( $value ) ) {
				$image_atts = wp_get_attachment_metadata( $value );
				if ( false !== $image_atts && $image_atts['width'] < 696 ) {
						$validity->add( 'image-size', __( 'The amp default image needs to be at least 696px wide', 'wordpress-seo' ) );
				}
			}

			return $validity;
		}

		/**
		 * Add AMP Customizer panel, sections and settings/controls
		 *
		 * @param object $wp_customize an instance of the WP_Customize_Manager class.
		 */
		public function amp_customizer_settings( $wp_customize ) {
			$default_labels = array(
				// Design settings
				'amp_site_icon'           => __( 'AMP icon', 'wordpress-seo' ),
				'default_image'           => __( 'Default image', 'wordpress-seo' ),
				'underline'               => __( 'Link underline', 'wordpress-seo' ),

				// Color settings
				'header-color'            => __( 'AMP Header color', 'wordpress-seo' ),
				'headings-color'          => __( 'Title color', 'wordpress-seo' ),
				'text-color'              => __( 'Text color', 'wordpress-seo' ),
				'meta-color'              => __( 'Post meta info color', 'wordpress-seo' ),
				'link-color'              => __( 'Link text color', 'wordpress-seo' ),
				'link-color-hover'        => __( 'Link hover color', 'wordpress-seo' ),
				'blockquote-text-color'   => __( 'Blockquote text color', 'wordpress-seo' ),
				'blockquote-bg-color'     => __( 'Blockquote background color', 'wordpress-seo' ),
				'blockquote-border-color' => __( 'Blockquote border color', 'wordpress-seo' ),

				// Advanced settings
				'extra-css'               => __( 'Extra CSS', 'wordpress-seo' ),
				'extra-head'              => sprintf( esc_html( __( 'Extra code in %s', 'wordpress-seo' ) ), '&lt;head&gt;' ),
			);

			/**
			 * Adds a panel for the AMP settings
			 *
			 * @link https://developer.wordpress.org/themes/advanced-topics/customizer-api/#panels
			 */
			$wp_customize->add_panel( 'wpseo_amp_settings', array(
				'title'       => __( 'AMP Settings', 'wordpress-seo' ),
				'description' => '<p>' . __( 'Options for the Yoast AMP SEO glue plugin.', 'wordpress-seo' ) . '</p>',
				'priority'    => 160,
			) );

			/*
			 * Add a design section to the AMP settings panel.
			 *
			 * @link https://developer.wordpress.org/themes/advanced-topics/customizer-api/#sections
			 */
			$wp_customize->add_section(
			// Use a unique, descriptive section slug to avoid conflicts.
				'wpseo_amp_design_settings',
				array(
					'title'           => __( 'AMP Design Settings', 'wordpress-seo' ),
					// Add the section to our custom panel.
					'panel'           => 'wpseo_amp_settings',
					// Only display the section if previewing an Amp supported post
					'active_callback' => array( $this, 'amp_post_support_callback' ),
				)
			);

			/**
			 * Add a field to the Customizer for the Amp site icon.
			 *
			 * @link https://developer.wordpress.org/themes/advanced-topics/customizer-api/#core-custom-controls
			 */
			$wp_customize->add_setting( $this->option_name . '[amp_site_icon]', array(
				'capability'        => 'manage_options',
				'type'              => 'theme_mod',
				'sanitize_callback' => 'absint',
				// Reload the entire page to get the new icon
				'transport'         => 'reload',
				'validate_callback' => array( $this, 'sanitize_amp_site_icon' ),
			) );

			$wp_customize->add_control(
				new WP_Customize_Media_Control(
					$wp_customize,
					$this->option_name . '[amp_site_icon]',
					array(
						'label'       => $default_labels['amp_site_icon'],
						'description' => __( 'Must be at least 32px &times; 32px', 'wordpress-seo' ),
						'section'     => 'wpseo_amp_design_settings',
						'mime_type'   => 'image',
						'width'       => 32,
						'height'      => 32,
					)
				)
			);

			/**
			 * Add a field to the Customizer for the Amp default image.
			 *
			 * @link https://developer.wordpress.org/themes/advanced-topics/customizer-api/#core-custom-controls
			 */
			$wp_customize->add_setting( $this->option_name . '[default_image]', array(
				'capability'        => 'manage_options',
				'type'              => 'theme_mod',
				'sanitize_callback' => 'absint',
				// Reload the entire page to get the default image
				'transport'         => 'reload',
				'validate_callback' => array( $this, 'sanitize_amp_default_image' ),
			) );

			$wp_customize->add_control(
				new WP_Customize_Media_Control(
					$wp_customize,
					$this->option_name . '[default_image]',
					array(
						'label'       => $default_labels['default_image'],
						'description' => __( 'The image must be at least 696px wide.', 'wordpress-seo' ),
						'section'     => 'wpseo_amp_design_settings',
						'mime_type'   => 'image',
						'width'       => 696,
					)
				)
			);

			/*
			 * Bulk add color a field for each color setting to the Customizer.
			 */
			foreach ( $default_labels as $key => $label ) {
				// Skip items that aren't colors
				if ( false === stripos( $key, 'color' ) ) {
					continue;
				}

				$option_name = $this->option_name . '[' . $key . ']';
				$default     = $this->defaults[ $key ];

				// Register each setting with the Customizer
				$wp_customize->add_setting( $option_name, array(
					'capability'        => 'manage_options',
					'type'              => 'option',
					'default'           => $default,
					'sanitize_callback' => 'sanitize_hex_color',
					'transport'         => 'postMessage',
				) );

				// Add a Customizer control for each setting.
				$wp_customize->add_control(
					new WP_Customize_Color_Control(
						$wp_customize,
						$option_name,
						array(
							'priority' => 10,
							'section'  => 'wpseo_amp_design_settings',
							'label'    => $label,
							'default'  => $default,
						)
					)
				);

			}

			/**
			 * Add a field to the Customizer for the link underline setting.
			 */
			$wp_customize->add_setting( $this->option_name . '[underline]', array(
				'capability'        => 'manage_options',
				'type'              => 'option',
				'default'           => 'underline',
				'sanitize_callback' => array( $this, 'sanitize_link_underline' ),
				'transport'         => 'postMessage',
			) );

			$wp_customize->add_control( $this->option_name . '[underline]', array(
					'type'    => 'radio',
					'section' => 'wpseo_amp_design_settings',
					'label'   => $default_labels['underline'],
					'choices' => array(
						'underline' => __( 'Enabled', 'wordpress-seo' ),
						'none' => __( 'Disabled', 'wordpress-seo' ),
					),
				)
			);


			/*
			 * Add an advanced section to the AMP settings panel.
			 *
			 * @link https://developer.wordpress.org/themes/advanced-topics/customizer-api/#sections
			 */
			$wp_customize->add_section(
			// Use a unique, descriptive section slug to avoid conflicts.
				'wpseo_amp_advanced_settings',
				array(
					'title'           => __( 'AMP Advanced Settings', 'wordpress-seo' ),
					// Add the section to our custom panel.
					'panel'           => 'wpseo_amp_settings',
					// Only display the section if previewing an Amp supported post
					'active_callback' => array( $this, 'amp_post_support_callback' ),
				)
			);

			/**
			 * Add a field to the Customizer for the custom CSS setting.
			 */
			$wp_customize->add_setting( $this->option_name . '[extra-css]', array(
				'capability'        => 'manage_options',
				'type'              => 'option',
				'default'           => '',
				'sanitize_callback' => array( $this, 'sanitize_css' ),
				// full page reload to get inline CSS
				'transport'         => 'reload',
			) );

			$wp_customize->add_control( $this->option_name . '[extra-css]', array(
					'type'    => 'textarea',
					'section' => 'wpseo_amp_advanced_settings',
					'label'   => $default_labels['extra-css'],
				)
			);

			/**
			 * Add a field to the Customizer for the extra <head> code setting.
			 */
			$wp_customize->add_setting( $this->option_name . '[extra-head]', array(
				'capability'        => 'manage_options',
				'type'              => 'option',
				'default'           => '',
				'sanitize_callback' => array( $this, 'sanitize_extra_head' ),
				'validate_callback' => array( $this, 'validate_extra_head' ),
				// full page reload to get updated <head>
				'transport'         => 'reload',
			) );

			$wp_customize->add_control( $this->option_name . '[extra-head]', array(
					'type'    => 'textarea',
					'section' => 'wpseo_amp_advanced_settings',
					'label'   => $default_labels['extra-head'],
				)
			);

			/*
			 * Add a section to the AMP settings panel if settings are unavailable die to viewing a non-amp post.
			 *
			 * @link https://developer.wordpress.org/themes/advanced-topics/customizer-api/#sections
			 */
			$wp_customize->add_section(
			// Use a unique, descriptive section slug to avoid conflicts.
				'wpseo_amp_settings_unavailable',
				array(
					'title'           => __( 'AMP Settings Unavailable', 'wordpress-seo' ),
					'description'     => '<h3>' . __( 'AMP settings are not available for this post type. Try navigating to a standard post.', 'wordpress-seo' ) . '</h3>',
					// Add the section to our custom panel.
					'panel'           => 'wpseo_amp_settings',
					// Only display the section if previewing a post without Amp support
					'active_callback' => array( $this, 'amp_post_anti_support_callback' ),
				)
			);

			// We need a control in the section so it displays but we'll keep it hidden
			$wp_customize->add_setting( '_amp_hidden', array(
				'capability' => 'manage_options',
				'type'       => 'option',
				'default'    => false,
			) );

			$wp_customize->add_control( '_amp_hidden', array(
				'type'     => 'hidden',
				'priority' => 10,
				'section'  => 'wpseo_amp_settings_unavailable',
			) );

		}

		/*
		 * Returns true if the current post type has Amp support
		 * otherwise, returns false.
		 *
		 * @return bool
		 */
		public function amp_post_support_callback() {
			$amp_post_types = get_post_types_by_support( 'amp' );

			// bail on archive and other non-singular templates
			if( !is_singular( $amp_post_types) ){
				return false;
			}

			$current_post_type = get_post_type();

			return in_array( $current_post_type, $amp_post_types );

		}

		/*
		 * Returns false if the current post type has Amp support
		 * otherwise, returns true.
		 *
		 * @return bool
		 */
		public function amp_post_anti_support_callback() {
			return ! $this->amp_post_support_callback();

		}

		/*
		 * Enqueue JavaScript for use with the Customizer postMessage transport.
		 *
		 * if_customize_preview is used so we don't enqueue this script outside of the Customizer, where it isn't needed.
		 *
		 * @link https://developer.wordpress.org/themes/advanced-topics/customizer-api/#using-postmessage-for-improved-setting-previewing
		 */
		public function amp_customizer_preview_enqueue() {
			if ( is_customize_preview() ) {
				wp_enqueue_script( 'wpseo-amp-customizer-preview-script', YoastSEO_AMP_PLUGIN_DIR_URL . '/assets/js/customizer-preview.js', array(
					'jquery',
					'customize-preview',
				), false, true );

				if ( ! class_exists( 'YoastSEO_AMP_Frontend' ) ) {
					require_once( YoastSEO_AMP_PLUGIN_DIR . 'classes/class-frontend.php' );
				}

				$wpseo_css_selectors = YoastSEO_AMP_Frontend::get_css_selectors();

				wp_localize_script( 'wpseo-amp-customizer-preview-script', 'wpseoCSSselectors', $wpseo_css_selectors );
			}
		}

		/*
		 * Enqueue JavaScript for use with the Customizer controls.
		 *
		 * @link https://developer.wordpress.org/themes/advanced-topics/customizer-api/#using-postmessage-for-improved-setting-previewing
		 */
		public function amp_customizer_controls_enqueue() {
			wp_enqueue_script( 'wpseo-amp-customizer-controls-script', YoastSEO_AMP_PLUGIN_DIR_URL . 'assets/js/customizer-controls.js', array(
				'jquery',
				'customize-controls',
			), false, true );
		}
	}
}