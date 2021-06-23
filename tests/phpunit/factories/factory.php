<?php
namespace Elementor\Testing\Factories;

use Elementor\Core\Base\Document;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Factory extends \WP_UnitTest_Factory {

	/**
	 * @var Documents
	 */
	public $documents;

	/**
	 * Factory constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->documents = new Documents();
	}

	/**
	 * @return \WP_Post
	 */
	public function get_default_post() {
		return $this->post->create_and_get();
	}

	/**
	 * @return \WP_Post
	 */
	public function create_and_get_default_post() {
		return $this->post->create_and_get();
	}

	/**
	 * @param array $args
	 *
	 * @return \WP_Post
	 */
	public function get_custom_post( $args ) {
		$custom_post = clone $this->get_default_post();

		$this->post->update_object( $custom_post->ID, $args );

		return $custom_post;
	}

	/**
	 * @param $args
	 *
	 * @return array|null|\WP_Post
	 */
	public function create_and_get_custom_post( $args ) {
		return $this->post->create_and_get( $args );
	}

	/**
	 * @return array parent_id | int; child_id | int; user_id | int.
	 */
	public function create_and_get_parent_and_child_posts() {
		$user_id = $this->user->create( [ 'display_name' => 'elementor' ] );
		$post_id = $this->post->create(
			[
				'post_author' => $user_id,
				'post_date' => '2014-11-11 23:45:30',
				'post_type' => 'revision',
			]
		);
		$inherent_post_id = $this->post->create(
			[
				'post_date' => '2014-11-12 23:45:30',
				'post_type' => 'revision',
				'post_author' => $user_id,
				'post_parent' => $post_id,
				'post_name' => $post_id . '-autosave',

			]
		);

		return [
			'parent_id' => $post_id,
			'child_id' => $inherent_post_id,
			'user_id' => $user_id,
		];
	}

	/**
	 * @return \WP_User
	 */
	public function create_and_get_administrator_user() {
		return $this->user->create_and_get( [ 'role' => 'administrator' ] );
	}

	/**
	 * @return \WP_User
	 */
	public function get_administrator_user() {
		return $this->user->create_and_get( [ 'role' => 'administrator' ] );
	}

	/**
	 * @return \WP_User
	 */
	public function get_subscriber_user() {
		return $this->user->create_and_get( [ 'role' => 'subscriber' ] );
	}

	/**
	 * @return \WP_User
	 */
	public function get_editor_user() {
		return $this->user->create_and_get( [ 'role' => 'editor' ] );
	}

	/**
	 * @return Document|false
	 */
	public function create_post() {
		$admin = $this->create_and_get_administrator_user();

		wp_set_current_user( $admin->ID );

		$post = $this->create_and_get_custom_post( [
			'post_author' => $admin->ID,
			'post_type' => 'post',
		] );

		add_post_meta( $post->ID, '_elementor_edit_mode', 'builder' );

		$document = Plugin::$instance->documents->get( $post->ID );

		return $document;
	}
}
