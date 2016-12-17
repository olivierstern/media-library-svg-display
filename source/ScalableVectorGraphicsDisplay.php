<?php

namespace Cyberhobo\MediaLibrary;

use add_action;
use add_filter;
use wp_add_inline_style;
use simplexml_load_file;
use WP_Post;

/**
 * Scalable Vector Graphics (SVG) Display
 *
 * @since 0.1.0
 */
class ScalableVectorGraphicsDisplay {

	/**
	 * Enable media library SVG display.
	 *
	 * @since 0.1.0
	 */
	public function enable() {
		add_action( 'admin_enqueue_scripts', array( $this, 'add_administration_styles' ) );
		add_filter( 'wp_prepare_attachment_for_js', array( $this, 'adjust_response_for_svg' ), 10, 3 );
	}

	/**
	 * Add styles necessary for media library display.
	 *
	 * @since 0.1.0
	 */
	public function add_administration_styles() {
		$this->add_media_listing_style();
		$this->add_featured_image_style();	
	}

	/**
	 * Add dimensions and orientation for SVG to attachment ajax data.
	 *
	 * @since 0.1.0
	 * @param array $response
	 * @param WP_Post $attachment
	 * @param array $meta
	 * @return array
	 */
	public function adjust_response_for_svg( $response, $attachment, $meta ) {
		if ( 'image/svg+xml' != $response['mime'] or ! empty( $response['sizes'] ) ) {
			return $response;
		}

		$dimensions = $this->get_dimensions( get_attached_file( $attachment->ID ) );

		$response['sizes'] = array(
			'full' => array(
				'url' => $response['url'],
				'width' => $dimensions->width,
				'height' => $dimensions->height,
				'orientation' => $dimensions->width > $dimensions->height ? 'landscape' : 'portrait',
			)
		);
		
		return $response;
	}

	/**
	 * @since 0.1.0
	 */
	protected function add_media_listing_style() {
		wp_add_inline_style( 'wp-admin', ".media .media-icon img[src$='.svg'] { width: auto; height: auto; }" );
	}

	/**
	 * @since 0.1.0
	 */
	protected function add_featured_image_style() {
		wp_add_inline_style( 'wp-admin', "#postimagediv .inside img[src$='.svg'] { width: 100%; height: auto; }" );
	}

	/**
	 * Parse width and height from an SVG file.
	 *
	 * @since 0.1.0
	 * @param string $svg_path
	 * @return object
	 */
	protected function get_dimensions( $svg_path ) {
		$svg = simplexml_load_file( $svg_path );
		$attributes = $svg->attributes();
		$width = (string) $attributes->width;
		$height = (string) $attributes->height;

		return (object) compact( 'width', 'height' );
	}
}
