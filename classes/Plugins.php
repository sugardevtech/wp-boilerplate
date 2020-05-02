<?php
/**
 * Container for all main Sugardev Plugin objects
 */
namespace Sugardev\Boilerplate;

class Plugins {
	private $plugins = [];

	public function get( $name ) {
		if ( ! isset( $this->plugins[ $name ] ) ) {
			return null;
		}
		return $this->plugins[ $name ];
	}

	public function set( $name, $object ) {
		$this->plugins[ $name ] = $object;
	}
}
