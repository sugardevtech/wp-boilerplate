<?php
namespace Sugardev\Boilerplate;

use Sugardev\Boilerplate\Hooks\Loader;

class ThemeBase extends RepoBase {
	/**
	 * Set up theme
	 *
	 * Set up name, title, file paths, dependencies, custom activation hooks
	 *
	 * @param 	string 	$name the name of the theme (theme-name)
	 * @param 	string 	$title title of the theme (Theme Title)
	 * @param 	string 	$file path to main theme file
	 * @param 	array 	$deps theme dependencies (see Sugardev\Boilerplate\Dependencies)
	 * @param 	object 	$installer activation/deactivation hooks (see Sugardev\Boilerplate\InstallManager)
	 */
	public function __construct( $name, $title, $file, $deps = [], InstallManager $installer = null ) {
		if ( $this->asset_path == null ) {
			error_log( sprintf( "%s : you must set {$asset_path}", __METHOD__ ) );
			return;
		}
		parent::__construct( $name, $title, $file, $deps, $installer );
	}
}
