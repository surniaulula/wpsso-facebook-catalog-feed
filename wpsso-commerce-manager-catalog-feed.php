<?php
/**
 * Plugin Name: WPSSO Commerce Manager Catalog Feed XML
 * Plugin Slug: wpsso-commerce-manager-catalog-feed
 * Text Domain: wpsso-commerce-manager-catalog-feed
 * Domain Path: /languages
 * Plugin URI: https://wpsso.com/extend/plugins/wpsso-commerce-manager-catalog-feed/
 * Assets URI: https://surniaulula.github.io/wpsso-commerce-manager-catalog-feed/assets/
 * Author: JS Morisset
 * Author URI: https://wpsso.com/
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Description: Facebook and Instagram Manager Catalog Feed XMLs for WooCommerce, Easy Digital Downloads, and Custom Product Pages.
 * Requires Plugins: wpsso
 * Requires PHP: 7.2
 * Requires At Least: 5.2
 * Tested Up To: 6.1.1
 * WC Tested Up To: 7.3.0
 * Version: 1.0.0-b.8
 *
 * Version Numbering: {major}.{minor}.{bugfix}[-{stage}.{level}]
 *
 *      {major}         Major structural code changes and/or incompatible API changes (ie. breaking changes).
 *      {minor}         New functionality was added or improved in a backwards-compatible manner.
 *      {bugfix}        Backwards-compatible bug fixes or small improvements.
 *      {stage}.{level} Pre-production release: dev < a (alpha) < b (beta) < rc (release candidate).
 *
 * Copyright 2014-2022 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoAbstractAddOn' ) ) {

	require_once dirname( __FILE__ ) . '/lib/abstract/add-on.php';
}

if ( ! class_exists( 'WpssoCmcf' ) ) {

	class WpssoCmcf extends WpssoAbstractAddOn {

		public $actions;	// WpssoCmcfActions class object.
		public $filters;	// WpssoCmcfFilters class object.
		public $rewrite;	// WpssoCmcfRewrite class object.

		protected $p;		// Wpsso class object.

		private static $instance = null;	// WpssoCmcf class object.

		public function __construct() {

			parent::__construct( __FILE__, __CLASS__ );
		}

		public static function &get_instance() {

			if ( null === self::$instance ) {

				self::$instance = new self;
			}

			return self::$instance;
		}

		public function init_textdomain() {

			load_plugin_textdomain( 'wpsso-commerce-manager-catalog-feed', false, 'wpsso-commerce-manager-catalog-feed/languages/' );
		}

		/**
		 * Require library files with functions or static methods in require_libs().
		 *
		 * Require and instantiate library files with dynamic methods in init_objects().
		 */
		public function init_objects() {

			$this->p =& Wpsso::get_instance();

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			if ( $this->get_missing_requirements() ) {	// Returns false or an array of missing requirements.

				return;	// Stop here.
			}

			require_once WPSSOCMCF_PLUGINDIR . 'lib/actions.php';

			$this->actions = new WpssoCmcfActions( $this->p, $this );

			require_once WPSSOCMCF_PLUGINDIR . 'lib/filters.php';

			$this->filters = new WpssoCmcfFilters( $this->p, $this );

			/**
			 * lib/rewrite.php already loaded in require_libs() for WpssoCmcfRegister->activate_plugin().
			 */
			$this->rewrite = new WpssoCmcfRewrite( $this->p, $this );
		}
	}

	WpssoCmcf::get_instance();
}