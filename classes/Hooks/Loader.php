<?php
/**
 * Helper for loading hooks and filters,
 *
 * Class that extend this should be instantiated inside of the main Plugin
 * class that extends RepoBase
 */
namespace Sugardev\Boilerplate\Hooks;

use Sugardev\Boilerplate\RepoBase;

abstract class Loader {
	/**
	 * The plugin
	 *
	 * @var Sugardev\Boilerplate\RepoBase
	 */
	public $plugin;
	/**
	 * Wordpress hooks
	 *
	 * @var array
	 */
	protected $actions = [];
	/**
	 * Wordpress filters
	 *
	 * @var array
	 */
	protected $filters = [];
	/**
	 * Shortcodes
	 *
	 * @var array
	 */
	protected $shortcodes = [];
	/**
	 * Set plugin
	 *
	 * @param RepoBase $plugin the main plugin class
	 */
	public function __construct( RepoBase $repo ) {
		$this->add_action( 'init', 'load_shortcodes' );
		$this->repo = $repo;
	}
	/**
	 * Add shortcode calls
	 */
	public function load_shortcodes() {
		if( ! $this->shortcodes ) { return; }
		foreach( $this->shortcodes as $shortcode ) {
			extract( $shortcode );
			if( ! method_exists( $this, $function ) ) {
				throw new \Exception( "The shortcode function for $name does not exist." );
			}
			$callback_func = [ $this, $function ];
			// adding two custom params to the shortcode call with the callback and echo params
			add_shortcode( $name, function( $atts, $content = '' ) use ( $callback_func, $echo ) {
				// if function echos content buffer it to variable
				if( $echo ) {
					ob_start();
					call_user_func( $callback_func, $atts, $content );
					return ob_get_clean();
				} else {
					return call_user_func( $callback_func, $atts, $content );
				}
			});
		}
	}
	/**
	 * Registers all actions and filters
	 */
	public function register() {
		try {
			$this->build_actions();
			$this->build_filters();
		} catch( \Exception $e ) {
			error_log( sprintf( "%s error: %s", get_called_class(), $e->getMessage() ) );
		}
	}
	/**
	 * Add an action
	 *
	 * Example: add a hook to the init action.
	 * If function isn't passed, it looks for the method 'init' in the current
	 * class.
	 *
	 * 		$this->add_action( 'init' );
	 *
	 * If the function is passed, it looks for the function in the current class
	 * You don't have to pass the function as an array like [$this, 'name_of_method']
	 *
	 * 		$this->add_action( 'init', 'name_of_method' )
	 *
	 * @param 	string 	$name the name of the hook
	 * @param 	string 	$function the name of the function to load
	 * @param 	int		$priority the hook priority
	 * @param 	int 	$args the number of args to send to the function
	 */
	protected function add_action( $name, $function = null, $priority = 10, $args = 1 ) {
		$function = $function ?: $name;
		$this->actions[] = [
			'name' => $name,
			'function' => $function,
			'priority' => $priority,
			'args' => $args
		];
	}
	/**
	 * Add admin notices to admin
	 */
	protected function add_admin_notices( $notices ) {
		add_action( 'admin_notices', function() use ( $notices ) {
			echo sprintf(
				'<div class="notice notice-error"><p>%s</p></div>',
				implode('</p><p>', $notices )
			);
		});
	}
	/**
	 * Add a filter
	 *
	 * See above documentation for add_action
	 *
	 * @param 	string 	$name the name of the filter
	 * @param 	string 	$function the name of the function to load
	 * @param 	int		$priority the hook priority
	 * @param 	int 	$args the number of args to send to the function
	 */
	protected function add_filter( $name, $function = null, $priority = 10, $args = 1 ) {
		$function = $function ?: $name;
		$this->filters[] = [
			'name' => $name,
			'function' => $function,
			'priority' => $priority,
			'args' => $args
		];
	}
	/**
	 * Helper for adding JavaScript file
	 *
	 * @param 	string 	$name the name of the script
	 * @param 	string 	$path the path to the script relative to the assets folder, WITHOUT .js
	 * @param 	array 	$deps script dependencies
	 * @param 	bool 	$footer put script in the footer?
	 * @param 	array 	$data data object to send to script
	 */
	protected function add_script( $name, $path, $deps = [], $footer = true, $data = [], $register = false ) {
		$script_name = $name;
		// either enqueue or register based on setting
		$script_function = $register ? 'wp_register_script' : 'wp_enqueue_script';
		// absolute urls
		if ( strpos( $path, "http" ) === 0 ) {
			$script_function( $script_name, $path, $deps, null, $footer );
			return;
		}
		// get full path to the script
		$script_path = $this->repo->asset_path( 'js', $path );
		$file_path = $this->repo->asset_path( 'js', $path, true );
		// if not vendor script, add plugin name as prefix to script
		if( 0 !== strpos( $path, 'vendor/' ) ) {
			$script_name = $this->repo->name . '_' . $script_name;
		}

		$script_function( $script_name, $script_path, $deps, $this->get_asset_version( $file_path ), $footer );
		// if data is sent, use wp_localize_script to send
		// object name will be script name, with dashes change to underscores
		// and "_vars" appended to the name
		if( count( $data ) ) {
			$object_name = str_replace( '-', '_', $name ) . '_vars';
			wp_localize_script( $script_name, $object_name, $data );
		}
	}
	/**
	 * Add shortcode
	 *
	 * @param 	string 	$name       the shortcode name
	 * @param 	string 	$function   Optional. the function name, defaults to shortcode name
	 * @param 	bool	$echo       Optional. whether the function echos content directly
	 *
	 * Because shortcodes need to return content, we use the $echo param to check whether
	 * the functions content should be buffer to a var and returned
	 */
	protected function add_shortcode( $name, $function = null, $echo = false ) {
		$function = $function ?: $name;
		$this->shortcodes[] = [
			'name' => $name,
			'function' => $function,
			'echo' => $echo
		];
	}
	/**
	 * Helper for adding styles
	 *
	 * @param 	string 	$name the name of the style
	 * @param 	string 	$path the path to the style relative to the assets folder, WITHOUT .css
	 * @param 	array 	$deps style dependencies
	 * @param 	string 	$media media type
	 * @param	bool    $register register only
	 * @param	string  $inline_or_link ('link'|'inline')
	 */
	protected function add_style( $name, $path, $deps = [], $media = 'all', $register = false, $inline_or_link = 'link' ) {
		$inline = ( $inline_or_link == 'inline' );
		$style_path = $this->repo->asset_path( 'css', $path );
		$file_path = $this->repo->asset_path( 'css', $path, true );
		$style_name = $this->repo->name . '_' . $name;

		if ( $inline ) {
			$data = file_get_contents( $file_path );
			if ( $data ) {
				add_action( 'wp_head', function() use ( $style_name, $data ) {
					echo sprintf( '<style type="text/css" id="%s-css">%s</style>', $style_name, $data );
				}, PHP_INT_MAX );
			}
			return;
		}

		// either enqueue or register based on setting
		$script_function = $register ? 'wp_register_style' : 'wp_enqueue_style';
		$script_function( $style_name, $style_path, $deps, $this->get_asset_version( $file_path ), $media );
	}
	/**
	 * Build actions
	 */
	protected function build_actions() {
		if( ! $this->actions ) { return; }
		$this->call_hooks( $this->actions, 'action' );
	}
	/**
	 * Build filters
	 */
	protected function build_filters() {
		if( ! $this->filters ) { return; }
		$this->call_hooks( $this->filters, 'filter' );
	}
	/**
	 * Get version number for asset based on last modified date
	 */
	protected function get_asset_version( $path ) {
		return filemtime( $path );
	}
	/**
	 * Retrieve value from $_REQUEST object and sanitize based on type
	 *
	 * @param	string	$key the array key
	 * @param 	string	$type the value type (string|int)
	 * @return	mixed	the sanitized value
	 */
	protected function get_request( $key, $type = 'string' ) {
		if( ! isset( $_REQUEST[ $key ] ) ) {
			return null;
		}
		switch( $type ) {
			case 'int':
				return (int) $_REQUEST[ $key ];
			// query param send using JSON.stringify
			case 'json':
				return json_decode(
					stripslashes( $_REQUEST[ $key ] ),
					true
				);
			default:
				return $_REQUEST[ $key ];
				break;
		}
	}
	/**
	 * Log messages
	 */
	protected function log( $msg ) {
		$this->repo->log( $msg );
	}
	/**
	 * Verify whether posted data is from the memberships admin page and not a draft or revision
	 *
	 * @param  	int 	$post_id      the post id
	 * @param   mixed   $psot_type    single post tyoe or array of post types to check against
	 * @return 	bool 	whether it is a verified request
	 */
	protected function verify_request( $post_id, $post_type = null ) {
		$post = get_post( $post_id );

		if ( empty( $post ) ) {
			return false;
		}

		// handle the case when the custom post is quick edited
		// otherwise all custom meta fields are cleared out
		$inline_edit = $_POST['_inline_edit'] ?? null;
		if ( wp_verify_nonce( $inline_edit, 'inlineeditnonce' ) ) {
			return false;
		}

		// verify post type on page submitted
		if ( null !== $post_type ) {
			if ( ! is_array( $post_type ) ) {
				$post_type = [ $post_type ];
			}
			if ( ! in_array( $post->post_type, $post_type ) ) {
				return false;
			}
		}

		// Don't save for revisions or autosaves
		if ( defined( 'DOING_AUTOSAVE' ) || $post->post_status === 'auto-draft' || is_int( wp_is_post_revision( $post_id ) ) || is_int( wp_is_post_autosave( $post_id ) ) ) {
			return false;
		}
		// Check user has permission to edit
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return false;
		}

		return true;
	}
	/**
	 * Create function calls for hooks
	 *
	 * @param 	array 	$hooks 	array of filters/actions
	 * @param  	string 	$type 	filter|action
	 */
	private function call_hooks( $hooks, $type ) {
		$hook_function = "add_{$type}";
		foreach( $hooks as $hook ) {
			extract( $hook );
			if( method_exists( $this, $function ) ) {
				$hook_function( $name, array( $this, $function ), $priority, $args );
			} elseif( function_exists( $function ) ) {
				$hook_function( $name, $function, $priority, $args );
			} else {
				throw new \Exception( "The $type hook $function does not exist." );
			}
		}
	}
}
