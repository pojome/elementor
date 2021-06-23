<?php

namespace Elementor\Core\App\Modules\ImportExport\Directories;

use Elementor\Modules\LandingPages\Module as Landing_Pages_Module;
use Elementor\TemplateLibrary\Source_Local;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WP_Content extends Base {

	protected function get_name() {
		return 'wp-content';
	}

	protected function get_default_sub_directories() {
		$post_types = get_post_types( [
			'public' => true,
			'can_export' => true,
		] );

		unset(
			$post_types['attachment'],
			$post_types[ Landing_Pages_Module::CPT ],
			$post_types[ Source_Local::CPT ]
		);

		$sub_directories = [];

		foreach ( $post_types as $post_type ) {
			$sub_directories[] = new WP_Post_Type( $this->iterator, $this, $post_type );
		}

		return $sub_directories;
	}
}
