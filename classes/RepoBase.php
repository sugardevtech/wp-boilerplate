<?php
/**
 * Repo base for all Sugardev repos
 */
namespace Sugardev\Boilerplate;

use Sugardev\Boilerplate\Hooks\Loader;

abstract class RepoBase {
	/**
	 * Repo name
	 *
	 * @var string
	 */
	public $name;
	/**
	 * Repo title
	 *
	 * @var string
	 */
	public $title;
	/**
	 * Path to Repo file
	 *
	 * @var string
	 */
	public $filename;
	/**
	 * Directory path to Repo
	 *
	 * @var string
	 */
	public $dir;
	/**
	 * Class for loading views
	 *
	 * @var Sugardev\Boilerplate\Views
	 */
	public $views;
	/**
	 * Whether the Repo loaded correctly
	 *
	 * @var bool
	 */
	public $loaded = false;
	/**
	 * URL Path to assets folder of Repo
	 *
	 * @var string
	 */
	protected $asset_path;
	/**
	 * File Path to assets folder of Repo
	 *
	 * @var string
	 */
	protected $asset_file_path;
	/**
	* Repo dependencies
	*
	* @var array
	*/
	public $dependencies = [];
	/**
	 * Set up Repo
	 *
	 * Set up name, title, file paths, dependencies, custom activation hooks
	 *
	 * @param 	string 	$name the name of the Repo (repo-name)
	 * @param 	string 	$title title of the repo (Repo Title)
	 * @param 	string 	$file path to main repo file
	 * @param 	array 	$deps repo dependencies (see Sugardev\Boilerplate\Dependencies)
	 * @param 	object 	$installer activation/deactivation hooks (see Sugardev\Boilerplate\Install_Manager)
	 */
	public function __construct( $name, $title, $file, $deps = [], InstallManager $installer = null ) {
		$this->dependencies = $deps;
		$this->dir = dirname( $file );
		$this->asset_file_path = $this->dir . '/assets';
		$this->filename = $file;
		$this->name = $name;
		$this->title = $title;
		$this->logger = new Logger( $name );
		$this->views = new Views( $this->dir );
		// check dependencies, possibly display notice
		if( ! $this->load_dependencies() ) {
			return;
		}
		// register activation and deactivation hooks
		if( $installer ) {
			$installer->register( $file );
		}

		if ( !( defined('DOING_AJAX') && DOING_AJAX ) && !( defined('DOING_CRON') && DOING_CRON ) && !( defined('WP_CLI') && WP_CLI ) ) {
			$this->run_migrations();
		}


		$this->loaded = true;
	}
	/**
	 * Get full path to assets
	 *
	 * If JS or CSS, .min is added to file when not in debug mode
	 * NOTE: MAKE SURE YOU COMMIT THE .min VERSION OF YOUR ASSETS
	 *
	 * @param 	string 	$type       the type of asset
	 * @param  	string 	$path       relative path inside of assets folder
	 * @param	string	$file_path  whether to return URL or File Path
	 */
	public function asset_path( $type, $path, $file_path = false, $ignore_minify = true, $append_filetype = true ) {
		$debug = defined( 'WP_DEBUG' ) && WP_DEBUG;
		$asset_path = $file_path ? $this->asset_file_path : $this->asset_path;
		switch( $type ) {
			case 'css':
				return sprintf(
					'%s/%s%s',
					$asset_path,
					$path,
					$append_filetype ? '.css' : ''
				);
			case 'image':
				return sprintf(
					'%s/%s',
					$asset_path,
					$path
				);
			case 'js':
				return sprintf(
					'%s/%s%s%s',
					$asset_path,
					$path,
					$debug || $ignore_minify ? '' : '.min',
					$append_filetype ? '.js' : ''
				);
		}
		return null;
	}
	/**
	 * Load all hooks
	 *
	 * Hooks should be defined in main repo constructor after calling
	 * parent::__construct and checking $this->loaded
	 *
	 * Example:
	 *
	 * parent::__construct();
	 *
	 * if( $this->loaded ) {
	 * 		$this->load_hooks( [
	 * 			Hook_Class( $this )
	 * 		] );
	 * }
	 *
	 * See Sugardev\Boilerplate\Hooks\Loader for more information on use
	 */
	public function load_hooks( $hooks ) {
		foreach( $hooks as $loader ) {
			if( $loader instanceof Loader ) {
				$loader->register();
			}
		}
	}
	/**
	 * Log message
	 */
	public function log( $msg, $var_dump = false ) {
		if ( $this->logger ) {
			$this->logger->log( $msg, $var_dump );
		}
	}
	/**
	 * Check for repo dependencies
	 *
	 * @param 	array 	$dependencies	the repo dependencies
	 * @return  bool 	whether the dependencies are loaded
	 */
	protected function load_dependencies() {
		$dependencies = new Dependencies( $this );
		return $dependencies->loaded();
	}

	/**
	 * Runs database migrations as defined in sql file collection in each repo
	 *
	 * General description of what's happening here:
	 * 	1. If there's a migration directory in the repo, we load the *.sql files w/in
	 * 	2. If the sql file contents of the migration directory (array of files) match an option in the DB
	 * 	   of the migrations that have run against the DB, we can exit
	 * 	3. If not, then we execute any migration files that haven't already been
	 *
	 * @return null
	 */
	protected function run_migrations() {
		global $wpdb;

		$migrations_option = 'sugardev_library_completed_migrations';
		$migration_running_option = sprintf( 'sugardev_library_migrations_running_%s', $this->name );

		if( ! file_exists( sprintf('%s/migrations', $this->dir ) ) ) return;

		// race condition prevention
		if( 'yes' === get_option( $migration_running_option ) ) return;

		$completed = (array)get_option( $migrations_option, [] );
		$files = array_diff(
			array_map( function( $file ) {
				return str_replace( WP_CONTENT_DIR, '', $file );
			}, glob( sprintf( '%s/migrations/*.sql', $this->dir ) ) ),
			$completed
		);

		if( empty( $files ) ) return;

		update_option( $migration_running_option, 'yes' );

		error_log( sprintf( 'Running database migrations for %s...', $this->name ) );

		foreach( $files as $file ) {
			$statements = explode( ";\n", file_get_contents( WP_CONTENT_DIR . $file ) );
			foreach( $statements as $statement ) {
				$statement = trim( $statement, "; \t\n\r\0\x0B" );
				if( empty( $statement ) ) continue;
				error_log( sprintf( 'Executing: %s', $statement ) );
				$wpdb->query( $statement );
				if( $wpdb->last_error ) {
  					error_log( sprintf( 'Error executing migration: ' . $wpdb->last_error ) );
				}
			}
			$completed[] = str_replace( WP_CONTENT_DIR, '', $file );
		}

		update_option( $migrations_option, $completed );
		update_option( $migration_running_option, null );
	}
}
