<?php
namespace Elementor\Modules\System_Info;

use Elementor\Core\Base\Module as BaseModule;
use Elementor\Modules\System_Info\Reporters\Base;
use Elementor\Modules\System_Info\Helpers\Model_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor system info module.
 *
 * Elementor system info module handler class is responsible for registering and
 * managing Elementor system info reports.
 *
 * @since 2.9.0
 */
class Module extends BaseModule {
	/**
	 * Get module name.
	 *
	 * Retrieve the system info module name.
	 *
	 * @since 2.9.0
	 * @access public
	 *
	 * @return string Module name.
	 */
	public function get_name() {
		return 'system-info';
	}

	/**
	 * Required user capabilities.
	 *
	 * Holds the user capabilities required to manage Elementor menus.
	 *
	 * @since 2.9.0
	 * @access private
	 *
	 * @var string
	 */
	private $capability = 'manage_options';

	/**
	 * Elementor system info reports.
	 *
	 * Holds an array of available reports in Elementor system info page.
	 *
	 * @since 2.9.0
	 * @access private
	 *
	 * @var array
	 */
	private static $reports = [
		'server' => [],
		'wordpress' => [],
		'theme' => [],
		'user' => [],
		'plugins' => [],
		'network_plugins' => [],
		'mu_plugins' => [],
	];

	/**
	 * Main system info page constructor.
	 *
	 * Initializing Elementor system info page.
	 *
	 * @since 2.9.0
	 * @access public
	 */
	public function __construct() {
		$this->add_actions();
	}

	/**
	 * Get default settings.
	 *
	 * Retrieve the default settings. Used to reset the report settings on
	 * initialization.
	 *
	 * @since 2.9.0
	 * @access protected
	 *
	 * @return array Default settings.
	 */
	protected function get_init_settings() {
		$settings = [];

		$reporter_properties = Base::get_properties_keys();

		array_push( $reporter_properties, 'category', 'name', 'class_name' );

		$settings['reporter_properties'] = $reporter_properties;

		$settings['reportFilePrefix'] = '';

		return $settings;
	}

	/**
	 * Add actions.
	 *
	 * Register filters and actions for the main system info page.
	 *
	 * @since 2.9.0
	 * @access private
	 */
	private function add_actions() {
		add_action( 'admin_menu', [ $this, 'register_menu' ], 500 );
		add_action( 'wp_ajax_elementor_system_info_download_file', [ $this, 'download_file' ] );
	}

	/**
	 * Register admin menu.
	 *
	 * Add new Elementor system info admin menu.
	 *
	 * Fired by `admin_menu` action.
	 *
	 * @since 2.9.0
	 * @access public
	 */
	public function register_menu() {
		$system_info_text = __( 'System Info', 'elementor' );

		add_submenu_page(
			'elementor',
			$system_info_text,
			$system_info_text,
			$this->capability,
			'elementor-system-info',
			[ $this, 'display_page' ]
		);
	}

	/**
	 * Display page.
	 *
	 * Output the content for the main system info page.
	 *
	 * @since 2.9.0
	 * @access public
	 */
	public function display_page() {
		$reports_info = self::get_allowed_reports();

		$reports = $this->load_reports( $reports_info, 'html' );

		$raw_reports = $this->load_reports( $reports_info, 'raw' );

		?>
		<div id="elementor-system-info">
			<h3 class="wp-heading-inline"><?php echo __( 'System Info', 'elementor' ); ?></h3>
			<div><?php $this->print_report( $reports, 'html' ); ?></div>
			<h3><?php echo __( 'Copy & Paste Info', 'elementor' ); ?></h3>
			<div id="elementor-system-info-raw">
				<label id="elementor-system-info-raw-code-label" for="elementor-system-info-raw-code"><?php echo __( 'You can copy the below info as simple text with Ctrl+C / Ctrl+V:', 'elementor' ); ?></label>
				<textarea id="elementor-system-info-raw-code" readonly>
					<?php
						unset( $raw_reports['wordpress']['report']['admin_email'] );

						$this->print_report( $raw_reports, 'raw' );
					?>
				</textarea>
				<script>
					var textarea = document.getElementById( 'elementor-system-info-raw-code' );
					var selectRange = function() {
						textarea.setSelectionRange( 0, textarea.value.length );
					};
					textarea.onfocus = textarea.onblur = textarea.onclick = selectRange;
					textarea.onfocus();
				</script>
			</div>
			<hr>
			<form action="<?php echo admin_url( 'admin-ajax.php' ); ?>" method="post">
				<input type="hidden" name="action" value="elementor_system_info_download_file">
				<input type="submit" class="button button-primary" value="<?php echo __( 'Download System Info', 'elementor' ); ?>">
			</form>
		</div>
		<?php
	}

	/**
	 * Download file.
	 *
	 * Download the reports files.
	 *
	 * Fired by `wp_ajax_elementor_system_info_download_file` action.
	 *
	 * @since 2.9.0
	 * @access public
	 */
	public function download_file() {
		if ( ! current_user_can( $this->capability ) ) {
			wp_die( __( 'You don\'t have permissions to download this file', 'elementor' ) );
		}

		$reports_info = self::get_allowed_reports();
		$reports = $this->load_reports( $reports_info, 'raw' );

		$domain = parse_url( site_url(), PHP_URL_HOST );

		header( 'Content-Type: text/plain' );
		header( 'Content-Disposition:attachment; filename=system-info-' . $domain . '-' . gmdate( 'd-m-Y' ) . '.txt' );

		$this->print_report( $reports );

		die;
	}

	/**
	 * Get report class.
	 *
	 * Retrieve the class of the report for any given report type.
	 *
	 * @since 2.9.0
	 * @access public
	 *
	 * @param string $reporter_type The type of the report.
	 *
	 * @return string The class of the report.
	 */
	public function get_reporter_class( $reporter_type ) {
		return __NAMESPACE__ . '\Reporters\\' . ucfirst( $reporter_type );
	}

	/**
	 * Load reports.
	 *
	 * Retrieve the system info reports.
	 *
	 * @since 2.9.0
	 * @access public
	 *
	 * @param array $reports An array of system info reports.
	 * @param string $format - possible values: 'raw' or empty string, meaning 'html'
	 *
	 * @return array An array of system info reports.
	 */
	public function load_reports( $reports, $format = '' ) {
		$result = [];

		foreach ( $reports as $report_name => $report_info ) {
			$reporter_params = [
				'name' => $report_name,
				'format' => $format,
			];

			$reporter_params = array_merge( $reporter_params, $report_info );

			$reporter = $this->create_reporter( $reporter_params );

			if ( ! $reporter instanceof Base ) {
				continue;
			}

			$result[ $report_name ] = [
				'report' => $reporter->get_report( $format ),
				'label' => $reporter->get_title(),
			];

			if ( ! empty( $report_info['sub'] ) ) {
				$result[ $report_name ]['sub'] = $this->load_reports( $report_info['sub'] );
			}
		}

		return $result;
	}

	/**
	 * Create a report.
	 *
	 * Register a new report that will be displayed in Elementor system info page.
	 *
	 * @param array $properties Report properties.
	 *
	 * @return \WP_Error|false|Base Base instance if the report was created,
	 *                                       False or WP_Error otherwise.
	 *@since 2.9.0
	 * @access public
	 *
	 */
	public function create_reporter( array $properties ) {
		$properties = Model_Helper::prepare_properties( $this->get_settings( 'reporter_properties' ), $properties );

		$reporter_class = $properties['class_name'] ? $properties['class_name'] : $this->get_reporter_class( $properties['name'] );

		$reporter = new $reporter_class( $properties );

		if ( ! ( $reporter instanceof Base ) ) {
			return new \WP_Error( 'Each reporter must to be an instance or sub-instance of `Base` class.' );
		}

		if ( ! $reporter->is_enabled() ) {
			return false;
		}

		return $reporter;
	}

	/**
	 * Print report.
	 *
	 * Output the system info page reports using an output template.
	 *
	 * @since 2.9.0
	 * @access public
	 *
	 * @param array  $reports  An array of system info reports.
	 * @param string $template Output type from the templates folder. Available
	 *                         templates are `raw` and `html`. Default is `raw`.
	 */
	public function print_report( $reports, $template = 'raw' ) {
		static $tabs_count = 0;

		static $required_plugins_properties = [
			'Name',
			'Version',
			'URL',
			'Author',
		];

		$template_path = __DIR__ . '/templates/' . $template . '.php';

		require $template_path;
	}

	/**
	 * Get allowed reports.
	 *
	 * Retrieve the available reports in Elementor system info page.
	 *
	 * @since 2.9.0
	 * @access public
	 * @static
	 *
	 * @return array Available reports in Elementor system info page.
	 */
	public static function get_allowed_reports() {
		return self::$reports;
	}

	/**
	 * Add report.
	 *
	 * Register a new report to Elementor system info page.
	 *
	 * @since 2.9.0
	 * @access public
	 * @static
	 *
	 * @param string $report_name The name of the report.
	 * @param array  $report_info Report info.
	 */
	public static function add_report( $report_name, $report_info ) {
		self::$reports[ $report_name ] = $report_info;
	}
}
