<?php
namespace Elementor\Core\Settings\Page;

use Elementor\CSS_File;
use Elementor\Core\Settings\Base\Manager as BaseManager;
use Elementor\Core\Settings\Manager as SettingsManager;
use Elementor\Core\Settings\Base\Model as BaseModel;
use Elementor\DB;
use Elementor\Plugin;
use Elementor\Post_CSS_File;
use Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Manager extends BaseManager {

	const TEMPLATE_CANVAS = 'elementor_canvas';

	const META_KEY = '_elementor_page_settings';

	/**
	 * @since 1.6.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'init', [ $this, 'init' ] );

		add_filter( 'template_include', [ $this, 'template_include' ] );
	}

	/**
	 * @since 1.6.0
	 * @access public
	 * @static
	 * @deprecated since 1.6.0
	 *
	 * @param int $id
	 *
	 * @return BaseModel
	 */
	public static function get_page( $id ) {
		return SettingsManager::get_settings_managers( 'page' )->get_model( $id );
	}

	/**
	 * @since 1.6.0
	 * @access public
	 * @static
	 */
	public static function add_page_templates( $post_templates ) {
		$post_templates = [
			self::TEMPLATE_CANVAS => __( 'Elementor', 'elementor' ) . ' ' . __( 'Canvas', 'elementor' ),
		] + $post_templates;

		return $post_templates;
	}

	/**
	 * @since 1.6.0
	 * @access public
	 * @static
	 * @deprecated
	 */
	public static function is_cpt_custom_templates_supported() {
		// Todo: _deprecated_function( __METHOD__, '2.0.0', 'Utils::is_cpt_custom_templates_supported' );

		return Utils::is_cpt_custom_templates_supported();
	}

	/**
	 * @since 1.6.0
	 * @access public
	 */
	public function template_include( $template ) {
		if ( is_singular() ) {
			$post_id = get_the_ID();

			if ( is_preview() || Plugin::$instance->preview->is_preview_mode() ) {
				$preview_post = Utils::get_post_autosave( $post_id, get_current_user_id() );
				if ( $preview_post ) {
					$post_id = $preview_post->ID;
				}
			}

			$page_template = get_post_meta( $post_id, '_wp_page_template', true );

			if ( self::TEMPLATE_CANVAS === $page_template ) {
				$template = ELEMENTOR_PATH . '/includes/page-templates/canvas.php';
			}
		}

		return $template;
	}

	/**
	 * @since 1.6.0
	 * @access public
	 */
	public function init() {
		$post_types = get_post_types_by_support( 'elementor' );

		foreach ( $post_types as $post_type ) {
			add_filter( "theme_{$post_type}_templates", [ __CLASS__, 'add_page_templates' ], 10, 4 );
		}
	}

	/**
	 * @since 1.6.0
	 * @access public
	 */
	public function get_name() {
		return 'page';
	}

	/**
	 * @since 1.6.0
	 * @access public
	 * @return BaseModel
	 */
	public function get_model_for_config() {
		$post_id = get_the_ID();

		$autosave = Plugin::$instance->db->get_newer_autosave( $post_id );

		if ( $autosave ) {
			$post_id = $autosave->ID;
	}

		$model = $this->get_model( $post_id );

		if ( $autosave ) {
			$model->set_settings( 'post_status', get_post_status( get_the_ID() ) );
		}

		return $model;
	}

	/**
	 * @since 1.6.0
	 * @access protected
	 *
	 * @throw \Exception If invalid post returned using the `$id`.
	 * @throw \Exception If current user don't have permissions to edit the post.
	 */
	public function ajax_before_save_settings( array $data, $id ) {
		$post = get_post( $id );

		if ( empty( $post ) ) {
			throw new \Exception( 'Invalid post.' );
		}

		if ( ! current_user_can( 'edit_post', $id ) ) {
			throw new \Exception( 'Access denied.' );
		}

		// Avoid save empty post title.
		if ( ! empty( $data['post_title'] ) ) {
			$post->post_title = $data['post_title'];
		}

		if ( isset( $data['post_excerpt'] ) && post_type_supports( $post->post_type, 'excerpt' ) ) {
			$post->post_excerpt = $data['post_excerpt'];
		}

		if ( isset( $data['post_status'] ) ) {
			$this->save_post_status( $id, $data['post_status'] );
			unset( $post->post_status );
		}

		wp_update_post( $post );

		if ( DB::STATUS_PUBLISH === $post->post_status ) {
			$autosave = Utils::get_post_autosave( $post->ID );
			if ( $autosave ) {
				wp_delete_post_revision( $autosave->ID );
			}
		}

		if ( Utils::is_cpt_custom_templates_supported() ) {
			$template = 'default';

			if ( isset( $data['template'] ) ) {
				$template = $data['template'];
			}

			// Use `update_metadata` in order to save also for revisions.
			update_metadata( 'post', $post->ID, '_wp_page_template', $template );
		}
	}

	/**
	 * @since 1.6.0
	 * @access protected
	 */
	protected function save_settings_to_db( array $settings, $id ) {
		if ( ! empty( $settings ) ) {
			update_post_meta( $id, self::META_KEY, $settings );
		} else {
			delete_post_meta( $id, self::META_KEY );
		}
	}

	/**
	 * @since 1.6.0
	 * @access protected
	 */
	protected function get_css_file_for_update( $id ) {
		return new Post_CSS_File( $id );
	}

	/**
	 * @since 1.6.0
	 * @access protected
	 */
	protected function get_saved_settings( $id ) {
		$settings = get_post_meta( $id, self::META_KEY, true );

		if ( ! $settings ) {
			$settings = [];
		}

		if ( self::is_cpt_custom_templates_supported() ) {
			$saved_template = get_post_meta( $id, '_wp_page_template', true );

			if ( $saved_template ) {
				$settings['template'] = $saved_template;
			}
		}

		return $settings;
	}

	/**
	 * @since 1.6.0
	 * @access protected
	 */
	protected function get_css_file_name() {
		return 'post';
	}

	/**
	 * @since 1.6.0
	 * @access protected
	 * @param CSS_File $css_file
	 *
	 * @return BaseModel
	 */
	protected function get_model_for_css_file( CSS_File $css_file ) {
		if ( ! $css_file instanceof Post_CSS_File ) {
			return null;
		}

		return $this->get_model( $css_file->get_post_id() );
	}

	/**
	 * @since 1.6.0
	 * @access protected
	 */
	protected function get_special_settings_names() {
		return [
			'id',
			'post_title',
			'post_status',
			'template',
		];
	}

	public function save_post_status( $post_id, $status ) {
		$parent_id = wp_is_post_revision( $post_id );

		if ( ! $parent_id ) {
			$parent_id = $post_id;
		}

		$post = get_post( $parent_id );

		$allowed_post_statuses = get_post_statuses();

		if ( isset( $allowed_post_statuses[ $status ] ) ) {
			$post_type_object = get_post_type_object( $post->post_type );
			if ( 'publish' !== $status || current_user_can( $post_type_object->cap->publish_posts ) ) {
				$post->post_status = $status;
			}
		}

		wp_update_post( $post );
	}
}
