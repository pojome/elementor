<?php
namespace Elementor\Core\App\Modules\KitLibrary\Connect;

use Elementor\Core\Common\Modules\Connect\Apps\Library;
use Elementor\Core\Common\Modules\Connect\Apps\Base_App;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Kit_Library extends Library {
	// TODO: NEED TO BE CHANGED!
	const DEFAULT_BASE_ENDPOINT = 'https://kits.dev.elementor.red';

	public function get_title() {
		return __( 'Kit Library', 'elementor' );
	}

	public function get_all() {
		return $this->http_request( 'GET', 'kits' );
	}

	public function get_taxonomies() {
		return $this->http_request( 'GET', 'taxonomies' );
	}

	public function get_manifest( $id ) {
		return $this->http_request( 'GET', "manifests/{$id}" );
	}

	public function download_link( $id ) {
		return $this->http_request( 'GET', "kits/{$id}/download-link" );
	}

	protected function get_api_url() {
		return static::DEFAULT_BASE_ENDPOINT;
	}

	protected function init() {
		// Remove parent init actions.
	}
}
