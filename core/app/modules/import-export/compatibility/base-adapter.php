<?php

namespace Elementor\Core\App\Modules\ImportExport\Compatibility;

use Elementor\Core\App\Modules\ImportExport\Import;
use Elementor\Core\Base\Base_Object;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class Base_Adapter extends Base_Object {

	protected $importer;

	public static function is_compatibility_needed( array $manifest_data, array $import_settings ) {
		return false;
	}

	public function get_manifest_data( array $manifest_data ) {
		return $manifest_data;
	}

	public function get_template_data( array $template_data, array $template_settings ) {
		return $template_data;
	}

	public function __construct( Import $importer ) {
		$this->importer = $importer;
	}
}
