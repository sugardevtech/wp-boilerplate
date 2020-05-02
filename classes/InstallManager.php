<?php
/**
 * Helper for setting activate and deactive scripts in plugin
 *
 * Extend in custom plugin
 */
namespace Sugardev\Boilerplate;

abstract class InstallManager {
	/**
	 * Register activation hooks
	 * @param  	string 	$filename path to the plugin file
	 */
	public function register( $filename ) {
		register_activation_hook( $filename, array( $this, 'activate' ) );
		register_deactivation_hook( $filename, array( $this, 'deactivate' ) );
	}
	// activation hook
	public function activate() {}
	// deavtivation hook
	public function deactivate() {}
}
