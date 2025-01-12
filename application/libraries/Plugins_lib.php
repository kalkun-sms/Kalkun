<?php
/**
 * CI3 Plugin System
 *
 * Simple plugin system for CodeIgniter v3
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2015, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CI Plugins
 * @author	Justin Hyland www.justinhyland.com
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://github.com/jhyland87/CI3_Plugin_System
 * @since	Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CI3 Plugin System CI Library
 *
 * @package		CI3 Plugin System
 * @author		Justin Hyland www.justinhyland.com
 * @link        https://github.com/jhyland87/CI3_Plugin_System
 */
class Plugins_lib {
    /**
     * Instance of this class (CI basically initiates all models/libs as
     * singletons, so essentially, this is a reference to a singleton
     * @var object
     */
    public static $instance;

    /**
     * Static CI reference property
     * @var object
     */
    protected static $CI;

    /**
     * Plugins Model
     * @var object
     */
    protected static $PM;

    /**
     * Plugin Path - Specified in config plugin_path
     * @var string
     */
    protected static $plugin_path;

    /**
     * Plugin messages (Errors, debugging, warnings)
     * @var array
     */
    protected static $messages;

    /**
     * Plugins List
     * @var  array
     */
    public static $plugins;

    /**
     * Array of active plugins - This is a reference of $plugins['enabled']
     * @var  array
     */
    public static $enabled_plugins = [];

    /**
     * Actions/Filters to run
     * @var array
     */
    public static $actions = array();

    /**
     * Current tag being executed
     * @var  string
     */
    public static $current_action;

    /**
     * Previously executed tags
     * @var array
     */
    public static $run_actions = array();

    // ------------------------------------------------------------------------

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

        if (get_called_class() === 'Plugins_lib')
        {
            die('Direct use of '.get_called_class().' class is not allowed. Use '.get_called_class().'_kalkun instead.');
        }

        // Codeigniter instance
        static::$CI =& get_instance();

        // Set a static handler for the instance (So it can be called outside of CI classes, IE: below)
        // No real need to setup a singleton with CI....
        static::$instance = $this;

        // Load the plugins model
        static::$CI->load->model('Plugins_kalkun_model');

        // Load the plugins helper
        static::$CI->load->helper('plugin_kalkun');

        // Set a shorter handle for the plugins model
        static::$PM = static::$CI->Plugins_kalkun_model;

        static::$messages = array(
            'error' => [],
            'debug' => [],
            'warn'  => []
        );
        // Set plugin path
        $this->set_plugin_dir();

        // Get all activated plugins
        $this->get_plugins();

        // Include plugins
        $this->include_enabled_plugins();

        // Load them
        $this->load_enabled_plugins();
    }

    // ------------------------------------------------------------------------

    /**
     * Set Plugin Directory
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
        if($path = static::$CI->config->item('plugin_path'))
        {
            $this->_debug("Plugin path set to {$path} via config setting");
        }
        elseif(defined('PLUGIN_DIR'))
        {
            $this->_debug("Plugin path set to ".PLUGIN_DIR."via constant PLUGIN_DIR");

            $path = PLUGIN_DIR;
        }
        else
        {
            $path = FCPATH . 'plugins/';

            $this->_debug("Plugin path defaulted to {$path}");

            $this->_warn("No plugin path specified in CI settings or PLUGIN_DIR constant, defaulting to {$path}");
        }

        // Make sure it ends in /
        if( preg_match('%/$%', $path) === FALSE )
        {
            $path = "{$path}/";
        }

        static::$plugin_path = str_replace('//','/',$path);
    }

    // ------------------------------------------------------------------------

    /**
     * View Controller
     *
     * View the controller from the plugin.
     *
     * @param   string  $plugin         Systen name of plugin
     * @param   mixed   $plugin_data    Any data to hand down to the plugin (Anything
     *                                  processed from the controller, etc)
     * @access  public
     * @since   0.1.0
     * @return  string  Whatever content is returned from the plugins controller (which
     *                  is usually HTML from the settings form)
     */
    public function view_controller($plugin, $plugin_data = NULL)
    {
        if( ! isset(static::$plugins[$plugin]))
        {
            log_message('error',"The plugin {$plugin} was not found");

            return FALSE;
        }
        elseif( ! method_exists(static::$plugins[$plugin]['handler'], 'controller'))
        {
            $this->_error('error','Plugin Error',"The plugin {$plugin} does not have a controller", TRUE);

            return FALSE;
        }

        return call_user_func(array(static::$plugins[$plugin]['handler'], 'controller'), $plugin_data);
    }

    // ------------------------------------------------------------------------

    /**
     * Plugin Error Collector
     *
     * Push an error message into the messages array
     *
     * @param   string     $message        Message to push
     * @access  private
     */
    protected function _error($message)
    {
        //log_message('error', 'PLUGIN-ERROR: ' . $message);

        array_push(static::$messages['error'], $message);
    }

    // ------------------------------------------------------------------------

    /**
     * Plugin Debug Collector
     *
     * Push a debug message into the messages array
     *
     * @param   string     $message        Message to push
     * @access  private
     */
    protected function _debug($message)
    {
        //log_message('debug', 'PLUGIN-DEBUG: ' . $message);

        array_push(static::$messages['debug'], $message);
    }

    // ------------------------------------------------------------------------

    /**
     * Plugin Warn Collector
     *
     * Push a warn message into the messages array
     *
     * @param   string     $message        Message to push
     * @access  private
     */
    protected function _warn($message)
    {
        //log_message('error', 'PLUGIN-WARN: ' . $message);

        array_push(static::$messages['warn'], $message);
    }

    // ------------------------------------------------------------------------

    /**
     * Enable Plugin
     *
     * Enable a plugin by setting the plugins.status to 1 in the plugins table
     *
     * @oaram   string  $plugin     Plugin Name
     * @param   mixed   $data       Any data that should be handed down to the plugins deactivate method (optional)
     * @access  public
     * @since   0.1.0
     * @return  bool
     */
    public function enable_plugin($plugin, $data = NULL)
    {
        // Run the plugins activation method to run anything required by the plugin
        call_user_func(array(static::$plugins[$plugin]['handler'], 'activate'), $data);

        // Enable it in the database
        return static::$PM->set_status($plugin, 1);
    }

    // ------------------------------------------------------------------------

    /**
     * Disable Plugin
     *
     * Disable a plugin by setting the plugins.status to 0 in the plugins table
     *
     * @oaram   string  $plugin     Plugin Name
     * @param   mixed   $data       Any data that should be handed down to the plugins activate method (optional)
     * @access  public
     * @since   0.1.0
     * @return  bool
     */
    public function disable_plugin($plugin, $data = NULL)
    {
        // Run the plugins deactivation method to run anything required by the plugin
        call_user_func(array(static::$plugins[$plugin]['handler'], 'deactivate'), $data);

        // Disable it in the database
        return static::$PM->set_status($plugin, 0);
    }

    // ------------------------------------------------------------------------

    /**
     * Install Plugin
     *
     * Install a plugin by adding it to the database and executing any installation code thats in
     * the plugins install method
     *
     * @param   string  $plugin     Plugins system name (Folder name)
     * @access  public
     * @param   boolean
     */
    public function install_plugin($plugin, $data = NULL)
    {
        // System name for folder and file
        $system_name = strtolower($plugin);

        // Class name is just system name with ucfirst
        $class_name = $this->plugin_class_name($system_name);

        // Path to plugins main file
        $plugin_path = static::$plugin_path . "{$system_name}/{$system_name}.php";

        // If the plugins class hasnt been loaded...
        if( ! class_exists($class_name))
        {
            // Make sure a valid plugin file by the same name as the folder exists
            if (file_exists($plugin_path))
            {
                if( ! include_once $plugin_path)
                {
                    $this->_error("Failed to install {$plugin}, there was an error loading the plugin file {$plugin_path}, is it readable?");
                }
                else
                {
                    $this->_debug("Successfully loaded the plugin file {$plugin_path}");
                }
            }
            else
            {
                $this->_error("Failed to install {$plugin}, unable to find the file {$plugin_path}");
            }
        }

        // Execute the plugin installation
        return call_user_func("{$class_name}::install", $data);
    }

    // ------------------------------------------------------------------------

    /**
     * Plugin Details
     *
     * Retrieve the details of a plugin from the database
     *
     * @param   string  $plugin Plugin system name
     * @access  public
     * @since   0.1.0
     * @return  object|bool
     */
    public function plugin_details($plugin)
    {
        return static::$PM->get_plugin($plugin);
    }

    // ------------------------------------------------------------------------

    /**
     * Get Enabled Plugins
     *
     * Retrieve the enabled plugins from the database and load them into the enabled_plugins
     * array. This does not initiate the plugins, thats done in a different method
     *
     * @access private
     */
    protected function get_plugins()
    {
        // Fetch all plugins
        if( ! $plugins = static::$PM->get_plugins())
        {
            return FALSE;
        }

        // Load the plugins
        foreach($plugins as $p)
        {
            if( ! isset( static::$plugins[ $p->system_name ] ) )
            {
                $this->_debug( "Adding plugin {$p->system_name}" );

                static::$plugins[$p->system_name] = array(
                    'data' =>  $p->data
                );

                // If its enabled, add it to $enabled_plugins referencing the plugin in $plugins
                if($p->status == '1')
                {
                    $this->_debug( "Enabling plugin {$p->system_name}" );

                    static::$enabled_plugins[ $p->system_name ] = &static::$plugins[$p->system_name];
                }
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Print Plugins
     *
     * Retrieve all plugin information from the database
     *
     * @access public
     * @since   0.1.0
     * @return array
     */
    public function retrieve_plugins()
    {
        return static::$PM->get_plugins();
    }

    // ------------------------------------------------------------------------

    /**
     * Include Enabled Plugins
     *
     * Iterate through the enabled_plugins property array and include the actual plugin files
     *
     * @access private
     */
    protected function include_enabled_plugins()
    {
        if(empty(static::$enabled_plugins))
        {
            $this->_error("Unable to include enabled plugin files, enabled plugins not retrieved");

            return FALSE;
        }

        foreach(static::$enabled_plugins as $name => $p)
        {
            $plugin_path = static::$plugin_path . "{$name}/{$name}.php";

            // Make sure a valid plugin file by the same name as the folder exists
            if (file_exists($plugin_path))
            {
                if( ! include_once $plugin_path)
                {
                    $this->_error("There was an error loading the plugin file {$plugin_path}");
                }
                else
                {
                    $this->_debug("Successfully loaded the plugin file {$plugin_path}");
                }
            }
            else
            {
                $this->_error("Failed to include the plugin {$name}, unable to find the file {$plugin_path}");
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Load Enabled Plugins
     *
     * Load the enabled plugins into objects, store the objects into the loaded plugins array
     *
     * @access private
     */
    protected function load_enabled_plugins()
    {
        if(static::$enabled_plugins)
        {
            foreach( static::$enabled_plugins as $name => $p )
            {
                $class_name = $this->plugin_class_name($name);
                new $class_name;
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Add Action
     *
     * Assign a function to be executed by a certain tag. An action will just fire off a function
     * when the tag is called, a filter will parse data and return the modified data
     *
     * @param string        $tag        Tag to add filter to
     * @param string|array  $function   Either a string (function), or an array (Object, method)
     * @param integer       $priority   Priority
     * @param string        $type       Either action or filter
     * @access public
     * @since   0.1.0
     * @return boolean
     */
    public function add_action($tag, $function, $priority = 10, $type = 'action')
    {
        if(is_array($function))
        {
            if(count($function) < 2)
            {
                // If its an array of one element, then just add the first element
                $function = $function[0];
            }
            elseif( ! is_object($function[0]))
            {
                $this->_error("Failing to add method '" . implode('::', $function) . "'' as {$type} to tag {$tag}, an array was given, first element was not an object");

                return FALSE;
            }
            elseif( ! method_exists($function[0], $function[1]))
            {
                $this->_error("Failing to add method '" . get_class($function[0]) . "::{$function[1]}' as {$type} to tag {$tag}, the method does not exist");

                return FALSE;
            }
        }

        // Execute is_array again, since the above could have converted it to an array
        if( ! is_array($function))
        {
            if( ! function_exists($function))
            {
                $this->_error("Failing to add function {$function} as {$type} to tag {$tag}, the function does not exist");

                return FALSE;
            }
        }

        if( ! in_array($type, ['action','filter']))
        {
            $this->_error("Unknown type '{$type}', must be 'filter' or 'action'");

            return FALSE;
        }

        static::$actions[$tag][$priority][] = array(
            'function' => $function,
            'type'  => $type
        );

        return TRUE;
    }

    // ------------------------------------------------------------------------

    /**
     * Add Filter
     *
     * Just a wrapper for add_function except adds it as type 'filter'. Filters will
     * take in data and perform an action on it, then return it, actions will just
     * fire off a function
     *
     * @param  string        $tag        Tag to add filter to
     * @param  string|array  $function   Either a string (function), or an array (Object, method)
     * @param  integer       $priority   Priority
     * @access public
     * @since  0.1.0
     * @return boolean
     */
    public function add_filter($tag, $function, $priority = 10)
    {
        return $this->add_action($tag, $function, $priority, 'filter');
    }

    // ------------------------------------------------------------------------

    /**
     * Get Actions
     *
     * Get actions....
     *
     * @access public
     * @since   0.1.0
     * @return array
     */
    public function get_actions()
    {
        foreach(static::$actions as $k => $a)
            ksort(static::$actions[$k]);

        return static::$actions;
    }

    // ------------------------------------------------------------------------

    /**
     * Do Action
     *
     * Execute a specific action, pass optional arguments to it
     * @param   string    $tag    Tag to execute
     * @param   null      $args   Arguments to hand to functions assigned to tag
     * @access  public
     * @since   0.1.0
     * @return  mixed    Returns whatever the type of $args is
     */
    public function do_action($tag, $args = NULL)
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
                        call_user_func_array( $a['function'], $args );
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
                        $args = call_user_func_array( $a['function'], $args );
                    }
                }
            }
        }

        static::$current_action = NULL;

        // Be polite, return it as you found it
        settype($args, gettype($args));

        return $args;
    }

    // ------------------------------------------------------------------------

    /**
     * Remove Action
     *
     * Remove a function from an action
     *
     * @param   string  $tag Tag to check in
     * @param   mixed   $function Function to be removed
     * @param   integer $priority Priority to check for function
     * @access  public
     * @since   0.1.0
     * @return  boolean
     */
    public function remove_action($tag, $function, $priority = 10)
    {
        if (isset(static::$actions[$tag][$priority][$function]))
        {
            // Remove the action hook from our hooks array
            unset(static::$actions[$tag][$priority][$function]);
        }

        return TRUE;

    }

    // ------------------------------------------------------------------------

    /**
     * Current Action
     *
     * Set the currently running action
     *
     * @access public
     * @since   0.1.0
     * @return string
     */
    public function current_action()
    {
        return static::$current_action;
    }

    // ------------------------------------------------------------------------

    /**
     * Has Run
     *
     * See if a particular action has ran yet
     *
     * @param  string  $action  Action to check for
     * @access public
     * @since   0.1.0
     * @return boolean
     */
    public function has_run($action)
    {
        if (isset(static::$run_actions[$action]))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Update Plugin Headers
     *
     * Parse a given plugins PHP file for the header information in the comments, and update the database info
     * accordingly
     *
     * @param   string  $plugin Plugin System Name to check
     * @access  public
     * @todo    Try to retrieve only the top X lines of the file, not the whole file
     * @since   0.1.0
     * @return  TRUE Always true
     */
    public function update_plugin_headers($plugin)
    {
        if (isset(static::$plugins[$plugin]))
        {
            $arr = array();

            $plugin_data = file_get_contents(static::$plugin_path.$plugin."/".$plugin.".php"); // Load the plugin we want

            preg_match ('|Plugin Name:(.*)$|mi', $plugin_data, $name);
            preg_match ('|Plugin URI:(.*)$|mi', $plugin_data, $uri);
            preg_match ('|Version:(.*)|i', $plugin_data, $version);
            preg_match ('|Description:(.*)$|mi', $plugin_data, $description);
            preg_match ('|Author:(.*)$|mi', $plugin_data, $author_name);
            preg_match ('|Author URI:(.*)$|mi', $plugin_data, $author_uri);

            if (isset($name[1]))
            {
                $arr['name'] = trim($name[1]);
            }

            if (isset($uri[1]))
            {
                $arr['uri'] = trim($uri[1]);
            }

            if (isset($version[1]))
            {
                $arr['version'] = trim($version[1]);
            }

            if (isset($description[1]))
            {
                $arr['description'] = trim($description[1]);
            }

            if (isset($author_name[1]))
            {
                $arr['author'] = trim($author_name[1]);
            }

            if (isset($author_uri[1]))
            {
                $arr['author_uri'] = trim($author_uri[1]);
            }

            if(empty($arr))
            {
                $this->_warn("Skipping header update for {$plugin}, no headers matched");
            }
            elseif(self::$PM->update_plugin_info($plugin, $arr))
            {
                $this->_debug("Updated plugin headers for {$plugin}: " . serialize($arr));
            }
            else
            {
                $this->_error("Failed to update plugin headers for {$plugin}: " . serialize($arr));
            }
        }

        return TRUE;
    }

    // ------------------------------------------------------------------------

    /**
     * Update All Plugin Headers
     *
     * Execute self::update_plugin_headers for each plugin found in static::$plugins
     *
     * @access public
     * @since   0.1.0
     * @return boolean
     */
    public function update_all_plugin_headers()
    {
        if(empty(static::$plugins))
        {
            $this->_warn("No plugins to update headers for");

            return TRUE;
        }

        foreach(static::$plugins as $name => $plugin)
        {
            $this->_debug("Updating plugin headers for {$name}");

            if( ! $this->update_plugin_headers($name))
            {
                return FALSE;
            }
        }

        return TRUE;
    }

    // ------------------------------------------------------------------------

    /**
     * Doing Action
     *
     * If the param is NULL, then this will return what action is being executed,
     * if an action is supplied, then it will return boolean based on if that action
     * is being executed or not
     *
     * @param null $action  Action to check for
     */
    public function doing_action($action = NULL)
    {
        if(is_null($action))
        {
            return static::$current_action;
        }
        else
        {
            return $action === static::$current_action;
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Did Action
     *
     * Returns if a tag has been fired or not
     *
     * @param $tag
     */
    public function did_action($tag)
    {
        return in_array($tag, static::$run_actions);
    }

    // ------------------------------------------------------------------------

    /**
     * Get Orphaned Plugins
     *
     * Look in the plugin directory for any folders that do not have an associated entry
     * in the plugins table
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
            if(in_array($f, ['.','..'])) continue;

            if( ! isset($plugins[$f]))
            {
                array_push($orphaned, $f);
            }
        }

        return (count($orphaned) > 0 ? $orphaned : FALSE);
    }

    // ------------------------------------------------------------------------

    /**
     * Get Messages
     *
     * Get all messages, or a specific type of message
     *
     * @param   string  $type   Type of error to retrieve (error, debug, warn)
     * @access  public
     * @since   0.1.0
     * @return  array|bool
     */
    public function get_messages($type = NULL)
    {
        if( ! $type)
        {
            return static::$messages;
        }
        elseif( ! isset(static::$messages[ strtolower($type) ]))
        {
            $this->_error("Failed to retrieve error type '{$type}', no such type found. Use 'error', 'warn' or 'debug'");

            return FALSE;
        }

        return static::$messages[strtolower($type)];
    }

    // ------------------------------------------------------------------------

    /**
     * Print Messages
     *
     * Print all messages, or messages of a certain type
     *
     * @param   string  $type   Type of error to display (error, debug, warn)
     * @access  public
     * @since   0.1.0
     * @return  array|bool
     */
    public function print_messages($type = NULL)
    {
        if($type)
        {
            if(@empty(static::$messages[ strtolower($type) ]) || ! isset(static::$messages[ strtolower($type) ]))
            {
                echo "{$type} IS EMPTY\n";
                return TRUE;
            }

            echo "<h3>Plugin Messages - <strong>" . ucfirst($type) . "</strong></h3>\n";

            echo "<ol>\n";

            foreach(static::$messages[ strtolower($type) ] as $m)
            {
                echo "<li>$m</li>\n";
            }

            echo "</ol>\n</hr>\n";

            return TRUE;
        }

        foreach(static::$messages as $type => $messages)
        {
            if(@empty($messages))
            {
                echo "{$type} IS EMPTY\n";
                continue;
            }

            echo "<h3>Plugin Messages - <strong>" . ucfirst($type) . "</strong></h3>\n";

            echo "<ol>\n";

            foreach($messages as $m)
            {
                echo "<li>$m</li>\n";
            }

            echo "</ol>\n</hr>\n";
        }

        return TRUE;
    }

    protected function plugin_class_name($system_name = '') {
        return ucfirst($system_name);
    }
}
