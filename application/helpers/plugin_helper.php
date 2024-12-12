<?php

defined('BASEPATH') OR exit('No direct script access allowed');

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
 *
 * https://github.com/jhyland87/CI3_Plugin_System/blob/65468cf92ce30e73db1041fb6c0feba347205277/application/helpers/plugin_helper.php
 */

if( ! function_exists('update_all_plugin_headers'))
{
    /**
     * Shortcut to Plugins_lib::update_all_plugin_headers()
     *
     * Executes update_plugin_headers() for all plugins in database
     *
     * @since   0.1.0
     * @return bool
     */
    function update_all_plugin_headers()
    {
        return Plugins_lib::$instance->update_all_plugin_headers();
    }
}

// ------------------------------------------------------------------------

if( ! function_exists('update_plugin_headers'))
{
    /**
     * Shortcut to Plugins_lib::update_plugin_headers()
     *
     * Updates the plugin headers for a specified plugin based on the plugins .php file comments
     *
     * @param   string $plugin Plugin system name
     *
     * @since   0.1.0
     * @return  bool
     */
    function update_plugin_headers( $plugin )
    {
        return Plugins_lib::$instance->update_plugin_headers( $plugin );
    }
}

// ------------------------------------------------------------------------

if( ! function_exists('install_plugin'))
{
    /**
     * Shortcut to Plugins_lib::install_plugin()
     *
     * Executes whatevers in the plugins install method
     *
     * @param   string $plugin Plugin system name
     *
     * @since   0.1.0
     * @return  bool
     */
    function install_plugin( $plugin, $data = NULL )
    {
        return Plugins_lib::$instance->install_plugin( $plugin, $data );
    }
}

// ------------------------------------------------------------------------

if( ! function_exists('enable_plugin'))
{
    /**
     * Shortcut to Plugins_lib::enable_plugin()
     *
     * Enable a specified plugin by setting the database status value to 1
     *
     * @param   string $plugin Plugin system name
     * @param   mixed  $data   Any data that should be handed down to the plugins activation method (optional)
     *
     * @since   0.1.0
     * @return  bool
     */
    function enable_plugin( $plugin, $data = NULL )
    {
        return Plugins_lib::$instance->enable_plugin( $plugin, $data );
    }
}

// ------------------------------------------------------------------------

if( ! function_exists('disable_plugin'))
{
    /**
     * Shortcut to Plugins_lib::disable_plugin()
     *
     * Disable a specified plugin by setting the database status value to 0
     *
     * @param   string $plugin Plugin system name
     * @param   mixed  $data   Any data that should be handed down to the plugins deactivate method (optional)
     *
     * @since   0.1.0
     * @return  bool
     */
    function disable_plugin( $plugin, $data = NULL )
    {
        return Plugins_lib::$instance->disable_plugin( $plugin, $data );
    }
}

// ------------------------------------------------------------------------

if( ! function_exists('plugin_details'))
{
    /**
     * Shortcut to Plugins_lib::plugin_details()
     *
     * Return the details of a plugin from the plugins database table
     *
     * @param   string $plugin Plugin system name
     *
     * @since   0.1.0
     * @return  array
     */
    function plugin_details( $plugin )
    {
        return Plugins_lib::$instance->plugin_details( $plugin );
    }
}

// ------------------------------------------------------------------------

if( ! function_exists('get_messages'))
{
    /**
     * Shortcut to Plugins_lib::get_messages()
     *
     * Gets all the plugin messages thus far (errors, debug messages, warnings)
     *
     * @param   string $type Specific type to retrieve, if NULL, all are returned
     *
     * @since   0.1.0
     * @return  array
     */
    function get_messages( $type = NULL )
    {
        return Plugins_lib::$instance->get_messages( $type );
    }
}

// ------------------------------------------------------------------------

if( ! function_exists('print_messages'))
{
    /**
     * Shortcut to Plugins_lib::print_messages()
     *
     * Displays all the plugin messages thus far (errors, debug messages, warnings)
     *
     * @param   string $type Specific type to retrieve, if NULL, all are printed
     *
     * @since   0.1.0
     * @return  array
     */
    function print_messages( $type = NULL )
    {
        return Plugins_lib::$instance->print_messages( $type );
    }
}

// ------------------------------------------------------------------------

if( ! function_exists('get_orphaned_plugins'))
{
    /**
     * Shortcut to Plugins_lib::get_orphaned_plugins()
     *
     * See if there are any plugins in the plugins directory that arent in the database
     *
     * @since   0.1.0
     * @return  array
     */
    function get_orphaned_plugins()
    {
        return Plugins_lib::$instance->get_orphaned_plugins();
    }
}

// ------------------------------------------------------------------------

if( ! function_exists('add_action'))
{
    /**
     * Shortcut to Plugins_lib::add_action()
     *
     * Add an action - a function that will fire off when a tag/action is executed (NOT
     * the same as add_filter - which will return a value
     *
     * @param   string       $tag      Tag/Action thats being executed
     * @param   string|array $function Either a single function (string), or a class and method (array)
     * @param   int          $priority Priority of this action
     *
     * @since   0.1.0
     * @return  boolean
     */
    function add_action( $tag, $function, $priority = 10 )
    {
        return Plugins_lib::$instance->add_action( $tag, $function, $priority );
    }
}

// ------------------------------------------------------------------------

if( ! function_exists('add_filter'))
{
    /**
     * Shortcut to Plugins_lib::add_filter()
     *
     * Add a filter - a function that can be used to effect/parse/filter out some content (NOT
     * the same as add_action - which will just fire off a function
     *
     * @param   string       $tag      Tag/Action thats being executed
     * @param   string|array $function Either a single function (string), or a class and method (array)
     * @param   int          $priority Priority of this action
     *
     * @since   0.1.0
     * @return  boolean
     */
    function add_filter( $tag, $function, $priority = 10 )
    {
        return Plugins_lib::$instance->add_filter( $tag, $function, $priority );
    }
}

// ------------------------------------------------------------------------

if( ! function_exists('get_actions'))
{
    /**
     * Shortcut to Plugins_lib::get_actions()
     *
     * Retrieve all actions/filters that are assigned to actions/tags
     *
     * @since   0.1.0
     * @return  array
     */
    function get_actions()
    {
        return Plugins_lib::$instance->get_actions();
    }
}

// ------------------------------------------------------------------------

if( ! function_exists('retrieve_plugins'))
{
    /**
     * Shortcut to Plugins_lib::retrieve_plugins()
     *
     * Retrieve all plugins
     *
     * @since   0.1.0
     * @return  array
     */
    function retrieve_plugins()
    {
        return Plugins_lib::$instance->retrieve_plugins();
    }
}

// ------------------------------------------------------------------------

if( ! function_exists('do_action'))
{
    /**
     * Shortcut to Plugins_lib::do_action()
     *
     * Execute all plugin functions tied to a specific tag
     *
     * @param   string $tag  Tag to execute
     * @param   mixed  $args Arguments to hand to plugin (Can be anything)
     *
     * @since   0.1.0
     * @return  mixed
     */
    function do_action( $tag, $args = NULL )
    {
        //log_message('error',"Doing $tag " . ($args ? "With args: " . serialize($args) : "With no args"));
        return Plugins_lib::$instance->do_action( $tag, $args );
    }
}

// ------------------------------------------------------------------------

if( ! function_exists('remove_action'))
{
    /**
     * Shortcut to Plugins_lib::remove_action()
     *
     * Remove a specific plugin function assigned to execute on a specific tag at a specific priority
     *
     * @param   string       $tag      Tag to clear actions from
     * @param   string|array $function Function or object/method to remove from tag
     * @param   int          $priority Priority to clear
     *
     * @since   0.1.0
     * @return  boolean
     */
    function remove_action( $tag, $function, $priority = 10 )
    {
        return Plugins_lib::$instance->remove_action( $tag, $function, $priority );
    }
}

// ------------------------------------------------------------------------

if( ! function_exists('current_action'))
{
    /**
     * Shortcut to Plugins_lib::current_action()
     *
     * Get the current plugin action being executed
     *
     * @since   0.1.0
     * @return  string
     */
    function current_action()
    {
        return Plugins_lib::$instance->current_action();
    }
}

// ------------------------------------------------------------------------

if( ! function_exists('has_run'))
{
    /**
     * Shortcut to Plugins_lib::has_run()
     *
     * See if an action has run or not
     *
     * @param   string $action Tag/action to check
     *
     * @since   0.1.0
     * @return  boolean
     */
    function has_run( $action = NULL )
    {
        return Plugins_lib::$instance->has_run( $action );
    }
}

// ------------------------------------------------------------------------

if( ! function_exists('doing_action'))
{
    /**
     * Shortcut to Plugins_lib::doing_action()
     *
     * If no action is specified, then the current action being executed will be returned, if
     * an action is specified, then TRUE/FALSE will be returned based on if the action is
     * being executed or not
     *
     * @oaran   string  $action     Action to check
     * @since   0.1.0
     * @return  boolean|string
     */
    function doing_action( $action = NULL )
    {
        return Plugins_lib::$instance->doing_action( $action );
    }
}

// ------------------------------------------------------------------------

if( ! function_exists('did_action'))
{
    /**
     * Shortcut to Plugins_lib::did_action()
     *
     * Check if an action/tag has been executed or not
     *
     * @param   string $tag Tag/action to check
     *
     * @since   0.1.0
     * @return  boolean
     */
    function did_action( $tag )
    {
        return Plugins_lib::$instance->did_action( $tag );
    }
}
