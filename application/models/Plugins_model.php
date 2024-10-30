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
 * CI3 Plugin System Abstract Class
 *
 * @package		CI3 Plugin System
 * @author		Justin Hyland www.justinhyland.com
 * @link        https://github.com/jhyland87/CI3_Plugin_System
 */
class Plugins_model extends MY_Model
{
    /**
     * @var CI_Controller|object Codeigniter instance (get_instance())
     */
    protected static $CI;

    protected static $db;

    // ------------------------------------------------------------------------

    function __construct()
    {
        parent::__construct();
		
        if (get_called_class() === 'Plugins_model')
        {
            die('Direct use of '.get_called_class().' class is not allowed. Use Plugins_kalkun_model instead.');
        }

        static::$CI =& get_instance();

        static::$CI->load->database();
        static::$db = static::$CI->db;
    }

    // ------------------------------------------------------------------------

    /**
     * Get Plugins
     *
     * Get all plugins, return an array of the plugins from the database, with the system_name
     * as the keys
     *
     * @access public
     * @since   0.1.0
     * @return array|bool
     */
    public function get_plugins()
    {
        $query = static::$db->get('plugins');

        if( ! $result = $query->result())
        {
            log_message('error','Error retrieving plugins from database');

            return FALSE;
        }

        $return = array();

        foreach($result as $r)
        {
            if( ! empty($r->data))
            {
                $r->data = unserialize($r->data);
            }

            $return[$r->system_name] = $r;
        }

        return $return;
    }

    // ------------------------------------------------------------------------

    /**
     * Update Plugin Info
     *
     * Update the plugin information in the database. This is typically executed by the
     * Plugins_lib::update_headers() which parses the comments of the plugin for the info
     *
     * @param   str     $plugin     The system_name of the plugin
     * @param   array   $settings   New settings for plugin
     * @access  public
     * @since   0.1.0
     * @return  bool
     */
    public function update_plugin_info($plugin, array $settings)
    {
        return static::$db->where('system_name', $plugin)->update('plugins', $settings);
    }

    // ------------------------------------------------------------------------

    /**
     * Set Status
     *
     * Enable/Disable plugin
     *
     * @param   string  $plugin     Plugin system name
     * @param   bool    $status     Status to set plugin as
     * @access  public
     * @since   0.1.0
     * @return  bool
     */
    public function set_status($plugin, $status)
    {
        log_message("error","PLUGIN: $plugin; STATUS: $status");

        if( ! static::$db
            ->where('system_name', $plugin)
            ->update('plugins', ['status' => $status]))
        {
            return FALSE;
        }

        return TRUE;
    }

    // ------------------------------------------------------------------------

    /**
     * Get Plugin
     *
     * Retrieve the data from the database for a single plugin by the plugin system name
     *
     * @param  string   $plugin  Plugin System Name
     * @access public
     * @since   0.1.0
     * @return bool|object
     */
    public function get_plugin($plugin)
    {
        $query = static::$db->get_where('plugins', ['system_name' => $plugin]);

        $result = $query->result();

        return ( ! @empty($result[0]) ? $result[0] : FALSE);
    }
}
