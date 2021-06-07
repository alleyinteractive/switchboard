<?php
namespace Switchboard;

/**
 * Test assorted cahe functions.
 *
 * @group cache
 */
class TestCache extends \WP_UnitTestCase {

	function setUp() {
        parent::setUp();

		// Create utility term
		$this->factory->term->create(
			[ 'taxonomy' => 'domain-controller-tax', 'slug' => 'limited' ]
		);

        // Let's create 2 site domains.
        $domains = [];
        for ( $i=0; $i < 2; $i++ ) {
            $domains[ $i ] = $this->factory->term->create(
                [
                    'taxonomy' => 'site-domain',
                    'name' => $i . '.com',
                ]
            );
        }
        $this->domains = $domains;

		// Let's create 2 categories.
		$cats = [];
		for ( $i=0; $i < 2; $i++ ) {
			$cats[ $i ] = $this->factory->term->create(
				[
					'taxonomy' => 'category',
					'name' => 'cat' . $i,
				]
			);
		}
		$this->cats = $cats;

        // Test post.
        $this->post = $this->factory->post->create(
            [
                'post_name' => 'test-post'
            ]
		);

		wp_set_post_terms( $this->post, [ $this->domains[0], $this->domains[1] ], 'site-domain' );
		wp_set_post_terms( $this->post, [ $this->cats[0], $this->cats[1] ], 'category' );
		wp_set_post_terms( $this->post, ['tag0', 'tag1'] );
    }

    function test_cache() {
		$purge_urls = [];
		$purge_urls = Cache::cache_purge_post_urls_domains( $purge_urls, $this->post );

		// Single post urls.
		$this->assertContains( 'http://0.com/' . date( 'Y/m/d') . '/test-post/', $purge_urls );
		$this->assertContains( 'http://1.com/' . date( 'Y/m/d') . '/test-post/', $purge_urls );

		// Archive urls.
		$this->assertContains( 'http://0.com/category/cat0/', $purge_urls );
		$this->assertContains( 'http://1.com/tag/tag0/', $purge_urls );
    }
}
