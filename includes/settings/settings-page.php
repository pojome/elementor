<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

abstract class Settings_Page {
	private $tabs;

	const PAGE_ID = '';

	abstract protected function create_tabs();

	abstract protected function get_page_title();

	public final static function get_url() {
		return admin_url( 'admin.php?page=' . static::PAGE_ID );
	}

	public function __construct() {
		add_action( 'admin_init', [ $this, 'register_settings_fields' ] );
	}

	public final function get_tabs() {
		$this->ensure_tabs();

		return $this->tabs;
	}

	public final function add_tab( $tab_id, array $tab_args = [] ) {
		$this->ensure_tabs();

		if ( isset( $this->tabs[ $tab_id ] ) ) {
			return;
		}

		if ( ! isset( $tab_args['sections'] ) ) {
			$tab_args['sections'] = [];
		}

		$this->tabs[ $tab_id ] = $tab_args;
	}

	public final function add_section( $tab_id, $section_id, array $section_args = [] ) {
		$this->ensure_tabs();

		if ( ! isset( $this->tabs[ $tab_id ] ) ) {
			return;
		}

		if ( isset( $this->tabs[ $tab_id ]['sections'][ $section_id ] ) ) {
			return;
		}

		if ( ! isset( $section_args['fields'] ) ) {
			$section_args['fields'] = [];
		}

		$this->tabs[ $tab_id ]['sections'][ $section_id ] = $section_args;
	}

	public final function add_field( $tab_id, $section_id, $field_id, array $field_args ) {
		$this->ensure_tabs();

		if ( isset( $this->tabs[ $tab_id ]['sections'][ $section_id ]['fields'][ $field_id ] ) ) {
			return;
		}

		$this->tabs[ $tab_id ]['sections'][ $section_id ]['fields'][ $field_id ] = $field_args;
	}

	public final function add_fields( $tab_id, $section_id, array $fields ) {
		foreach ( $fields as $field_id => $field ) {
			$this->add_field( $tab_id, $section_id, $field_id, $field );
		}
	}

	public final function register_settings_fields() {
		$controls_class_name = __NAMESPACE__ . '\Settings_Controls';

		$tabs = $this->get_tabs();

		foreach ( $tabs as $tab_id => $tab ) {

			foreach ( $tab['sections'] as $section_id => $section ) {
				$full_section_id = 'elementor_' . $section_id . '_section';

				$label = isset( $section['label'] ) ? $section['label'] : '';

				$section_callback = isset( $section['callback'] ) ? $section['callback'] : '__return_empty_string';

				add_settings_section( $full_section_id, $label, $section_callback, static::PAGE_ID );

				foreach ( $section['fields'] as $field_id => $field ) {
					$full_field_id = ! empty( $field['full_field_id'] ) ? $field['full_field_id'] : 'elementor_' . $field_id;

					$field['field_args']['id'] = $full_field_id;

					$field_classes = [ $full_field_id ];

					if ( ! empty( $field['class'] ) ) {
						$field_classes[] = $field['field_args']['class'];
					}

					$field['field_args']['class'] = implode( ' ', $field_classes );

					add_settings_field(
						$full_field_id,
						isset( $field['label'] ) ? $field['label'] : '',
						[ $controls_class_name, 'render' ],
						static::PAGE_ID,
						$full_section_id,
						$field['field_args']
					);

					$setting_args = [];

					if ( ! empty( $field['setting_args'] ) ) {
						$setting_args = $field['setting_args'];
					}

					register_setting( static::PAGE_ID, $full_field_id, $setting_args );
				}
			}
		}
	}

	public function display_settings_page() {
		$tabs = $this->get_tabs();
		?>
		<div class="wrap">
			<h1><?php echo $this->get_page_title(); ?></h1>
			<div id="elementor-settings-tabs-wrapper" class="nav-tab-wrapper">
				<?php
				foreach ( $tabs as $tab_id => $tab ) {
					if ( empty( $tab['sections'] ) ) {
						continue;
					}

					$active_class = '';

					if ( 'general' === $tab_id ) {
						$active_class = ' nav-tab-active';
					}

					echo "<a id='elementor-settings-tab-$tab_id' class='nav-tab$active_class' href='#tab-$tab_id'>$tab[label]</a>";
				} ?>
			</div>
			<form id="elementor-settings-form" method="post" action="options.php">
				<?php
				settings_fields( static::PAGE_ID );

				foreach ( $tabs as $tab_id => $tab ) {
					if ( empty( $tab['sections'] ) ) {
						continue;
					}

					$active_class = '';

					if ( 'general' === $tab_id ) {
						$active_class = ' elementor-active';
					}

					echo "<div id='tab-$tab_id' class='elementor-settings-form-page$active_class'>";

					foreach ( $tab['sections'] as $section_id => $section ) {
						$full_section_id = 'elementor_' . $section_id . '_section';

						if ( ! empty( $section['label'] ) ) {
							echo "<h2>$section[label]</h2>";
						}

						if ( ! empty( $section['callback'] ) ) {
							$section['callback']();
						}

						echo '<table class="form-table">';

						do_settings_fields( static::PAGE_ID, $full_section_id );

						echo '</table>';
					}

					echo '</div>';
				}

				submit_button();
				?>
			</form>
		</div><!-- /.wrap -->
		<?php
	}

	private function ensure_tabs() {
		if ( null === $this->tabs ) {
			$this->tabs = $this->create_tabs();

			do_action( 'elementor/admin/after_create_settings/' . static::PAGE_ID, $this );
		}
	}
}
