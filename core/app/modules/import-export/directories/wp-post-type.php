<?php
namespace Elementor\Core\App\Modules\ImportExport\Directories;

use Elementor\Core\App\Modules\ImportExport\Iterator;
use Elementor\Core\Utils\ImportExport\WP_Exporter;
use Elementor\Core\Utils\ImportExport\WP_Import;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WP_Post_Type extends Base {

	private $post_type;

	protected function get_name() {
		return $this->post_type;
	}

	public function __construct( Iterator $iterator, Base $parent, $post_type ) {
		parent::__construct( $iterator, $parent );

		$this->post_type = $post_type;
	}

	public function export() {
		$wp_exporter = new WP_Exporter( [
			'content' => $this->post_type,
			'status' => 'publish',
			'limit' => 20,
			'meta_query' => [
				[
					'key' => '_elementor_edit_mode',
					'compare' => 'NOT EXISTS',
				],
			],
			'include_post_featured_image_as_attachment' => true, // Will export 'featured_image' as attachment.
		] );

		$export_result = $wp_exporter->run();

		$this->exporter->add_file( $this->post_type . '.xml', $export_result['xml'] );

		return $export_result['ids'];
	}

	protected function import( array $import_settings ) {
		$wp_importer = new WP_Import( $this->importer->get_archive_file_full_path( $this->post_type . '.xml' ), [
			'fetch_attachments' => true,
		] );

		$result = $wp_importer->run();

		return $result['summary']['posts'];
	}
}
