<?php
/**
* Plugin Name: Whitelist Number
* Plugin URI: ?
* Version: 1.0
* Description: Autoremove outgoing SMS not in whitelist
* Author: Максим Морэ
* Author URI: https://bitbucket.org/maxsamael
*/

// Add hook for outgoing message
add_action("message.outgoing", "whitelist_number_outgoing", 1);

function whitelist_number_activate()
{
    return true;
}

/**
* Function called when plugin deactivated
* Utility function must be prefixed with the plugin name
* followed by an underscore.
* 
* Format: pluginname_deactivate
* 
*/
function whitelist_number_deactivate()
{
    return true;
}

/**
* Function called when plugin first installed into the database
* Utility function must be prefixed with the plugin name
* followed by an underscore.
* 
* Format: pluginname_install
* 
*/
function whitelist_number_install()
{
	$CI =& get_instance();
	$CI->load->helper('kalkun');
	// check if table already exist
	if (!$CI->db->table_exists('plugin_whitelist_number'))
	{
		$db_driver = $CI->db->platform();
		$db_prop = get_database_property($db_driver);
		execute_sql(APPPATH."plugins/whitelist_number/media/".$db_prop['file']."_whitelist_number.sql");
	}
  return true;
}

function whitelist_number_outgoing($numbers = array())
{
    $CI =& get_instance();
    $CI->load->model('whitelist_number/whitelist_number_model', 'plugin_model');
    $heaven = array();
    
    // Get whitelist number
    $lists = $CI->plugin_model->get('all')->result_array();
    foreach($lists as $tmp)
    {
    	$heaven[] = $tmp['match'];
    }
    
    // Delete number if it's on whitelist number
    foreach($numbers as $key => $number)
    {
	    foreach($heaven as $match) {
        if(preg_match($match,$number))
  	    {
  	    	continue;
  	    } else {
          unset($numbers[$key]);
        }
      }
	}
	return $numbers;
}

/* End of file whitelist_number.php */
/* Location: ./application/plugins/whitelist_number/whitelist_number.php */
