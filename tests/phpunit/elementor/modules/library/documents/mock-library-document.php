<?php
namespace Elementor\Tests\Phpunit\Elementor\Modules\Library\Documents;

use Elementor\Modules\Library\Documents\Library_Document as Base_Library_Document;

class Mock_Library_Document extends Base_Library_Document {
	public static function get_type() {
		return 'library-type-example';
	}
}
