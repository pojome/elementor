<?php
namespace Elementor\Modules\System_Info\Reporters;

use Elementor\Plugin;

/**
 * Elementor experiments report.
 *
 * Elementor experiment report handler class responsible for generating a report for
 * the experiments included in Elementor and their status.
 *
 * @since 3.3.0
 */
class Experiments extends Base {

	/**
	 * Get experiments reporter title.
	 *
	 * @since 3.3.0
	 * @access public
	 *
	 * @return string Reporter title.
	 */
	public function get_title() {
		return 'Elementor Experiments';
	}

	/**
	 * Get experiments report fields.
	 *
	 * @since 3.3.0
	 * @access public
	 *
	 * @return array Required report fields with field ID and field label.
	 */
	public function get_fields() {
		return [
			'experiments' => '',
		];
	}

	/**
	 * Get Experiments.
	 *
	 * Retrieve the list of Elementor experiments and each experiment's status (active/inactive), in HTML table format.
	 *
	 * @since 3.3.0
	 * @access public
	 *
	 * @return array
	 */
	public function get_experiments() {
		$experiments = Plugin::$instance->experiments->get_features();

		$output = '';

		foreach ( $experiments as $experiment ) {
			// If the state is default, add the default state to the string.
			$state = Plugin::$instance->experiments->get_feature_state_label( $experiment );

			$output .= '<tr><td>' . $experiment['title'] . ': </td>';
			$output .= '<td>' . $state . '</td>';
			$output .= '</tr>';
		}

		return [
			'value' => $output,
		];
	}

	/**
	 * Get Raw Experiments.
	 *
	 * Retrieve a string containing the list of Elementor experiments and each experiment's status (active/inactive).
	 * The string is formatted in a non-table structure, and it is meant for export/download of the system info reports.
	 *
	 * @since 3.3.0
	 * @access public
	 *
	 * @return array
	 */
	public function get_raw_experiments() {
		$experiments = Plugin::$instance->experiments->get_features();

		$output = '';

		$is_first_item = true;

		foreach ( $experiments as $experiment ) {
			// If the state is default, add the default state to the string.
			$state = Plugin::$instance->experiments->get_feature_state_label( $experiment );

			// The first item automatically has a tab character before it. Add tabs only to the rest of the items.
			if ( ! $is_first_item ) {
				$output .= "\t";
			}

			$output .= $experiment['title'] . ': ' . $state . PHP_EOL;

			$is_first_item = false;
		}

		return [
			'value' => $output,
		];
	}
}
