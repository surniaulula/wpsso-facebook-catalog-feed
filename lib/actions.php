<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2022 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoFcfActions' ) ) {

	class WpssoFcfActions {

		private $p;	// Wpsso class object.
		private $a;	// WpssoFcf class object.

		/**
		 * Instantiated by WpssoFcf->init_objects().
		 */
		public function __construct( &$plugin, &$addon ) {

			static $do_once = null;

			if ( true === $do_once ) {

				return;	// Stop here.
			}

			$do_once = true;

			$this->p =& $plugin;
			$this->a =& $addon;

			$this->p->util->add_plugin_actions( $this, array(
				'check_head_info'    => 3,
				'refresh_post_cache' => 2,
			) );
		}

		/**
		 * The post, term, or user has an ID, is public, and (in the case of a post) the post status is published.
		 */
		public function action_check_head_info( array $head_info, array $mod, $ref_url ) {

			$is_product = isset( $head_info[ 'og:type' ] ) && 'product' === $head_info[ 'og:type' ] ? true : false;

			if ( $is_product && ! $mod[ 'is_archive' ] ) {	// Exclude the shop page.

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'getting open graph array for ' . $mod[ 'name' ] . ' id ' . $mod[ 'id' ] );
				}

				$this->p->util->maybe_set_ref( $ref_url, $mod, __( 'checking facebook catalog feeds', 'wpsso' ) );

				$mt_og = $this->p->og->get_array( $mod, $size_names = 'wpsso-fcf', $md_pre = array( 'fcf', 'og' ) );

				$this->p->util->maybe_unset_ref( $ref_url );

				if ( empty( $mt_og[ 'product:offers' ] ) ) {

					$image_url = $this->get_product_image_url( $mt_og, $mod, $ref_url );

				} elseif ( is_array( $mt_og[ 'product:offers' ] ) ) {

					foreach ( $mt_og[ 'product:offers' ] as $num => $mt_offer ) {

						$image_url = $this->get_product_image_url( $mt_offer, $mod, $ref_url );
					}
				}
			}
		}

		/**
		 * Once the post cache is cleared and refreshed, clear the feed XML.
		 */
		public function action_refresh_post_cache( $post_id, $mod ) {

			$og_type = $this->p->og->get_mod_og_type_id( $mod );

			if ( 'product' === $og_type ) {

				$locale = SucomUtil::get_locale( $mod );

				$xml = WpssoFcfXml::clear_cache( $locale );
			}
		}

		private function get_product_image_url( $mt_data, $mod, $canonical_url ) {

			$mt_images = array();

			if ( isset( $mt_data[ 'og:image' ] ) && is_array( $mt_data[ 'og:image' ] ) ) {

				$mt_images = $mt_data[ 'og:image' ];

			} elseif ( ! empty( $mt_data[ 'product:retailer_item_id' ] ) && is_numeric( $mt_data[ 'product:retailer_item_id' ] ) ) {

				$post_id   = $mt_data[ 'product:retailer_item_id' ];
				$mod       = $this->p->post->get_mod( $post_id );	// Redefine the $mod array for the variation post ID.
				$max_nums  = $this->p->util->get_max_nums( $mod, 'og' );

				$this->p->util->maybe_set_ref( $canonical_url, $mod, __( 'getting facebook catalog feeds images', 'wpsso' ) );

				$mt_images = $this->p->media->get_all_images( $max_nums[ 'og_img_max' ], $size_names = 'wpsso-fcf', $mod, $md_pre = array( 'fcf', 'og' ) );

				$this->p->util->maybe_unset_ref( $canonical_url );
			}

			if ( is_array( $mt_images ) ) {	// Just in case.

				foreach ( $mt_images as $mt_image ) {

					if ( $image_url = SucomUtil::get_first_og_image_url( $mt_image ) ) {

						return $image_url;
					}
				}

				/**
				 * An is_admin() test is required to make sure the WpssoMessages class is available.
				 */
				if ( $this->p->notice->is_admin_pre_notices() ) {

					if ( ! empty( $mod[ 'post_type_label_single' ] ) ) {

						$this->p->util->maybe_set_ref( $canonical_url, $mod, __( 'checking facebook catalog feeds images', 'wpsso' ) );

						$notice_msg = sprintf( __( 'A Facebook catalog feed XML %1$s attribute could not be generated for %2$s ID %3$s.', 'wpsso' ), '<code>image_link</code>', $mod[ 'post_type_label_single' ], $mod[ 'id' ] ) . ' ';

						$notice_msg .= sprintf( __( 'Facebook requires at least one %1$s attribute for each product variation in the Facebook catalog feed XML.', 'wpsso' ), '<code>image_link</code>' );

						$notice_key = $mod[ 'name' ] . '-' . $mod[ 'id' ] . '-notice-missing-fcf-image';

						$this->p->notice->err( $notice_msg, null, $notice_key );

						$this->p->util->maybe_unset_ref( $canonical_url );
					}
				}
			}
		}
	}
}
