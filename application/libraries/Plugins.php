<?php
  
class Plugins {
    
    // Codeigniter instance
    protected $_ci;
    
    // Instance of this class
    public static $instance;
    
    // Action statics
    public static $actions;
    public static $current_action;
    public static $run_actions;
    
    // Plugins
    public static $plugins_pool;
    public static $plugins_active;
    
    // Directory
    public $plugins_dir;
    
    // Error and Message Pools
    public static $errors;
    public static $messages;
    
    public function __construct($params = array())
    {
        $this->_ci      = get_instance(); // Codeigniter instance
        self::$instance = $this;          // Instance of this class

		// Driver library only available on CI 2.0+
        if (CI_VERSION >= 2.0)
        {
        	$this->_ci->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
        }
        $this->_ci->load->database();
        $this->_ci->load->helper('directory');
        $this->_ci->load->helper('file');
        
        // Set the plugins directory if passed via paramater
        if (array_key_exists('plugins_dir', $params))
        {
        	$this->set_plugin_dir($params['plugins_dir']);
        }
        else // else set to default value
        {
        	$this->set_plugin_dir(FCPATH . "plugins/");
        }  
        
        // Remove index.php string on the plugins directory if any
       	$this->plugins_dir = str_replace("index.php", "", $this->plugins_dir);      
                
        $this->find_plugins();          // Find all plugins
        $this->get_activated_plugins(); // Get all activated plugins
        $this->get_plugin_infos();      // Gets information about all plugins and stores it
        $this->include_plugins();       // Include plugins
        
        self::$messages = ""; // Clear messages
        self::$errors   = ""; // Clear errors                      
    }
    
    /**
    * Set the location of where all of the plugins are located
    * 
    * @param mixed $directory
    */
    private function set_plugin_dir($directory)
    {
        if ( !empty($directory) )
        {
            $this->plugins_dir = trim($directory)."/";
        }        
    }
    
    /**
    * The instance of this plugin class
    * 
    */
    public static function instance()
    {
        return Plugins::$instance;
    }
    
    
    /**
    * Find plugins in the plugins directory. 
    * This just gets all plugins oh, and then it stores 
    * the plugin header information.
    * 
    */
    public function find_plugins()
    {        
        $plugins = directory_map($this->plugins_dir, 1); // Find plugins
        
        if ($plugins !== false)
        {        
            // Iterate through every plugin found
            foreach ($plugins AS $key => $name)
            {                 
                $name = strtolower(trim($name)); // Trim any whitespace and lowercase
                      
                // If the plugin hasn't already been added and isn't a file
                if ( !isset(self::$plugins_pool[$name]) AND !stripos($name, ".") )
                {                
                    // Make sure a valid plugin file by the same name as the folder exists
                    if ( file_exists($this->plugins_dir.$name."/".$name.".php") )
                    {
                        // Register the plugin
                        self::$plugins_pool[$name]['plugin'] = $name; 
                    }
                    else
                    {
                        self::$errors[$name][] = "Plugin file ".$name.".php does not exist.";
                    }
                }
            }
        }
    }
    
    
    /**
    * Get all activated plugins from the database
    * 
    */
    public function get_activated_plugins()
    {
        // Only plugins in the database are active ones
        $plugins = $this->_ci->db->get('plugins');
        
        // If we have activated plugins
        if ( $plugins->num_rows() > 0 )
        {
            // For every plugin, store it
            foreach ($plugins->result_array() AS $plugin)
            {
                self::$plugins_active[$plugin['plugin_system_name']] = $plugin['plugin_system_name'];
            }
        }
        else
        {
            return true;
        }
            
    }
    
    /**
    * Include all active plugins that are in the database
    * 
    */
    public function include_plugins()
    {
    	if (isset(self::$plugins_active))
    	{
	        // Validate and include our found plugins
	        foreach (self::$plugins_active AS $name => $value)
	        {
	            // The plugin information being added to the database
	            $data = array(
	                "plugin_system_name" => $name,
	                "plugin_name"        => trim(self::$plugins_pool[$name]['plugin_info']['plugin_name']),
	                "plugin_uri"         => trim(self::$plugins_pool[$name]['plugin_info']['plugin_uri']),
	                "plugin_version"     => trim(self::$plugins_pool[$name]['plugin_info']['plugin_version']),
	                "plugin_description" => trim(self::$plugins_pool[$name]['plugin_info']['plugin_description']),
	                "plugin_author"      => trim(self::$plugins_pool[$name]['plugin_info']['plugin_author']),
	                "plugin_author_uri"  => trim(self::$plugins_pool[$name]['plugin_info']['plugin_author_uri'])
	            );
	            $this->_ci->db->where('plugin_system_name', $name)->update('plugins', $data);
	            
	            // If the file was included
	            include_once $this->plugins_dir.$name."/".$name.".php";
	            
	            // Trigger an install event
	            $this->trigger_install_plugin($name);   
	        } 
    	}
    }
    
    
    /**
    * Get the header information from all plugins in
    * the plugins pool for use later on.
    * 
    * @param mixed $name
    */
    public function get_plugin_infos()
    {
        if (self::$plugins_pool !== false)
        {
            // Iterate over all plugins
            foreach (self::$plugins_pool AS $plugin => $value )
            {        
                $plugin = strtolower(trim($plugin)); // Lowercase and trim the plugin name
                
                $plugin_data = read_file($this->plugins_dir.$plugin."/".$plugin.".php"); // Load the plugin we want
                       
                preg_match ( '|Plugin Name:(.*)$|mi', $plugin_data, $name );
                preg_match ( '|Plugin URI:(.*)$|mi', $plugin_data, $uri );
                preg_match ( '|Version:(.*)|i', $plugin_data, $version );
                preg_match ( '|Description:(.*)$|mi', $plugin_data, $description );
                preg_match ( '|Author:(.*)$|mi', $plugin_data, $author_name );
                preg_match ( '|Author URI:(.*)$|mi', $plugin_data, $author_uri );
                
                if (isset($name[1]))
                {
                    $arr['plugin_name'] = trim($name[1]);
                }
                
                if (isset($uri[1]))
                {
                    $arr['plugin_uri'] = trim($uri[1]);
                }
                
                if (isset($version[1]))
                {
                    $arr['plugin_version'] = trim($version[1]);
                }
                
                if (isset($description[1]))
                {
                    $arr['plugin_description'] = trim($description[1]);
                }
                
                if (isset($author_name[1]))
                {
                    $arr['plugin_author'] = trim($author_name[1]);
                }
                
                if (isset($author_uri[1]))
                {
                    $arr['plugin_author_uri'] = trim($author_uri[1]);
                }
                
                // For every plugin header item
                foreach ($arr AS $k => $v)
                {
                    // If the key doesn't exist or the value is not the same, update the array
                    if ( !isset(self::$plugins_pool[$plugin]['plugin_info'][$k]) OR self::$plugins_pool[$plugin]['plugin_info'][$k] != $v )
                    {
                        self::$plugins_pool[$plugin]['plugin_info'][$k] = trim($v);
                    }
                    else
                    {
                        return true;
                    }
                }
            }
        } 
    }
    
    /**
    * Activates a plugin only if it exists in the
    * plugins_pool. After activating, reload page
    * to get the newly activated plugin
    * 
    * @param mixed $name
    */
    public function activate_plugin($name)
    {
        $name = strtolower(trim($name)); // Make sure the name is lowercase and no spaces
        
        // Okay the plugin exists, push it to the activated array
        if ( isset(self::$plugins_pool[$name]) AND !isset(self::$plugins_active[$name]) )
        {
            $this->_ci->db->insert('plugins', array('plugin_system_name' => $name));
            self::$messages[] = "Plugin ".self::$plugins_pool[$name]['plugin_info']['plugin_name']." successfully activated!";
        }
    }
    
    public function deactivate_plugin($name)
    {
        $name = strtolower(trim($name)); // Make sure the name is lowercase and no spaces
        
        // Okay the plugin exists, push it to the activated array
        if ( isset(self::$plugins_active[$name]) )
        {
            $this->_ci->db->where('plugin_system_name', $name)->delete('plugins');
            self::$messages[] = "Plugin ".self::$plugins_pool[$name]['plugin_info']['plugin_name']." has been deactivated!";
        }        
    }
    
    
    /**
    * Get information about a specific plugin
    * 
    * @param mixed $name
    */
    public function plugin_info($name)
    {
        if ( isset(self::$plugins_pool[$name]) )
        {
            return self::$plugins_pool[$name]['plugin_info'];
        }
        else
        {
            return true;
        }
    }
    
    public function add_action($name, $function, $priority=10)
    {
        // If we have already registered this action return true
        if ( isset(self::$actions[$name][$priority][$function]) )
        {
            return true;
        }
        
        /**
        * Allows us to iterate through multiple action hooks.
        */
        if ( is_array($name) )
        {
            foreach ($name AS $name)
            {
                // Store the action hook in the $hooks array
                self::$actions[$name][$priority][$function] = array("function" => $function);
            }
        }
        else
        {
            // Store the action hook in the $hooks array
            self::$actions[$name][$priority][$function] = array("function" => $function);
        }
        
        return true;
    }
    
    /**
    * Trigger an action for a particular action hook
    * 
    * @param mixed $name
    * @param mixed $arguments
    * @return mixed
    */
    public function do_action($name, $arguments = "")
    {
        // Oh, no you didn't. Are you trying to run an action hook that doesn't exist?
        if ( !isset(self::$actions[$name]) )
        {
            return $arguments;
        }
        
        // Set the current running hook to this
        self::$current_action = $name;
        
        // Key sort our action hooks
        ksort(self::$actions[$name]);
        
        foreach(self::$actions[$name] AS $priority => $names)
        {
            if ( is_array($names) )
            {
                foreach($names AS $name)
                {
                    // This line runs our function and stores the result in a variable                    
                    $returnargs = call_user_func_array($name['function'], array(&$arguments));
                    
                    if ($returnargs)
                    {
                        $arguments = $returnargs;
                    }
                    
                    // Store our run hooks in the hooks history array
                    self::$run_actions[$name][$priority];
                }
            }
        }
        
        // No hook is running any more
        self::$current_action = '';
        
        return $arguments;
    }  
    
    /**
    * Remove an action hook. No more needs to be said.
    * 
    * @param mixed $name
    * @param mixed $function
    * @param mixed $priority
    */
    public function remove_action($name, $function, $priority=10)
    {
        // If the action hook doesn't, just return true
        if ( !isset(self::$actions[$name][$priority][$function]) )
        {
            return true;
        }
        
        // Remove the action hook from our hooks array
        unset(self::$actions[$name][$priority][$function]);
    }
    
    /**
    * Get the currently running action hook
    * 
    */
    public function current_action()
    {
        return self::$current_action;
    }
    
    /**
    * Check if a particular hook has been run
    * 
    * @param mixed $hook
    * @param mixed $priority
    */
    public function has_run($action, $priority = 10)
    {
        if ( isset(self::$actions[$action][$priority]) )
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    /**
    * Does a particular action hook even exist?
    * 
    * @param mixed $name
    */
    public function action_exists($name)
    {
        if ( isset(self::$actions[$name]) )
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
/**
    * Triggers the functionname_activate function when a plugin is activated
    * 
    * @param mixed $name
    */
    public function trigger_activate_plugin($name)
    {
        // Call plugin activate function
        @call_user_func($name."_activate");
    }
    
    /**
    * Triggers the functionname_deactivate function when a plugin is deactivated
    * 
    * @param mixed $name
    */
    public function trigger_deactivate_plugin($name)
    {
        // Call our plugin deactivate function
        @call_user_func($name."_deactivate");
    }
    
    /**
    * Triggers the functionname_install function when a plugin is first installed
    * 
    * @param mixed $name
    */
    public function trigger_install_plugin($name)
    {        
        // Call our plugin deactivate function
        @call_user_func($name."_install");
    }
    
    /**
    * Will print our information about all plugins and actions
    * neatly presented to the user.
    * 
    */
    public static function debug_class()
    {
        if ( isset(self::$plugins_pool) )
        {
            echo "<h2>Found plugins</h2>";
            echo "<p>All plugins found in the plugins directory.</p>";
            echo "<pre>";
            print_r(self::$plugins_pool);
            echo "</pre>";
            echo "<br />";
            echo "<br />";
        }
        
        if ( isset(self::$plugins_active) )
        {
            echo "<h2>Activated plugins</h2>";
            echo "<p>Activated plugins that have already been included and are usable.</p>";
            echo "<pre>";
            print_r(self::$plugins_active);
            echo "</pre>";
            echo "<br />";
            echo "<br />";
        }
        
        if ( isset(self::$actions) )
        {
            echo "<h2>Register action hooks</h2>";
            echo "<p>Action hooks that have been registered by the application and can be called via plugin files.</p>";
            echo "<pre>";
            print_r(self::$actions);
            echo "</pre>";
            echo "<br />";
            echo "<br />";
        }        
        
        if ( isset(self::$run_actions) )
        {
            echo "<h2>Previously run action hooks</h2>";
            echo "<p>Hooks that have been called previously.</p>";
            echo "<pre>";
            print_r(self::$run_actions);
            echo "</pre>";
            echo "<br />";
            echo "<br />";
        }       
    }   
}

/**
* Add a new action hook
* 
* @param mixed $name
* @param mixed $function
* @param mixed $priority
*/
function add_action($name, $function, $priority=10)
{
    return Plugins::instance()->add_action($name, $function, $priority);
}

/**
* Run an action
* 
* @param mixed $name
* @param mixed $arguments
* @return mixed
*/
function do_action($name, $arguments = "")
{
    return Plugins::instance()->do_action($name, $arguments);
}

/**
* Remove an action
* 
* @param mixed $name
* @param mixed $function
* @param mixed $priority
*/
function remove_action($name, $function, $priority=10)
{
    return Plugins::instance()->remove_action($name, $function, $priority);
}

/**
* Check if an action actually exists
* 
* @param mixed $name
*/
function action_exists($name)
{
    return Plugins::instance()->action_exists($name);
}

/**
* Set the location of where our plugins are located
* 
* @param mixed $directory
*/
function set_plugin_dir($directory)
{
    Plugins::instance()->set_plugin_dir($directory);
}

/**
* Activate a specific plugin
* 
* @param mixed $name
*/
function activate_plugin($name)
{
    return Plugins::instance()->activate_plugin($name);
}

/**
* Deactivate a specific plugin
* 
* @param mixed $name
*/
function deactivate_plugin($name)
{
    return Plugins::instance()->deactivate_plugin($name);
}

/**
* Return the number of plugins found
* 
*/
function count_found_plugins()
{
    return count(Plugins::$plugins_pool);
}

/**
* Return number of plugins activated
* 
*/
function count_activated_plugins()
{
    return count(Plugins::$plugins_active);
}

/**
* Debug function will return all plugins registered and hooks
* 
*/
function debug_class()
{
    Plugins::debug_class();
}

function plugin_errors()
{
    if ( is_array(Plugins::$errors) )
    {
        foreach (Plugins::$errors AS $k => $error)
        {
            echo $error."\n\r";   
        }
    }
    else
    {
        return true;
    }
}

function plugin_messages()
{
    if ( is_array(Plugins::$messages) )
    {
        foreach (Plugins::$messages AS $k => $message)
        {
            echo $message."\n\r";   
        }
    }
    else
    {
        return true;
    }
}


function get_available_plugin()
{
	return Plugins::$plugins_pool;
}

function get_installed_plugin()
{
	return Plugins::$plugins_active;
}

function check_available_plugin($name)
{
	$available_plugin = Plugins::$plugins_pool;
	return array_key_exists($name, $available_plugin);
}

function check_installed_plugin($name)
{
	$installed_plugin = Plugins::$plugins_active;
	return array_key_exists($name, $installed_plugin);
}