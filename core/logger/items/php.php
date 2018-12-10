<?php

namespace Elementor\Core\Logger\Items;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PHP extends File {
	const FORMAT = 'PHP :date [type] message [file::line] X times';
}
