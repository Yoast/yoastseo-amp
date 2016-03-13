<?php
/**
 * @package     YoastSEO_AMP_Glue\Frontend
 * @author      Joost de Valk
 * @copyright   2016 Yoast BV
 * @license     GPL-2.0+
 */

if ( ! class_exists( 'YoastSEO_AMP_Analytics' ) ) {
	/**
	 * This class adds analytics tracking to AMP pages.
	 */
	class YoastSEO_AMP_Analytics {

		/**
		 * @var array
		 */
		private $options;

		/**
		 * The GA AMP tracking code, shown as an array
		 *
		 * @var array
		 */
		private $tracking_code = array(
			'type'        => 'googleanalytics',
			'attributes'  => array(),
			'config_data' => array(
				'vars'     => array(
					'account' => ''
				),
				'triggers' => array(
					'trackPageview' => array(
						'on'      => 'visible',
						'request' => 'pageview',
					),
				),
			),
		);

		/**
		 * YoastSEO_AMP_Frontend constructor.
		 */
		public function __construct() {
			$this->options = YoastSEO_AMP_Options::get();

			if ( ! empty( $this->options['analytics-extra'] ) ) {
				add_action( 'amp_post_template_footer', array( $this, 'extra_footer' ) );
				return;
			}
			add_filter( 'amp_post_template_analytics', array( $this, 'analytics' ) );
		}

		/**
		 * If analytics tracking has been set, output it now.
		 *
		 * @param array $analytics
		 *
		 * @return array
		 */
		public function analytics( $analytics ) {
			if ( ! class_exists( 'Yoast_GA_Options' ) || Yoast_GA_Options::instance()->get_tracking_code() === null ) {
				return $analytics;
			}
			$this->tracking_code['config_data']['vars']['account'] = Yoast_GA_Options::instance()->get_tracking_code();

			$analytics['yst-googleanalytics'] = $this->tracking_code;

			return $analytics;
		}

		/**
		 * Outputs analytics code in the footer, if set
		 */
		public function extra_footer() {
			echo $this->options['analytics-extra'];
		}

	}
}
