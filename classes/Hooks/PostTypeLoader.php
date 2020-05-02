<?php
/**
 * Helper for adding custom post types and taxonomies
 */
namespace Sugardev\Boilerplate\Hooks;

abstract class PostTypeLoader extends Loader {
	/**
	 * Load functions for post types and taxonomies
	 */
	public function __construct( $plugin ) {
		$this->add_action( 'init', 'register_post_types' );
		$this->add_action( 'init', 'register_taxonomies' );
		parent::__construct( $plugin );
	}
	// extend to load post types
	public function register_post_types() {}
	// extend to load taxonomies
	public function register_taxonomies() {}
}
