<?php

namespace Elementor\Tests\Phpunit\Elementor\Core\Experiments;

use Elementor\Core\Experiments\Manager as Experiments_Manager;
use Elementor\Core\Upgrade\Manager;
use Elementor\Testing\Elementor_Test_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Test_Manager extends Elementor_Test_Base {

	public function tearDown() {
		parent::tearDown();

		$experiments = $this->elementor()->experiments;

		$features = $experiments->get_features();

		foreach ( $features as $feature_name => $feature ) {
			$experiments->remove_feature( $feature_name );
		}
	}

	public function test_add_feature() {
		$test_feature_data = [
			'default' => Experiments_Manager::STATE_ACTIVE,
			'unaccepted_key' => 'some value',
		];

		$test_set = [
			'description' => '',
			'release_status' => Experiments_Manager::RELEASE_STATUS_ALPHA,
			'default' => Experiments_Manager::STATE_ACTIVE,
			'name' => 'test_feature',
			'state' => Experiments_Manager::STATE_DEFAULT,
			'on_state_change' => null,
			'new_site' => [
				'default_active' => false,
				'always_active' => false,
				'minimum_installation_version' => null,
			],
			'mutable' => true,
		];

		$new_feature = $this->add_test_feature( $test_feature_data );

		$re_added_feature = $this->add_test_feature( $test_feature_data );

		$this->assertEquals( $test_set, $new_feature );

		$this->assertEquals( null, $re_added_feature );
	}

	public function test_get_features() {
		$experiments = $this->elementor()->experiments;

		// Assert that `get_features` is called correctly even before adding any feature
		$experiments->get_features();

		$this->add_test_feature();

		$features = $experiments->get_features();

		$test_feature = $experiments->get_features( 'test_feature' );

		$this->assert_array_have_keys( [ 'test_feature' ], $features );

		$this->assertNotEmpty( $test_feature );
	}

	public function test_get_active_features() {
		$this->add_test_feature();

		$experiments = $this->elementor()->experiments;

		$experiments->set_feature_default_state( 'test_feature', Experiments_Manager::STATE_DEFAULT );

		$default_activated_test_feature_data = [
			'name' => 'default_activated_test_feature',
			'default' => Experiments_Manager::STATE_ACTIVE,
		];

		$this->add_test_feature( $default_activated_test_feature_data );

		$active_features = $this->elementor()->experiments->get_active_features();

		$expected_features = [
			'default_activated_test_feature' => [
				'description' => '',
				'release_status' => Experiments_Manager::RELEASE_STATUS_ALPHA,
				'default' => Experiments_Manager::STATE_ACTIVE,
				'on_state_change' => null,
				'name' => 'default_activated_test_feature',
				'state' => Experiments_Manager::STATE_DEFAULT,
				'new_site' => [
					'default_active' => false,
					'always_active' => false,
					'minimum_installation_version' => null,
				],
				'mutable' => true,
			],
		];

		$this->assertEquals( $expected_features, $active_features );
	}

	public function test_is_feature_active() {
		$this->add_test_feature();

		$is_test_feature_active = $this->elementor()->experiments->is_feature_active( 'test_feature' );

		$this->assertTrue( $is_test_feature_active );
	}

	public function test_is_feature_active__new_site() {
		update_option( Manager::INSTALLS_HISTORY_META, [
			time() => '3.1.0',
		] );

		$this->add_test_feature( [
			'default' => Experiments_Manager::STATE_INACTIVE,
			'new_site' => [
				'default_active' => true,
				'minimum_installation_version' => '3.1.0',
			],
		] );

		$experiments = $this->elementor()->experiments;

		$is_test_feature_active = $experiments->is_feature_active( 'test_feature' );

		$this->assertTrue( $is_test_feature_active );
	}

	public function test_is_feature_active__immutable() {
		update_option( Manager::INSTALLS_HISTORY_META, [
			time() => '3.1.0',
		] );

		$this->add_test_feature( [
			'default' => Experiments_Manager::STATE_INACTIVE,
			'new_site' => [
				'always_active' => true,
				'minimum_installation_version' => '3.1.0',
			],
		] );

		$experiments = $this->elementor()->experiments;

		$is_test_feature_active = $experiments->is_feature_active( 'test_feature' );

		$test_feature = $experiments->get_features( 'test_feature' );

		$this->assertTrue( $is_test_feature_active );

		$this->assertFalse( $test_feature['mutable'] );
	}

	public function test_is_feature_active__default_activated() {
		$experiments = $this->elementor()->experiments;

		$default_activated_test_feature_data = [
			'default' => Experiments_Manager::STATE_ACTIVE,
		];

		$this->add_test_feature( $default_activated_test_feature_data );

		$is_default_activated_test_feature_active = $experiments->is_feature_active( 'test_feature' );

		$this->assertTrue( $is_default_activated_test_feature_active );
	}

	public function test_is_feature_active__not_exists() {
		$experiments = $this->elementor()->experiments;

		$is_non_exist_active = $experiments->is_feature_active( 'not_exists_feature' );

		$this->assertFalse( $is_non_exist_active );
	}

	public function test_is_feature_active__saved_state() {
		add_option( 'elementor_experiment-test_feature', Experiments_Manager::STATE_ACTIVE );

		$this->add_test_feature();

		$experiments = $this->elementor()->experiments;

		$is_test_feature_active = $experiments->is_feature_active( 'test_feature' );

		$this->assertTrue( $is_test_feature_active );
	}

	public function test_set_feature_default_state() {
		$this->add_test_feature();

		$experiments = $this->elementor()->experiments;

		$experiments->set_feature_default_state( 'test_feature', Experiments_Manager::STATE_ACTIVE );

		$feature = $experiments->get_features( 'test_feature' );

		$this->assertEquals( Experiments_Manager::STATE_ACTIVE, $feature['default'] );
	}

	public function test_remove_feature() {
		$this->add_test_feature();

		$experiments = $this->elementor()->experiments;

		$experiments->remove_feature( 'test_feature' );

		$feature = $experiments->get_features( 'test_feature' );

		$this->assertNull( $feature );
	}

	private function add_test_feature( array $args = [] ) {
		$experiments = $this->elementor()->experiments;

		$test_feature_data = [
			'name' => 'test_feature',
		];

		if ( $args ) {
			$test_feature_data = array_merge( $test_feature_data, $args );
		}

		return $experiments->add_feature( $test_feature_data );
	}
}
