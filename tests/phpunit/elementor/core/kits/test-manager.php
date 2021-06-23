<?php
namespace Elementor\Tests\Phpunit\Elementor\Core\Kits;

use Elementor\Core\Base\Document;
use Elementor\Core\Kits\Documents\Kit;
use Elementor\Core\Kits\Manager;
use Elementor\Plugin;
use Elementor\Testing\Elementor_Test_Base;
use Elementor\Core\Files\Manager as Files_Manager;
use Elementor\Modules\AdminBar\Module as Adminbar_Module;

class Test_Manager extends Elementor_Test_Base {
	public function test_create_default_kit() {
		// Arrange
		$manager = Plugin::$instance->kits_manager;
		$this->remove_default_kit();

		// Act
		$id = Manager::create_default_kit();

		// Assert
		$this->assertEquals( $manager->get_active_id(), $id );
	}

	public function test_get_active_kit() {
		// Arrange
		$manager = Plugin::$instance->kits_manager;

		// Act
		$kit = $manager->get_active_kit();

		// Assert
		$post = get_post( $manager->get_active_id() );

		$this->assertEquals( $post->ID, $kit->get_id() );
	}

	public function test_get_active_kit__when_there_is_no_kit(  ) {
		// Arrange
		$manager = Plugin::$instance->kits_manager;

		$this->remove_default_kit();

		// Act
		$kit = $manager->get_active_kit();

		// Assert
		$this->assertEquals( 0, $kit->get_id() );
	}


	public function test_get_active_kit__when_kit_is_in_trash() {
		// Arrange
		// Make sure the the 'wp_trash_post' function will actually trash the kit.
		$_GET['force_delete_kit'] = '1';

		$manager = Plugin::$instance->kits_manager;
		wp_trash_post( $manager->get_active_id() );

		// Act
		$kit = $manager->get_active_kit();

		// Assert
		$this->assertEquals( 0, $kit->get_id() );
	}

	public function test_when_updating_blogname_or_blogdescription_option_it_should_update_the_kit() {
		wp_set_current_user( $this->factory()->get_administrator_user()->ID );

		$kit = Plugin::$instance->kits_manager->get_active_kit();

		$custom_colors_array = [
			[
				'_id' => 'test-id',
				'title' => 'test',
				'color' => '#000000',
			],
		];

		$kit->update_settings(['custom_colors' => $custom_colors_array]);

		// Make sure that it fetched again from the DB and not from the cache.
		$kit = Plugin::$instance->documents->get( $kit->get_id(), false );

		$name = get_option( 'blogname' );
		$description = get_option( 'blogdescription' );

		$expected_name = 'Test name';
		$expected_description = 'Test description';

		$this->assertNotEquals( $name, $expected_name );
		$this->assertNotEquals( $description, $expected_description );

		update_option( 'blogname', $expected_name );
		update_option( 'blogdescription', $expected_description );

		$this->assertEquals( $expected_name, $kit->get_settings( 'site_name' ) );
		$this->assertEquals( $expected_description, $kit->get_settings( 'site_description' ) );
		$this->assertEquals( $custom_colors_array, $kit->get_settings( 'custom_colors' ), 'It should not remove the old kit settings.' );

		update_option( 'blogname', $name );
		update_option( 'blogdescription', $description );
	}

	public function test_it_should_clear_files_cache_when_saving_kit() {
		$kit = Plugin::$instance->kits_manager->get_active_kit();

		wp_set_current_user( $this->factory()->get_administrator_user()->ID );

		$file_manager = Plugin::instance()->files_manager;

		$mock = $this->getMockBuilder( Files_Manager::class )
		             ->setMethods( [ 'clear_cache' ] )
		             ->getMock();

		$mock->expects( $this->once() )
		     ->method( 'clear_cache' );

		Plugin::instance()->files_manager = $mock;

		$kit->save( [] );

		Plugin::instance()->files_manager = $file_manager;
	}

	public function test_before_trash_kit() {
		// Arrange
		$kit_id = Plugin::$instance->kits_manager->get_active_id();

		// Assert (Expect)
		$this->expectException(\WPDieException::class);

		// Act
		do_action( 'wp_trash_post', $kit_id );
	}

	public function test_before_trash_kit__when_permanently_deleting() {
		// Arrange
		$kit = Plugin::$instance->kits_manager->get_active_kit();

		// Assert (Expect)
		$this->expectException(\WPDieException::class);

		// Act
		do_action( 'before_delete_post', $kit->get_id() );
	}

	public function test_before_trash_kit__when_permanently_deleting_and_kit_in_trash() {
		// Arrange
		$kit = Plugin::$instance->kits_manager->get_active_kit();

		wp_update_post([
			'ID' => $kit->get_id(),
			'post_status' => 'trash'
		]);

		// Refresh cache.
		Plugin::$instance->documents->get( $kit->get_id(), false );

		// Act
		do_action( 'before_delete_post', $kit->get_id() );

		// This assert is just to make sure that the test is passed the exception.
		$this->assertTrue( true );
	}

	public function test_before_trash_kit__should_not_die_if_the_post_is_not_a_kit() {
		// Arrange
		$post_id = $this->factory()->post->create_and_get()->ID;

		// Act
		do_action( 'wp_trash_post', $post_id);

		// This assert is just to make sure that the test is passed the exception.
		$this->assertTrue( true );
	}

	public function test_before_trash_kit__should_not_die_if_force_trash_query_string_param_passed(  ) {
		// Arrange
		$_GET['force_delete_kit'] = '1';
		$kit_id = Plugin::$instance->kits_manager->get_active_id();

		// Act
		do_action( 'wp_trash_post', $kit_id );

		// This assert is just to make sure that the test is passed the exception.
		$this->assertTrue( true );
	}

	public function test_add_menu_in_admin_bar__ensure_menu_item() {
		global $post;

		$document = self::factory()->create_post();

		$post = $document->get_post();

		$expected = [
			'id' => 'elementor_site_settings',
			'title' => 'Site Settings',
			'sub_title' => 'Site',
			'href' => $document->get_edit_url() . '#' . Manager::E_HASH_COMMAND_OPEN_SITE_SETTINGS,
			'class' => 'elementor-site-settings',
			'parent_class' => 'elementor-second-section',
		];

		$adminbar_settings = ( new Adminbar_Module() )->get_settings();

		$this->assertEqualSets( $expected, $adminbar_settings['elementor_edit_page']['children'][0] );
	}

	public function test_add_menu_in_admin_bar__not_built_with_elementor_ensure_item_from_recent_edited_post() {
		global $post;

		$document_that_built_with_elementor = self::factory()->create_post();
		wp_update_post( $document_that_built_with_elementor->get_post() );

		$document_not_built_with_elementor = self::factory()->create_post();
		$document_not_built_with_elementor->update_meta( Document::BUILT_WITH_ELEMENTOR_META_KEY, false );

		$post = $document_not_built_with_elementor->get_post();

		$adminbar_settings = ( new Adminbar_Module() )->get_settings();
		$expected = $document_that_built_with_elementor->get_edit_url() . '#' . Manager::E_HASH_COMMAND_OPEN_SITE_SETTINGS;

		$this->assertEquals( $expected, $adminbar_settings['elementor_edit_page']['children'][0]['href'] );
	}
}
