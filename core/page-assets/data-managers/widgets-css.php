<?php
namespace Elementor\Core\Page_Assets\Data_Managers;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor Assets Data.
 *
 * @since 3.3.0
 */
class Widgets_Css extends Base {
	protected $content_type = 'css';

	protected $assets_category = 'widgets';

	protected function get_asset_content() {
		$asset_css_file_size = $this->get_file_data( 'size' );

		$widget_css = '';

		if ( $asset_css_file_size ) {
			// If the file size is larger than 8KB then calling the external CSS file, otherwise, printing inline CSS.
			if ( $asset_css_file_size > 8000 ) {
				$asset_url = $this->get_config_data( 'file_url' );

				$widget_css = sprintf( '<link rel="stylesheet" href="%s">', $asset_url );
			} else {
				$widget_css = $this->get_file_data( 'content' );
				$widget_css = sprintf( '<style>%s</style>', $widget_css );
			}
		}

		return $widget_css;
	}
}
