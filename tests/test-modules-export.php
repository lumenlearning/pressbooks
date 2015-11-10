<?php


class ExportMock extends \PressBooks\Modules\Export\Export {

	function convert() {
		$this->outputPath = \PressBooks\Utility\create_tmp_file();
		return true;
	}

	function validate() {
		return file_exists( $this->outputPath );
	}
}


class Modules_ExportTest extends \WP_UnitTestCase {

	/**
	 * @var \ExportMock
	 */
	protected $export;


	/**
	 *
	 */
	public function setUp() {
		parent::setUp();
		$this->export = new \ExportMock();
	}

	/**
	 * Create and switch to a new test book
	 */
	private function _book() {

		$blog_id = $this->factory->blog->create();
		switch_to_blog( $blog_id );
		switch_theme( 'pressbooks-book' );
	}


	/**
	 * @covers \PressBooks\Modules\Export\Export::getExportStylePath
	 */
	public function test_getExportStylePath() {

		$this->_book();

		$path = $this->export->getExportStylePath( 'epub' );
		$this->assertStringEndsWith( '/export/epub/style.scss', $path );

		$path = $this->export->getExportStylePath( 'prince' );
		$this->assertStringEndsWith( '/export/prince/style.scss', $path );

		$path = $this->export->getExportStylePath( 'foobar' );
		$this->assertFalse( $path );

		switch_theme( 'pressbooks-custom-css' );

		$path = $this->export->getExportStylePath( 'epub' );
		$this->assertStringEndsWith( '/export/epub/style.css', $path );

		$path = $this->export->getExportStylePath( 'prince' );
		$this->assertStringEndsWith( '/export/prince/style.css', $path );

		$path = $this->export->getExportStylePath( 'foobar' );
		$this->assertFalse( $path );
	}


	/**
	 * @covers \PressBooks\Modules\Export\Export::getMixinsPath
	 */
	public function test_getMixinsPath() {

		$path = $this->export->getMixinsPath();

		$this->assertInternalType( 'string', $path );
		$this->assertNotEmpty( $path );

	}


//	/**
//	 * @covers \PressBooks\Modules\Export\Export::getGlobalTypographyMixinPath
//	 */
//	public function test_getGlobalTypographyMixinPath() {
//		// TODO: Testing this as-is triggers updateGlobalTypographyMixin, generates _mixins.css, generates _global-font-stack.scss... Code needs to be decoupled?
//		$this->markTestIncomplete();
//	}


	/**
	 * @covers \PressBooks\Modules\Export\Export::getExportScriptPath
	 */
	public function test_getExportScriptPath() {

		$this->_book();

		$path = $this->export->getExportScriptPath( 'epub' );
		$this->assertFalse( $path );

		$path = $this->export->getExportScriptPath( 'prince' );
		$this->assertStringEndsWith( '/export/prince/script.js', $path );

		$path = $this->export->getExportScriptPath( 'foobar' );
		$this->assertFalse( $path );

		switch_theme( 'pressbooks-custom-css' );

		$old = get_option( 'pressbooks_theme_options_pdf' );
		$opt = $old;

		$opt['pdf_romanize_parts'] = 0;
		update_option( 'pressbooks_theme_options_pdf', $opt);

		$path = $this->export->getExportScriptPath( 'epub' );
		$this->assertFalse( $path );

		$path = $this->export->getExportScriptPath( 'prince' );
		$this->assertStringEndsWith( '/export/prince/script.js', $path );

		$opt['pdf_romanize_parts'] = 1;
		update_option( 'pressbooks_theme_options_pdf', $opt);

		$path = $this->export->getExportScriptPath( 'prince' );
		$this->assertStringEndsWith( '/export/prince/script-romanize.js', $path );

		$path = $this->export->getExportScriptPath( 'foobar' );
		$this->assertFalse( $path );

		update_option( 'pressbooks_theme_options_pdf', $old );
	}



	/**
	 * @covers \PressBooks\Modules\Export\Export::isScss
	 */
	public function test_isScss() {

		$this->_book();

		$val = $this->export->isScss();
		$this->assertTrue( $val );

		switch_theme( 'pressbooks-custom-css' );

		$val = $this->export->isScss();;
		$this->assertFalse( $val );
	}


	/**
	 * @covers \PressBooks\Modules\Export\Export::isParsingSections
	 */
	public function test_isParsingSections() {

		$val = $this->export->isParsingSections();
		$this->assertInternalType( 'bool', $val );
	}


//	/**
//	 * @covers \PressBooks\Modules\Export\Export::logError
//	 */
//	public function test_logError() {
//		// TODO: Testing this as-is would send emails. Need to refactor to allow mocking of postmarkapp endpoint.
//		$this->markTestIncomplete();
//	}


	/**
	 * @covers \PressBooks\Modules\Export\Export::createTmpFile
	 */
	public function test_createTmpFile() {

		$file = $this->export->createTmpFile();
		$this->assertFileExists( $file );

		file_put_contents( $file, 'Hello world!' );
		$this->assertEquals( 'Hello world!', file_get_contents( $file ) );
	}


}