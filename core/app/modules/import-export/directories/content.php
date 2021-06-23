<?php

namespace Elementor\Core\App\Modules\ImportExport\Directories;

use Elementor\TemplateLibrary\Source_Local;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Content extends Base {

	protected function get_name() {
		return 'content';
	}

	protected function get_default_sub_directories() {
		$post_types = get_post_types_by_support( 'elementor' );

		$sub_directories = [];

		foreach ( $post_types as $post_type ) {
			if ( Source_Local::CPT === $post_type ) {
				continue;
			}

			$sub_directories[] = new Post_Type( $this->iterator, $this, $post_type );
		}

		return $sub_directories;
	}
}
