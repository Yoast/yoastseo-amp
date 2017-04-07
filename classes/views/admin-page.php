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
	</h2>

	<div class="tabwrapper">

		<div id="posttypes" class="wpseotab">
			<h2><?php echo esc_html( __( 'Post types that have AMP support', 'wordpress-seo' ) ); ?></h2>
			<p><?php echo esc_html( __( 'Generally you\'d want this to be your news post types.', 'wordpress-seo' ) ); ?><br/>
			   <?php echo esc_html( __( 'Post is enabled by default, feel free to enable any of them.', 'wordpress-seo' ) ); ?></p>
			<?php

			$post_types = apply_filters( 'wpseo_sitemaps_supported_post_types', get_post_types( array( 'public' => true ), 'objects' ) );

			// Allow specific AMP post type overrides, especially needed for Page support.
			$post_types = apply_filters( 'wpseo_amp_supported_post_types', $post_types );

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

			if ( ! post_type_supports( 'page', AMP_QUERY_VAR ) ):
				?>
                <br>
                <strong><?php echo esc_html( __( 'Please note:', 'wordpress-seo' ) ); ?></strong>
				<?php echo esc_html( __( 'Currently pages are not supported by the AMP plugin.', 'wordpress-seo' ) ); ?>
				<?php
			endif;
			?>
			</p>
		</div>

		<div id="design" class="wpseotab">
			<h3><?php echo esc_html( __( 'Images', 'wordpress-seo' ) ); ?></h3>

			<?php
			$yform->media_input( 'amp_site_icon', __( 'AMP icon', 'wordpress-seo' ) ); ?>
			<p class="desc"><?php echo esc_html( __( 'Must be at least 32px &times; 32px', 'wordpress-seo' ) ); ?></p>
			<br/>

			<?php
			$yform->media_input( 'default_image', __( 'Default image', 'wordpress-seo' ) ); ?>
			<p class="desc"><?php echo esc_html( __( 'Used when a post doesn\'t have an image associated with it.', 'wordpress-seo' ) ); ?>
				<br><?php echo esc_html( __( 'The image must be at least 696px wide.', 'wordpress-seo' ) ) ?></p>
			<br/>

			<h3><?php echo esc_html( __( 'Content colors', 'wordpress-seo' ) ); ?></h3>

			<?php
			$this->color_picker( 'header-color', __( 'AMP Header color', 'wordpress-seo' ) );
			$this->color_picker( 'headings-color', __( 'Title color', 'wordpress-seo' ) );
			$this->color_picker( 'text-color', __( 'Text color', 'wordpress-seo' ) );
			$this->color_picker( 'meta-color', __( 'Post meta info color', 'wordpress-seo' ) );
			?>
			<br/>

			<h3><?php echo esc_html( __( 'Links', 'wordpress-seo' ) ); ?></h3>
			<?php
			$this->color_picker( 'link-color', __( 'Text color', 'wordpress-seo' ) );
			$this->color_picker( 'link-color-hover', __( 'Hover color', 'wordpress-seo' ) );
			?>

			<?php $yform->light_switch( 'underline', __( 'Underline', 'wordpress-seo' ), array(
				__( 'Underline', 'wordpress-seo' ),
				__( 'No underline', 'wordpress-seo' )
			) ); ?>

			<br/>

			<h3><?php echo esc_html( __( 'Blockquotes', 'wordpress-seo' ) ); ?></h3>
			<?php
			$this->color_picker( 'blockquote-text-color', __( 'Text color', 'wordpress-seo' ) );
			$this->color_picker( 'blockquote-bg-color', __( 'Background color', 'wordpress-seo' ) );
			$this->color_picker( 'blockquote-border-color', __( 'Border color', 'wordpress-seo' ) );
			?>
			<br/>

			<h3><?php echo esc_html( __( 'Extra CSS', 'wordpress-seo' ) ); ?></h3>
			<?php $yform->textarea( 'extra-css', __( 'Extra CSS', 'wordpress-seo' ), array(
				'rows' => 5,
				'cols' => 100
			) ); ?>

			<br/>

			<h3><?php printf( esc_html( __( 'Extra code in %s', 'wordpress-seo' ) ), '<code>&lt;head&gt;</code>' ); ?></h3>
			<p><?php echo sprintf( esc_html( __( 'Only %s and %s tags are allowed, other tags will be removed automatically.', 'wordpress-seo' ) ), '<code>meta</code>', '<code>link</code>' ) ?></p>
			<?php $yform->textarea( 'extra-head', __( 'Extra code', 'wordpress-seo' ), array(
				'rows' => 5,
				'cols' => 100
			) ); ?>

		</div>
	</div>

<?php

$yform->admin_footer();
