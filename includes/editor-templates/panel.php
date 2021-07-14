<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$document = Plugin::$instance->documents->get( Plugin::$instance->editor->get_post_id() );
?>
<script type="text/template" id="tmpl-elementor-panel">
	<div id="elementor-mode-switcher"></div>
	<div id="elementor-panel-state-loading">
		<i class="eicon-loading eicon-animation-spin"></i>
	</div>
	<header id="elementor-panel-header-wrapper"></header>
	<main id="elementor-panel-content-wrapper"></main>
	<footer id="elementor-panel-footer">
		<div class="elementor-panel-container"></div>
	</footer>
</script>

<script type="text/template" id="tmpl-elementor-panel-menu">
	<div id="elementor-panel-page-menu-content"></div>
	<# if ( elementor.config.document.panel.needHelpUrl ) { #>
	<div id="elementor-panel__editor__help">
		<a id="elementor-panel__editor__help__link" href="{{{ elementor.config.document.panel.needHelpUrl }}}" target="_blank">
			<?php echo __( 'Need Help', 'elementor' ); ?>
			<i class="eicon-help-o"></i>
		</a>
	</div>
	<# } #>
</script>

<script type="text/template" id="tmpl-elementor-panel-menu-group">
	<div class="elementor-panel-menu-group-title">{{{ title }}}</div>
	<div class="elementor-panel-menu-items"></div>
</script>

<script type="text/template" id="tmpl-elementor-panel-menu-item">
	<div class="elementor-panel-menu-item-icon">
		<i class="{{ icon }}"></i>
	</div>
	<# if ( 'undefined' === typeof type || 'link' !== type ) { #>
		<div class="elementor-panel-menu-item-title">{{{ title }}}</div>
	<# } else {
		let target = ( 'undefined' !== typeof newTab && newTab ) ? '_blank' : '_self';
	#>
		<a href="{{ link }}" target="{{ target }}"><div class="elementor-panel-menu-item-title">{{{ title }}}</div></a>
	<# } #>
</script>

<script type="text/template" id="tmpl-elementor-panel-header">
	<div id="elementor-panel-header-menu-button" class="elementor-header-button">
		<i class="elementor-icon eicon-menu-bar tooltip-target" aria-hidden="true" data-tooltip="<?php esc_attr_e( 'Menu', 'elementor' ); ?>"></i>
		<span class="elementor-screen-only"><?php echo __( 'Menu', 'elementor' ); ?></span>
	</div>
	<div id="elementor-panel-header-title"></div>
	<div id="elementor-panel-header-add-button" class="elementor-header-button">
		<i class="elementor-icon eicon-apps tooltip-target" aria-hidden="true" data-tooltip="<?php esc_attr_e( 'Widgets Panel', 'elementor' ); ?>"></i>
		<span class="elementor-screen-only"><?php echo __( 'Widgets Panel', 'elementor' ); ?></span>
	</div>
</script>

<script type="text/template" id="tmpl-elementor-panel-footer-content">
	<div id="elementor-panel-footer-settings" class="elementor-panel-footer-tool elementor-leave-open tooltip-target" data-tooltip="<?php esc_attr_e( 'Settings', 'elementor' ); ?>">
		<i class="eicon-cog" aria-hidden="true"></i>
		<span class="elementor-screen-only"><?php printf( __( '%s Settings', 'elementor' ), $document::get_title() ); ?></span>
	</div>
	<div id="elementor-panel-footer-navigator" class="elementor-panel-footer-tool tooltip-target" data-tooltip="<?php esc_attr_e( 'Navigator', 'elementor' ); ?>">
		<i class="eicon-navigator" aria-hidden="true"></i>
		<span class="elementor-screen-only"><?php echo __( 'Navigator', 'elementor' ); ?></span>
	</div>
	<div id="elementor-panel-footer-history" class="elementor-panel-footer-tool elementor-leave-open tooltip-target" data-tooltip="<?php esc_attr_e( 'History', 'elementor' ); ?>">
		<i class="eicon-history" aria-hidden="true"></i>
		<span class="elementor-screen-only"><?php echo __( 'History', 'elementor' ); ?></span>
	</div>
	<div id="elementor-panel-footer-responsive" class="elementor-panel-footer-tool elementor-toggle-state">
		<i class="eicon-device-responsive tooltip-target" aria-hidden="true" data-tooltip="<?php esc_attr_e( 'Responsive Mode', 'elementor' ); ?>"></i>
		<span class="elementor-screen-only">
			<?php echo __( 'Responsive Mode', 'elementor' ); ?>
		</span>
	</div>
	<div id="elementor-panel-footer-saver-preview" class="elementor-panel-footer-tool tooltip-target" data-tooltip="<?php esc_attr_e( 'Preview Changes', 'elementor' ); ?>">
		<span id="elementor-panel-footer-saver-preview-label">
			<i class="eicon-preview-medium" aria-hidden="true"></i>
			<span class="elementor-screen-only"><?php echo __( 'Preview Changes', 'elementor' ); ?></span>
		</span>
	</div>
	<div id="elementor-panel-footer-saver-publish" class="elementor-panel-footer-tool">
		<button id="elementor-panel-saver-button-publish" class="elementor-button elementor-button-success elementor-disabled">
			<span class="elementor-state-icon">
				<i class="eicon-loading eicon-animation-spin" aria-hidden="true"></i>
			</span>
			<span id="elementor-panel-saver-button-publish-label">
				<?php echo __( 'Publish', 'elementor' ); ?>
			</span>
		</button>
	</div>
	<div id="elementor-panel-footer-saver-options" class="elementor-panel-footer-tool elementor-toggle-state">
		<button id="elementor-panel-saver-button-save-options" class="elementor-button elementor-button-success tooltip-target elementor-disabled" data-tooltip="<?php esc_attr_e( 'Save Options', 'elementor' ); ?>" data-tooltip-offset="7">
			<i class="eicon-caret-up" aria-hidden="true"></i>
			<span class="elementor-screen-only"><?php echo __( 'Save Options', 'elementor' ); ?></span>
		</button>
		<div class="elementor-panel-footer-sub-menu-wrapper">
			<p class="elementor-last-edited-wrapper">
				<span class="elementor-state-icon">
					<i class="eicon-loading eicon-animation-spin" aria-hidden="true"></i>
				</span>
				<span class="elementor-last-edited">
				</span>
			</p>
			<div class="elementor-panel-footer-sub-menu">
				<div id="elementor-panel-footer-sub-menu-item-save-draft" class="elementor-panel-footer-sub-menu-item elementor-disabled">
					<i class="elementor-icon eicon-save" aria-hidden="true"></i>
					<span class="elementor-title"><?php echo __( 'Save Draft', 'elementor' ); ?></span>
				</div>
				<div id="elementor-panel-footer-sub-menu-item-save-template" class="elementor-panel-footer-sub-menu-item">
					<i class="elementor-icon eicon-folder" aria-hidden="true"></i>
					<span class="elementor-title"><?php echo __( 'Save as Template', 'elementor' ); ?></span>
				</div>
			</div>
		</div>
	</div>
</script>

<script type="text/template" id="tmpl-elementor-mode-switcher-content">
	<input id="elementor-mode-switcher-preview-input" type="checkbox">
	<label for="elementor-mode-switcher-preview-input" id="elementor-mode-switcher-preview">
		<i class="eicon" aria-hidden="true" title="<?php esc_attr_e( 'Hide Panel', 'elementor' ); ?>"></i>
		<span class="elementor-screen-only"><?php echo __( 'Hide Panel', 'elementor' ); ?></span>
	</label>
</script>

<script type="text/template" id="tmpl-editor-content">
	<div class="elementor-panel-navigation">
		<# _.each( elementData.tabs_controls, function( tabTitle, tabSlug ) {
			if ( 'content' !== tabSlug && ! elementor.userCan( 'design' ) ) {
				return;
			}
			$e.bc.ensureTab( 'panel/editor', tabSlug );
			#>
			<div class="elementor-component-tab elementor-panel-navigation-tab elementor-tab-control-{{ tabSlug }}" data-tab="{{ tabSlug }}">
				<a href="#">{{{ tabTitle }}}</a>
			</div>
		<# } ); #>
	</div>
	<# if ( elementData.reload_preview ) { #>
		<div class="elementor-update-preview">
			<div class="elementor-update-preview-title"><?php echo __( 'Update changes to page', 'elementor' ); ?></div>
			<div class="elementor-update-preview-button-wrapper">
				<button class="elementor-update-preview-button elementor-button elementor-button-success"><?php echo __( 'Apply', 'elementor' ); ?></button>
			</div>
		</div>
	<# } #>
	<div id="elementor-controls"></div>
	<# if ( elementData.help_url ) { #>
		<div id="elementor-panel__editor__help">
			<a id="elementor-panel__editor__help__link" href="{{ elementData.help_url }}" target="_blank">
				<?php echo __( 'Need Help', 'elementor' ); ?>
				<i class="eicon-help-o"></i>
			</a>
		</div>
	<# } #>
</script>

<script type="text/template" id="tmpl-elementor-panel-schemes-disabled">
	<img class="elementor-nerd-box-icon" src="<?php echo ELEMENTOR_ASSETS_URL . 'images/information.svg'; ?>" />
	<div class="elementor-nerd-box-title">{{{ '<?php echo __( '%s are disabled', 'elementor' ); ?>'.replace( '%s', disabledTitle ) }}}</div>
	<div class="elementor-nerd-box-message"><?php printf( __( 'You can enable it from the <a href="%s" target="_blank">Elementor settings page</a>.', 'elementor' ), Settings::get_url() ); ?></div>
</script>

<script type="text/template" id="tmpl-elementor-panel-scheme-color-item">
	<div class="elementor-panel-scheme-color-picker-placeholder"></div>
	<div class="elementor-panel-scheme-color-title">{{{ title }}}</div>
</script>

<script type="text/template" id="tmpl-elementor-panel-scheme-typography-item">
	<div class="elementor-panel-heading">
		<div class="elementor-panel-heading-toggle">
			<i class="eicon" aria-hidden="true"></i>
		</div>
		<div class="elementor-panel-heading-title">{{{ title }}}</div>
	</div>
	<div class="elementor-panel-scheme-typography-items elementor-panel-box-content">
		<?php
		$scheme_fields_keys = Group_Control_Typography::get_scheme_fields_keys();

		$typography_group = Plugin::$instance->controls_manager->get_control_groups( 'typography' );
		$typography_fields = $typography_group->get_fields();

		$scheme_fields = array_intersect_key( $typography_fields, array_flip( $scheme_fields_keys ) );

		foreach ( $scheme_fields as $option_name => $option ) :
			?>
			<div class="elementor-panel-scheme-typography-item elementor-control elementor-control-type-select">
				<div class="elementor-panel-scheme-item-title elementor-control-title"><?php echo $option['label']; ?></div>
				<div class="elementor-panel-scheme-typography-item-value elementor-control-input-wrapper">
					<?php if ( 'select' === $option['type'] ) : ?>
						<select name="<?php echo esc_attr( $option_name ); ?>" class="elementor-panel-scheme-typography-item-field">
							<?php foreach ( $option['options'] as $field_key => $field_value ) : ?>
								<option value="<?php echo esc_attr( $field_key ); ?>"><?php echo $field_value; ?></option>
							<?php endforeach; ?>
						</select>
					<?php elseif ( 'font' === $option['type'] ) : ?>
						<select name="<?php echo esc_attr( $option_name ); ?>" class="elementor-panel-scheme-typography-item-field">
							<option value=""><?php echo __( 'Default', 'elementor' ); ?></option>
							<?php foreach ( Fonts::get_font_groups() as $group_type => $group_label ) : ?>
								<optgroup label="<?php echo esc_attr( $group_label ); ?>">
									<?php foreach ( Fonts::get_fonts_by_groups( [ $group_type ] ) as $font_title => $font_type ) : ?>
										<option value="<?php echo esc_attr( $font_title ); ?>"><?php echo $font_title; ?></option>
									<?php endforeach; ?>
								</optgroup>
							<?php endforeach; ?>
						</select>
					<?php elseif ( 'text' === $option['type'] ) : ?>
						<input name="<?php echo esc_attr( $option_name ); ?>" class="elementor-panel-scheme-typography-item-field" />
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</script>

<script type="text/template" id="tmpl-elementor-control-responsive-switchers">
	<div class="elementor-control-responsive-switchers">
		<div class="elementor-control-responsive-switchers__holder">
		<#
			var devices = responsive.devices || [ 'desktop', 'tablet', 'mobile' ];

			_.each( devices, function( device ) {
				var deviceLabel = device.charAt(0).toUpperCase() + device.slice(1),
					tooltipDir = "<?php echo is_rtl() ? 'e' : 'w'; ?>";
			#>
				<a class="elementor-responsive-switcher tooltip-target elementor-responsive-switcher-{{ device }}" data-device="{{ device }}" data-tooltip="{{ deviceLabel }}" data-tooltip-pos="{{ tooltipDir }}">
					<i class="eicon-device-{{ device }}"></i>
				</a>
			<# } );
		#>
		</div>
	</div>
</script>

<script type="text/template" id="tmpl-elementor-control-dynamic-switcher">
	<div class="elementor-control-dynamic-switcher elementor-control-unit-1" data-tooltip="<?php echo __( 'Dynamic Tags', 'elementor' ); ?>">
		<i class="eicon-database"></i>
	</div>
</script>
<script type="text/template" id="tmpl-elementor-control-element-color-picker">
	<div class="elementor-control-element-color-picker e-control-tool" data-tooltip="<?php echo esc_attr__( 'Color Sampler', 'elementor' ); ?>">
		<i class="eicon-eyedropper"></i>
	</div>
</script>

<script type="text/template" id="tmpl-elementor-control-dynamic-cover">
	<div class="elementor-dynamic-cover__settings">
		<i class="eicon-{{ hasSettings ? 'wrench' : 'database' }}"></i>
	</div>
	<div class="elementor-dynamic-cover__title" title="{{{ title + ' ' + content }}}">{{{ title + ' ' + content }}}</div>
	<# if ( isRemovable ) { #>
		<div class="elementor-dynamic-cover__remove">
			<i class="eicon-close-circle"></i>
		</div>
	<# } #>
</script>

<script type="text/template" id="tmpl-elementor-dynamic-tags-promo">
	<div class="elementor-tags-list__teaser">
		<div class="elementor-tags-list__group-title elementor-tags-list__teaser-title">
			<i class="eicon-info-circle"></i><?php echo __( 'Elementor Dynamic Content', 'elementor' ); ?>
		</div>
		<div class="elementor-tags-list__teaser-text">
			<?php echo __( 'You’re missing out!', 'elementor' ); ?><br />
			<?php echo __( 'Get more dynamic capabilities by incorporating dozens of Elementor\'s native dynamic tags.', 'elementor' ); ?>
			<a href="{{{ elementor.config.dynamicPromotionURL }}}" class="elementor-tags-list__teaser-link" target="_blank">
				<?php echo __( 'See it in action', 'elementor' ); ?>
			</a>
		</div>
	</div>
</script>
