<?php
namespace Switchboard;

/**
 * Test redirects.
 *
 * @package Switchboard
 */

class RedirectsTest extends \WP_UnitTestCase {
	public function test_redirect_to_domain() {
		$this->assertSame( 'http://' . WP_TESTS_DOMAIN, home_url() );
		try {
			Core::redirect_to_domain( 'foo.com' );
			$this->fail( "Failed to redirect to foo.com" );
		} catch ( \Switchboard_Redirect_Exception $e ) {
			// Verify the redirect url.
			$this->assertSame( 'http://foo.com/', $e->getMessage() );
		}
	}

	public function test_redirect_to_same_domain() {
		$this->assertFalse( Core::redirect_to_domain( WP_TESTS_DOMAIN ) );
	}

	public function test_redirect_to_domain_with_uri() {
		$post_id = self::factory()->post->create();
		$permalink = get_permalink( $post_id );
		$this->assertContains( WP_TESTS_DOMAIN, $permalink );
		$this->go_to( $permalink );

		try {
			Core::redirect_to_domain( 'foo.com' );
			$this->fail( "Failed to redirect to foo.com" );
		} catch ( \Switchboard_Redirect_Exception $e ) {
			// Verify the redirect url.
			$this->assertSame( str_replace( WP_TESTS_DOMAIN, 'foo.com', $permalink ), $e->getMessage() );
		}
	}
}
