<?php
namespace Elementor;

use Elementor\Core\Files\Assets\Svg\Svg_Handler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor icons manager.
 *
 * Elementor icons manager handler class
 *
 * @since 2.4.0
 */
class Icons_Manager {

	const NEEDS_UPDATE_OPTION = 'icon_manager_needs_update';
	/**
	 * Tabs.
	 *
	 * Holds the list of all the tabs.
	 *
	 * @access private
	 * @static
	 * @since 2.4.0
	 * @var array
	 */
	private static $tabs;

	private static function get_needs_upgrade_option() {
		return get_option( 'elementor_' . self::NEEDS_UPDATE_OPTION, null );
	}

	/**
	 * register styles
	 *
	 * Used to register all icon types stylesheets so they could be enqueued later by widgets
	 */
	public function register_styles() {
		$config = self::get_icon_manager_tabs_config();

		$shared_styles = [];

		foreach ( $config as $type => $icon_type ) {
			if ( ! isset( $icon_type['url'] ) ) {
				continue;
			}
			$dependencies = [];
			if ( ! empty( $icon_type['enqueue'] ) ) {
				foreach ( (array) $icon_type['enqueue'] as $font_css_url ) {
					if ( ! in_array( $font_css_url, array_keys( $shared_styles ) ) ) {
						$style_handle = 'elementor-icons-shared-' . count( $shared_styles );
						wp_register_style(
							$style_handle,
							$font_css_url,
							[],
							$icon_type['ver']
						);
						$shared_styles[ $font_css_url ] = $style_handle;
					}
					$dependencies[] = $shared_styles[ $font_css_url ];
				}
			}
			wp_register_style(
				'elementor-icons-' . $icon_type['name'],
				$icon_type['url'],
				$dependencies,
				$icon_type['ver']
			);
		}
	}

	/**
	 * Init Tabs
	 *
	 * Initiate Icon Manager Tabs.
	 *
	 * @access private
	 * @static
	 * @since 2.4.0
	 */
	private static function init_tabs() {
		self::$tabs = apply_filters( 'elementor/icons_manager/native', [
			'fa-regular' => [
				'name' => 'fa-regular',
				'label' => __( 'Font Awesome - Regular', 'elementor' ),
				'url' => self::get_fa_asset_url( 'regular' ),
				'enqueue' => [ self::get_fa_asset_url( 'fontawesome' ) ],
				'prefix' => 'fa-',
				'displayPrefix' => 'far',
				'labelIcon' => 'fab fa-font-awesome-alt',
				'ver' => '5.15.1',
				'fetchJson' => self::get_fa_asset_url( 'regular', 'js', false ),
				'native' => true,
			],
			'fa-solid' => [
				'name' => 'fa-solid',
				'label' => __( 'Font Awesome - Solid', 'elementor' ),
				'url' => self::get_fa_asset_url( 'solid' ),
				'enqueue' => [ self::get_fa_asset_url( 'fontawesome' ) ],
				'prefix' => 'fa-',
				'displayPrefix' => 'fas',
				'labelIcon' => 'fab fa-font-awesome',
				'ver' => '5.15.1',
				'fetchJson' => self::get_fa_asset_url( 'solid', 'js', false ),
				'native' => true,
			],
			'fa-brands' => [
				'name' => 'fa-brands',
				'label' => __( 'Font Awesome - Brands', 'elementor' ),
				'url' => self::get_fa_asset_url( 'brands' ),
				'enqueue' => [ self::get_fa_asset_url( 'fontawesome' ) ],
				'prefix' => 'fa-',
				'displayPrefix' => 'fab',
				'labelIcon' => 'fab fa-font-awesome-flag',
				'ver' => '5.15.1',
				'fetchJson' => self::get_fa_asset_url( 'brands', 'js', false ),
				'native' => true,
			],
		] );
	}

	/**
	 * Get Icon Manager Tabs
	 * @return array
	 */
	public static function get_icon_manager_tabs() {
		if ( ! self::$tabs ) {
			self::init_tabs();
		}
		$additional_tabs = apply_filters( 'elementor/icons_manager/additional_tabs', [] );
		return array_merge( self::$tabs, $additional_tabs );
	}

	public static function enqueue_shim() {
		wp_enqueue_script(
			'font-awesome-4-shim',
			self::get_fa_asset_url( 'v4-shims', 'js' ),
			[],
			ELEMENTOR_VERSION
		);
		// Make sure that the CSS in the 'all' file does not override FA Pro's CSS
		if ( ! wp_script_is( 'font-awesome-pro' ) ) {
			wp_enqueue_style(
				'font-awesome-5-all',
				self::get_fa_asset_url( 'all' ),
				[],
				ELEMENTOR_VERSION
			);
		}
		wp_enqueue_style(
			'font-awesome-4-shim',
			self::get_fa_asset_url( 'v4-shims' ),
			[],
			ELEMENTOR_VERSION
		);
	}

	private static function get_fa_asset_url( $filename, $ext_type = 'css', $add_suffix = true ) {
		static $is_test_mode = null;
		if ( null === $is_test_mode ) {
			$is_test_mode = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || defined( 'ELEMENTOR_TESTS' ) && ELEMENTOR_TESTS;
		}
		$url = ELEMENTOR_ASSETS_URL . 'lib/font-awesome/' . $ext_type . '/' . $filename;
		if ( ! $is_test_mode && $add_suffix ) {
			$url .= '.min';
		}

		return $url . '.' . $ext_type;
	}

	public static function get_icon_manager_tabs_config() {
		$tabs = [
			'all' => [
				'name' => 'all',
				'label' => __( 'All Icons', 'elementor' ),
				'labelIcon' => 'eicon-filter',
				'native' => true,
			],
		];

		return array_values( array_merge( $tabs, self::get_icon_manager_tabs() ) );
	}

	/**
	 * is_font_awesome_inline
	 *
	 * @return bool
	 */
	private static function is_font_awesome_inline() {
		return Plugin::$instance->experiments->is_feature_active( 'e_font_awesome_inline' );
	}

	/**
	 * store_svg_symbols
	 *
	 *
	 */
	public static function store_svg_symbols() {
		global $post;

		$symbols = apply_filters( 'elementor/icons_manager/svg_symbols', [] );

		if ( ! count( $symbols ) ) {
			return;
		}

		update_post_meta( $post->ID, '_elementor_svg_symbols', $symbols );
	}

	/**
	 * render_svg_symbols
	 *
	 */
	public static function render_svg_symbols() {
		global $post;
		$saved_symbols = get_post_meta( $post->ID, '_elementor_svg_symbols' )[0];
		$symbols = ! empty( $saved_symbols ) ? $saved_symbols : [];

		if ( ! count( $symbols ) ) {
			return;
		}

		$svg = '<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">';

		foreach ( $symbols as $symbol_id => $icon ) {
			$symbol = self::get_icon_svg_data( $icon );

			$svg .= '<symbol id="' . $symbol_id . '" viewBox="0 0 ' . $symbol['width'] . ' ' . $symbol['height'] . '">';
			$svg .= '<path d="' . $symbol['path'] . '"></path>';
			$svg .= '</symbol>';
		}

		$svg .= '</svg>';

		echo $svg;
	}

	/**
	 * get_font_awesome_svg_from_library
	 * @param $icon array ( 'value' => string, 'library' => string )
	 *
	 * @return array|mixed
	 */
	private static function get_font_awesome_svg_from_library( $icon ) {
		preg_match( '/fa(.*) fa-/', $icon['value'], $matches );
		$icon_name = str_replace( $matches[0], '', $icon['value'] );
		$filename = str_replace( 'fa-', '', $icon['library'] );
		$icon_list_url = self::get_fa_asset_url( $filename, 'json', false );
		$icon_list = json_decode( file_get_contents( $icon_list_url ), true );

		return $icon_list[ 'icons' ][ $icon_name ];
	}

	public static function get_icon_svg_data( $icon ) {
		$icon_option_key = str_replace( ' fa-', '-', $icon['value'] );  // i.e. 'fab-apple' | 'far-cart'
		$icon_data = get_option( $icon_option_key );

		if ( empty( $icon_data ) ) {
			// On first use, load the SVG from the Font Awesome json file
			$icon_data = self::get_font_awesome_svg_from_library( $icon );
			$icon_data = [
				'width' => $icon_data[0],
				'height' => $icon_data[1],
				'key' => $icon_option_key,
				'path' => $icon_data[4],
			];

			// Save the $icon_data in the database for future renders
			update_option( $icon_option_key, $icon_data );
		}

		return $icon_data;
	}

	/**
	 * render_font_awesome_svg
	 * @param $icon array [ 'value' => string, 'library' => string ]
	 *
	 * @return bool|mixed|string
	 */
	public static function render_font_awesome_svg( $icon ) {
		$is_edit_mode = Plugin::$instance->editor->is_edit_mode();

		// Load the SVG from the database
		$icon_data = self::get_icon_svg_data( $icon );

		// Add the icon data to the symbols array for later use in page rendering process.
		add_filter( 'elementor/icons_manager/svg_symbols', function( $symbols ) use ( $icon_data, $icon ) {
			if ( ! in_array( $icon_data[ 'key' ], $symbols ) ) {
				$symbols[ $icon_data['key'] ] = $icon;
			}

			return $symbols;
		} );

		// If in edit mode inline the full svg, otherwise use the symbol
		if ( $is_edit_mode ) {
			return '<svg xmlns="http://www.w3.org/2000/svg"
				viewBox="0 0 ' . $icon_data['width'] . ' ' . $icon_data['height'] . '">
				<path d="' . $icon_data['path'] . '"></path>
			</svg>';
		}

		return '<svg><use xlink:href="#'. $icon_data['key'] .'" /></svg>';
	}

	private static function render_svg_icon( $value ) {
		if ( ! isset( $value['id'] ) ) {
			return '';
		}

		return Svg_Handler::get_inline_svg( $value['id'] );
	}

	private static function is_fa5_icon( $icon ) {
		preg_match( '/fa(.*) fa-/', $icon['value'], $matches );

		return ! empty( $matches );
	}

	public static function render_icon_html( $icon, $attributes = [], $tag = 'i' ) {
		$icon_types = self::get_icon_manager_tabs();
		if ( isset( $icon_types[ $icon['library'] ]['render_callback'] ) && is_callable( $icon_types[ $icon['library'] ]['render_callback'] ) ) {
			return call_user_func_array( $icon_types[ $icon['library'] ]['render_callback'], [ $icon, $attributes, $tag ] );
		}

		$content = '';

		if ( self::is_font_awesome_inline() && self::is_fa5_icon( $icon ) ) {
			$content = self::render_font_awesome_svg( $icon );
		}

		if ( ! self::is_font_awesome_inline() || ! self::is_fa5_icon( $icon ) ) {
			if ( empty( $attributes['class'] ) ) {
				$attributes['class'] = $icon['value'];
			} else {
				if ( is_array( $attributes['class'] ) ) {
					$attributes['class'][] = $icon['value'];
				} else {
					$attributes['class'] .= ' ' . $icon['value'];
				}
			}
		}

		return '<' . $tag . ' ' . Utils::render_html_attributes( $attributes ) . '>' . $content . '</' . $tag . '>';
	}

	/**
	 * Render Icon
	 *
	 * Used to render Icon for \Elementor\Controls_Manager::ICONS
	 * @param array $icon             Icon Type, Icon value
	 * @param array $attributes       Icon HTML Attributes
	 * @param string $tag             Icon HTML tag, defaults to <i>
	 *
	 * @return mixed|string
	 */
	public static function render_icon( $icon, $attributes = [], $tag = 'i' ) {
		if ( empty( $icon['library'] ) ) {
			return false;
		}
		$output = '';
		// handler SVG Icon
		if ( 'svg' === $icon['library'] ) {
			$output = self::render_svg_icon( $icon['value'] );
		} else {
			$output = self::render_icon_html( $icon, $attributes, $tag );
		}

		echo $output;

		return true;
	}

	/**
	 * Font Awesome 4 to font Awesome 5 Value Migration
	 *
	 * used to convert string value of Icon control to array value of Icons control
	 * ex: 'fa fa-star' => [ 'value' => 'fas fa-star', 'library' => 'fa-solid' ]
	 *
	 * @param $value
	 *
	 * @return array
	 */
	public static function fa4_to_fa5_value_migration( $value ) {
		static $migration_dictionary = false;
		if ( '' === $value ) {
			return [
				'value' => '',
				'library' => '',
			];
		}
		if ( false === $migration_dictionary ) {
			$migration_dictionary = json_decode( file_get_contents( ELEMENTOR_ASSETS_PATH . 'lib/font-awesome/migration/mapping.js' ), true );
		}
		if ( isset( $migration_dictionary[ $value ] ) ) {
			return $migration_dictionary[ $value ];
		}

		return [
			'value' => 'fas ' . str_replace( 'fa ', '', $value ),
			'library' => 'fa-solid',
		];
	}

	/**
	 * on_import_migration
	 * @param array $element        settings array
	 * @param string $old_control   old control id
	 * @param string $new_control   new control id
	 * @param bool $remove_old      boolean weather to remove old control or not
	 *
	 * @return array
	 */
	public static function on_import_migration( array $element, $old_control = '', $new_control = '', $remove_old = false ) {

		if ( ! isset( $element['settings'][ $old_control ] ) || isset( $element['settings'][ $new_control ] ) ) {
			return $element;
		}

		// Case when old value is saved as empty string
		$new_value = [
			'value' => '',
			'library' => '',
		];

		// Case when old value needs migration
		if ( ! empty( $element['settings'][ $old_control ] ) && ! self::is_migration_allowed() ) {
			$new_value = self::fa4_to_fa5_value_migration( $element['settings'][ $old_control ] );
		}

		$element['settings'][ $new_control ] = $new_value;

		//remove old value
		if ( $remove_old ) {
			unset( $element['settings'][ $old_control ] );
		}

		return $element;
	}

	/**
	 * is_migration_allowed
	 * @return bool
	 */
	public static function is_migration_allowed() {
		static $migration_allowed = false;
		if ( false === $migration_allowed ) {
			$migration_allowed = null === self::get_needs_upgrade_option();

			/**
			 * allowed to filter migration allowed
			 */
			$migration_allowed = apply_filters( 'elementor/icons_manager/migration_allowed', $migration_allowed );
		}
		return $migration_allowed;
	}

	/**
	 * Register_Admin Settings
	 *
	 * adds Font Awesome migration / update admin settings
	 * @param Settings $settings
	 */
	public function register_admin_settings( Settings $settings ) {
		$settings->add_field(
			Settings::TAB_ADVANCED,
			Settings::TAB_ADVANCED,
			'load_fa4_shim',
			[
				'label' => __( 'Load Font Awesome 4 Support', 'elementor' ),
				'field_args' => [
					'type' => 'select',
					'std' => 1,
					'options' => [
						'' => __( 'No', 'elementor' ),
						'yes' => __( 'Yes', 'elementor' ),
					],
					'desc' => __( 'Font Awesome 4 support script (shim.js) is a script that makes sure all previously selected Font Awesome 4 icons are displayed correctly while using Font Awesome 5 library.', 'elementor' ),
				],
			]
		);
	}

	public function register_admin_tools_settings( Tools $settings ) {
		$settings->add_tab( 'fontawesome4_migration', [ 'label' => __( 'Font Awesome Upgrade', 'elementor' ) ] );

		$settings->add_section( 'fontawesome4_migration', 'fontawesome4_migration', [
			'callback' => function() {
				echo '<h2>' . esc_html__( 'Font Awesome Upgrade', 'elementor' ) . '</h2>';
				echo '<p>' .
				__( 'Access 1,500+ amazing Font Awesome 5 icons and enjoy faster performance and design flexibility.', 'elementor' ) . '<br>' .
				__( 'By upgrading, whenever you edit a page containing a Font Awesome 4 icon, Elementor will convert it to the new Font Awesome 5 icon.', 'elementor' ) .
				'</p><p><strong>' .
				__( 'Please note that the upgrade process may cause some of the previously used Font Awesome 4 icons to look a bit different due to minor design changes made by Font Awesome.', 'elementor' ) .
				'</strong></p><p>' .
				__( 'The upgrade process includes a database update', 'elementor' ) . ' - ' .
				__( 'We highly recommend backing up your database before performing this upgrade.', 'elementor' ) .
				'</p>' .
				__( 'This action is not reversible and cannot be undone by rolling back to previous versions.', 'elementor' ) .
				'</p>';
			},
			'fields' => [
				[
					'label'      => __( 'Font Awesome Upgrade', 'elementor' ),
					'field_args' => [
						'type' => 'raw_html',
						'html' => sprintf( '<span data-action="%s" data-_nonce="%s" class="button" id="elementor_upgrade_fa_button">%s</span>',
							self::NEEDS_UPDATE_OPTION . '_upgrade',
							wp_create_nonce( self::NEEDS_UPDATE_OPTION ),
							__( 'Upgrade To Font Awesome 5', 'elementor' )
						),
					],
				],
			],
		] );
	}

	/**
	 * Ajax Upgrade to FontAwesome 5
	 */
	public function ajax_upgrade_to_fa5() {
		check_ajax_referer( self::NEEDS_UPDATE_OPTION, '_nonce' );

		delete_option( 'elementor_' . self::NEEDS_UPDATE_OPTION );

		wp_send_json_success( [ 'message' => '<p>' . __( 'Hurray! The upgrade process to Font Awesome 5 was completed successfully.', 'elementor' ) . '</p>' ] );
	}

	/**
	 * Add Update Needed Flag
	 * @param array $settings
	 *
	 * @return array;
	 */
	public function add_update_needed_flag( $settings ) {
		$settings['icons_update_needed'] = true;
		return $settings;
	}

	public function enqueue_fontawesome_css() {
		if ( ! self::is_migration_allowed() ) {
			wp_enqueue_style( 'font-awesome' );
		} else {
			$current_filter = current_filter();
			$load_shim = get_option( 'elementor_load_fa4_shim', false );
			if ( 'elementor/editor/after_enqueue_styles' === $current_filter ) {
				self::enqueue_shim();
			} else if ( 'yes' === $load_shim ) {
				self::enqueue_shim();
			}
		}
	}

	/**
	 * @deprecated 3.1.0
	 */
	public function add_admin_strings() {
		Plugin::$instance->modules_manager->get_modules( 'dev-tools' )->deprecation->deprecated_function( __METHOD__, '3.1.0' );

		return [];
	}

	/**
	 * @since 3.0.0
	 * @deprecated 3.0.0
	 */
	public function register_ajax_actions() {
		_deprecated_function( __METHOD__, '3.0.0' );
	}

	/**
	 * @since 3.0.0.
	 * @deprecated 3.0.0
	 */
	public function ajax_enable_svg_uploads() {
		_deprecated_function( __METHOD__, '3.0.0' );
	}

	/**
	 * Icons Manager constructor
	 */
	public function __construct() {
		if ( is_admin() ) {
			// @todo: remove once we deprecate fa4
			add_action( 'elementor/admin/after_create_settings/' . Settings::PAGE_ID, [ $this, 'register_admin_settings' ], 100 );
		}

		if ( self::is_font_awesome_inline() ) {
			add_action( 'wp_footer', [ $this, 'store_svg_symbols' ], 10 );
			add_action( 'wp_footer', [ $this, 'render_svg_symbols' ] );
			add_action( 'wp_footer', function() {
				echo  '<style> .elementor-icon { display: inline-block; } .elementor-icon svg { width: 1em; height: 1em; display: block; }</style>';
			} );
			add_action( 'elementor/editor/after_save', function( $post_id ) {
				update_post_meta( $post_id, '_elementor_svg_symbols', [] );
			} );
		}

		add_action( 'elementor/frontend/after_enqueue_styles', [ $this, 'enqueue_fontawesome_css' ] );
		add_action( 'elementor/frontend/after_register_styles', [ $this, 'register_styles' ] );

		if ( ! self::is_migration_allowed() ) {
			add_filter( 'elementor/editor/localize_settings', [ $this, 'add_update_needed_flag' ] );
			add_action( 'elementor/admin/after_create_settings/' . Tools::PAGE_ID, [ $this, 'register_admin_tools_settings' ], 100 );

			if ( ! empty( $_POST ) ) { // phpcs:ignore -- nonce validation done in callback
				add_action( 'wp_ajax_' . self::NEEDS_UPDATE_OPTION . '_upgrade', [ $this, 'ajax_upgrade_to_fa5' ] );
			}
		}
	}
}
