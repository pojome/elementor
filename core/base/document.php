<?php
namespace Elementor\Core\Base;

use Elementor\Core\Base\Elements_Iteration_Actions\Assets as Assets_Iteration_Action;
use Elementor\Core\Base\Elements_Iteration_Actions\Base as Elements_Iteration_Action;
use Elementor\Core\Files\CSS\Post as Post_CSS;
use Elementor\Core\Settings\Page\Model as Page_Model;
use Elementor\Core\Utils\Exceptions;
use Elementor\Includes\Elements\Container;
use Elementor\Plugin;
use Elementor\Controls_Manager;
use Elementor\Controls_Stack;
use Elementor\TemplateLibrary\Source_Local;
use Elementor\User;
use Elementor\Core\Settings\Manager as SettingsManager;
use Elementor\Utils;
use Elementor\Widget_Base;
use Elementor\Core\Settings\Page\Manager as PageManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Elementor document.
 *
 * An abstract class that provides the needed properties and methods to
 * manage and handle documents in inheriting classes.
 *
 * @since 2.0.0
 * @abstract
 */
abstract class Document extends Controls_Stack {

	/**
	 * Document type meta key.
	 */
	const TYPE_META_KEY = '_elementor_template_type';
	const PAGE_META_KEY = '_elementor_page_settings';

	const BUILT_WITH_ELEMENTOR_META_KEY = '_elementor_edit_mode';

	/**
	 * Document publish status.
	 */
	const STATUS_PUBLISH = 'publish';

	/**
	 * Document draft status.
	 */
	const STATUS_DRAFT = 'draft';

	/**
	 * Document private status.
	 */
	const STATUS_PRIVATE = 'private';

	/**
	 * Document autosave status.
	 */
	const STATUS_AUTOSAVE = 'autosave';

	/**
	 * Document pending status.
	 */
	const STATUS_PENDING = 'pending';


	private $main_id;

	/**
	 * @var bool
	 */
	private $is_saving = false;

	private static $properties = [];

	/**
	 * @var Elements_Iteration_Action[]
	 */
	private $elements_iteration_actions = [];

	/**
	 * Document post data.
	 *
	 * Holds the document post data.
	 *
	 * @since 2.0.0
	 * @access protected
	 *
	 * @var \WP_Post WordPress post data.
	 */
	protected $post;

	/**
	 * @since 2.1.0
	 * @access protected
	 * @static
	 */
	protected static function get_editor_panel_categories() {
		return Plugin::$instance->elements_manager->get_categories();
	}

	/**
	 * Get properties.
	 *
	 * Retrieve the document properties.
	 *
	 * @since 2.0.0
	 * @access public
	 * @static
	 *
	 * @return array Document properties.
	 */
	public static function get_properties() {
		return [
			'has_elements' => true,
			'is_editable' => true,
			'edit_capability' => '',
			'show_in_finder' => true,
			'show_on_admin_bar' => true,
			'support_kit' => false,
		];
	}

	/**
	 * @since 2.1.0
	 * @access public
	 * @static
	 */
	public static function get_editor_panel_config() {
		$default_route = 'panel/elements/categories';

		if ( ! Plugin::instance()->role_manager->user_can( 'design' ) ) {
			$default_route = 'panel/page-settings/settings';
		}

		return [
			'title' => static::get_title(), // JS Container title.
			'widgets_settings' => [],
			'elements_categories' => static::get_editor_panel_categories(),
			'default_route' => $default_route,
			'has_elements' => static::get_property( 'has_elements' ),
			'support_kit' => static::get_property( 'support_kit' ),
			'messages' => [
				/* translators: %s: the document title. */
				'publish_notification' => sprintf( __( 'Hurray! Your %s is live.', 'elementor' ), static::get_title() ),
			],
		];
	}

	/**
	 * Get element title.
	 *
	 * Retrieve the element title.
	 *
	 * @since 2.0.0
	 * @access public
	 * @static
	 *
	 * @return string Element title.
	 */
	public static function get_title() {
		return __( 'Document', 'elementor' );
	}

	public static function get_plural_title() {
		return static::get_title();
	}

	/**
	 * Get property.
	 *
	 * Retrieve the document property.
	 *
	 * @since 2.0.0
	 * @access public
	 * @static
	 *
	 * @param string $key The property key.
	 *
	 * @return mixed The property value.
	 */
	public static function get_property( $key ) {
		$id = static::get_class_full_name();

		if ( ! isset( self::$properties[ $id ] ) ) {
			self::$properties[ $id ] = static::get_properties();
		}

		return self::get_items( self::$properties[ $id ], $key );
	}

	/**
	 * @since 2.0.0
	 * @access public
	 * @static
	 */
	public static function get_class_full_name() {
		return get_called_class();
	}

	public static function get_create_url() {
		$base_create_url = Plugin::$instance->documents->get_create_new_post_url( Source_Local::CPT );

		return add_query_arg( [ 'template_type' => static::get_type() ], $base_create_url );
	}

	public function get_name() {
		return static::get_type();
	}

	/**
	 * @since 2.0.0
	 * @access public
	 */
	public function get_unique_name() {
		return static::get_type() . '-' . $this->post->ID;
	}

	/**
	 * @since 2.3.0
	 * @access public
	 */
	public function get_post_type_title() {
		$post_type_object = get_post_type_object( $this->post->post_type );

		return $post_type_object->labels->singular_name;
	}

	/**
	 * @since 2.0.0
	 * @access public
	 */
	public function get_main_id() {
		if ( ! $this->main_id ) {
			$post_id = $this->post->ID;

			$parent_post_id = wp_is_post_revision( $post_id );

			if ( $parent_post_id ) {
				$post_id = $parent_post_id;
			}

			$this->main_id = $post_id;
		}

		return $this->main_id;
	}

	/**
	 * @since 2.0.0
	 * @access public
	 *
	 * @param $data
	 *
	 * @throws \Exception If the widget was not found.
	 *
	 * @return string
	 */
	public function render_element( $data ) {
		// Start buffering
		ob_start();

		/** @var Widget_Base $widget */
		$widget = Plugin::$instance->elements_manager->create_element_instance( $data );

		if ( ! $widget ) {
			throw new \Exception( 'Widget not found.' );
		}

		$widget->render_content();

		$render_html = ob_get_clean();

		return $render_html;
	}

	/**
	 * @since 2.0.0
	 * @access public
	 */
	public function get_main_post() {
		return get_post( $this->get_main_id() );
	}

	public function get_container_attributes() {
		$id = $this->get_main_id();

		$attributes = [
			'data-elementor-type' => $this->get_name(),
			'data-elementor-id' => $id,
			'class' => 'elementor elementor-' . $id,
		];

		$version_meta = $this->get_main_meta( '_elementor_version' );

		if ( version_compare( $version_meta, '2.5.0', '<' ) ) {
			$attributes['class'] .= ' elementor-bc-flex-widget';
		}

		if ( Plugin::$instance->preview->is_preview() ) {
			$attributes['data-elementor-title'] = static::get_title();
		} else {
			$attributes['data-elementor-settings'] = wp_json_encode( $this->get_frontend_settings() );
		}

		return $attributes;
	}

	/**
	 * @since 2.0.0
	 * @access public
	 */
	public function get_wp_preview_url() {
		$main_post_id = $this->get_main_id();
		$document = $this;

		// Ajax request from editor.
		if ( ! empty( $_POST['initial_document_id'] ) ) {
			$document = Plugin::$instance->documents->get( $_POST['initial_document_id'] );
		}

		$url = get_preview_post_link(
			$document->get_main_id(),
			[
				'preview_id' => $main_post_id,
				'preview_nonce' => wp_create_nonce( 'post_preview_' . $main_post_id ),
			]
		);

		/**
		 * Document "WordPress preview" URL.
		 *
		 * Filters the WordPress preview URL.
		 *
		 * @since 2.0.0
		 *
		 * @param string   $url  WordPress preview URL.
		 * @param Document $this The document instance.
		 */
		$url = apply_filters( 'elementor/document/urls/wp_preview', $url, $this );

		return $url;
	}

	/**
	 * @since 2.0.0
	 * @access public
	 */
	public function get_exit_to_dashboard_url() {
		$url = get_edit_post_link( $this->get_main_id(), 'raw' );

		/**
		 * Document "exit to dashboard" URL.
		 *
		 * Filters the "Exit To Dashboard" URL.
		 *
		 * @since 2.0.0
		 *
		 * @param string   $url  The exit URL
		 * @param Document $this The document instance.
		 */
		$url = apply_filters( 'elementor/document/urls/exit_to_dashboard', $url, $this );

		return $url;
	}

	/**
	 * Get auto-saved post revision.
	 *
	 * Retrieve the auto-saved post revision that is newer than current post.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 *
	 * @return bool|Document
	 */

	public function get_newer_autosave() {
		$autosave = $this->get_autosave();

		// Detect if there exists an autosave newer than the post.
		if ( $autosave && mysql2date( 'U', $autosave->get_post()->post_modified_gmt, false ) > mysql2date( 'U', $this->post->post_modified_gmt, false ) ) {
			return $autosave;
		}

		return false;
	}

	/**
	 * @since 2.0.0
	 * @access public
	 */
	public function is_autosave() {
		return wp_is_post_autosave( $this->post->ID );
	}

	/**
	 * Check if the current document is a 'revision'
	 *
	 * @return bool
	 */
	public function is_revision() {
		return 'revision' === $this->post->post_type;
	}

	/**
	 * Checks if the current document status is 'trash'.
	 *
	 * @return bool
	 */
	public function is_trash() {
		return 'trash' === $this->post->post_status;
	}

	/**
	 * @since 2.0.0
	 * @access public
	 *
	 * @param int  $user_id
	 * @param bool $create
	 *
	 * @return bool|Document
	 */
	public function get_autosave( $user_id = 0, $create = false ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		$autosave_id = $this->get_autosave_id( $user_id );

		if ( $autosave_id ) {
			$document = Plugin::$instance->documents->get( $autosave_id );
		} elseif ( $create ) {
			$autosave_id = wp_create_post_autosave( [
				'post_ID' => $this->post->ID,
				'post_type' => $this->post->post_type,
				'post_title' => $this->post->post_title,
				'post_excerpt' => $this->post->post_excerpt,
				// Hack to cause $autosave_is_different=true in `wp_create_post_autosave`.
				'post_content' => '<!-- Created With Elementor -->',
				'post_modified' => current_time( 'mysql' ),
			] );

			Plugin::$instance->db->copy_elementor_meta( $this->post->ID, $autosave_id );

			$document = Plugin::$instance->documents->get( $autosave_id );
			$document->save_template_type();
		} else {
			$document = false;
		}

		return $document;
	}

	/**
	 * Add/Remove edit link in dashboard.
	 *
	 * Add or remove an edit link to the post/page action links on the post/pages list table.
	 *
	 * Fired by `post_row_actions` and `page_row_actions` filters.
	 *
	 * @access public
	 *
	 * @param array    $actions An array of row action links.
	 *
	 * @return array An updated array of row action links.
	 */
	public function filter_admin_row_actions( $actions ) {
		if ( $this->is_built_with_elementor() && $this->is_editable_by_current_user() ) {
			$actions['edit_with_elementor'] = sprintf(
				'<a href="%1$s">%2$s</a>',
				$this->get_edit_url(),
				__( 'Edit with Elementor', 'elementor' )
			);
		}

		return $actions;
	}

	/**
	 * @since 2.0.0
	 * @access public
	 */
	public function is_editable_by_current_user() {
		$edit_capability = static::get_property( 'edit_capability' );
		if ( $edit_capability && ! current_user_can( $edit_capability ) ) {
			return false;
		}

		return self::get_property( 'is_editable' ) && User::is_current_user_can_edit( $this->get_main_id() );
	}

	/**
	 * @since 2.9.0
	 * @access protected
	 */
	protected function get_initial_config() {
		// Get document data *after* the scripts hook - so plugins can run compatibility before get data, but *before* enqueue the editor script - so elements can enqueue their own scripts that depended in editor script.

		$locked_user = Plugin::$instance->editor->get_locked_user( $this->get_main_id() );

		if ( $locked_user ) {
			$locked_user = $locked_user->display_name;
		}

		$post_type_object = get_post_type_object( $this->get_main_post()->post_type );

		$settings = SettingsManager::get_settings_managers_config();

		$config = [
			'id' => $this->get_main_id(),
			'type' => $this->get_name(),
			'version' => $this->get_main_meta( '_elementor_version' ),
			'settings' => $settings['page'],
			'remoteLibrary' => $this->get_remote_library_config(),
			'last_edited' => $this->get_last_edited(),
			'panel' => static::get_editor_panel_config(),
			'container' => 'body',
			'post_type_title' => $this->get_post_type_title(),
			'user' => [
				'can_publish' => current_user_can( $post_type_object->cap->publish_posts ),

				// Deprecated config since 2.9.0.
				'locked' => $locked_user,
			],
			'urls' => [
				'exit_to_dashboard' => $this->get_exit_to_dashboard_url(),
				'preview' => $this->get_preview_url(),
				'wp_preview' => $this->get_wp_preview_url(),
				'permalink' => $this->get_permalink(),
				'have_a_look' => $this->get_have_a_look_url(),
			],
		];

		if ( static::get_property( 'has_elements' ) ) {
			$container = [];
			$experiments_manager = Plugin::$instance->experiments;

			if ( $experiments_manager->is_feature_active( 'container' ) ) {
				$container = [
					'container' => ( new Container() )->get_config(),
				];
			}

			$config['elements'] = $this->get_elements_raw_data( null, true );
			$config['widgets'] = $container + Plugin::$instance->widgets_manager->get_widget_types_config();
		}

		$additional_config = apply_filters( 'elementor/document/config', [], $this->get_main_id() );

		if ( ! empty( $additional_config ) ) {
			$config = array_replace_recursive( $config, $additional_config );
		}

		return $config;
	}

	/**
	 * @since 3.1.0
	 * @access protected
	 */
	protected function register_controls() {
		$this->register_document_controls();
		/**
		 * Register document controls.
		 *
		 * Fires after Elementor registers the document controls.
		 *
		 * @since 2.0.0
		 *
		 * @param Document $this The document instance.
		 */
		do_action( 'elementor/documents/register_controls', $this );
	}

	/**
	 * @since 2.0.0
	 * @access public
	 *
	 * @param $data
	 *
	 * @return bool
	 */
	public function save( $data ) {
		/**
		 * Fires when document save starts on Elementor.
		 *
		 * @param array $data.
		 * @param \Elementor\Core\Base\Document $this The current document.
		 *
		 * @since 3.3.0
		 */
		$data = apply_filters( 'elementor/document/save/data', $data, $this );

		$this->add_handle_revisions_changed_filter();

		if ( ! $this->is_editable_by_current_user() ) {
			return false;
		}

		$this->set_is_saving( true );

		/**
		 * Before document save.
		 *
		 * Fires when document save starts on Elementor.
		 *
		 * @since 2.5.12
		 *
		 * @param \Elementor\Core\Base\Document $this The current document.
		 * @param $data.
		 */
		do_action( 'elementor/document/before_save', $this, $data );

		if ( ! current_user_can( 'unfiltered_html' ) ) {
			$data = wp_kses_post_deep( $data );
		}

		if ( ! empty( $data['settings'] ) ) {
			if ( isset( $data['settings']['post_status'] ) && self::STATUS_AUTOSAVE === $data['settings']['post_status'] ) {
				if ( ! defined( 'DOING_AUTOSAVE' ) ) {
					define( 'DOING_AUTOSAVE', true );
				}
			}

			$this->save_settings( $data['settings'] );

			$this->refresh_post();
		}

		// Don't check is_empty, because an empty array should be saved.
		if ( isset( $data['elements'] ) && is_array( $data['elements'] ) ) {
			$this->save_elements( $data['elements'] );
		}

		$this->save_template_type();

		$this->save_version();

		// Remove Post CSS
		$post_css = Post_CSS::create( $this->post->ID );

		$post_css->delete();

		/**
		 * After document save.
		 *
		 * Fires when document save is complete.
		 *
		 * @since 2.5.12
		 *
		 * @param \Elementor\Core\Base\Document $this The current document.
		 * @param $data.
		 */
		do_action( 'elementor/document/after_save', $this, $data );

		$this->set_is_saving( false );

		$this->remove_handle_revisions_changed_filter();

		return true;
	}

	public function refresh_post() {
		$this->post = get_post( $this->post->ID );
	}

	/**
	 * @param array $new_settings
	 *
	 * @return static
	 */
	public function update_settings( array $new_settings ) {
		$document_settings = $this->get_meta( PageManager::META_KEY );

		if ( ! $document_settings ) {
			$document_settings = [];
		}

		$this->save_settings(
			array_replace_recursive( $document_settings, $new_settings )
		);

		return $this;
	}

	/**
	 * Is built with Elementor.
	 *
	 * Check whether the post was built with Elementor.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return bool Whether the post was built with Elementor.
	 */
	public function is_built_with_elementor() {
		return ! ! $this->get_meta( self::BUILT_WITH_ELEMENTOR_META_KEY );
	}

	/**
	 * Mark the post as "built with elementor" or not.
	 *
	 * @param bool $is_built_with_elementor
	 *
	 * @return $this
	 */
	public function set_is_built_with_elementor( $is_built_with_elementor ) {
		if ( $is_built_with_elementor ) {
			// Use the string `builder` and not a boolean for rollback compatibility
			$this->update_meta( self::BUILT_WITH_ELEMENTOR_META_KEY, 'builder' );
		} else {
			$this->delete_meta( self::BUILT_WITH_ELEMENTOR_META_KEY );
		}

		return $this;
	}

	/**
	 * @since 2.0.0
	 * @access public
	 * @static
	 *
	 * @return mixed
	 */
	public function get_edit_url() {
		$url = add_query_arg(
			[
				'post' => $this->get_main_id(),
				'action' => 'elementor',
			],
			admin_url( 'post.php' )
		);

		/**
		 * Document edit url.
		 *
		 * Filters the document edit url.
		 *
		 * @since 2.0.0
		 *
		 * @param string   $url  The edit url.
		 * @param Document $this The document instance.
		 */
		$url = apply_filters( 'elementor/document/urls/edit', $url, $this );

		return $url;
	}

	/**
	 * @since 2.0.0
	 * @access public
	 */
	public function get_preview_url() {
		/**
		 * Use a static var - to avoid change the `ver` parameter on every call.
		 */
		static $url;

		if ( empty( $url ) ) {

			add_filter( 'pre_option_permalink_structure', '__return_empty_string' );

			$url = set_url_scheme( add_query_arg( [
				'elementor-preview' => $this->get_main_id(),
				'ver' => time(),
			], $this->get_permalink() ) );

			remove_filter( 'pre_option_permalink_structure', '__return_empty_string' );

			/**
			 * Document preview URL.
			 *
			 * Filters the document preview URL.
			 *
			 * @since 2.0.0
			 *
			 * @param string   $url  The preview URL.
			 * @param Document $this The document instance.
			 */
			$url = apply_filters( 'elementor/document/urls/preview', $url, $this );
		}

		return $url;
	}

	/**
	 * @since 2.0.0
	 * @access public
	 *
	 * @param string $key
	 *
	 * @return array
	 */
	public function get_json_meta( $key ) {
		$meta = get_post_meta( $this->post->ID, $key, true );

		if ( is_string( $meta ) && ! empty( $meta ) ) {
			$meta = json_decode( $meta, true );
		}

		if ( empty( $meta ) ) {
			$meta = [];
		}

		return $meta;
	}

	/**
	 * @since 2.0.0
	 * @access public
	 *
	 * @param null $data
	 * @param bool $with_html_content
	 *
	 * @return array
	 */
	public function get_elements_raw_data( $data = null, $with_html_content = false ) {
		if ( ! static::get_property( 'has_elements' ) ) {
			return [];
		}

		if ( is_null( $data ) ) {
			$data = $this->get_elements_data();
		}

		// Change the current documents, so widgets can use `documents->get_current` and other post data
		Plugin::$instance->documents->switch_to_document( $this );

		$editor_data = [];

		foreach ( $data as $element_data ) {
			$element = Plugin::$instance->elements_manager->create_element_instance( $element_data );

			if ( ! $element ) {
				continue;
			}

			$editor_data[] = $element->get_raw_data( $with_html_content );
		} // End foreach().

		Plugin::$instance->documents->restore_document();

		return $editor_data;
	}

	/**
	 * @since 2.0.0
	 * @access public
	 *
	 * @param string $status
	 *
	 * @return array
	 */
	public function get_elements_data( $status = self::STATUS_PUBLISH ) {
		$elements = $this->get_json_meta( '_elementor_data' );

		if ( self::STATUS_DRAFT === $status ) {
			$autosave = $this->get_newer_autosave();

			if ( is_object( $autosave ) ) {
				$autosave_elements = Plugin::$instance->documents
					->get( $autosave->get_post()->ID )
					->get_json_meta( '_elementor_data' );
			}
		}

		if ( Plugin::$instance->editor->is_edit_mode() ) {
			if ( empty( $elements ) && empty( $autosave_elements ) ) {
				// Convert to Elementor.
				$elements = $this->convert_to_elementor();
				if ( $this->is_autosave() ) {
					Plugin::$instance->db->copy_elementor_meta( $this->post->post_parent, $this->post->ID );
				}
			}
		}

		if ( ! empty( $autosave_elements ) ) {
			$elements = $autosave_elements;
		}

		return $elements;
	}

	/**
	 * Get document setting from DB.
	 *
	 * @return array
	 */
	public function get_db_document_settings() {
		return $this->get_meta( static::PAGE_META_KEY );
	}

	/**
	 * @since 2.3.0
	 * @access public
	 */
	public function convert_to_elementor() {
		$this->save( [] );

		if ( empty( $this->post->post_content ) ) {
			return [];
		}

		// Check if it's only a shortcode.
		preg_match_all( '/' . get_shortcode_regex() . '/', $this->post->post_content, $matches, PREG_SET_ORDER );
		if ( ! empty( $matches ) ) {
			foreach ( $matches as $shortcode ) {
				if ( trim( $this->post->post_content ) === $shortcode[0] ) {
					$widget_type = Plugin::$instance->widgets_manager->get_widget_types( 'shortcode' );
					$settings = [
						'shortcode' => $this->post->post_content,
					];
					break;
				}
			}
		}

		if ( empty( $widget_type ) ) {
			$widget_type = Plugin::$instance->widgets_manager->get_widget_types( 'text-editor' );
			$settings = [
				'editor' => $this->post->post_content,
			];
		}

		// TODO: Better coding to start template for editor
		return [
			[
				'id' => Utils::generate_random_string(),
				'elType' => 'section',
				'elements' => [
					[
						'id' => Utils::generate_random_string(),
						'elType' => 'column',
						'elements' => [
							[
								'id' => Utils::generate_random_string(),
								'elType' => $widget_type::get_type(),
								'widgetType' => $widget_type->get_name(),
								'settings' => $settings,
							],
						],
					],
				],
			],
		];
	}

	/**
	 * @since 2.1.3
	 * @access public
	 */
	public function print_elements_with_wrapper( $elements_data = null ) {
		if ( ! $elements_data ) {
			$elements_data = $this->get_elements_data();
		}

		$is_dom_optimization_active = Plugin::$instance->experiments->is_feature_active( 'e_dom_optimization' );
		?>
		<div <?php echo Utils::render_html_attributes( $this->get_container_attributes() ); ?>>
			<?php if ( ! $is_dom_optimization_active ) { ?>
			<div class="elementor-inner">
			<?php } ?>
				<div class="elementor-section-wrap">
					<?php $this->print_elements( $elements_data ); ?>
				</div>
			<?php if ( ! $is_dom_optimization_active ) { ?>
			</div>
			<?php } ?>
		</div>
		<?php
	}

	/**
	 * @since 2.0.0
	 * @access public
	 */
	public function get_css_wrapper_selector() {
		return '';
	}

	/**
	 * @since 2.0.0
	 * @access public
	 */
	public function get_panel_page_settings() {
		return [
			/* translators: %s: Document title */
			'title' => sprintf( __( '%s Settings', 'elementor' ), static::get_title() ),
		];
	}

	/**
	 * @since 2.0.0
	 * @access public
	 */
	public function get_post() {
		return $this->post;
	}

	/**
	 * @since 2.0.0
	 * @access public
	 */
	public function get_permalink() {
		return get_permalink( $this->get_main_id() );
	}

	/**
	 * @since 2.0.8
	 * @access public
	 */
	public function get_content( $with_css = false ) {
		return Plugin::$instance->frontend->get_builder_content( $this->post->ID, $with_css );
	}

	/**
	 * @since 2.0.0
	 * @access public
	 */
	public function delete() {
		if ( 'revision' === $this->post->post_type ) {
			$deleted = wp_delete_post_revision( $this->post );
		} else {
			$deleted = wp_delete_post( $this->post->ID );
		}

		return $deleted && ! is_wp_error( $deleted );
	}

	/**
	 * Save editor elements.
	 *
	 * Save data from the editor to the database.
	 *
	 * @since 2.0.0
	 * @access protected
	 *
	 * @param array $elements
	 */
	protected function save_elements( $elements ) {
		$editor_data = $this->get_elements_raw_data( $elements );

		// We need the `wp_slash` in order to avoid the unslashing during the `update_post_meta`
		$json_value = wp_slash( wp_json_encode( $editor_data ) );

		// Don't use `update_post_meta` that can't handle `revision` post type
		$is_meta_updated = update_metadata( 'post', $this->post->ID, '_elementor_data', $json_value );

		/**
		 * Before saving data.
		 *
		 * Fires before Elementor saves data to the database.
		 *
		 * @since 1.0.0
		 *
		 * @param string   $status          Post status.
		 * @param int|bool $is_meta_updated Meta ID if the key didn't exist, true on successful update, false on failure.
		 */
		do_action( 'elementor/db/before_save', $this->post->post_status, $is_meta_updated );

		Plugin::$instance->db->save_plain_text( $this->post->ID );

		$elements_iteration_actions = $this->get_elements_iteration_actions();

		if ( $elements_iteration_actions ) {
			$this->iterate_elements( $elements, $elements_iteration_actions, 'save' );
		}

		/**
		 * After saving data.
		 *
		 * Fires after Elementor saves data to the database.
		 *
		 * @since 1.0.0
		 *
		 * @param int   $post_id     The ID of the post.
		 * @param array $editor_data Sanitize posted data.
		 */
		do_action( 'elementor/editor/after_save', $this->post->ID, $editor_data );
	}

	/**
	 * @since 2.0.0
	 * @access public
	 *
	 * @param int $user_id Optional. User ID. Default value is `0`.
	 *
	 * @return bool|int
	 */
	public function get_autosave_id( $user_id = 0 ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		$autosave = Utils::get_post_autosave( $this->post->ID, $user_id );
		if ( $autosave ) {
			return $autosave->ID;
		}

		return false;
	}

	public function save_version() {
		if ( ! defined( 'IS_ELEMENTOR_UPGRADE' ) ) {
			// Save per revision.
			$this->update_meta( '_elementor_version', ELEMENTOR_VERSION );

			/**
			 * Document version save.
			 *
			 * Fires when document version is saved on Elementor.
			 * Will not fire during Elementor Upgrade.
			 *
			 * @since 2.5.12
			 *
			 * @param \Elementor\Core\Base\Document $this The current document.
			 *
			 */
			do_action( 'elementor/document/save_version', $this );
		}
	}

	/**
	 * @since 2.3.0
	 * @access public
	 */
	public function save_template_type() {
		return $this->update_main_meta( self::TYPE_META_KEY, $this->get_name() );
	}

	/**
	 * @since 2.3.0
	 * @access public
	 */
	public function get_template_type() {
		return $this->get_main_meta( self::TYPE_META_KEY );
	}

	/**
	 * @since 2.0.0
	 * @access public
	 *
	 * @param string $key Meta data key.
	 *
	 * @return mixed
	 */
	public function get_main_meta( $key ) {
		return get_post_meta( $this->get_main_id(), $key, true );
	}

	/**
	 * @since 2.0.4
	 * @access public
	 *
	 * @param string $key   Meta data key.
	 * @param string $value Meta data value.
	 *
	 * @return bool|int
	 */
	public function update_main_meta( $key, $value ) {
		return update_post_meta( $this->get_main_id(), $key, $value );
	}

	/**
	 * @since 2.0.4
	 * @access public
	 *
	 * @param string $key   Meta data key.
	 * @param string $value Optional. Meta data value. Default is an empty string.
	 *
	 * @return bool
	 */
	public function delete_main_meta( $key, $value = '' ) {
		return delete_post_meta( $this->get_main_id(), $key, $value );
	}

	/**
	 * @since 2.0.0
	 * @access public
	 *
	 * @param string $key Meta data key.
	 *
	 * @return mixed
	 */
	public function get_meta( $key ) {
		return get_post_meta( $this->post->ID, $key, true );
	}

	/**
	 * @since 2.0.0
	 * @access public
	 *
	 * @param string $key   Meta data key.
	 * @param mixed  $value Meta data value.
	 *
	 * @return bool|int
	 */
	public function update_meta( $key, $value ) {
		// Use `update_metadata` in order to work also with revisions.
		return update_metadata( 'post', $this->post->ID, $key, $value );
	}

	/**
	 * @since 2.0.3
	 * @access public
	 *
	 * @param string $key   Meta data key.
	 * @param string $value Meta data value.
	 *
	 * @return bool
	 */
	public function delete_meta( $key, $value = '' ) {
		// Use `delete_metadata` in order to work also with revisions.
		return delete_metadata( 'post', $this->post->ID, $key, $value );
	}

	/**
	 * @since 2.0.0
	 * @access public
	 */
	public function get_last_edited() {
		$post = $this->post;
		$autosave_post = $this->get_autosave();

		if ( $autosave_post ) {
			$post = $autosave_post->get_post();
		}

		$date = date_i18n( _x( 'M j, H:i', 'revision date format', 'elementor' ), strtotime( $post->post_modified ) );
		$display_name = get_the_author_meta( 'display_name', $post->post_author );

		if ( $autosave_post || 'revision' === $post->post_type ) {
			/* translators: 1: Saving date, 2: Author display name */
			$last_edited = sprintf( __( 'Draft saved on %1$s by %2$s', 'elementor' ), '<time>' . $date . '</time>', $display_name );
		} else {
			/* translators: 1: Editing date, 2: Author display name */
			$last_edited = sprintf( __( 'Last edited on %1$s by %2$s', 'elementor' ), '<time>' . $date . '</time>', $display_name );
		}

		return $last_edited;
	}


	/**
	 * @return bool
	 */
	public function is_saving() {
		return $this->is_saving;
	}

	/**
	 * @param $is_saving
	 *
	 * @return $this
	 */
	public function set_is_saving( $is_saving ) {
		$this->is_saving = $is_saving;

		return $this;
	}

	/**
	 * @since 2.0.0
	 * @access public
	 *
	 * @param array $data
	 *
	 * @throws \Exception If the post does not exist.
	 */
	public function __construct( array $data = [] ) {
		if ( $data ) {
			if ( empty( $data['post_id'] ) ) {
				$this->post = new \WP_Post( (object) [] );
			} else {
				$this->post = get_post( $data['post_id'] );

				if ( ! $this->post ) {
					throw new \Exception( sprintf( 'Post ID #%s does not exist.', $data['post_id'] ), Exceptions::NOT_FOUND );
				}
			}

			// Each Control_Stack is based on a unique ID.
			$data['id'] = $data['post_id'];

			if ( ! isset( $data['settings'] ) ) {
				$data['settings'] = [];
			}

			$saved_settings = get_post_meta( $this->post->ID, '_elementor_page_settings', true );
			if ( ! empty( $saved_settings ) && is_array( $saved_settings ) ) {
				$data['settings'] += $saved_settings;
			}
		}

		parent::__construct( $data );
	}

	/*
	 * Get Export Data
	 *
	 * Filters a document's data on export
	 *
	 * @since 3.2.0
	 * @access public
	 *
	 * @return array The data to export
	 */
	public function get_export_data() {
		$content = Plugin::$instance->db->iterate_data( $this->get_elements_data(), function( $element_data ) {
			$element_data['id'] = Utils::generate_random_string();

			$element = Plugin::$instance->elements_manager->create_element_instance( $element_data );

			// If the widget/element isn't exist, like a plugin that creates a widget but deactivated
			if ( ! $element ) {
				return null;
			}

			return $this->process_element_import_export( $element, 'on_export' );
		} );

		return [
			'content' => $content,
			'settings' => $this->get_data( 'settings' ),
			'metadata' => $this->get_export_metadata(),
		];
	}

	public function get_export_summary() {
		return [
			'title' => $this->post->post_title,
			'doc_type' => $this->get_name(),
			'thumbnail' => get_the_post_thumbnail_url( $this->post ),
		];
	}

	/*
	 * Get Import Data
	 *
	 * Filters a document's data on import
	 *
	 * @since 3.2.0
	 * @access public
	 *
	 * @return array The data to import
	 */
	public function get_import_data( array $data ) {
		$data['content'] = Plugin::$instance->db->iterate_data( $data['content'], function( $element_data ) {
			$element = Plugin::$instance->elements_manager->create_element_instance( $element_data );

			// If the widget/element isn't exist, like a plugin that creates a widget but deactivated
			if ( ! $element ) {
				return null;
			}

			return $this->process_element_import_export( $element, 'on_import' );
		} );

		if ( ! empty( $data['settings'] ) ) {
			$template_model = new Page_Model( [
				'id' => 0,
				'settings' => $data['settings'],
			] );

			$page_data = $this->process_element_import_export( $template_model, 'on_import' );

			$data['settings'] = $page_data['settings'];
		}

		return $data;
	}

	/**
	 * Import
	 *
	 * Allows to import an external data to a document
	 *
	 * @since 3.2.0
	 * @access public
	 *
	 * @param array $data
	 */
	public function import( array $data ) {
		$data = $this->get_import_data( $data );

		$this->save( [
			'elements' => $data['content'],
			'settings' => $data['settings'],
		] );

		if ( $data['import_settings']['thumbnail'] ) {
			$attachment = Plugin::$instance->templates_manager->get_import_images_instance()->import( [ 'url' => $data['import_settings']['thumbnail'] ] );

			set_post_thumbnail( $this->get_main_post(), $attachment['id'] );
		}

		if ( ! empty( $data['metadata'] ) ) {
			foreach ( $data['metadata'] as $key => $value ) {
				$this->update_meta( $key, $value );
			}
		}
	}

	private function process_element_import_export( Controls_Stack $element, $method ) {
		$element_data = $element->get_data();

		if ( method_exists( $element, $method ) ) {
			// TODO: Use the internal element data without parameters.
			$element_data = $element->{$method}( $element_data );
		}

		foreach ( $element->get_controls() as $control ) {
			$control_class = Plugin::$instance->controls_manager->get_control( $control['type'] );

			// If the control isn't exist, like a plugin that creates the control but deactivated.
			if ( ! $control_class ) {
				return $element_data;
			}

			if ( method_exists( $control_class, $method ) ) {
				$element_data['settings'][ $control['name'] ] = $control_class->{$method}( $element->get_settings( $control['name'] ), $control );
			}

			// On Export, check if the control has an argument 'export' => false.
			if ( 'on_export' === $method && isset( $control['export'] ) && false === $control['export'] ) {
				unset( $element_data['settings'][ $control['name'] ] );
			}
		}

		return $element_data;
	}

	protected function get_export_metadata() {
		$metadata = get_post_meta( $this->get_main_id() );

		foreach ( $metadata as $meta_key => $meta_value ) {
			if ( is_protected_meta( $meta_key, 'post' ) ) {
				unset( $metadata[ $meta_key ] );

				continue;
			}

			$metadata[ $meta_key ] = $meta_value[0];
		}

		return $metadata;
	}

	protected function get_remote_library_config() {
		$config = [
			'type' => 'block',
			'default_route' => 'templates/blocks',
			'category' => $this->get_name(),
			'autoImportSettings' => false,
		];

		return $config;
	}

	/**
	 * @since 2.0.4
	 * @access protected
	 *
	 * @param $settings
	 */
	protected function save_settings( $settings ) {
		$page_settings_manager = SettingsManager::get_settings_managers( 'page' );
		$page_settings_manager->ajax_before_save_settings( $settings, $this->post->ID );
		$page_settings_manager->save_settings( $settings, $this->post->ID );
	}

	/**
	 * @since 2.1.3
	 * @access protected
	 */
	protected function print_elements( $elements_data ) {
		// Collect all data updaters that should be updated on runtime.
		$runtime_elements_iteration_actions = $this->get_runtime_elements_iteration_actions();

		if ( $runtime_elements_iteration_actions ) {
			$this->iterate_elements( $elements_data, $runtime_elements_iteration_actions, 'render' );
		}

		foreach ( $elements_data as $element_data ) {
			$element = Plugin::$instance->elements_manager->create_element_instance( $element_data );

			if ( ! $element ) {
				continue;
			}

			$element->print_element();
		}
	}

	protected function register_document_controls() {
		$this->start_controls_section(
			'document_settings',
			[
				'label' => __( 'General Settings', 'elementor' ),
				'tab' => Controls_Manager::TAB_SETTINGS,
			]
		);

		$this->add_control(
			'post_title',
			[
				'label' => __( 'Title', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'default' => $this->post->post_title,
				'label_block' => true,
				'separator' => 'none',
			]
		);

		$post_type_object = get_post_type_object( $this->post->post_type );

		$can_publish = $post_type_object && current_user_can( $post_type_object->cap->publish_posts );
		$is_published = self::STATUS_PUBLISH === $this->post->post_status || self::STATUS_PRIVATE === $this->post->post_status;

		if ( $is_published || $can_publish || ! Plugin::$instance->editor->is_edit_mode() ) {

			$statuses = $this->get_post_statuses();
			if ( 'future' === $this->get_main_post()->post_status ) {
				$statuses['future'] = __( 'Future', 'elementor' );
			}

			$this->add_control(
				'post_status',
				[
					'label' => __( 'Status', 'elementor' ),
					'type' => Controls_Manager::SELECT,
					'default' => $this->get_main_post()->post_status,
					'options' => $statuses,
				]
			);
		}

		$this->end_controls_section();
	}

	protected function get_post_statuses() {
		return get_post_statuses();
	}

	protected function get_have_a_look_url() {
		return $this->get_permalink();
	}

	public function handle_revisions_changed( $post_has_changed, $last_revision, $post ) {
		// In case default, didn't determine the changes.
		if ( ! $post_has_changed ) {
			$last_revision_id = $last_revision->ID;
			$last_revision_document = Plugin::instance()->documents->get( $last_revision_id );
			$post_document = Plugin::instance()->documents->get( $post->ID );

			$last_revision_settings = $last_revision_document->get_settings();
			$post_settings = $post_document->get_settings();

			// TODO: Its better to add crc32 signature for each revision and then only compare one part of the checksum.
			$post_has_changed = $last_revision_settings !== $post_settings;
		}

		return $post_has_changed;
	}

	private function add_handle_revisions_changed_filter() {
		add_filter( 'wp_save_post_revision_post_has_changed', [ $this, 'handle_revisions_changed' ], 10, 3 );
	}

	private function remove_handle_revisions_changed_filter() {
		remove_filter( 'wp_save_post_revision_post_has_changed', [ $this, 'handle_revisions_changed' ] );
	}

	private function get_runtime_elements_iteration_actions() {
		$runtime_elements_iteration_actions = [];

		$elements_iteration_actions = $this->get_elements_iteration_actions();

		foreach ( $elements_iteration_actions as $elements_iteration_action ) {
			if ( $elements_iteration_action->is_action_needed() ) {
				$runtime_elements_iteration_actions[] = $elements_iteration_action;
			}
		}

		return $runtime_elements_iteration_actions;
	}

	private function iterate_elements( $elements, $elements_iteration_actions, $mode ) {
		$unique_page_elements = [];

		foreach ( $elements_iteration_actions as $elements_iteration_action ) {
			$elements_iteration_action->set_mode( $mode );
		}

		Plugin::$instance->db->iterate_data( $elements, function( array $element_data ) use ( &$unique_page_elements, $elements_iteration_actions ) {
			$element_type = 'widget' === $element_data['elType'] ? $element_data['widgetType'] : $element_data['elType'];

			$element = Plugin::$instance->elements_manager->create_element_instance( $element_data );

			if ( $element ) {
				if ( ! in_array( $element_type, $unique_page_elements, true ) ) {
					$unique_page_elements[] = $element_type;

					foreach ( $elements_iteration_actions as $elements_iteration_action ) {
						$elements_iteration_action->unique_element_action( $element );
					}
				}

				foreach ( $elements_iteration_actions as $elements_iteration_action ) {
					$elements_iteration_action->element_action( $element );
				}
			}

			return $element_data;
		} );

		foreach ( $elements_iteration_actions as $elements_iteration_action ) {
			$elements_iteration_action->after_elements_iteration();
		}
	}

	private function get_elements_iteration_actions() {
		if ( ! $this->elements_iteration_actions ) {
			$this->elements_iteration_actions[] = new Assets_Iteration_Action( $this );
		}

		return $this->elements_iteration_actions;
	}
}
