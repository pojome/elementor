<?php
namespace Elementor\Modules\LandingPages;

use Elementor\Core\Base\Module as BaseModule;
use Elementor\Core\Documents_Manager;
use Elementor\Core\Experiments\Manager as Experiments_Manager;
use Elementor\Modules\LandingPages\Documents\Landing_Page;
use Elementor\Modules\LandingPages\Module as Landing_Pages_Module;
use Elementor\Plugin;
use Elementor\TemplateLibrary\Source_Local;
use Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Module extends BaseModule {

	const DOCUMENT_TYPE = 'landing-page';
	const CPT = 'e-landing-page';
	const ADMIN_PAGE_SLUG = 'edit.php?post_type=' . self::CPT;

	private $posts;
	private $trashed_posts;
	private $new_lp_url;
	private $permalink_structure;

	public function get_name() {
		return 'landing-pages';
	}

	/**
	 * Get Experimental Data
	 *
	 * Implementation of this method makes the module an experiment.
	 *
	 * @since 3.1.0
	 *
	 * @return array
	 */
	public static function get_experimental_data() {
		return [
			'name' => 'landing-pages',
			'title' => __( 'Landing Pages', 'elementor' ),
			'description' => __( 'Adds a new Elementor content type that allows creating beautiful landing pages instantly in a streamlined workflow.', 'elementor' ),
			'release_status' => Experiments_Manager::RELEASE_STATUS_BETA,
			'default' => Experiments_Manager::STATE_ACTIVE,
		];
	}

	/**
	 * Get Trashed Landing Pages Posts
	 *
	 * Returns the posts property of a WP_Query run for Landing Pages with post_status of 'trash'.
	 *
	 * @since 3.1.0
	 *
	 * @return array trashed posts
	 */
	private function get_trashed_landing_page_posts() {
		if ( $this->trashed_posts ) {
			return $this->trashed_posts;
		}

		// `'posts_per_page' => 1` is because this is only used as an indicator to whether there are any trashed landing pages.
		$trashed_posts_query = new \WP_Query( [
			'post_type' => self::CPT,
			'post_status' => 'trash',
			'posts_per_page' => 1,
			'meta_key' => '_elementor_template_type',
			'meta_value' => self::DOCUMENT_TYPE,
		] );

		$this->trashed_posts = $trashed_posts_query->posts;

		return $this->trashed_posts;
	}

	/**
	 * Get Landing Pages Posts
	 *
	 * Returns the posts property of a WP_Query run for posts with the Landing Pages CPT.
	 *
	 * @since 3.1.0
	 *
	 * @return array posts
	 */
	private function get_landing_page_posts() {
		if ( $this->posts ) {
			return $this->posts;
		}

		// `'posts_per_page' => 1` is because this is only used as an indicator to whether there are any landing pages.
		$posts_query = new \WP_Query( [
			'post_type' => self::CPT,
			'post_status' => 'any',
			'posts_per_page' => 1,
			'meta_key' => '_elementor_template_type',
			'meta_value' => self::DOCUMENT_TYPE,
		] );

		$this->posts = $posts_query->posts;

		return $this->posts;
	}

	/**
	 * Is Elementor Landing Page.
	 *
	 * Check whether the post is an Elementor Landing Page.
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @param \WP_Post $post Post Object
	 *
	 * @return bool Whether the post was built with Elementor.
	 */
	public function is_elementor_landing_page( $post ) {
		return self::CPT === $post->post_type;
	}

	/**
	 * Add Submenu Page
	 *
	 * Adds the 'Landing Pages' submenu item to the 'Templates' menu item.
	 *
	 * @since 3.1.0
	 */
	private function add_submenu_page() {
		$posts = $this->get_landing_page_posts();

		// If there are no Landing Pages, show the "Create Your First Landing Page" page.
		// If there are, show the pages table.
		if ( ! empty( $posts ) ) {
			$landing_page_menu_slug = self::ADMIN_PAGE_SLUG;
			$landing_page_menu_callback = null;
		} else {
			$landing_page_menu_slug = self::CPT;
			$landing_page_menu_callback = [ $this, 'print_empty_landing_pages_page' ];
		}

		$landing_pages_title = __( 'Landing Pages', 'elementor' );

		add_submenu_page(
			Source_Local::ADMIN_MENU_SLUG,
			$landing_pages_title,
			$landing_pages_title,
			'manage_options',
			$landing_page_menu_slug,
			$landing_page_menu_callback
		);
	}

	/**
	 * Get 'Add New' Landing Page URL
	 *
	 * Retrieves the custom URL for the admin dashboard's 'Add New' button in the Landing Pages admin screen. This URL
	 * creates a new Landing Pages and directly opens the Elementor Editor with the Template Library modal open on the
	 * Landing Pages tab.
	 *
	 * @since 3.1.0
	 *
	 * @return string
	 */
	private function get_add_new_landing_page_url() {
		if ( ! $this->new_lp_url ) {
			$this->new_lp_url = Utils::get_create_new_post_url( self::CPT, self::DOCUMENT_TYPE ) . '#library';
		}
		return $this->new_lp_url;
	}

	/**
	 * Get Empty Landing Pages Page
	 *
	 * Prints the HTML content of the page that is displayed when there are no existing landing pages in the DB.
	 * Added as the callback to add_submenu_page.
	 *
	 * @since 3.1.0
	 */
	public function print_empty_landing_pages_page() {
		$template_sources = Plugin::$instance->templates_manager->get_registered_sources();
		$source_local = $template_sources['local'];
		$trashed_posts = $this->get_trashed_landing_page_posts();

		?>
		<div class="e-landing-pages-empty">
		<?php
		/** @var Source_Local $source_local */
		$source_local->print_blank_state_template( __( 'Landing Page', 'elementor' ), $this->get_add_new_landing_page_url(), __( 'Build Effective Landing Pages for your business\' marketing campaigns.', 'elementor' ) );

		if ( ! empty( $trashed_posts ) ) : ?>
			<div class="e-trashed-items">
				<?php echo sprintf( __( 'Or view <a href="%s">Trashed Items</a>', 'elementor' ), admin_url( 'edit.php?post_status=trash&post_type=' . self::CPT ) ); ?>
			</div>
		<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Is Current Admin Page Edit LP
	 *
	 * Checks whether the current page is a native WordPress edit page for a landing page.
	 */
	private function is_landing_page_admin_edit() {
		$screen = get_current_screen();

		if ( 'post' === $screen->base ) {
			return $this->is_elementor_landing_page( get_post() );
		}

		return false;
	}

	/**
	 * Admin Localize Settings
	 *
	 * Enables adding properties to the globally available elementorAdmin.config JS object in the Admin Dashboard.
	 * Runs on the 'elementor/admin/localize_settings' filter.
	 *
	 * @since 3.1.0
	 *
	 * @param $settings
	 * @return array|null
	 */
	private function admin_localize_settings( $settings ) {
		$additional_settings = [
			'urls' => [
				'addNewLandingPageUrl' => $this->get_add_new_landing_page_url(),
			],
			'landingPages' => [
				'landingPagesHasPages' => [] !== $this->get_landing_page_posts(),
				'isLandingPageAdminEdit' => $this->is_landing_page_admin_edit(),
			],
		];

		return array_replace_recursive( $settings, $additional_settings );
	}

	/**
	 * Register Landing Pages CPT
	 *
	 * @since 3.1.0
	 */
	private function register_landing_page_cpt() {
		$labels = [
			'name' => __( 'Landing Pages', 'elementor' ),
			'singular_name' => __( 'Landing Page', 'elementor' ),
			'add_new' => __( 'Add New', 'elementor' ),
			'add_new_item' => __( 'Add New Landing Page', 'elementor' ),
			'edit_item' => __( 'Edit Landing Page', 'elementor' ),
			'new_item' => __( 'New Landing Page', 'elementor' ),
			'all_items' => __( 'All Landing Pages', 'elementor' ),
			'view_item' => __( 'View Landing Page', 'elementor' ),
			'search_items' => __( 'Search Landing Pages', 'elementor' ),
			'not_found' => __( 'No landing pages found', 'elementor' ),
			'not_found_in_trash' => __( 'No landing pages found in trash', 'elementor' ),
			'parent_item_colon' => '',
			'menu_name' => __( 'Landing Pages', 'elementor' ),
		];

		$args = [
			'labels' => $labels,
			'public' => true,
			'show_in_menu' => 'edit.php?post_type=elementor_library&tabs_group=library',
			'capability_type' => 'page',
			'taxonomies' => [ Source_Local::TAXONOMY_TYPE_SLUG ],
			'supports' => [ 'title', 'editor', 'comments', 'revisions', 'trackbacks', 'author', 'excerpt', 'page-attributes', 'thumbnail', 'custom-fields', 'post-formats', 'elementor' ],
		];

		register_post_type( self::CPT, $args );
	}

	/**
	 * Remove Post Type Slug
	 *
	 * Landing Pages are supposed to act exactly like pages. This includes their URLs being directly under the site's
	 * domain name. Since "Landing Pages" is a CPT, WordPress automatically adds the landing page slug as a prefix to
	 * it's posts' permalinks. This method checks if the post's post type is Landing Pages, and if it is, it removes
	 * the CPT slug from the requested post URL.
	 *
	 * Runs on the 'post_type_link' filter.
	 *
	 * @since 3.1.0
	 *
	 * @param $post_link
	 * @param $post
	 * @param $leavename
	 * @return string|string[]
	 */
	private function remove_post_type_slug( $post_link, $post, $leavename ) {
		// Only try to modify the permalink if the post is a Landing Page.
		if ( self::CPT !== $post->post_type || 'publish' !== $post->post_status ) {
			return $post_link;
		}

		// Any slug prefixes need to be removed from the post link.
		return get_home_url() . '/' . $post->post_name . '/';
	}

	/**
	 * Adjust Landing Page Query
	 *
	 * Since Landing Pages are a CPT but should act like pages, the WP_Query that is used to fetch the page from the
	 * database needs to be adjusted. This method adds the Landing Pages CPT to the list of queried post types, to
	 * make sure the database query finds the correct Landing Page to display.
	 * Runs on the 'pre_get_posts' action.
	 *
	 * @since 3.1.0
	 *
	 * @param \WP_Query $query
	 */
	private function adjust_landing_page_query( \WP_Query $query ) {
		// Only handle actual pages.
		if (
			! $query->is_main_query()
			// If the query is not for a page.
			|| ! isset( $query->query['page'] )
			// If the query is for a static home/blog page.
			|| is_home()
			// If the post type comes already set, the main query is probably a custom one made by another plugin.
			// In this case we do not want to intervene in order to not cause a conflict.
			|| isset( $query->query['post_type'] )
		) {
			return;
		}

		// Create the post types property as an array and include the landing pages CPT in it.
		$query_post_types = [ 'post', 'page', self::CPT ];

		// Since WordPress determined this is supposed to be a page, we'll pre-set the post_type query arg to make sure
		// it includes the Landing Page CPT, so when the query is parsed, our CPT will be a legitimate match to the
		// Landing Page's permalink (that is directly under the domain, without a CPT slug prefix). In some cases,
		// The 'name' property will be set, and in others it is the 'pagename', so we have to cover both cases.
		if ( ! empty( $query->query['name'] ) ) {
			$query->set( 'post_type', $query_post_types );
		} elseif ( ! empty( $query->query['pagename'] ) && false === strpos( $query->query['pagename'], '/' ) ) {
			$query->set( 'post_type', $query_post_types );

			// We also need to set the name query var since redirect_guess_404_permalink() relies on it.
			$query->set( 'name', $query->query['pagename'] );
		}
	}

	/**
	 * Handle 404
	 *
	 * This method runs after a page is not found in the database, but before a page is returned as a 404.
	 * These cases are handled in this filter callback, that runs on the 'pre_handle_404' filter.
	 *
	 * In some cases (such as when a site uses custom permalink structures), WordPress's WP_Query does not identify a
	 * Landing Page's URL as a post belonging to the Landing Page CPT. Some cases are handled successfully by the
	 * adjust_landing_page_query() method, but some are not and still trigger a 404 process. This method handles such
	 * cases by overriding the $wp_query global to fetch the correct landing page post entry.
	 *
	 * For example, since Landing Pages slugs come directly after the site domain name, WP_Query might parse the post
	 * as a category page. Since there is no category matching the slug, it triggers a 404 process. In this case, we
	 * run a query for a Landing Page post with the passed slug ($query->query['category_name']. If a Landing Page
	 * with the passed slug is found, we override the global $wp_query with the new, correct query.
	 *
	 * @param $current_value
	 * @param $query
	 * @return false
	 */
	private function handle_404( $current_value, $query ) {
		global $wp_query;

		// If another plugin/theme already used this filter, exit here to avoid conflicts.
		if ( $current_value ) {
			return $current_value;
		}

		if (
			// Make sure we only intervene in the main query.
			! $query->is_main_query()
			// If a post was found, this is not a 404 case, so do not intervene.
			|| ! empty( $query->posts )
			// This filter is only meant to deal with wrong queries where the only query var is 'category_name'.
			// If there is no 'category_name' query var, do not intervene.
			|| empty( $query->query['category_name'] )
			// If the query is for a real taxonomy (determined by it including a table to search in, such as the
			// wp_term_relationships table), do not intervene.
			|| ! empty( $query->tax_query->table_aliases )
		) {
			return false;
		}

		// Search for a Landing Page with the same name passed as the 'category name'.
		$possible_new_query = new \WP_Query( [
			'post_type' => self::CPT,
			'name' => $query->query['category_name'],
		] );

		// Only if such a Landing Page is found, override the query to fetch the correct page.
		if ( ! empty( $possible_new_query->posts ) ) {
			$wp_query = $possible_new_query; //phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}

		return false;
	}

	public function __construct() {
		$this->permalink_structure = get_option( 'permalink_structure' );

		$this->register_landing_page_cpt();

		// If there is a permalink structure set to the site, run the hooks that modify the Landing Pages permalinks to
		// match WordPress' native 'Pages' post type.
		if ( '' !== $this->permalink_structure ) {
			// Landing Pages' post link needs to be modified to be identical to the pages permalink structure. This
			// needs to happen in both the admin and the front end, since post links are also used in the admin pages.
			add_filter( 'post_type_link', function( $post_link, $post, $leavename ) {
				return $this->remove_post_type_slug( $post_link, $post, $leavename );
			}, 10, 3 );

			// The query itself only has to be manipulated when pages are viewed in the front end.
			if ( ! is_admin() || wp_doing_ajax() ) {
				add_action( 'pre_get_posts', function ( $query ) {
					$this->adjust_landing_page_query( $query );
				} );

				// Handle cases where visiting a Landing Page's URL returns 404.
				add_filter( 'pre_handle_404', function ( $value, $query ) {
					return $this->handle_404( $value, $query );
				}, 10, 2 );
			}
		}

		add_action( 'elementor/documents/register', function( Documents_Manager $documents_manager ) {
			$documents_manager->register_document_type( self::DOCUMENT_TYPE, Landing_Page::get_class_full_name() );
		} );

		add_action( 'admin_menu', function() {
			$this->add_submenu_page();
		}, 30 );

		// Add the custom 'Add New' link for Landing Pages into Elementor's admin config.
		add_action( 'elementor/admin/localize_settings', function( array $settings ) {
			return $this->admin_localize_settings( $settings );
		} );

		add_filter( 'elementor/template_library/sources/local/register_taxonomy_cpts', function( array $cpts ) {
			$cpts[] = self::CPT;

			return $cpts;
		} );

		// In the Landing Pages Admin Table page - Overwrite Template type column header title.
		add_action( 'manage_' . Landing_Pages_Module::CPT . '_posts_columns', function( $posts_columns ) {
			/** @var Source_Local $source_local */
			$source_local = Plugin::$instance->templates_manager->get_source( 'local' );

			return $source_local->admin_columns_headers( $posts_columns );
		} );

		// In the Landing Pages Admin Table page - Overwrite Template type column row values.
		add_action( 'manage_' . Landing_Pages_Module::CPT . '_posts_custom_column', function( $column_name, $post_id ) {
			/** @var Landing_Page $document */
			$document = Plugin::$instance->documents->get( $post_id );

			$document->admin_columns_content( $column_name );
		}, 10, 2 );

		// Overwrite the Admin Bar's 'New +' Landing Page URL with the link that creates the new LP in Elementor
		// with the Template Library modal open.
		add_action( 'admin_bar_menu', function( $admin_bar ) {
			// Get the Landing Page menu node.
			$new_landing_page_node = $admin_bar->get_node( 'new-e-landing-page' );

			if ( $new_landing_page_node ) {
				$new_landing_page_node->href = $this->get_add_new_landing_page_url();

				$admin_bar->add_node( $new_landing_page_node );
			}
		}, 100 );
	}
}
