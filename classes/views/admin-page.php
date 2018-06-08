<?php
/**
 * YoastSEO_AMP_Glue plugin file.
 *
 * @package   YoastSEO_AMP_Glue
 * @copyright 2016 Yoast BV
 * @license   GPL-2.0+
 */

if ( ! defined( 'WPSEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

$yoast_amp_yform = Yoast_Form::get_instance();
$yoast_amp_yform->admin_header( true, 'wpseo_amp', false, 'wpseo_amp_settings' );

?>

	<h2 class="nav-tab-wrapper" id="wpseo-tabs">
		<a class="nav-tab" id="posttypes-tab" href="#top#posttypes"><?php esc_html_e( 'Post types', 'yoastseo-amp' ); ?></a>
		<a class="nav-tab" id="design-tab" href="#top#design"><?php esc_html_e( 'Design', 'yoastseo-amp' ); ?></a>
		<a class="nav-tab" id="analytics-tab" href="#top#analytics"><?php esc_html_e( 'Analytics', 'yoastseo-amp' ); ?></a>
	</h2>

	<div class="tabwrapper">

		<div id="posttypes" class="wpseotab">
			<h2><?php esc_html_e( 'Post types that have AMP support', 'yoastseo-amp' ); ?></h2>
			<p><?php esc_html_e( 'Generally you\'d want this to be your news post types.', 'yoastseo-amp' ); ?><br/>
				<?php esc_html_e( 'Post is enabled by default, feel free to enable any of them.', 'yoastseo-amp' ); ?></p>
			<?php

			$yoast_amp_post_types = apply_filters( 'wpseo_sitemaps_supported_post_types', get_post_types( array( 'public' => true ), 'objects' ) );

			// Allow specific AMP post type overrides, especially needed for Page support.
			$yoast_amp_post_types = apply_filters( 'wpseo_amp_supported_post_types', $yoast_amp_post_types );

			if ( is_array( $yoast_amp_post_types ) && $yoast_amp_post_types !== array() ) {
				foreach ( $yoast_amp_post_types as $yoast_amp_pt ) {
					$yoast_amp_yform->toggle_switch(
						'post_types-' . $yoast_amp_pt->name . '-amp',
						array(
							'on'  => __( 'Enabled', 'yoastseo-amp' ),
							'off' => __( 'Disabled', 'yoastseo-amp' ),
						),
						$yoast_amp_pt->labels->name . ' (<code>' . $yoast_amp_pt->name . '</code>)'
					);
				}
			}

			if ( ! post_type_supports( 'page', AMP_QUERY_VAR ) ) :
				?>
				<br>
				<strong><?php esc_html_e( 'Please note:', 'yoastseo-amp' ); ?></strong>
				<?php esc_html_e( 'Currently pages are not supported by the AMP plugin.', 'yoastseo-amp' ); ?>
				<?php
			endif;
			?>
			</p>
		</div>

		<div id="design" class="wpseotab">
			<h3><?php esc_html_e( 'Images', 'yoastseo-amp' ); ?></h3>

			<?php
			$yoast_amp_yform->media_input( 'amp_site_icon', __( 'AMP icon', 'yoastseo-amp' ) );
			?>
			<p class="desc"><?php esc_html_e( 'Must be at least 32px &times; 32px', 'yoastseo-amp' ); ?></p>
			<br/>

			<?php
			$yoast_amp_yform->media_input( 'default_image', __( 'Default image', 'yoastseo-amp' ) );
			?>
			<p class="desc"><?php esc_html_e( 'Used when a post doesn\'t have an image associated with it.', 'yoastseo-amp' ); ?>
				<br><?php esc_html_e( 'The image must be at least 696px wide.', 'yoastseo-amp' ); ?></p>
			<br/>

			<h3><?php esc_html_e( 'Content colors', 'yoastseo-amp' ); ?></h3>

			<?php
			$this->color_picker( 'header-color', __( 'AMP Header color', 'yoastseo-amp' ) );
			$this->color_picker( 'headings-color', __( 'Title color', 'yoastseo-amp' ) );
			$this->color_picker( 'text-color', __( 'Text color', 'yoastseo-amp' ) );
			$this->color_picker( 'meta-color', __( 'Post meta info color', 'yoastseo-amp' ) );
			?>
			<br/>

			<h3><?php esc_html_e( 'Links', 'yoastseo-amp' ); ?></h3>
			<?php
			$this->color_picker( 'link-color', __( 'Text color', 'yoastseo-amp' ) );
			$this->color_picker( 'link-color-hover', __( 'Hover color', 'yoastseo-amp' ) );
			?>

			<?php
			$yoast_amp_yform->light_switch(
				'underline',
				__( 'Underline', 'yoastseo-amp' ),
				array(
					__( 'Underline', 'yoastseo-amp' ),
					__( 'No underline', 'yoastseo-amp' ),
				)
			);
			?>

			<br/>

			<h3><?php esc_html_e( 'Blockquotes', 'yoastseo-amp' ); ?></h3>
			<?php
			$this->color_picker( 'blockquote-text-color', __( 'Text color', 'yoastseo-amp' ) );
			$this->color_picker( 'blockquote-bg-color', __( 'Background color', 'yoastseo-amp' ) );
			$this->color_picker( 'blockquote-border-color', __( 'Border color', 'yoastseo-amp' ) );
			?>
			<br/>

			<h3><?php esc_html_e( 'Extra CSS', 'yoastseo-amp' ); ?></h3>
			<?php
			$yoast_amp_yform->textarea(
				'extra-css',
				__( 'Extra CSS', 'yoastseo-amp' ),
				array(
					'rows' => 5,
					'cols' => 100,
				)
			);
			?>

			<br/>

			<h3>
				<?php
				/* translators: %s: 'head' - as in HTML head - wrapped in <code> tags. */
				printf( esc_html__( 'Extra code in %s', 'yoastseo-amp' ), '<code>&lt;head&gt;</code>' );
				?>
			</h3>
			<p>
				<?php
				printf(
					/* translators: 1: 'meta'; 2: 'link' - both wrapped in <code> tags. */
					esc_html__( 'Only %1$s and %2$s tags are allowed, other tags will be removed automatically.', 'yoastseo-amp' ),
					'<code>meta</code>', '<code>link</code>'
				);
				?>
			</p>
			<?php
			$yoast_amp_yform->textarea(
				'extra-head',
				__( 'Extra code', 'yoastseo-amp' ),
				array(
					'rows' => 5,
					'cols' => 100,
				)
			);
			?>

		</div>

		<div id="analytics" class="wpseotab">
			<h2><?php esc_html_e( 'AMP Analytics', 'yoastseo-amp' ); ?></h2>

			<?php
			if ( class_exists( 'Yoast_GA_Options' ) ) {
				echo '<p>', esc_html__( 'Because your Google Analytics plugin by Yoast is active, your AMP pages will also be tracked.', 'yoastseo-amp' ), '<br>';
				$yoastseo_amp_ga_tracking_code = Yoast_GA_Options::instance()->get_tracking_code();
				if ( $yoastseo_amp_ga_tracking_code === null ) {
					esc_html_e( 'Make sure to connect your Google Analytics plugin properly.', 'yoastseo-amp' );
				}
				else {
					printf(
						/* translators: %s: google analytics tracking code. */
						esc_html__( 'Pageviews will be tracked using the following account: %s.', 'yoastseo-amp' ),
						'<code>' . esc_html( $yoastseo_amp_ga_tracking_code ) . '</code>'
					);
				}

				echo '</p>';

				echo '<p>', esc_html__( 'Optionally you can override the default AMP tracking code with your own by putting it below:', 'yoastseo-amp' ), '</p>';
				$yoast_amp_yform->textarea(
					'analytics-extra',
					__( 'Analytics code', 'yoastseo-amp' ),
					array(
						'rows' => 5,
						'cols' => 100,
					)
				);
			}
			else {
				echo '<p>', esc_html__( 'Optionally add a valid google analytics tracking code.', 'yoastseo-amp' ), '</p>';
				$yoast_amp_yform->textarea(
					'analytics-extra',
					__( 'Analytics code', 'yoastseo-amp' ),
					array(
						'rows' => 5,
						'cols' => 100,
					)
				);
			}
			?>
		</div>
	</div>

<?php

$yoast_amp_yform->admin_footer();
