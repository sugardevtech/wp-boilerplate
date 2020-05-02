<?php
/**
 * Check for plugin and theme dependencies
 */
namespace Sugardev\Boilerplate;

class Logger {

	private $name;

	public function __construct( $name = null ) {
		$this->name = $name;
	}

	public function log( $msg, $var_dump = false ) {
		if ( $var_dump ) {
			var_dump( $msg );
			return;
		}
		if ( is_array( $msg ) || is_object( $msg ) ) {
			error_log( "\n\r" . "({$this->name}) : " . print_r( $msg, true ) . "\n\r" );
		}
		else {
			error_log( "\n\r" . "({$this->name}) : $msg" . "\n\r" );
		}
	}
}
