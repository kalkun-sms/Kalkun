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
 * CI3 Plugin System Trait
 *
 * @package		CI3 Plugin System
 * @author		Justin Hyland www.justinhyland.com
 * @link        https://github.com/jhyland87/CI3_Plugin_System
 */
trait plugin_trait {

    /**
     * Fetch an item from the GET array
     *
     * Shortcut to Loader::get()
     *
     * @param	mixed	    $index		Index for item to be fetched from $_GET
     * @param	bool	    $xss_clean	Whether to apply XSS filtering
     * @access  protected
     * @since   0.1.0
     * @return	mixed
     */
    protected function get($index = NULL, $xss_clean = NULL)
    {
        return parent::$CI->input->get($index, $xss_clean);
    }

    // ------------------------------------------------------------------------

    /**
     * Fetch an item from the POST array
     *
     * Shortcut to Loader::post()
     *
     * @param	mixed	    $index		Index for item to be fetched from $_POST
     * @param	bool	    $xss_clean	Whether to apply XSS filtering
     * @access  protected
     * @since   0.1.0
     * @return	mixed
     */
    protected function post($index = NULL, $xss_clean = NULL)
    {
        return parent::$CI->input->post($index, $xss_clean);
    }

    // ------------------------------------------------------------------------

    /**
     * Fetch an item from POST data with fallback to GET
     *
     * Shortcut to Loader::post_get()
     *
     * @param	string	    $index		Index for item to be fetched from $_POST or $_GET
     * @param	bool	    $xss_clean	Whether to apply XSS filtering
     * @access  protected
     * @since   0.1.0
     * @return	mixed
     */
    protected function post_get($index, $xss_clean = NULL)
    {
        return parent::$CI->input->post_get($index, $xss_clean);
    }

    // ------------------------------------------------------------------------

    /**
     * Fetch an item from GET data with fallback to POST
     *
     * Shortcut to Loader::get_post()
     *
     * @param	string	    $index		Index for item to be fetched from $_GET or $_POST
     * @param	bool	    $xss_clean	Whether to apply XSS filtering
     * @access  protected
     * @since   0.1.0
     * @return	mixed
     */
    protected function get_post($index, $xss_clean = NULL)
    {
        return parent::$CI->input->get_post($index, $xss_clean);
    }

    // ------------------------------------------------------------------------

    /**
     * Plugin View
     *
     * Load a plugin view via Loader::plugin_view()
     *
     * @param   str         $view       Plugin view to load, must exist in the /views/ folder of the plugins root directory
     * @param   array       $data       Data to load into view.
     * @access  protected
     * @since   0.1.0
     * @return  str         String value of processed view
     */
    protected function view($view, array $data = array())
    {
        $backtrace = debug_backtrace();

        if( ! isset($backtrace[1]['class']))
        {
            return FALSE;
        }

        // The strtolower version of the class name should be the plugin folder name
        $class = strtolower($backtrace[1]['class']);

        return parent::$CI->load->plugin_view($class, $view, $data, TRUE);
    }
}
