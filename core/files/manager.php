<?php
namespace Elementor\Core\Files;

use Elementor\Core\Common\Modules\Ajax\Module as Ajax;
use Elementor\Core\Files\Assets\Files_Upload_Handler;
use Elementor\Core\Files\Assets\Json\Json_Handler;
use Elementor\Core\Files\Assets\Svg\Svg_Handler;
use Elementor\Core\Files\CSS\Global_CSS;
use Elementor\Core\Files\CSS\Post as Post_CSS;
use Elementor\Core\Page_Assets\Data_Managers\Base as Page_Assets_Data_Manager;
use Elementor\Core\Responsive\Files\Frontend;
use Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor files manager.
 *
 * Elementor files manager handler class is responsible for creating files.
 *
 * @since 1.2.0
 */
class Manager {

	private $files = [];

	/**
	 * Files manager constructor.
	 *
	 * Initializing the Elementor files manager.
	 *
	 * @since 1.2.0
	 * @access public
	 */
	public function __construct() {
		$this->register_actions();

		new Svg_Handler();
		new Json_Handler();
	}

	public function get( $class, $args ) {
		$id = $class . '-' . wp_json_encode( $args );

		if ( ! isset( $this->files[ $id ] ) ) {
			// Create an instance from dynamic args length.
			$reflection_class = new \ReflectionClass( $class );
			$this->files[ $id ] = $reflection_class->newInstanceArgs( $args );
		}

		return $this->files[ $id ];
	}

	/**
	 * On post delete.
	 *
	 * Delete post CSS immediately after a post is deleted from the database.
	 *
	 * Fired by `deleted_post` action.
	 *
	 * @since 1.2.0
	 * @access public
	 *
	 * @param string $post_id Post ID.
	 */
	public function on_delete_post( $post_id ) {
		if ( ! Utils::is_post_support( $post_id ) ) {
			return;
		}

		$css_file = Post_CSS::create( $post_id );

		$css_file->delete();
	}

	/**
	 * On export post meta.
	 *
	 * When exporting data using WXR, skip post CSS file meta key. This way the
	 * export won't contain the post CSS file data used by Elementor.
	 *
	 * Fired by `wxr_export_skip_postmeta` filter.
	 *
	 * @since 1.2.0
	 * @access public
	 *
	 * @param bool   $skip     Whether to skip the current post meta.
	 * @param string $meta_key Current meta key.
	 *
	 * @return bool Whether to skip the post CSS meta.
	 */
	public function on_export_post_meta( $skip, $meta_key ) {
		if ( Post_CSS::META_KEY === $meta_key ) {
			$skip = true;
		}

		return $skip;
	}

	/**
	 * Clear cache.
	 *
	 * Delete all meta containing files data. And delete the actual
	 * files from the upload directory.
	 *
	 * @since 1.2.0
	 * @access public
	 */
	public function clear_cache() {
		// Delete files.
		$path = Base::get_base_uploads_dir() . Base::DEFAULT_FILES_DIR . '*';

		foreach ( glob( $path ) as $file_path ) {
			unlink( $file_path );
		}

		delete_post_meta_by_key( Post_CSS::META_KEY );

		delete_option( Global_CSS::META_KEY );
		delete_option( Frontend::META_KEY );

		$this->reset_assets_data();

		/**
		 * Elementor clear files.
		 *
		 * Fires after Elementor clears files
		 *
		 * @since 2.1.0
		 */
		do_action( 'elementor/core/files/clear_cache' );
	}

	public function register_ajax_actions( Ajax $ajax ) {
		$ajax->register_ajax_action( 'enable_unfiltered_files_upload', [ $this, 'ajax_unfiltered_files_upload' ] );
	}

	public function ajax_unfiltered_files_upload() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		update_option( Files_Upload_Handler::OPTION_KEY, 1 );
	}

	/**
	 * Register actions.
	 *
	 * Register filters and actions for the files manager.
	 *
	 * @since 1.2.0
	 * @access private
	 */
	private function register_actions() {
		add_action( 'deleted_post', [ $this, 'on_delete_post' ] );

		// Ajax.
		add_action( 'elementor/ajax/register_actions', [ $this, 'register_ajax_actions' ] );

		add_filter( 'wxr_export_skip_postmeta', [ $this, 'on_export_post_meta' ], 10, 2 );
	}

	/**
	 * Reset Assets Data.
	 *
	 * Reset the page assets data.
	 *
	 * @since 3.3.0
	 * @access private
	 */
	private function reset_assets_data() {
		delete_option( Page_Assets_Data_Manager::ASSETS_DATA_KEY );
	}
}
