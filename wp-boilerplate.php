<?php
/**
 * Plugin Name: WP Boilerplate
 * Description: Boilplate framework for themes and plugins
 * Author: Sugardev
 */

$GLOBALS['sdplugins'] = new Sugardev\Boilerplate\Plugins();

function sugarplugin( $plugin_name ) {
	global $sdplugins;
	try {
		if ( $sdplugins->get( $plugin_name ) !== null ) {
			return $sdplugins->get( $plugin_name );
		}

		$prefix = 'Sugardev';

		if ( empty( $plugin_name ) ) {
			throw new \Exception( "Missing plugin name" );
		}

		// convert plugin string to namespaced class
		$class = sprintf( "%s\\%s\\Plugin",
			$prefix,
			implode( '_', array_map( 'ucfirst', explode( '-', $plugin_name ) ) )
		);

		if ( ! class_exists( $class ) ) {
			throw new \Exception( "{$class} does not exist" );
		}

		$sdplugins->set( $plugin_name, new $class() );

		return $sdplugins->get( $plugin_name );
	}
	catch ( \Exception $e ) {
		error_log( __FUNCTION__ . " error - " . $e->getMessage() );
		wp_die(
			"There was an error with the request",
			"Page Load Error",
			[ 'back_link' => true ]
		);
	}
}
