<?php
namespace Elementor\Testing\Core\Base\Mock;

use Elementor\Core\Upgrade\Upgrades;

class Mock_Upgrades {
	public static function _on_each_version( $updater ) {
		Upgrades::_on_each_version( $updater );
	}
}
