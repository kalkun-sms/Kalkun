<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
| 	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['scaffolding_trigger'] = 'scaffolding';
|
| This route lets you set a "secret" word that will trigger the
| scaffolding feature for added security. Note: Scaffolding must be
| enabled in the controller in which you intend to use it.   The reserved 
| routes must come before any wildcard or regular expression routes.
|
*/

$route['default_controller'] = "kalkun";
$route['scaffolding_trigger'] = "";

$route['logout'] = "login/logout";

//$route['phonebook'] = "kalkun/phonebook/0";
//$route['phonebook/(:num)'] = "kalkun/phonebook/$1";

$route['plugin/(:any)'] = "$1";
$route['settings/(:any)'] = "kalkun/settings/$1";
$route['about'] = "kalkun/about";

// pretty URL mockup
//$route['inbox'] = "messages/folder/inbox/0";
//$route['inbox/(:num)'] = "messages/folder/inbox/$1";
//$route['outbox'] = "messages/folder/outbox";
//$route['sentitems'] = "messages/folder/sentitems";

//$route['label/(:num)'] = "messages/my_folder/inbox/$1";

/* End of file routes.php */
/* Location: ./application/config/routes.php */
