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
abstract class CI3_plugin_system
{
    /**
     * @access protected    Must be protected so the trait can use it as well
     * @var object          Object reference to the CI Instance
     */
    protected static $CI;

    /**
     * Constructor
     *
     * Set the local static CI reference, get the name of the extending class for
     * the plugin name, and set a reference handler to the plugin in the static
     * $plugins variable in the Plugins library
     *
     * @access public
     */
    public function __construct()
    {
        self::$CI =& get_instance();

        $plugin = str_replace('_plugin', '', strtolower(get_called_class()));

        Plugins_lib::$plugins[ $plugin ][ 'handler' ] = &$this;

    }

    // ------------------------------------------------------------------------

    /**
     * Install Plugin
     *
     * Anything that needs to happen when this plugin gets installed
     *
     * @access public
     * @since   0.1.0
     * @return bool    TRUE by default
     */
    public static function install($data = NULL)
    {
        return TRUE;
    }

    // ------------------------------------------------------------------------

    /**
     * Activate Plugin
     *
     * Anything that needs to happen when this plugin gets activate
     *
     * @access public
     * @since   0.1.0
     * @return bool    TRUE by default
     */
    public function activate($data = NULL)
    {
        return TRUE;
    }

    // ------------------------------------------------------------------------

    /**
     * Deactivate Plugin
     *
     * Anything that needs to happen when this plugin gets deactivate
     *
     * @access public
     * @since   0.1.0
     * @return bool    TRUE by default
     */
    public function deactivate($data = NULL)
    {
        return TRUE;
    }

    // ------------------------------------------------------------------------

    /**
     * NOTE: Uncomment this abstract class to require controllers for plugins
     * Plugin Controller
     *
     * Main controller for plugin, handles all the output, as well as input from the output
     *
     * @access  protected
     * @since   0.1.0
     * @return  string      HTML for form
     */
    //abstract public function controller($plugin_data = array());
}
