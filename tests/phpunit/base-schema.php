<?php
namespace Elementor\Testing;

use Elementor\Core\Utils\Collection;
use Elementor\Tracker;
use JsonSchema\Exception\ValidationException;
use JsonSchema\SchemaStorage;
use JsonSchema\Validator;

class Base_Schema extends Elementor_Test_Base {
	const HTTP_USER_AGENT = 'test-agent';

	public function setUp() {
		parent::setUp();

		// Required by `Tracker::get_tracking_data`.
		$_SERVER['HTTP_USER_AGENT'] = self::HTTP_USER_AGENT;
	}

	protected function validate_against_schema( $data_for_validation, $schema_file ) {
		// Since the usage system represents objects as array instead of stdClass.
		$data_for_validation = json_decode( json_encode( $data_for_validation ) );

		// Validate
		$validator = new Validator;
		$validator->validate( $data_for_validation, (object) [ '$ref' => 'file://' . $schema_file ] );

		if ( ! $validator->isValid() ) {
			$error_message = 'JSON does not validate. Violations:' . PHP_EOL;
			foreach ( $validator->getErrors() as $error ) {
				$error_message .= sprintf( '[%s] %s' . PHP_EOL, $error['property'], $error['message'] );
			}

			throw new ValidationException( $error_message );
		}

		return true;
	}

	protected function validate_current_tracking_data_against_schema( $schema_file ) {
		return $this->validate_against_schema( Tracker::get_tracking_data(), $schema_file );
	}

	protected function assert_schema_has_no_additional_properties( $schema_file ) {
		$json_schema_object = json_decode( file_get_contents( $schema_file ) );

		$schema_storage = new SchemaStorage();
		$schema_storage->addSchema( 'validation', $json_schema_object );

		$properties_all_objects_recursive = function ( $node, callable $callback ) use ( &$properties_all_objects_recursive ) {
			if ( ! ( $node instanceof \stdClass ) ) {
				return;
			}

			if ( isset( $node->properties ) || isset( $node->patternProperties ) ) {
				$callback( $node );
			}

			foreach ( $node as $part ) {
				$properties_all_objects_recursive( $part, $callback );
			}
		};

		// Act.
		$usage_schema = $schema_storage->getSchema( 'validation' );

		// Assert.
		$properties_all_objects_recursive( $usage_schema, function ( $node ) {
			$id = $node->{'$id'};
			$this->assertTrue( isset( $node->additionalProperties ), "Ensure node: '$id' 'additionalProperties' exists" );
			$this->assertFalse( $node->additionalProperties, "Ensure node: '$id' 'additionalProperties' is false" );
		} );
	}

	// TODO: Should be in batter place, like dedicated factory.
	protected function generate_plugins_mock() {
		// Arrange
		$plugins = new Collection( [
			'elementor/elementor.php' => [
				'Elementor tested up to' => '',
				'Name' => 'Elementor',
				'PluginURI' => 'https:\/\/elementor.com\/?utm_source=wp-plugins&utm_campaign=plugin-uri&utm_medium=wp-dash',
				'Version' => ELEMENTOR_VERSION,
				'Description' => 'The Elementor Website Builder has it all: drag and drop page builder, pixel perfect design, mobile responsive editing, and more. Get started now!',
				'Author' => "Elementor.com",
				'AuthorURI' => 'https:\/\/elementor.com\/?utm_source=wp-plugins&utm_campaign=author-uri&utm_medium=wp-dash',
				'TextDomain' => 'elementor',
				'DomainPath' => '',
				'Network' => false,
				'RequiresWP' => '',
				'RequiresPHP' => '',
				'Title' => 'Elementor',
				'AuthorName' => 'Elementor.com',
			],
		] );

		$this->mock_wp_api( [
			'get_plugins' => $plugins,
		] );
	}
}
