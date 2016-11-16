<?php
namespace Split_Domain;

/**
 * Test permalinks.
 *
 * @package Split Domain
 */

class PermalinksTest extends \WP_UnitTestCase {
	protected $default_site_id, $primary_site_id, $current_site_id, $post_id, $taxonomy, $old_screen;

	public function setUp() {
		parent::setUp();

		$this->old_screen = get_current_screen();

		$this->taxonomy = Site::instance()->name;

		// Create default objects.
		$this->default_site_id = $this->factory->term->create( [
			'name' => 'default-site.com',
			'slug' => 'default-site',
			'taxonomy' => $this->taxonomy,
		] );
		$this->primary_site_id = $this->factory->term->create( [
			'name' => 'primary-site.com',
			'slug' => 'primary-site',
			'taxonomy' => $this->taxonomy,
		] );
		$this->current_site_id = $this->factory->term->create( [
			'name' => WP_TESTS_DOMAIN,
			'slug' => 'current',
			'taxonomy' => $this->taxonomy,
		] );
		$this->post_id = $this->factory->post->create( [
			'post_name' => 'alpha',
			'post_status' => 'publish',
		] );

		update_option( 'site_domain_settings', [ 'default' => $this->default_site_id ] );
	}

	public function tearDown() {
		$GLOBALS['current_screen'] = $this->old_screen;

		parent::tearDown();
	}

	public function test_primary_domain() {
		add_post_meta( $this->post_id, 'post_domains', [ 'primary' => $this->primary_site_id ] );
		wp_set_object_terms( $this->post_id, [ $this->default_site_id, $this->primary_site_id, $this->current_site_id ], $this->taxonomy );

		$primary = Core::get_post_primary_site( $this->post_id );
		$this->assertNotFalse( $primary );
		$this->assertSame( $primary->term_id, $this->primary_site_id );
	}

	public function test_post_site_primary() {
		add_post_meta( $this->post_id, 'post_domains', [ 'primary' => $this->primary_site_id ] );
		wp_set_object_terms( $this->post_id, [ $this->default_site_id, $this->primary_site_id, $this->current_site_id ], $this->taxonomy );

		$primary = Core::get_post_site( $this->post_id, 'primary' );
		$this->assertNotFalse( $primary );
		$this->assertSame( $primary->term_id, $this->primary_site_id );
	}

	public function test_post_site_current() {
		add_post_meta( $this->post_id, 'post_domains', [ 'primary' => $this->primary_site_id ] );
		wp_set_object_terms( $this->post_id, [ $this->default_site_id, $this->primary_site_id, $this->current_site_id ], $this->taxonomy );

		$current = Core::get_post_site( $this->post_id, 'current' );
		$this->assertNotFalse( $current );
		$this->assertSame( $current->term_id, $this->current_site_id );
	}

	public function test_current_site_allowed() {
		wp_set_object_terms( $this->post_id, [ $this->default_site_id, $this->primary_site_id, $this->current_site_id ], $this->taxonomy );
		$this->assertTrue( Core::is_post_allowed_on_current_site( $this->post_id ) );

		wp_set_object_terms( $this->post_id, [ $this->default_site_id, $this->primary_site_id ], $this->taxonomy );
		$this->assertFalse( Core::is_post_allowed_on_current_site( $this->post_id ) );
	}

	public function test_no_primary_not_current_has_terms() {
		// Even though we're using $primary_site_id, we're not setting the post
		// meta to indicate that this is the primary domain.
		wp_set_object_terms( $this->post_id, [ $this->primary_site_id ], $this->taxonomy );
		$site = Core::get_post_site( $this->post_id );
		$this->assertNotFalse( $site );
		$this->assertSame( $site->term_id, $this->primary_site_id );
	}

	public function test_default_site_fallback() {
		$default = Core::get_post_site( $this->post_id );
		$this->assertNotFalse( $default );
		$this->assertSame( $default->term_id, $this->default_site_id );
	}

	public function test_get_permalink_primary() {
		add_post_meta( $this->post_id, 'post_domains', [ 'primary' => $this->primary_site_id ] );
		$this->assertContains( 'primary-site.com', get_permalink( $this->post_id ) );
	}

	public function test_get_permalink_current() {
		add_post_meta( $this->post_id, 'post_domains', [ 'primary' => $this->primary_site_id ] );
		wp_set_object_terms( $this->post_id, [ $this->default_site_id, $this->primary_site_id, $this->current_site_id ], $this->taxonomy );
		$this->assertContains( WP_TESTS_DOMAIN, get_permalink( $this->post_id ) );
	}

	public function test_get_permalink_has_terms() {
		wp_set_object_terms( $this->post_id, [ $this->primary_site_id ], $this->taxonomy );
		$this->assertContains( 'primary-site.com', get_permalink( $this->post_id ) );
	}

	public function test_get_permalink_fallback() {
		$this->assertContains( 'default-site.com', get_permalink( $this->post_id ) );
	}

	public function test_permalinks_from_admin() {
		set_current_screen( 'dashboard-user' );

		add_post_meta( $this->post_id, 'post_domains', [ 'primary' => $this->primary_site_id ] );
		wp_set_object_terms( $this->post_id, [ $this->default_site_id, $this->primary_site_id, $this->current_site_id ], $this->taxonomy );
		$this->assertContains( 'primary-site.com', get_permalink( $this->post_id ) );
	}

	public function test_canonical_urls() {
		add_post_meta( $this->post_id, 'post_domains', [ 'primary' => $this->primary_site_id ] );
		wp_set_object_terms( $this->post_id, [ $this->default_site_id, $this->primary_site_id, $this->current_site_id ], $this->taxonomy );
		$this->assertContains( 'primary-site.com', wp_get_canonical_url( $this->post_id ) );
	}
}
