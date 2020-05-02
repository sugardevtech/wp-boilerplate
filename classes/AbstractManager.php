<?php
/**
 * Manages getting and updating data for a plugin post type or table
 */
namespace Sugardev\Boilerplate;

abstract class AbstractManager {
	protected $wpdb;
	protected $plugin;
	public function __construct( $plugin ) {
		global $wpdb;
		$this->plugin = $plugin;
		$this->wpdb = $wpdb;
	}

	public function build_where( $where = [] ) {
		if ( ! is_array( $where ) || empty( $where ) ) {
			return null;
		}
		$where = sprintf( "WHERE (%s)", implode( ") AND (", $where ) );
		return $where;
	}
	/**
	 * Log messages
	 */
	protected function log( $msg ) {
		$this->plugin->log( $msg );
	}
}
