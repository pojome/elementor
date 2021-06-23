<?php
namespace Elementor\Tests\Phpunit\Elementor\Core\Common\Modules\Connect\Apps\Mock;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once __DIR__ . '/mock-app.php';

class Mock_App_Multiple_Urls extends Mock_App {
	const BASE_URL = 'localhost';
	const FAllBACK_URL = 'localhost2';
	const FAllBACK2_URL = 'localhost3';

	protected function get_api_url() {
		return [
			static::BASE_URL,
			static::FAllBACK_URL,
			static::FAllBACK2_URL,
		];
	}
}
