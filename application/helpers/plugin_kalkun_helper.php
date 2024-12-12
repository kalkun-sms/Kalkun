<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Kalkun
 * An open source web based SMS Manager
 *
 * @package     Kalkun
 * @author      Kalkun Dev Team
 * @license     <https://spdx.org/licenses/GPL-2.0-or-later.html> GPL-2.0-or-later
 * @link        https://kalkun.sourceforge.io/
 */

# This file includes/requires the original upstream file at the end of the document
# which permits us to override some methods before they are defined by upstream

if( ! function_exists('do_action_kalkun'))
{
    /**
     * Shortcut to Plugins_lib::do_action_kalkun()
     *
     * Execute all plugin functions tied to a specific tag
     *
     * @param   string $tag  Tag to execute
     * @param   mixed  $args Arguments to hand to plugin (Can be anything)
     *
     * @since   0.1.0
     * @return  mixed
     */
    function do_action_kalkun( $tag, $args = NULL )
    {
        //log_message('error',"Doing $tag " . ($args ? "With args: " . serialize($args) : "With no args"));
        return Plugins_lib::$instance->do_action_kalkun( $tag, $args );
    }
}

require_once('plugin_helper.php');
