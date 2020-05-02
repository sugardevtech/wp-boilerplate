<?php
/**
 * Helper for loading views
 */
namespace Sugardev\Boilerplate;

class Views {
	/**
	 * Path to plugin views
	 *
	 * @var string
	 */
	private $path;
	/**
	 * Set path to views
	 */
	public function __construct( $path ) {
		$this->path = $path;
	}
	/**
	 * Get path of a view file
	 *
	 * @param  	string 	$view_name the relative path to the view
	 * @return 	string 	the full path to the view file
	 */
	public function get_path( $view_name ) {
		return sprintf( '%s/views/%s.php', $this->path, $view_name );
	}
	/**
	 * Load HTML views
	 *
	 * @param   string  $view_name path to view relative to views folder
	 * @param   array   $data associative array of variables
	 * @param 	bool 	$return whether to echo of return the view data
	 */
	public function load( $view_name, $data = [], $return = false ) {
		try{
			// extract array into variables
			extract( $data );

			if( $return ) {
				ob_start();
			}

			$view_path = $this->get_path( $view_name );

			if( ! file_exists( $view_path ) ) {
				throw new \Exception( "The view {$view_path} could not be loaded" );
			}

			include( $view_path );

			if( $return ) {
				return ob_get_clean();
			}

		} catch( \Exception $e ) {
			error_log( sprintf( "View error: %s", $e->getMessage() ) );
		}
	}
}
