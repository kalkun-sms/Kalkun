<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Kalkun
 * An open source web based SMS Manager
 *
 * @copyright 2025 Fab Stz
 * @author Fab Stz <fabstz-it@yahoo.fr>
 * @license <https://spdx.org/licenses/GPL-2.0-or-later.html> GPL-2.0-or-later
 * @link https://kalkun.sourceforge.io/
 */

class MY_Hooks {

	function kalkun_set_error_handler()
	{
		if ( ! function_exists ('kalkun_error_handler'))
		{
			function kalkun_error_handler($errno, $errstr, $errfile, $errline)
			{
				// error was suppressed with the @-operator
				if (0 === error_reporting())
				{
					return FALSE;
				}

				if ($errno === E_DEPRECATED && $errstr === 'ini_set(): Use of mbstring.internal_encoding is deprecated')
				{
					return;
				}
				if ($errno === E_DEPRECATED && $errstr === 'ini_set(): Use of iconv.internal_encoding is deprecated')
				{
					return;
				}

				switch ($errno)
			{
				case E_ERROR:
				  throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
				case E_WARNING:
				  throw new WarningException($errstr, 0, $errno, $errfile, $errline);
				case E_PARSE:
				  throw new ParseException($errstr, 0, $errno, $errfile, $errline);
				case E_NOTICE:
				  throw new NoticeException($errstr, 0, $errno, $errfile, $errline);
				case E_CORE_ERROR:
				  throw new CoreErrorException($errstr, 0, $errno, $errfile, $errline);
				case E_CORE_WARNING:
				  throw new CoreWarningException($errstr, 0, $errno, $errfile, $errline);
				case E_COMPILE_ERROR:
				  throw new CompileErrorException($errstr, 0, $errno, $errfile, $errline);
				case E_COMPILE_WARNING:
				  throw new CoreWarningException($errstr, 0, $errno, $errfile, $errline);
				case E_USER_ERROR:
				  throw new UserErrorException($errstr, 0, $errno, $errfile, $errline);
				case E_USER_WARNING:
				  throw new UserWarningException($errstr, 0, $errno, $errfile, $errline);
				case E_USER_NOTICE:
				  throw new UserNoticeException($errstr, 0, $errno, $errfile, $errline);
				case E_STRICT:
				  throw new StrictException($errstr, 0, $errno, $errfile, $errline);
				case E_RECOVERABLE_ERROR:
				  throw new RecoverableErrorException($errstr, 0, $errno, $errfile, $errline);
				case E_DEPRECATED:
				  throw new DeprecatedException($errstr, 0, $errno, $errfile, $errline);
				case E_USER_DEPRECATED:
				  throw new UserDeprecatedException($errstr, 0, $errno, $errfile, $errline);
			}
			}
		}
		set_error_handler('kalkun_error_handler');
	}

	function kalkun_restore_error_handler()
	{
		restore_error_handler();
	}
}

class WarningException extends ErrorException {

}
class ParseException extends ErrorException {

}
class NoticeException extends ErrorException {

}
class CoreErrorException extends ErrorException {

}
class CoreWarningException extends ErrorException {

}
class CompileErrorException extends ErrorException {

}
class CompileWarningException extends ErrorException {

}
class UserErrorException extends ErrorException {

}
class UserWarningException extends ErrorException {

}
class UserNoticeException extends ErrorException {

}
class StrictException extends ErrorException {

}
class RecoverableErrorException extends ErrorException {

}
class DeprecatedException extends ErrorException {

}
class UserDeprecatedException extends ErrorException {

}
