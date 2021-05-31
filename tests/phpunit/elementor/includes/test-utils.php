<?php
namespace Elementor\Testing\Includes;

use Elementor\Core\Settings\Page\Manager;
use Elementor\Plugin;
use Elementor\Utils;
use Elementor\Testing\Elementor_Test_Base;

class Elementor_Test_Utils extends Elementor_Test_Base {

	const BASE_LINK = 'https://elementor.com/pro/?utm_source=wp-role-manager&utm_campaign=gopro&utm_medium=wp-dash';

	public function test_should_return_elementor_pro_link() {
		$this->assertSame( self::BASE_LINK . '&utm_term=twentynineteen', Utils::get_pro_link( self::BASE_LINK ) );
	}

	public function test_should_return_source_of_placeholder_image() {
		$this->assertSame( ELEMENTOR_ASSETS_URL . 'images/placeholder.png', Utils::get_placeholder_image_src() );
	}

	public function test_should_return_edit_link() {
		$post_id = $this->factory()->create_and_get_default_post()->ID;
		$document = Plugin::$instance->documents->get( $post_id );
		$edit_link = $document->get_edit_url();
		$this->assertContains( '/post.php?post=', $edit_link );
		$this->assertContains( '&action=elementor', $edit_link );
	}

	/**
	 * @todo need to test when DOING_AJAX is 100% defined and when it's not.
	 */
	public function test_should_confirm_ajax() {
		if ( defined( 'DOING_AJAX' ) ) {
			$this->assertTrue( wp_doing_ajax() );
		} else {
			$this->assertFalse( wp_doing_ajax() );
		}
	}

	public function test_should_return_false_from_is_script_debug() {
		$this->assertSame( Utils::is_script_debug(), defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG );
	}

	public function test_should_get_preview_url() {
		$post_id = $this->factory()->create_and_get_default_post()->ID;
		$preview_url = Plugin::$instance->documents->get( $post_id )->get_preview_url();
		$this->assertContains( '/?p=', $preview_url );
		$this->assertContains( '&elementor-preview=', $preview_url );
		$this->assertContains( '&ver=', $preview_url );
	}

	public function test_should_get_wordpress_preview_url() {
		$post_id = $this->factory()->create_and_get_default_post()->ID;
		$wp_preview_url = Plugin::$instance->documents->get( $post_id )->get_wp_preview_url();
		$this->assertContains( '/?p=', $wp_preview_url );
		$this->assertContains( '&preview_nonce=', $wp_preview_url );
		$this->assertContains( '&preview=', $wp_preview_url );
	}

	/**
	 * @throws \Exception
	 */
	public function test_should_replace_0_urls() {
		$this->assertSame( '0 rows affected.', Utils::replace_urls( 'http://' . home_url() . '/elementor', 'https://' . home_url() . '/elementor' ) );
	}

	/**
	 * @expectedExceptionMessage The `from` and `to` URL's must be different
	 * @expectedException        \Exception
	 * @throws                   \Exception
	 */
	public function test_should_throw_error_because_urls_are_equal() {
		//$this->expectExceptionMessage( 'The `from` and `to` URL\'s must be different' );
		Utils::replace_urls( 'http://' . home_url() . '/elementor', 'http://' . home_url() . '/elementor' );
	}

	/**
	 * @expectedExceptionMessage The `from` and `to` URL's must be valid URL's
	 * @expectedException        \Exception
	 * @throws                   \Exception
	 */
	public function test_should_throw_error_because_urls_are_invalid() {
		Utils::replace_urls( 'elementor', '/elementor' );
	}


	public function test_should_not_get_exit_to_dashboard_url() {
		$post_id = $this->factory()->create_and_get_default_post()->ID;
		$this->assertNull( Plugin::$instance->documents->get( $post_id )->get_exit_to_dashboard_url() );
	}

	public function test_should_get_updated_timezone_string() {
		for ( $time_offset = 0; $time_offset < 13; $time_offset++ ) {
			update_option( 'gmt_offset', $time_offset );
			$this->assertSame( "UTC+$time_offset", Utils::get_timezone_string() );
		}
	}

	public function test_should_get_timezone_string_default_option() {
		delete_option( 'timezone_string' );
		delete_option( 'gmt_offset' );
		$this->assertSame( 'UTC+0', Utils::get_timezone_string() );
	}

	public function test_should_get_negative_timezone_string() {
		for ( $time_offset = -1; $time_offset > -13; $time_offset-- ) {
			update_option( 'gmt_offset', $time_offset );
			$this->assertSame( "UTC$time_offset", Utils::get_timezone_string() );
		}
	}

	public function test_should_get_when_and_how_edited_the_post_last() {
		$post_id = $this->factory()->create_and_get_default_post()->ID;
		$this->assertRegExp( '/Last edited on \<time\>.*\<\/time\>\ by .*/', Plugin::$instance->documents->get( $post_id )->get_last_edited() );
	}

	public function test_should_get_post_auto_save() {
		$posts = $this->factory()->create_and_get_parent_and_child_posts();
		$this->assertEquals( $posts['child_id'], Utils::get_post_autosave( $posts['parent_id'], $posts['user_id'] )->ID );
	}

	public function test_getYoutubeId() {
		$youtube_id = '9uOETcuFjbE';
		$youtube_urls = [
			'https://www.youtube.com/watch?v=' . $youtube_id,
			'https://www.youtube.com/watch?v=' . $youtube_id . '&feature=player_embedded',
			'https://youtu.be/' . $youtube_id,
		];

		foreach ( $youtube_urls as $youtube_url ) {
			$video_properties = \Elementor\Embed::get_video_properties( $youtube_url );

			$this->assertEquals( $youtube_id, $video_properties['video_id'] );
		}

		$this->assertNull( \Elementor\Embed::get_video_properties( 'https://www.youtube.com/' ) );
	}

	/**
	 * Test Should Receive a string with HTML entities and return the string with the HTML Entities decoded
	 *
	 * This tests the urlencode_html_entities() utility method, to see that it properly decodes HTML Entities and then correctly URL-encodes them to be used in URLs, such as social media sharing
	 */
	public function test_should_return_decoded_string() {
		// This is a filter from the WordPress core wp_get_document_title() function.
		// The filter is used here to make the wp_get_document_title() function return our test title.
		add_filter( 'document_title_parts', function () {
			return [
				'title' => '"This is a string" with a variety of ‘HTML entities’. \'What?\' & (more) #stupid “things”'
			];
		} );

		$before_encoding = wp_get_document_title();

		$valid_encoding = '%22This%20is%20a%20string%22%20with%20a%20variety%20of%20%E2%80%98HTML%20entities%E2%80%99.%20%27What%3F%27%20%26%20%28more%29%20%23stupid%20%E2%80%9Cthings%E2%80%9D';
		$after_encoding = Utils::urlencode_html_entities( $before_encoding );

		$this->assertSame( $valid_encoding, $after_encoding );
	}

	public function test_is_empty() {
		$this->assertEquals( false, Utils::is_empty('0' ),
			"'0' is not empty" );

		$this->assertEquals( true, Utils::is_empty('' ),
			"'' is empty" );

		$this->assertEquals( true, Utils::is_empty( [], 'key' ),
			"[] with undefined key, is empty" );

		$this->assertEquals( true, Utils::is_empty( [ 'key' => '' ], 'key' ),
			"[ 'key' => '' ] is empty" );

		$this->assertEquals( false, Utils::is_empty( [ 'key' => '0' ], 'key' ),
			"[ 'key' => '0' ] is empty" );
	}

	public function test_replace_urls__ensure_page_settings() {
		// Arrange.
		$setting_key = 'some-url';
		$from_url = 'http://localhost/';
		$to_url = 'http://127.0.0.1/';

		$document = self::factory()->create_post();

		$document->update_meta( Manager::META_KEY, [ $setting_key => $from_url ] );

		// Act.
		$affected_rows = Utils::replace_urls( $from_url, $to_url );

		// Assert.
		$this->assertSame( '1 row affected.', $affected_rows );
		$this->assertSame( [ $setting_key => $to_url ], $document->get_meta( Manager::META_KEY ) );
	}
}
