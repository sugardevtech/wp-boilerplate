<?php
/**
 * Manages creating, deleting and modifying DB tables
 */
namespace Sugardev\Boilerplate;

abstract class AbstractSchemaManager {
	protected $tables = [];
	/**
	 * Creates database tables on installation
	 */
	public function create_tables() {
		global $wpdb;
		if ( empty( $this->tables ) ) return;
		foreach ( $this->tables as $table => $lines ) {
			$table = $wpdb->prefix . $table;
			$this->db_delta(
				sprintf(
					"CREATE TABLE %s (%s) %s;",
					$table,
					implode( ",\n", $lines ),
					$this->get_collation()
				)
			);
		}
	}
	/**
	 * Deletes database tables when uninstalled
	 */
	public function delete_tables() {
		global $wpdb;
		if ( empty( $this->tables ) ) return;
		foreach ( $this->tables as $table => $lines ) {
			$table = $wpdb->prefix . $table;
			$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
		}
		$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
	}
	/**
	 * Wrapper for WordPress function dbDelta
	 *
	 * @param  	string		$schema 	the SQL to be run
	 */
	protected function db_delta( $schema ) {
		global $wpdb;
		$wpdb->hide_errors();
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $schema );
	}
	/**
	 * Get's the DB collation settings for creating tables
	 *
	 * @return 	string 		the collate command
	 */
	protected function get_collation() {
		global $wpdb;
		$collate = '';
		if ( $wpdb->has_cap( 'collation' ) ) {
			if ( ! empty( $wpdb->charset ) ) {
				$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if ( ! empty( $wpdb->collate ) ) {
				$collate .= " COLLATE $wpdb->collate";
			}
		}
		return $collate;
	}
}
