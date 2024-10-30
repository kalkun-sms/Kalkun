<?php
/**
* Plugin Name: Welcome
* Plugin URI: http://azhari.harahap.us
* Version: 0.2
* Description: Example plugin
* Author: Unknown
* Author URI: http://azhari.harahap.us
*/

class Welcome_plugin extends CI3_plugin_system {

	use plugin_trait;

	public function __construct()
	{
		parent::__construct();
		// Add hooks here like add_action, or add_filter...
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
}
