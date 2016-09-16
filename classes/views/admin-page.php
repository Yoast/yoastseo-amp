<?php

if ( ! defined( 'WPSEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

$yform = Yoast_Form::get_instance();
$yform->admin_header( true, 'wpseo_amp', false, 'wpseo_amp_settings' );

?>

	<h2 class="nav-tab-wrapper" id="wpseo-tabs">
		<a class="nav-tab" id="posttypes-tab" href="#top#posttypes"><?php echo esc_html( __( 'Post types', 'wordpress-seo' ) ); ?></a>
		<a class="nav-tab" id="design-tab" href="#top#design"><?php echo esc_html( __( 'Design', 'wordpress-seo' ) ); ?></a>
		<a class="nav-tab" id="analytics-tab" href="#top#analytics"><?php echo esc_html( __( 'Analytics', 'wordpress-seo' ) ); ?></a>
	</h2>

	<div class="tabwrapper">

		<div id="posttypes" class="wpseotab">
			<h2><?php echo esc_html( __( 'Post types that have AMP support', 'wordpress-seo' ) ); ?></h2>
			<p><?php echo esc_html( __( 'Generally you\'d want this to be your news post types.', 'wordpress-seo' ) ); ?><br/>
			   <?php echo esc_html( __( 'Post is enabled by default, feel free to enable any of them.', 'wordpress-seo' ) ); ?></p>
			<?php

			$post_types = apply_filters( 'wpseo_sitemaps_supported_post_types', get_post_types( array( 'public' => true ), 'objects' ) );
			if ( is_array( $post_types ) && $post_types !== array() ) {
				foreach ( $post_types as $pt ) {
					$yform->toggle_switch(
						'post_types-' . $pt->name . '-amp',
						array(
							'on' => __( 'Enabled', 'wordpress-seo' ),
							'off' => __( 'Disabled', 'wordpress-seo' )
						),
						$pt->labels->name . ' (<code>' . $pt->name . '</code>)'
					);
				}
			}

			?>
		</div>

		<div id="design" class="wpseotab">
			<h2>
				<a href="<?php echo 'customize.php?autofocus[panel]=amp_settings&return=' . urlencode( add_query_arg( 'page', 'wpseo_amp#top#design', get_admin_url( null, 'admin.php' ) ) );?>">
					<?php _e( 'Click here to edit the AMP design in the Customizer', 'wordpress-seo' ); ?>
				</a>
			</h2>

		</div>

		<div id="analytics" class="wpseotab">
			<h2><?php echo esc_html( __( 'AMP Analytics', 'wordpress-seo' ) ); ?></h2>

			<?php
			if ( class_exists( 'Yoast_GA_Options' ) ) {
				echo '<p>', esc_html( __( 'Because your Google Analytics plugin by Yoast is active, your AMP pages will also be tracked.', 'wordpress-seo' ) ), '<br>';
				$UA = Yoast_GA_Options::instance()->get_tracking_code();
				if ( $UA === null ) {
					echo esc_html( __( 'Make sure to connect your Google Analytics plugin properly.', 'wordpress-seo' ) );
				} else {
					echo sprintf( esc_html( __( 'Pageviews will be tracked using the following account: %s.', 'wordpress-seo' ) ), '<code>' . $UA . '</code>' );
				}

				echo '</p>';

				echo '<p>', esc_html( __( 'Optionally you can override the default AMP tracking code with your own by putting it below:', 'wordpress-seo' ) ), '</p>';
				$yform->textarea( 'analytics-extra', __( 'Analytics code', 'wordpress-seo' ), array(
					'rows' => 5,
					'cols' => 100
				) );
			} else {
				echo '<p>', esc_html( __( 'Optionally add a valid google analytics tracking code.', 'wordpress-seo' ) ), '</p>';
				$yform->textarea( 'analytics-extra', __( 'Analytics code', 'wordpress-seo' ), array(
					'rows' => 5,
					'cols' => 100
				) );
			}
			?>
		</div>
	</div>

<?php

$yform->admin_footer();
