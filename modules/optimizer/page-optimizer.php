<?php




namespace Elementor\Modules\Optimizer;

require __DIR__ . '/../../vendor/autoload.php';

use WebPConvert\Convert\Exceptions\ConversionFailedException;
use WebPConvert\WebPConvert;
use Sqz\Squeezio;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


class Page_Optimizer {
	private $analyzer_data;
	private $post_id;

	public function __construct( $analyzer_data, $post_id ) {
		$this->analyzer_data = $analyzer_data;
		$this->post_id = $post_id;
		add_filter( 'optimizer_widgets_queue', '__return_empty_array' );
	}

	public function get_analyzer_data( $index ) {
		$index_exists = ( ! ! $index && isset( $this->analyzer_data[ $index ] ) );

		return $index_exists ?
			$this->analyzer_data[ $index ] :
			$index ?
				false :
				$this->analyzer_data;
	}

	public function optimize_page() {
		$images = $this->analyzer_data['images'];
		$bg_images = $this->analyzer_data['backgroundImages'];
		$operations_results = [];

		if ( count( $images ) ) {
			// $this->optimize_images( $images );
			$operations_results['save_images_placeholders'] = $this->save_placeholders();
		}

		if ( count( $bg_images ) ) {
			// $this->optimize_images( $images );
			$operations_results['save_bg_images_placeholders'] = $this->save_placeholders( true );
		}

		return $operations_results;
	}

	public function save_placeholders( $background_images = false ) {
		$images_data_key = $background_images ? 'backgroundImages' : 'images';
		$images = $this->analyzer_data[ $images_data_key ];
		$saved_placeholders = [];

		foreach ( $images as $index => $image ) {
			$saved_placeholders[ $index ] = false;
			$url_key = $background_images ? $image['url'] : $image['src'];
			$image_id = attachment_url_to_postid( $url_key );

			if ( $image_id && $image['placeholder'] ) {
				$meta_key = '_e_optimizer_placeholder_' . $image['placeholder']['size'];
				$saved_placeholders[ $index ] =
					update_post_meta( $image_id, $meta_key, $image['placeholder']['data'] );
			}
		}

		return [ $saved_placeholders ];
	}

	public function optimize_images( $images ) {
		$wp_upload_dir = wp_get_upload_dir();
		foreach ( $images as $image ) {
			$image_url = wp_parse_url( $image['src'] );
			$image_info = pathinfo( $image['src'] );
			$src_path = $this->sanitize_path( str_replace( $wp_upload_dir['baseurl'], $wp_upload_dir['basedir'], $image['src'] ) );
			$destination_path = $src_path . '.webp';
			var_dump( [ $src_path, $destination_path ] );
			try {
				WebPConvert::convert(
					$this->sanitize_path( $src_path ),
					$this->sanitize_path( $destination_path ),
					[
						'quality' => 40,
						'converters' => [ 'cwebp', 'vips', 'imagick', 'gmagick', 'imagemagick', 'graphicsmagick', 'wpc', 'ewww', 'gd' ],
					]
				);
			} catch ( ConversionFailedException $e ) {
				// var_dump( $e );
				$this->squeezio( $this->sanitize_path( $src_path ) );
			}
		}
	}

	public function squeezio( $src_path ) {
		$sqz = Squeezio::getInstance( $src_path );
		$sqz->setSize( 120, 120 )->setQuality( 20 )->exec();
		var_dump( $sqz->getInfos() );
	}

	public function optimize_fonts( $fonts ) {

	}

	public static function remove_nul( $string ) {
		return str_replace( chr( 0 ), '', $string );
	}

	public static function remove_stream_wrappers( $string ) {
		return preg_replace( '#^\\w+://#', '', $string );
	}

	public static function sanitize_path( $string ) {
		$string = self::remove_nul( $string );
		$string = self::remove_stream_wrappers( $string );

		return $string;
	}
}
