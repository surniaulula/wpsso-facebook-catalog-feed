<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2022 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoCmcfFiltersEdit' ) ) {

	class WpssoCmcfFiltersEdit {

		private $p;	// Wpsso class object.
		private $a;	// WpssoCmcf class object.

		/*
		 * Instantiated by WpssoCmcfFilters->__construct().
		 */
		public function __construct( &$plugin, &$addon ) {

			$this->p =& $plugin;
			$this->a =& $addon;

			if ( is_admin() ) {

				$this->p->util->add_plugin_filters( $this, array(
					'metabox_sso_edit_media_og_rows' => 5,
				) );
			}
		}

		public function filter_metabox_sso_edit_media_og_rows( $table_rows, $form, $head_info, $mod, $canonical_url ) {

			if ( ! $mod[ 'is_public' ] ) {

				return $table_rows;
			}

			$is_product    = isset( $head_info[ 'og:type' ] ) && 'product' === $head_info[ 'og:type' ] ? true : false;
			$media_info    = array( 'pid' => '' );
			$media_request = array( 'pid' );

			if ( $is_product ) {

				$this->p->util->maybe_set_ref( $canonical_url, $mod, __( 'getting facebook catalog feeds image', 'wpsso-commerce-manager-catalog-feed' ) );

				$media_info = $this->p->media->get_media_info( $size_name = 'wpsso-cmcf', $media_request, $mod, $md_pre = array( 'og' ) );

			} else {

				$this->p->util->maybe_set_ref( $canonical_url, $mod, __( 'getting open graph image', 'wpsso-commerce-manager-catalog-feed' ) );

				$media_info = $this->p->media->get_media_info( $size_name = 'wpsso-opengraph', $media_request, $mod, $md_pre = array( 'none' ) );
			}

			$this->p->util->maybe_unset_ref( $canonical_url );

			$form_rows = array(
				'subsection_cmcf' => array(
					'tr_class' => 'hide_og_type hide_og_type_product',
					'td_class' => 'subsection',
					'header'   => 'h4',
					'label'    => _x( 'Commerce Manager Catalog Feed XML (Main Product)', 'metabox title', 'wpsso-commerce-manager-catalog-feed' )
				),
				'cmcf_img_info' => array(
					'tr_class'  => 'hide_og_type hide_og_type_product',
					'table_row' => '<td colspan="2">' . $this->p->msgs->get( 'info-cmcf-img', array( 'mod' => $mod ) ) . '</td>',
				),
				'cmcf_img_id' => array(
					'tr_class' => 'hide_og_type hide_og_type_product',
					'th_class' => 'medium',
					'label'    => _x( 'Image ID', 'option label', 'wpsso-commerce-manager-catalog-feed' ),
					'tooltip'  => 'meta-cmcf_img_id',
					'content'  => $form->get_input_image_upload( 'cmcf_img', $media_info[ 'pid' ] ),
				),
				'cmcf_img_url' => array(
					'tr_class' => 'hide_og_type hide_og_type_product',
					'th_class' => 'medium',
					'label'    => _x( 'or an Image URL', 'option label', 'wpsso-commerce-manager-catalog-feed' ),
					'tooltip'  => 'meta-cmcf_img_url',
					'content'  => $form->get_input_image_url( 'cmcf_img' ),
				),
			);

			return $form->get_md_form_rows( $table_rows, $form_rows, $head_info, $mod );
		}
	}
}
