<?php
/**
 * Plugin base for all Sugardev Plugins
 *
 * All Sugardev plugins should extend this Class inside of the main plugin file.
 *
 * 		namespace Sugardev\Plugin;
 *
 * 		use Sugardev\Boilerplate\Plugin_Base;
 *
 * 		class Plugin_Name extends Plugin_Base {
 * 			// custom plugin code in here
 *    	}
 *
 * 		return new Plugin_Name();
 *
 * Use composer for autoloading classes inside plugin
 *
 * Plugin structure should look like this:
 * 		assets
 * 			/admin
 * 				/css
 * 				/images
 * 				/js
 * 			/public
 * 				/css
 * 				/images
 * 				/js
 * 			/vendor
 * 				/vendor_name
 * 		classes
 * 			// all namespaced classes go here
 * 		functions
 * 			// functions for use outside of plugin
 * 		migrations
 * 			// collection of SQL files with instructions for migrations, like:
 * 				001-update-foo-options.sql
 * 				002-delete-bar-post.sql
 * 			   ** NOTE: our sql file parser is pretty simple for now, just split on any semi-colon followed by a line break.
 * 			   			Ideally we shouldn't even have to parse it and run it with a mysqli multi-query, but wpdb doesn't
 * 			   			support it, so we have to run a query at a time.
 * 		views
 * 			admin
 * 			public
 * 		plugin-name.php
 *
 */
namespace Sugardev\Boilerplate;

use Sugardev\Boilerplate\Hooks\Loader;

class PluginBase extends RepoBase {
	/**
	 * Set up plugin
	 *
	 * Set up name, title, file paths, dependencies, custom activation hooks
	 *
	 * @param 	string 	$name the name of the plugin (plugin-name)
	 * @param 	string 	$title title of the plugin (Plugin Title)
	 * @param 	string 	$file path to main plugin file
	 * @param 	array 	$deps plugin dependencies (see Sugardev\Boilerplate\Dependencies)
	 * @param 	object 	$installer activation/deactivation hooks (see Sugardev\Boilerplate\InstallManager)
	 */
	public function __construct( $name, $title, $file, $deps = [], InstallManager $installer = null ) {
		$this->asset_path = trailingslashit( plugin_dir_url( $file ) ) . 'assets';
		parent::__construct( $name, $title, $file, $deps, $installer );
	}
}
