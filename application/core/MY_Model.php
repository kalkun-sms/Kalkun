<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Kalkun
 * An open source web based SMS Manager
 *
 * @copyright 2021 Fab Stz
 * @author Fab Stz <fabstz-it@yahoo.fr>
 * @license <https://spdx.org/licenses/GPL-3.0-or-later.html> GPL-3.0-or-later
 * @link https://github.com/kalkun-sms/Kalkun/
 */

/**
 * Model Class
  */
class MY_Model extends CI_Model {

	/**
	 * Class constructor
	 *
	 * @return	void
	 */
	public function __construct()
	{
		parent::__construct();

		set_error_handler(function($errno, $errstr, $errfile, $errline) {
			throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
		});
	}
}
