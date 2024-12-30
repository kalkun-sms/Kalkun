<?php
/**
 * Kalkun
 * An open source web based SMS Management
 *
 * @package		Kalkun
 * @author		Kalkun Dev Team
 * @license		https://spdx.org/licenses/GPL-2.0-or-later.html
 * @link		https://kalkun.sourceforge.io/
 */

// ------------------------------------------------------------------------

/**
 * Pluginss Class
 *
 * @package		Kalkun
 * @subpackage	Plugin
 * @category	Controllers
 */

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CI3 Plugin System CI Library
 *
 * @package		CI3 Plugin System
 * @author		Justin Hyland www.justinhyland.com
 * @link        https://github.com/jhyland87/CI3_Plugin_System
 */
class Plugins_lib_kalkun extends Plugins_lib {

    // ------------------------------------------------------------------------

	const P_STATUS_ENABLED = 1; // Bitflag:   0: disabled          1: enabled
	const P_STATUS_MISSING = 2; // Bitflag:   0: files present     1: files missing

    /**
     * Plugin Constructor
     *
     * Constructor performs...
     *  - Set $CI Reference
     *  - Load Plugins Model
     *  - Set the plugin directory
     *  - Get plugins from DB and include enabled plugins
     *  - Load the plugins by initializing the plugin Classes
     *
     * @access public
     */
    public function __construct()
    {
        parent::__construct();
    }

    // ------------------------------------------------------------------------

    /**
     * Set Plugin Directory
	 * 
	 * Override the method of upstream because we don't autoload the config
	 * file like they do.
	 *
     * Set the plugins directory that contains all the plugin folders, at the local
     * private property: static::$plugin_path
     *
     * @param   str $dir Directory, an ending slash is appended if not found, and any
     *                   accidental double slashes are replaced with single slashes
     * @access private
     */
    protected function set_plugin_dir()
    {
	    static::$CI->config->load('plugins');
	    parent::set_plugin_dir();
    }

    private function create_plugin_instance($name)
    {
        include_once static::$plugin_path."{$name}/{$name}.php";

        $c = $this->plugin_class_name($name);
        $plugin_instance = new $c;
    }

    // ------------------------------------------------------------------------

    /**
     * Enable Plugin
     *
     * Enable a plugin by setting the plugins.status to 1 in the plugins table
	 * 
	 * There is an issue in upstream where they want to access the 'handler'
	 * key with $plugins[$plugin]['handler'] but this key is only there if the plugin
	 * object is actually instatiated. So this cannot work. We have to instantiate the
	 * object before.
     *
     * @oaram   string  $plugin     Plugin Name
     * @param   mixed   $data       Any data that should be handed down to the plugins deactivate method (optional)
     * @access  public
     * @since   0.1.0
     * @return  bool
     */
    public function enable_plugin($plugin, $data = NULL)
	{
		if (! array_key_exists('handler', static::$plugins[$plugin]))
		{
			$this->create_plugin_instance($plugin);
		}
		parent::enable_plugin($plugin, $data);
	}

	public function install_plugin($plugin, $data = NULL)
	{
		$this->insert_plugin_headers($plugin);

		$ret = parent::install_plugin($plugin, $data);

		if ($ret !== TRUE) {
			return $ret;
		}

	}

	// ------------------------------------------------------------------------

	/**
	 * Insert Plugin Headers in a minimal fashion
	 *
	 * Parse a given plugins PHP file for the header information in the comments, and update the database info
	 * accordingly
	 *
	 * @param   string  $plugin Plugin System Name to insert
	 * @access  private
	 * @return  TRUE Always true
	 */
	private function insert_plugin_headers_minimal($plugin)
	{
		$arr = array();

		if ( ! isset(static::$plugins[$plugin]))
		{
			$arr['system_name'] = $plugin;
			$arr['name'] = $plugin;
			$arr['version'] = 'not_set';
			$arr['status'] = 0;
			if (self::$PM->insert_plugin($plugin, $arr))
			{
				$this->_debug("Inserted plugin for {$plugin}: " . serialize($arr));
				// Insert an element for that plugin into static::$plugins array
				// so that it gets filled by update_plugin_headers($plugin)
				static::$plugins[$plugin] = $plugin;
			}
			else
			{
				$this->_error("Failed to insert plugin for {$plugin}: " . serialize($arr));
			}
		}

		return TRUE;
	}

	// ------------------------------------------------------------------------

	/**
	 * Insert All Plugin Headers
	 *
	 * Insert all plugin headers
	 *
	 * @param   string  $plugin Plugin System Name to insert
	 * @access  private
	 * @return  TRUE Always true
	 */
	public function insert_plugin_headers($plugin)
	{
		// Insert the minimal plugin headers
		$this->insert_plugin_headers_minimal($plugin);

		// Now update the other headers for that plugin
		$this->update_plugin_headers($plugin);

		// Update static::$plugins[$plugin] of that object to have same content
		// as if it were filled by get_plugins()
		$resultObject = static::$PM->get_plugin($plugin);
		if( ! empty($resultObject->data)) {
			$resultObject->data = unserialize($resultObject->data);
		}
		static::$plugins[$plugin] = array('data' =>  $resultObject->data);;

		// By default, when inserting a new plugin, it is disabled, so we
		// don't have to update $enabled_plugins, and neither to load them into an object.


		return TRUE;
	}

    // ------------------------------------------------------------------------

    /**
     * Get Orphaned Plugins
     *
     * Look in the plugin directory for any folders that do not have an associated entry
     * in the plugins table
	 *
	 * Upstream bug here is that it doesn't check only for folders, but also for files.
	 * So we have to skip the files in the loop.
     *
     * @access public
     * @since   0.1.0
     * @return array|bool   If no orphaned plugins are found, return false
     */
    public function get_orphaned_plugins()
    {
        $files = scandir(static::$plugin_path);

        $plugins = static::$PM->get_plugins();

        $orphaned = array();

        foreach($files as $f)
        {
            // Skip directories
            if (in_array($f, ['.', '..']))
			{
				continue;
			}
			if ( ! is_dir(static::$plugin_path . "/" . $f))
			{
				continue;
			}

			if( ! isset($plugins[$f]))
            {
                array_push($orphaned, $f);
            }
        }

        return (count($orphaned) > 0 ? $orphaned : FALSE);
    }

	public function restore_orphaned_plugins() {
		// Insert orphaned plugins to plugins table & install the plugin to the DB
		$orphaned_plugins = $this->get_orphaned_plugins();
		if ($orphaned_plugins)
		{
			foreach ($orphaned_plugins as $op)
			{
				$this->install_plugin($op);
			}
		}
	}

	/*
	 * method to return static member of the function.
	 * Required for php 5.6 because using $this->plugins_lib_kalkun::$enabled_plugins
	 * would not be a valid call.
	 */
	public static function get_enabled_plugins()
	{
		return self::$enabled_plugins;
	}

	public function do_action($tag, $args = NULL)
	{
		die("Call to do_action forbidden, call do_action_kalkun instead.");
	}

    /**
     * Do Action.
	 * 
	 * Override upstream's do_action because the call to call_user_func_array 
	 * was missing the array(&$args)
	 * 
	 * Moreover, in kalkun, we need to pass an Object as argument. So the do_action function
	 * which requires an arrays doesn't fit.
     *
     * Execute a specific action, pass optional arguments to it
     * @param   string    $tag    Tag to execute
     * @param   null      $args   Arguments to hand to functions assigned to tag
     * @access  public
     * @since   0.1.0
     * @return  mixed    Returns whatever the type of $args is
     */
    public function do_action_kalkun($tag, $args = NULL)
    {
        static::$current_action = $tag;

        array_push(static::$run_actions, $tag);

        if( ! isset(static::$actions[$tag]))
        {
            $this->_debug("No actions found for tag {$tag}");

            return $args;
        }

        ksort(static::$actions[$tag]);

        //die('<pre>' . print_r(static::$actions, TRUE));

        foreach(static::$actions[$tag] as $actions)
        {
            foreach($actions as $a)
            {
                // Make sure the function or method exists
                if(is_array($a['function']))
                {
                    // Methods are setup as an array, [0] is the object/class, [1] is the method
                    if( ! method_exists($a['function'][0], $a['function'][1]))
                    {
                        $this->_error("Unable to execute method '" . get_class($a['function'][0]) . "::{$a['function'][1]}' for action {$tag}, the method doesn't exist");

                        return $args;
                    }
                }
                else
                {
                    // Strings are just functions
                    if( ! function_exists($a['function']))
                    {
                        $this->_error("Unable to execute function '{$a['function']}' for action {$tag}, the function doesn't exist");

                        return $args;
                    }
                }

                // Actions
                if($a['type'] == 'action')
                {
                    // No arguments/null
                    if( ! $args)
                    {
                        call_user_func( $a['function'] );
                    }
                    else
                    {
                        call_user_func_array( $a['function'], array(&$args) );
                    }
                }
                // Filters
                else
                {
                    // No arguments/null
                    if( ! $args)
                    {
                        $args = call_user_func( $a['function'] );
                    }
                    else
                    {
                        $args = call_user_func_array( $a['function'], array(&$args) );
                    }
                }
            }
        }

        static::$current_action = NULL;

        // Be polite, return it as you found it
        settype($args, gettype($args));

        return $args;
    }

	protected function plugin_class_name($system_name = '') {
		return ucfirst($system_name).'_plugin';
	}

    /**
     * Get Enabled Plugins
	 * 
	 * Override upstream's method so that we can support an additional status
	 * being whether the files for the plugin are present on the filesystem.
     *
     * Retrieve the enabled plugins from the database and load them into the enabled_plugins
     * array. This does not initiate the plugins, thats done in a different method
	 * 
     * @access private
     */
    protected function get_plugins()
	{
		// Fetch all plugins
		if ( ! $plugins = static::$PM->get_plugins())
		{
			return FALSE;
		}

		// Load the plugins
		foreach ($plugins as $p)
		{
			// Update plugin status to current real state
			$this->update_plugin_presence_status($p);

			if (isset(static::$plugins[$p->system_name]))
			{
				continue;
			}

			// Skip the plugins that are missing on the filesystem
			if ($p->status & self::P_STATUS_MISSING)
			{
				continue;
			}

			// Add the others to the list of plugins.
			$this->_debug("Adding plugin {$p->system_name}");

			static::$plugins[$p->system_name] = array(
				'data' => $p->data
			);

			// If its enabled, add it to $enabled_plugins referencing the plugin in $plugins
			if ($p->status & self::P_STATUS_ENABLED)
			{
				$this->_debug("Enabling plugin {$p->system_name}");

				static::$enabled_plugins[$p->system_name] = &static::$plugins[$p->system_name];
			}
		}
	}

	protected function update_plugin_presence_status($p)
	{
		$plugin_path = static::$plugin_path . "{$p->system_name}/{$p->system_name}.php";
		if (file_exists($plugin_path))
		{
			// set plugin as present on the filesystem
			$p->status = $p->status & ~self::P_STATUS_MISSING;
		}
		else
		{
			// set plugin as missing on the filesystem
			$p->status = $p->status | self::P_STATUS_MISSING;
		}
		static::$PM->set_status($p->system_name, $p->status);
	}
}
