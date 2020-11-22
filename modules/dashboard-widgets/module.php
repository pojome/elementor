<?php

namespace Elementor\Modules\DashboardWidgets;

use Elementor\Api;
use Elementor\Core\Base\Module as BaseModule;
use Elementor\Plugin;
use Elementor\Utils;
use ElementorLabs\Classes\Module_Base;
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Module
 *
 * @package ElementorLabs\Modules\Widgets
 */
class Module extends BaseModule {

	/**
	 * @var WP_Query $recently_edited_query
	 */
	private $recently_edited_query;

	public function get_name() {
		return 'widgets';
	}

	public function __construct() {

		$this->set_vars();

		remove_action( 'welcome_panel', 'wp_welcome_panel' );

		if ( 3 >= $this->recently_edited_query->post_count ) {
			add_action( 'welcome_panel', [ $this, 'welcome_dashboard_widget_render' ], PHP_INT_MAX );
		}

		//add_action( 'admin_enqueue_scripts', [ $this, 'load_assets' ] );

		if ( is_network_admin() ) {
			add_action( 'wp_network_dashboard_setup', [ $this, 'add_dashboard_widgets' ], 999 );
		} elseif ( is_user_admin() ) {
			add_action( 'wp_user_dashboard_setup', [ $this, 'add_dashboard_widgets' ], 999 );
		} else {
			add_action( 'wp_dashboard_setup', [ $this, 'add_dashboard_widgets' ], 999 );
		}
	}

	public function set_vars() {
		$recently_edited_query_args = [
			'post_type' => 'any',
			'post_status' => [ 'publish', 'draft' ],
			'posts_per_page' => '3',
			'meta_key' => '_elementor_edit_mode',
			'meta_value' => 'builder',
			'orderby' => 'modified',
		];

		$this->recently_edited_query = new WP_Query( $recently_edited_query_args );

	}

	public function load_assets() {
		$screen = get_current_screen();

		if ( 'dashboard' !== $screen->id ) {
			return;
		}

		wp_enqueue_script( 'elementor-labs-widgets', ELEMENTOR_LABS_ASSETS_URL . '/widgets.js', array( 'jquery' ), ELEMENTOR_VERSION, true );
		wp_enqueue_style( 'elementor-labs-widgets', ELEMENTOR_LABS_ASSETS_URL . '/widgets.css', array(), ELEMENTOR_VERSION );
	}

	public function add_dashboard_widgets() {

		$widgets = array(
			'elementor_quick_actions_dashboard_widget' => array(
				'label' => esc_html__( 'Elementor Quick Actions', 'elementor' ),
				'callback' => [ $this, 'dashboard_quick_actions_render' ],
			),
			'elementor_resources_dashboard_widget' => array(
				'label' => esc_html__( 'Elementor Resources', 'elementor' ),
				'callback' => [ $this, 'dashboard_resources_render' ],
			),
			'elementor_news_updates_dashboard_widget' => array(
				'label' => esc_html__( 'Elementor News & Updates', 'elementor' ),
				'callback' => [ $this, 'dashboard_news_updates_render' ],
			),
		);

		$show_welcome_panel = get_user_meta( get_current_user_id(), 'show_welcome_panel', true );
		if ( ! $show_welcome_panel ) {
			$widgets['elementor_videos_dashboard_widget'] = array(
				'label' => esc_html__( 'Elementor Video tutorials', 'elementor' ),
				'callback' => [ $this, 'dashboard_videos_render' ],
			);
		}

		$widget_backup = array();

		foreach ( $widgets as $widget_id => $widget ) {

			add_filter( "postbox_classes_dashboard_{$widget_id}", array( $this, 'add_global_widget_class' ) );

			wp_add_dashboard_widget( $widget_id, $widget['label'], $widget['callback'] );

			$widget_backup[] = $widget_id;

		}

		global $wp_meta_boxes;

		$default_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];

		$temp_widget_backup = [];
		foreach ( $widget_backup as $widget_id ) {
			$temp_widget_backup[ $widget_id ] = $default_dashboard[ $widget_id ];
			unset( $default_dashboard[ $widget_id ] );
		}

		$sorted_dashboard = $temp_widget_backup + $default_dashboard;

		// Save the sorted array back into the original metaboxes.
		$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
	}

	public function add_global_widget_class( $classes ) {
		$classes[] = 'elementor-dashboard-widget';

		return $classes;
	}

	public function welcome_dashboard_widget_render() {
		$create_page_url = Utils::get_create_new_post_url();
		$create_post_url = Utils::get_create_new_post_url( 'post' );

		$action_links = array(
				'write_blog' => array(
						'icon' => 'dashicons-welcome-write-blog',
						'label' => esc_html__( 'Create your first blog page', 'elementor' ),
						'url' => esc_url( $create_post_url ),
				),
				'about_page' => array(
						'icon' => 'dashicons-plus-alt2',
						'label' => esc_html__( 'Add an about page', 'elementor' ),
						'url' => esc_url( $create_page_url ),
				),
				'home_page' => array(
						'icon' => 'dashicons-admin-home',
						'label' => esc_html__( 'Setup your homepage', 'elementor' ),
						'url' => esc_url( admin_url( 'options-reading.php' ) ),
				),
				'view_site' => array(
						'icon' => 'dashicons-welcome-view-site',
						'label' => esc_html__( 'View your site', 'elementor' ),
						'url' => esc_url( get_site_url() ),
				),
		);
		?>
		<div class="elementor_welcome_dashboard_widget_wrap metabox-holder">
			<div class="postbox-container" style="width: 100%; float: none;">
				<div class="meta-box-sortables ui-sortable">
					<div id="elementor_welcome_dashboard_widget" class="postbox elementor-dashboard-widget">
						<div class="postbox-header">
							<h2 class="hndle ui-sortable-handle"><span>Welcome Dashboard Widget</span></h2>
							<button type="button" style="padding: 0;" class="welcome-panel-close handlediv close-hndle" aria-expanded="true"><span class="screen-reader-text">Toggle panel: Welcome Dashboard Widget</span><span class="toggle-indicator" aria-hidden="true"></span></button>
						</div>
						<div class="flex inside">
							<div class="video flex-child">
								<iframe width="560" height="315" src="https://www.youtube.com/embed/_X0eYtY8T_U" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen=""></iframe>
							</div>
							<div class="intro flex-child">
								<h3><?php esc_html_e( 'Welcome to Elementor', 'elementor' ); ?></h3>
								<p>
									You’re about to create your professional WordPress site with Elementor. From here, you can quickly start working on your site, watch video tutorials, read up on news updates and much more. Let’s get started!
								</p>
								<p>
									<a class="button button-primary button-large" href="<?php echo esc_url( $create_page_url ); ?>"><?php esc_html_e( 'Create a new page', 'elementor' ); ?></a>
								</p>
							</div>
							<div class="next-steps flex-child">
								<h4><?php esc_html_e( 'Next steps', 'elementor' ); ?></h4>
								<ul>
									<?php foreach ( $action_links as $action_link ) : ?>
										<li>
											<span class="dashicons <?php echo $action_link['icon']; ?>"></span>
											<a href="<?php echo $action_link['url']; ?>"><?php echo $action_link['label']; ?></a>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	public function dashboard_quick_actions_render() {
		$action_links = array(
			'page' => array(
				'post_type' => true,
				'icon' => 'dashicons-media-text',
				'label' => __( 'Page', 'elementor' ),
			),
			'post' => array(
				'post_type' => true,
				'icon' => 'dashicons-sticky',
				'label' => __( 'Post', 'elementor' ),
			),
			/*
			'landing_page' => array(
				'post_type' => false,
				'icon' => 'dashicons-media-text',
				'label' => __( 'Landing Page', 'elementor' ),
				'args' => array(
					'library_type' => 'landing_page',
					'add_new'      => 1,
				),
			),
			*/
		);

		$cpt_support = $this->get_elementor_cpt_support();

		if ( defined( 'ELEMENTOR_PRO__FILE__' ) ) {
			$action_links['elementor_library'] = array(
					'post_type' => false,
					'icon' => 'dashicons-format-gallery',
					'label' => __( 'Popup', 'elementor' ),
					'args' => array(
							'template_type' => 'popup',
					),
			);
		}

		?>
		<div class="elementor-quick-actions-wrap">
			<div class="flex">
				<div class="flex-child">
					<h3><?php esc_html_e( 'Add New', 'elementor' ); ?></h3>
					<ul>
						<?php foreach ( $action_links as $key => $action_link ) :

							if ( ! in_array( $key, $cpt_support ) ) {
								continue;
							}

							if ( ! $action_link['post_type'] ) {
								$url = add_query_arg(
									$action_link['args'],
									Utils::get_create_new_post_url()
								);
							} else {
								$url = Utils::get_create_new_post_url( $key );
							}
							?>
							<li>
								<span class="dashicons <?php echo $action_link['icon']; ?>"></span>
								<a href="<?php echo esc_url( $url ); ?>"><?php echo $action_link['label']; ?></a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>

				<div class="flex-child">
					<h3><?php esc_html_e( 'Manage', 'elementor' ); ?></h3>
					<ul>
						<?php
						$action_links = array(
							'elementor_finder' => array(
								'icon' => 'dashicons-search',
								'label' => __( 'Find Anything', 'elementor' ),
								'url' => '#',
							),
							'site_menu' => array(
								'icon' => 'dashicons-menu-alt3',
								'label' => __( 'Setup site menu', 'elementor' ),
								'url' => 'nav-menus.php',
							),
							'global_settings' => array(
								'icon' => 'dashicons-admin-site',
								'label' => __( 'Global Settings', 'elementor' ),
								'url' => 'options-general.php',
							),
							'theme_builder' => array(
								'icon' => 'dashicons-networking',
								'label' => __( 'Theme builder', 'elementor' ),
								'url' => 'edit.php?post_type=elementor_library&tabs_group=theme',
							),
							'view_site' => array(
								'icon' => 'dashicons-welcome-view-site',
								'label' => __( 'View your site', 'elementor' ),
								'url' => get_site_url(),
							),
						);

						if ( ! defined( 'ELEMENTOR_PRO__FILE__' ) ) {
							unset( $action_links['theme_builder'] );
						}

						foreach ( $action_links as $key => $action_link ) :
							?>
							<li>
								<span class="dashicons <?php echo $action_link['icon']; ?>"></span>
								<a class="<?php echo $key; ?>" href="<?php echo esc_url( $action_link['url'] ); ?>"><?php echo $action_link['label']; ?></a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>

			<?php
			if ( $this->recently_edited_query->have_posts() ) : ?>
				<div class="e-overview__recently-edited">
					<h3 class="e-overview__heading"><?php echo __( 'Recently Edited', 'elementor' ); ?></h3>
					<ul class="e-overview__posts">
						<?php
						while ( $this->recently_edited_query->have_posts() ) :
							$this->recently_edited_query->the_post();
							$document = Plugin::$instance->documents->get( get_the_ID() );

							$date = date_i18n( _x( 'M jS', 'Dashboard Overview Widget Recently Date', 'elementor' ), get_the_modified_time( 'U' ) );
							?>
							<li class="e-overview__post">
								<a href="<?php echo esc_attr( $document->get_edit_url() ); ?>" class="e-overview__post-link"><?php echo esc_html( get_the_title() ); ?> <span class="dashicons dashicons-edit"></span></a> <span><?php echo $date; ?>, <?php the_time(); ?></span>
							</li>
						<?php endwhile; ?>
					</ul>
				</div>
			<?php endif; ?>

			<div class="version-updates-wrap">
				<h3><?php esc_html_e( 'Versions Updates', 'elementor' ); ?></h3>
				<div class="versions-info">
					<div class="elementor-version">
						<div class="version-row">
							Elementor V<?php echo ELEMENTOR_VERSION; ?> | <a href="update-core.php"><?php _e( 'update', 'elementor' ); ?></a>
						</div>
						<?php if ( defined( 'ELEMENTOR_PRO_VERSION' ) ) : ?>
							<div class="version-row">
								Elementor Pro V<?php echo ELEMENTOR_PRO_VERSION; ?> | <a href="update-core.php"><?php _e( 'update', 'elementor' ); ?></a>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	public function dashboard_resources_render() {
		$sections = array(
			'knowledge_base' => array(
				'heading' => __( 'Knowledge Base', 'elementor' ),
				'links' => array(
					array(
						'icon' => 'dashicons-video-alt2',
						'text' => __( 'Video tutorials', 'elementor' ),
						'url' => 'https://go.elementor.com/wp-dash-video-tutorials',
						'desc' => 'Browse a lot of videos',
					),
					array(
						'icon' => 'dashicons-media-document',
						'text' => __( 'Technical docs', 'elementor' ),
						'url' => 'https://go.elementor.com/wp-dash-technical-docs',
						'desc' => 'Browse a lot of docs',
					),
					array(
						'icon' => 'dashicons-editor-help',
						'text' => __( 'FAQs', 'elementor' ),
						'url' => 'https://go.elementor.com/wp-dash-faq',
						'desc' => 'Answer your questions',
					),
				),
			),
			'community' => array(
				'heading' => __( 'Community', 'elementor' ),
				'links' => array(
					array(
						'icon' => 'dashicons-money',
						'text' => __( 'Hire an expert', 'elementor' ),
						'url' => 'https://go.elementor.com/wp-dash-experts',
						'desc' => 'Browse a lot of videos',
					),
					array(
						'icon' => 'dashicons-media-document',
						'text' => __( 'Facebook Community', 'elementor' ),
						'url' => 'https://go.elementor.com/wp-dash-facebook-community',
						'desc' => 'Browse a lot of docs',
					),
					array(
						'icon' => 'dashicons-editor-help',
						'text' => __( 'Upcoming Meetups', 'elementor' ),
						'url' => 'https://go.elementor.com/wp-dash-meetups',
						'desc' => 'Answer your questions',
					),
				),
			),
		);
		?>
		<div class="elementor-resources-wrap">
			<?php foreach ( $sections as $section ) : ?>
				<h3><?php echo $section['heading']; ?></h3>
				<?php foreach ( $section['links'] as $link ) : ?>
					<div class="resource-link">
						<span class="dashicons <?php echo $link['icon']; ?>"></span>
						<a target="_blank" href="<?php echo $link['url']; ?>"><?php echo $link['text']; ?></a>
						<p><?php echo $link['desc']; ?></p>
					</div>
				<?php endforeach; ?>
			<?php endforeach; ?>
		</div>
		<?php
	}

	public function dashboard_news_updates_render() {
		?>
		<div class="elementor-news-updates-wrap">
			<?php
			$elementor_feed = Api::get_feed_data();

			if ( ! empty( $elementor_feed ) ) : ?>
				<div class="e-overview__feed">
					<ul class="e-overview__posts">
						<?php foreach ( $elementor_feed as $feed_item ) : ?>
							<li class="e-overview__post">
								<a href="<?php echo esc_url( $feed_item['url'] ); ?>" class="e-overview__post-link" target="_blank">
									<?php if ( ! empty( $feed_item['badge'] ) ) : ?>
										<span class="e-overview__badge"><?php echo esc_html( $feed_item['badge'] ); ?></span>
									<?php endif; ?>
									<?php echo esc_html( $feed_item['title'] ); ?>
								</a>
								<p class="e-overview__post-description"><?php echo esc_html( $feed_item['excerpt'] ); ?></p>
							</li>
						<?php endforeach; ?>
					</ul>
					<div class="e-overview__footer">
						<a target="_blank" href="https://go.elementor.com/overview-widget-blog/"><?php esc_html_e( 'Vist blog', 'elementor' ); ?></a>
						<span class="dashicons dashicons-arrow-right-alt2"></span>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	public function dashboard_videos_render() {

		$links = array(
			array(
				'text' => 'How to Use Elementor Site Settings',
				'url' => 'https://youtu.be/GX4AKb2mYHw',
			),
			array(
				'text' => 'Elementor Theme Builder Overview',
				'url' => 'https://youtu.be/BWx8NQm2hdI',
			),
			array(
				'text' => 'Global Colors & Fonts: Creating a Design System With Elementor',
				'url' => 'https://youtu.be/OvETB43I7_w',
			),
			array(
				'text' => 'Elementor Pro Live Webinar: Create a Lead Generating Form Popup',
				'url' => 'https://youtu.be/3jAGJtPb0Us',
			),
			array(
				'text' => 'How to Use Elementor\'s Lottie Widget',
				'url' => 'https://youtu.be/5m8G57735fQ',
			),
		)
		?>
		<div class="elementor-video-tutorials-wrap">
			<div class="embed">
				<iframe width="560" height="315" src="https://www.youtube.com/embed/nZlgNmbC-Cw?controls=0" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen=""></iframe>
			</div>
			<div class="links">
				<ul>
					<?php foreach ( $links as $link ) : ?>
						<li>
							<span class="dashicons dashicons-video-alt3"></span>
							<a href="<?php echo $link; ?>"><?php echo $link['text']; ?></a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
		<?php
	}

	private function get_elementor_cpt_support() {
		return get_option( 'elementor_cpt_support' );
	}
}
