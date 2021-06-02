<?php
namespace Elementor\Modules\Optimizer;

use Elementor\Core\Base\Module as BaseModule;
use Elementor\Plugin;
use Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Module extends BaseModule {

	/**
	 * Page Optimizer
	 *
	 * @access private
	 *
	 * @var Page_Optimizer
	 */
	private $optimizer;

	/**
	 * Optimized Cache Manager.
	 *
	 * @access private
	 *
	 * @var Page_Loader
	 */
	private $page_loader;

	/**
	 * Page Analyzer.
	 *
	 * @access private
	 *
	 * @var Analyzer
	 */
	private $analyzer;

	/**
	 * Module constructor.
	 *
	 * @since 2.3.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct();
		$this->init_optimizer();
	}

	public function get_name() {
		return 'optimizer';
	}

	private function init_optimizer() {
		if ( $this->is_analyzer_active() ) {
			add_action( 'elementor/frontend/before_enqueue_scripts', function() {
				$nonce = wp_create_nonce( 'save_analyzer_data_report' );
				echo '<script>const nonce ="' . $nonce . '"; const post_id ="' . get_the_ID() . '";</script>';
			} );

			$this->analyzer = new Analyzer();
			return;
		}

		add_action( 'elementor/init', function() {
			$is_edit_mode          = Plugin::$instance->editor->is_edit_mode();
			$is_preview_mode       = isset( $_GET['elementor-preview'] );
			$is_optimized_mode     = Plugin::$instance->experiments->is_feature_active( 'e_optimized_css_loading' );
			$is_admin              = is_admin();
			$is_static_render_mode = Plugin::$instance->frontend->is_static_render_mode();
			$is_editor             = isset( $_GET['action'] ) && 'elementor' === $_GET['action'];
			$is_unoptimized        = isset( $_GET['unoptimized'] );

			if ( ! $is_admin && ! $is_static_render_mode && ! $is_optimized_mode && ! $is_edit_mode
			     && ! $is_preview_mode && ! $is_editor && ! $is_unoptimized ) {
				$this->page_loader = new Page_Loader( get_the_ID() );
			}
		} );
	}

	public static function is_active() {
		return Plugin::$instance->experiments->is_feature_active( 'elementor_optimizer' );
	}

	private function is_analyzer_active() {
		$is_user_admin = ( current_user_can( 'editor' ) || current_user_can( 'administrator' ) );

		return $is_user_admin && isset( $_REQUEST['analyzer'] );
	}
}
