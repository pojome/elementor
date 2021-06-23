<?php

namespace Elementor\Core\App\Modules\ImportExport\Compatibility;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Kit_Library extends Base_Adapter {
	public static function is_compatibility_needed( array $manifest_data, array $import_settings ) {
		return ! empty( $import_settings['referrer'] ) && 'kit-library' === $import_settings['referrer'];
	}

	public function get_manifest_data( array $manifest_data ) {
		if ( ! empty( $manifest_data['content']['page'] ) ) {
			foreach ( $manifest_data['content']['page'] as & $page ) {
				$page['thumbnail'] = false;
			}
		}

		if ( ! empty( $manifest_data['templates'] ) ) {
			foreach ( $manifest_data['templates'] as & $template ) {
				$template['thumbnail'] = false;
			}
		}

		return $manifest_data;
	}
}
