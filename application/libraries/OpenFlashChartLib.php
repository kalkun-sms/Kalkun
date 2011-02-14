<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Provides a factory for Open Flash Chart2 objects
 *
 * @package CodeIgniter
 * @subpackage Open Flash Chart 2
 * @category Library
 * @original-author thomas(at)kbox.ch
 */

 class OpenFlashChartLib
{
	/**
     * Constructor
     * 
     * Loads OFC2 class definition files. 
     */
    public function __construct()
	{
		include_once 'php-ofc-library/open-flash-chart.php';
	}

    /**
     * Creates OFC2 objects from a passed classname and optional
     * array of arguments
     *
     * @param string $classname
     * @param array $arguments
     * @return mixed
     */
	public function create($classname, $arguments = array())
    {
        // check if class is defined
        if (class_exists($classname))
        {
            return call_user_func_array(
                    array(new ReflectionClass($classname), 'newInstance'),
                    $arguments
                   );
        }
        else
        {
            die("Sorry can't create the object, class [$classname] not defined");
        }
    }
}