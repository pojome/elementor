<?php


namespace Elementor\Modules\Optimizer;

use Elementor\Core\Base\Module as BaseModule;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Analyzer extends BaseModule {
	private $post_id;

	public function get_name() {
		return 'analyzer';
	}

	public function __construct() {
		parent::__construct();

		add_filter( 'show_admin_bar', '__return_false' );

		add_action( 'wp_footer', function() {
			wp_register_script( 'analyzer',
				ELEMENTOR_ASSETS_URL . '/js/analyzer.js',
				'jquery',
				ELEMENTOR_VERSION,
				true
			);
			wp_enqueue_script( 'analyzer' );
			wp_localize_script( 'analyzer', 'analyzerAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
		} );

		$is_analyzer_data_report = isset( $_POST['action'] ) && 'save_analyzer_data_report' === $_POST['action'];
		$this->post_id = isset( $_POST['post_id'] ) && ! ! get_post_status( $_POST['post_id'] ) ? $_POST['post_id'] : false;
		$post_id = $this->post_id;

		if ( ! $is_analyzer_data_report ) {
			$nonce_verified = isset( $_REQUEST['nonce'] ) && wp_verify_nonce( $_REQUEST['nonce'], 'save_analyzer_data_report' );

			if ( ! $post_id || ! $nonce_verified ) {
				return;
			}
		}

		if ( $post_id ) {
			$this->save_analyzer_data_report();
		} else {
			$result = 'No valid post ID found. ' . $post_id;
			wp_send_json_error( $result, 400 );
		}
	}

	private function save_images( $images ) {
		$wp_upload_dir = wp_get_upload_dir();
		foreach ( $images as $image ) {
			$url_key = isset( $image['url'] ) ? $image['url'] : $image['src'];
			$src_path = str_replace( $wp_upload_dir['baseurl'], $wp_upload_dir['basedir'], $url_key );
			$destination_path = $src_path . '.webp';
			$image_id = attachment_url_to_postid( $url_key );
			update_post_meta( $image_id, '_elementor_optimizer_optimized_' . $image['optimized']['size'], true );
			file_put_contents( $destination_path, file_get_contents( $image['optimized']['data'] ) );
		}
	}

	private function save_optimized_images( $optimized_images ) {
		foreach ( [ 'images', 'backgroundImages' ] as $images_data_key ) {
			if ( isset( $optimized_images[ $images_data_key ] ) && is_array( $optimized_images[ $images_data_key ] ) && count( $optimized_images[ $images_data_key ] ) ) {
				$this->save_images( $optimized_images[ $images_data_key ] );
			}
		}

		wp_send_json_success( [ $optimized_images ], 200 );
	}

	private function save_analyzer_data_report() {
		$post_id = $this->post_id;

		try {
			$page_data = json_decode( stripslashes( $_POST['page_data'] ), true );

			if ( isset( $page_data['optimizedImages'] ) ) {
				$this->save_optimized_images( $page_data['optimizedImages'] );
			}

			$original_data = $page_data;
			$optimizer = new Page_Optimizer( $page_data, $post_id );
			$optimization_results = $optimizer->optimize_page();

			$css = $page_data['css'];
			$critical_css = $page_data['criticalCSS'];
			$did_save_data['css'] = update_post_meta( $post_id, '_elementor_optimizer_used_css', $css );
			$did_save_data['critical_CSS'] = update_post_meta( $post_id, '_elementor_optimizer_critical_css', $critical_css );

			unset( $page_data['css'] );
			unset( $page_data['criticalCSS'] );
			foreach ( $page_data['images'] as $key => $image ) {
				unset( $page_data['images'][ $key ]['placeholder']['data'] );
			}
			foreach ( $page_data['backgroundImages'] as $key => $image ) {
				unset( $page_data['backgroundImages'][ $key ]['placeholder']['data'] );
			}

			$did_save_data['report'] = update_post_meta( $post_id, '_elementor_analyzer_report', $page_data );

			$result = [
				'type' => 'success',
				'optimization_results' => $optimization_results,
				'did_save_data' => $did_save_data,
				'saved_report' => $page_data,
			];

			wp_send_json_success( $result, 200 );
		} catch ( \Error $error ) {
			$result = [
				'type' => 'error',
				'error' => $error->getMessage(),
			];
			wp_send_json_error( $result, 400 );
		}
	}
}
