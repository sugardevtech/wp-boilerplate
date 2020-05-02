<?php
/**
 * Check for plugin and theme dependencies
 */
namespace Sugardev\Boilerplate;

class Dependencies {
	/**
	 * Notices to display
	 *
	 * @var array
	 */
	private $notices = array();
	/**
	 * The plugin
	 *
	 * @var Sugardev\Boilerplate\RepoBase
	 */
	private $plugin;
	/**
	 * Set plugin
	 */
	public function __construct( RepoBase $plugin ) {
		$this->repo = $plugin;
	}
	/**
	 * Check for plugin and theme dependencies
	 *
	 * Uses RepoBase::$dependencies
	 *
	 * Dependencies should be set like this in plugin:
	 *
	 * array(
	 * 		'theme' => 'theme_name' // set to null if there is no depedency on a theme
	 * 		'plugins' => array(
	 * 			'Plugin Friendly Name' => 'plugin/plugin.php'
	 * 		)
	 * )
	 *
	 * @return 	bool 	are the dependencies loaded?
	 */
	public function loaded() {
		$dependencies_loaded = true;
		$dependencies = $this->repo->dependencies;
		$plugin_name = $this->repo->name;
		// no required dependencies
		if( ! isset( $dependencies ) || ! is_array( $dependencies ) ) {
			return $dependencies_loaded;
		}
		$theme = isset( $dependencies['theme'] ) ? $dependencies['theme'] : null;
		$plugins = isset( $dependencies['plugins'] ) ? $dependencies['plugins'] : [];
		// check theme dependency
		if( null !== $theme ) {
			$theme_data = wp_get_theme();
			$theme_name = $theme_data->get( 'Name' );
			$theme_template =  $theme_data->get( 'Template' );
			if( $theme_template !== $theme && $theme_name !== $theme ) {
				$dependencies_loaded = false;
				self::$notices[] = "The {$theme} theme must be activated for the {$plugin_name} plugin to be enabled.";
			}
		}
		// check for plugin dependencies
		if( 0 < count( $plugins ) ) {
			$active_plugins = (array) get_option( 'active_plugins', array() );
			foreach( $plugins as $name => $path ) {
				if( ! in_array( $path, $active_plugins ) ) {
					$dependencies_loaded = false;
					$this->notices[] = "The {$name} plugin must be active for the {$plugin_name} plugin to be enabled";
				}
			}
		}
		// add notice to screen if dependencies are missing
		if( ! $dependencies_loaded ) {
			add_action( 'admin_notices', array( $this, 'add_notice' ) );
		}

		return $dependencies_loaded;
	}
	/**
	 * Add notice to the screen for missing dependencies
	 * Called when dependencies aren't loaded
	 */
	public function add_notice() {
		if ( current_user_can( 'activate_plugins' ) ) {
			echo sprintf(
				'<div id="message" class="error"><p>%s</p></div>',
				implode( '<br>', $this->notices )
			);
		}
	}
}
