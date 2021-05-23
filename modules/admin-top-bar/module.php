<?php
namespace Elementor\Modules\AdminTopBar;

use Elementor\Core\Base\App as BaseApp;
use Elementor\Core\Experiments\Manager;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Module extends BaseApp {
	/**
	 * @return bool
	 */
	public static function is_active() {
		return is_admin();
	}

	public static function get_experimental_data() {
		return [
			'name' => 'admin-top-bar',
			'title' => __( 'Admin Top Bar', 'elementor' ),
			'description' => __( 'Adds a top bar to elementors pages in admin area.', 'elementor' ),
			'release_status' => Manager::RELEASE_STATUS_ALPHA,
			'default' => Manager::STATE_INACTIVE,
		];
	}

	/**
	 * @return string
	 */
	public function get_name() {
		return 'admin-top-bar';
	}

	private function render_admin_top_bar() {
		?>
			<div id="e-admin-top-bar">
			</div>
		<?php
	}

	/**
	 * Enqueue admin scripts
	 */
	private function enqueue_scripts() {
		wp_enqueue_style(
			'elementor-admin-top-bar',
			$this->get_css_assets_url( 'modules/admin-top-bar/admin', null, 'default', true ),
			[],
			ELEMENTOR_VERSION
		);

		wp_enqueue_script(
			'elementor-admin-top-bar',
			$this->get_js_assets_url( 'admin-top-bar' ),
			[
				'react',
				'react-dom',
			],
			ELEMENTOR_VERSION,
			true
		);

		$this->print_config();
	}

	/**
	 * Module constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'in_admin_header', function() {
			$this->render_admin_top_bar();
		} );

		add_action( 'admin_enqueue_scripts', function () {
			$this->enqueue_scripts();
		} );
	}
}
