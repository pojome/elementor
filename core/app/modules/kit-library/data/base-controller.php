<?php
namespace Elementor\Core\App\Modules\KitLibrary\Data;

use Elementor\Plugin;
use Elementor\Data\Base\Controller;
use Elementor\Modules\Library\User_Favorites;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

abstract class Base_Controller extends Controller {
	/**
	 * @var Repository
	 */
	private $repository;

	/**
	 * @return Repository
	 */
	public function get_repository() {
		if ( ! $this->repository ) {
			$this->repository = new Repository(
				Plugin::$instance->common->get_component( 'connect' )->get_app( 'kit-library' ),
				new User_Favorites( get_current_user_id() )
			);
		}

		return $this->repository;
	}
}
