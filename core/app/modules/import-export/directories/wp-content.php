<?php

namespace Elementor\Core\App\Modules\ImportExport\Directories;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WP_Content extends Base {

	protected function get_name() {
		return 'wp-content';
	}

	protected function get_default_sub_directories() {
		$post_types = [ 'page', 'post' ];

		$sub_directories = [];

		foreach ( $post_types as $post_type ) {
			$sub_directories[] = new WP_Post_Type( $this->iterator, $this, $post_type );
		}

		return $sub_directories;
	}
}
