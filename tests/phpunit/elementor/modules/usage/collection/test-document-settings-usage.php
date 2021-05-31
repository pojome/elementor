<?php
namespace Elementor\Tests\Phpunit\Elementor\Modules\Usage;

use Elementor\Modules\Usage\Collection\DocumentSettingsUsage;
use Elementor\Testing\Elementor_Test_Base;

class TestDocumentSettingsUsage extends Elementor_Test_Base {
	const DOCUMENTS_TEST_COUNT = 2;
	const DOCUMENT_TEST_SETTINGS = [
		'background_background' => 'red',
		'hide_title' => 'yes',
	];

	/**
	 * @param DocumentSettingsUsage $usage
	 *
	 * @return array new documents
	 */
	private function add_mock_documents( $usage ) {
		$documents = [];

		for ( $i = 0; $i < self::DOCUMENTS_TEST_COUNT; ++$i ) {
			$document = $this->factory()->create_post();
			$document->save( [
				'settings' => self::DOCUMENT_TEST_SETTINGS,
			] );

			$documents [] = $document;

			$usage->add( $document );
		}

		return $documents;
	}

	public function test_add() {
		// Arrange.
		$usage = DocumentSettingsUsage::create();

		// Act.
		$this->add_mock_documents( $usage );
		$usage = $usage->get( 'wp-post' );

		// Assert.
		$this->assertCount( self::DOCUMENTS_TEST_COUNT, $usage );

		foreach ( $usage as $setting_key => $setting_count ) {
			$this->arrayHasKey( $setting_key, self::DOCUMENT_TEST_SETTINGS );
			$this->assertEquals( self::DOCUMENTS_TEST_COUNT, $setting_count );
		}
	}

	public function test_remove() {
		// Arrange.
		$usage = DocumentSettingsUsage::create();

		$documents = $this->add_mock_documents( $usage );

		$differ_count = 0;

		// Act.
		foreach ( $documents as $document ) {

			// Assert.
			foreach ( $usage->get( 'wp-post' ) as $setting_key => $setting_count ) {
				$this->assertEquals( self::DOCUMENTS_TEST_COUNT - $differ_count, $setting_count );
			}

			$usage->remove( $document );

			$differ_count++;
		}

		// Assert - Validate nothing left.
		$this->assertEmpty( $usage->get('wp-post') );
	}

	public function test_save() {
		// Arrange.
		$usage = DocumentSettingsUsage::create();

		$this->add_mock_documents( $usage );

		// Act.
		$usage->save();

		// Assert.
		$this->assertCount( self::DOCUMENTS_TEST_COUNT,
			DocumentSettingsUsage::create()->get( 'wp-post' )
		);
	}
}
