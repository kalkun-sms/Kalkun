<?php
/**
 * Kalkun
 * An open source web based SMS Management
 *
 * @package		Kalkun
 * @author		Kalkun Dev Team
 * @license		https://spdx.org/licenses/GPL-2.0-or-later.html
 * @link		https://kalkun.sourceforge.io/
 */

class DBVars {

	const DATABASE = 'kalkun_testing';
	const USERNAME = 'kalkun_test_user';
	const PASSWORD = 'kalkun_test_user';
}

if (php_sapi_name() === 'cli' && isset($argv))
{
	if (isset($argv[1]))
	{
		switch ($argv[1])
		{
			case 'database':
				echo DBVars::DATABASE;
				break;
			case 'username':
				echo DBVars::USERNAME;
				break;
			case 'password':
				echo DBVars::PASSWORD;
				break;
		}
	}
}
