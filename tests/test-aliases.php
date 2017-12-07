<?php
namespace Switchboard;

/**
 * Test redirects.
 *
 * @package Switchboard
 */

class AliasesTest extends \WP_UnitTestCase {
	public function test_no_alias_no_redirects() {
		$site_id = $this->factory->term->create( [
			'name' => 'aliased-domain-1.com',
			'slug' => 'aliased-domain',
			'taxonomy' => Site::instance()->name,
		] );
		$this->assertTrue( Core::alias_redirects() );
	}

	public function test_alias_redirects() {
		$site_id = $this->factory->term->create( [
			'name' => 'aliased-domain-2.com',
			'slug' => 'aliased-domain',
			'taxonomy' => Site::instance()->name,
		] );
		add_term_meta( $site_id, 'alias', WP_TESTS_DOMAIN );
		Core::update_domain_aliases_cache();

		try {
			Core::alias_redirects();
			$this->fail( "Failed to redirect to aliased-domain-2.com" );
		} catch ( \Switchboard_Redirect_Exception $e ) {
			// Verify the redirect url.
			$this->assertSame( 'http://aliased-domain-2.com/', $e->getMessage() );
		}
	}

	public function test_alias_redirect_to_self() {
		$site_id = $this->factory->term->create( [
			'name' => WP_TESTS_DOMAIN,
			'slug' => 'example',
			'taxonomy' => Site::instance()->name,
		] );
		add_term_meta( $site_id, 'alias', WP_TESTS_DOMAIN );
		Core::update_domain_aliases_cache();

		// This will attempt to redirect to itself and should fail, returning false.
		$this->assertFalse( Core::alias_redirects() );
	}
}
