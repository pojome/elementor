<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $wp_version;

$body_classes = [
	'elementor-editor-active',
	'wp-version-' . str_replace( '.', '-', $wp_version ),
];

if ( is_rtl() ) {
	$body_classes[] = 'rtl';
}

if ( ! Plugin::$instance->role_manager->user_can( 'design' ) ) {
	$body_classes[] = 'elementor-editor-content-only';
}

$notice = Plugin::$instance->editor->notice_bar->get_notice();
$optimizer_active = Plugin::$instance->experiments->is_feature_active( 'elementor_optimizer' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title><?php echo __( 'Elementor', 'elementor' ) . ' | ' . get_the_title(); ?></title>
	<?php wp_head(); ?>
	<script>
		var ajaxurl = '<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>';
	</script>
</head>
<body class="<?php echo implode( ' ', $body_classes ); ?>">
<div id="elementor-editor-wrapper">
	<div id="elementor-panel" class="elementor-panel"></div>
	<div id="elementor-preview">
		<div id="elementor-loading">
			<div class="elementor-loader-wrapper">
				<div class="elementor-loader">
					<div class="elementor-loader-boxes">
						<div class="elementor-loader-box"></div>
						<div class="elementor-loader-box"></div>
						<div class="elementor-loader-box"></div>
						<div class="elementor-loader-box"></div>
					</div>
				</div>
				<div class="elementor-loading-title"><?php echo __( 'Loading', 'elementor' ); ?></div>
			</div>
		</div>
		<div id="elementor-responsive-bar"></div>
		<div id="elementor-preview-responsive-wrapper" class="elementor-device-desktop elementor-device-rotate-portrait">
			<div id="elementor-preview-loading">
				<i class="eicon-loading eicon-animation-spin" aria-hidden="true"></i>
			</div>
			<?php if ( $notice ) { ?>
				<div id="elementor-notice-bar">
					<i class="eicon-elementor-square"></i>
					<div id="elementor-notice-bar__message"><?php echo sprintf( $notice['message'], $notice['action_url'] ); ?></div>
					<div id="elementor-notice-bar__action"><a href="<?php echo $notice['action_url']; ?>" target="_blank"><?php echo $notice['action_title']; ?></a></div>
					<i id="elementor-notice-bar__close" class="eicon-close"></i>
				</div>
			<?php } // IFrame will be created here by the Javascript later. ?>
		</div>
	</div>
	<div id="elementor-navigator"></div>
</div>
<?php if ( $optimizer_active ) { ?>
<div id="elementor-optimizer"></div>
<?php } ?>
<?php
	wp_footer();
	/** This action is documented in wp-admin/admin-footer.php */
	do_action( 'admin_print_footer_scripts' );
?>
</body>
</html>
