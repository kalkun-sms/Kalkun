<?php
/**
 * Kalkun
 * An open source web based SMS Manager
 *
 * @copyright 2024 Fab Stz
 * @author Fab Stz <fabstz-it@yahoo.fr>
 * @license <https://spdx.org/licenses/GPL-2.0-or-later.html> GPL-2.0-or-later
 * @link https://kalkun.sourceforge.io/
 */

require_once __DIR__.'/../testutils/ConfigFile.php';
require_once __DIR__.'/../testutils/DBSetup.php';
require_once __DIR__.'/../testutils/KalkunTestCase.php';
require_once __DIR__.'/../controllers/Pluginss_test.php';

class Soap_test extends KalkunTestCase {

	public function setUp() : void
	{
		if (file_exists(FCPATH . 'install'))
		{
			unlink(FCPATH . 'install');
		}
		//DBSetup::setup_db_kalkun_testing2($this);
	}

	/*public function reloadCIwithEngine($db_engine)
	{
		DBSetup::$current_setup[$db_engine]->write_config_file_for_database();
	}*/

	public static function database_Provider()
	{
		return DBSetup::$db_engines_to_test;
	}

	/**
	 * @dataProvider database_Provider
	 */
	public function test_index($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '1';
				$_SESSION['level'] = 'admin';
				$_SESSION['username'] = 'kalkun';
			}
		);

		$dbsetup->install_plugin($this, 'soap');
		Pluginss_test::reset_plugins_lib_static_members();

		$output = $this->request('GET', 'plugin/soap');
		$this->assertValidHtml($output);
		$this->markTestIncomplete();
	}

	/**
	 * @dataProvider database_Provider
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 */
	public function test_api($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '1';
				$_SESSION['level'] = 'admin';
				$_SESSION['username'] = 'kalkun';
			}
		);

		$dbsetup->install_plugin($this, 'soap');
		Pluginss_test::reset_plugins_lib_static_members();

		$output = $this->request('GET', 'plugin/soap/api');

		$expected = '<title>NuSOAP: KalkunRemoteAccess</title>';
		$this->_assertStringContainsString($expected, $output);
		//$this->assertValidHtml($output);
		$this->markTestIncomplete();
	}

	/**
	 * @dataProvider database_Provider
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 */
	public function test_api_wsdl($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '1';
				$_SESSION['level'] = 'admin';
				$_SESSION['username'] = 'kalkun';
			}
		);

		$dbsetup->install_plugin($this, 'soap');
		Pluginss_test::reset_plugins_lib_static_members();

		$output = $this->request('GET', 'plugin/soap/api/wsdl');
		$expected = 'targetNamespace="urn:KalkunRemoteAccess">';
		$this->_assertStringContainsString($expected, $output);
	}

	/**
	 * @dataProvider database_Provider
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 */
	public function test_api_getApiVersion($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '1';
				$_SESSION['level'] = 'admin';
				$_SESSION['username'] = 'kalkun';
			}
		);

		$dbsetup->install_plugin($this, 'soap');
		Pluginss_test::reset_plugins_lib_static_members();

		$output = $this->request('GET', 'plugin/soap/api/getApiVersion');
		$this->assertEmpty($output);
	}

	/**
	 * @dataProvider database_Provider
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 */
	public function test_api_index_login($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '1';
				$_SESSION['level'] = 'admin';
				$_SESSION['username'] = 'kalkun';
			}
		);

		$dbsetup->install_plugin($this, 'soap');
		Pluginss_test::reset_plugins_lib_static_members();


		$ip = '127.0.0.1';
		$_SERVER['REMOTE_ADDR'] = $ip;
		$token = '87bbccd33a008694c25a49c0e03ed8bdca3ea6a5fb353e1e1beba866cc5f0614';
		$dbsetup->insert('plugin_remote_access', ['token' => $token, 'ip_address' => $ip]);
		$this->request->addCallable($dbsetup->closure());

		$message = '<ns9011:login xmlns:ns9011="urn:Api">
<!-- token value is as configured by the plugin -->
<token xsi:type="xsd:string">'.$token.'</token>
</ns9011:login>';

		$data = '<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"
xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
xmlns:xsd="http://www.w3.org/2001/XMLSchema"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/"
xmlns:ns9011="urn:Api">
<SOAP-ENV:Body>
'.$message.'
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>';

		$this->request->setHeader('Content-Type', 'Content-Type: text/xml;charset=UTF-8');

		$output = $this->request('POST', 'plugin/soap/api/index/login', $data);
		$expected_session_id = '';
		$expected = '<ns1:loginResponse xmlns:ns1="urn:Api"><result xsi:type="xsd:string">'.$expected_session_id.'</result></ns1:loginResponse>';
		$this->_assertStringContainsString($expected, $output);
	}

	/**
	 * @dataProvider database_Provider
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 */
	public function test_api_index_version($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '1';
				$_SESSION['level'] = 'admin';
				$_SESSION['username'] = 'kalkun';
			}
		);

		$dbsetup->install_plugin($this, 'soap');
		Pluginss_test::reset_plugins_lib_static_members();

		$message = '<ns9011:version xmlns:ns9011="urn:Api"></ns9011:version>';
		$data = '<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"
xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
xmlns:xsd="http://www.w3.org/2001/XMLSchema"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/"
xmlns:ns9011="urn:Api">
<SOAP-ENV:Body>
'.$message.'
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>';
		$this->request->setHeader('Content-Type', 'Content-Type: text/xml;charset=UTF-8');

		$output = $this->request('POST', 'plugin/soap/api/index/version', $data);
		$expected_version = '0.0.8';
		$expected = '<SOAP-ENV:Body><ns1:versionResponse xmlns:ns1="urn:Api"><result xsi:type="xsd:string">'.$expected_version.'</result></ns1:versionResponse></SOAP-ENV:Body>';
		$this->_assertStringContainsString($expected, $output);
	}

	/**
	 * @dataProvider database_Provider
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 */
	public function test_api_index_sendMessage($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '1';
				$_SESSION['level'] = 'admin';
				$_SESSION['username'] = 'kalkun';
			}
		);

		$dbsetup->install_plugin($this, 'soap');
		Pluginss_test::reset_plugins_lib_static_members();

		$text = 'This is test from SOAP (sendMessage)';
		$message = '<ns9011:sendMessage xmlns:ns9011="urn:Api">
<destinationNumber xsi:type="xsd:string">+1234</destinationNumber>
<message xsi:type="xsd:string">'.$text.'</message>
</ns9011:sendMessage>';
		$data = '<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"
xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
xmlns:xsd="http://www.w3.org/2001/XMLSchema"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/"
xmlns:ns9011="urn:Api">
<SOAP-ENV:Body>
'.$message.'
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>';
		$this->request->setHeader('Content-Type', 'Content-Type: text/xml;charset=UTF-8');

		$output = $this->request('POST', 'plugin/soap/api/index/sendMessage', $data);
		$expected_result = 1;
		$expected = '<SOAP-ENV:Body><ns1:sendMessageResponse xmlns:ns1="urn:Api"><result xsi:type="xsd:integer">'.$expected_result.'</result></ns1:sendMessageResponse></SOAP-ENV:Body>';
		$this->_assertStringContainsString($expected, $output);
	}

	/**
	 * @dataProvider database_Provider
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 */
	public function test_api_index_sendFlashMessage($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '1';
				$_SESSION['level'] = 'admin';
				$_SESSION['username'] = 'kalkun';
			}
		);

		$dbsetup->install_plugin($this, 'soap');
		Pluginss_test::reset_plugins_lib_static_members();

		$text = 'This is test from SOAP (sendFlashMessage)';
		$message = '<ns9011:sendFlashMessage xmlns:ns9011="urn:Api">
<destinationNumber xsi:type="xsd:string">+1234</destinationNumber>
<message xsi:type="xsd:string">'.$text.'</message>
</ns9011:sendFlashMessage>';
		$data = '<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"
xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
xmlns:xsd="http://www.w3.org/2001/XMLSchema"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/"
xmlns:ns9011="urn:Api">
<SOAP-ENV:Body>
'.$message.'
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>';
		$this->request->setHeader('Content-Type', 'Content-Type: text/xml;charset=UTF-8');

		$output = $this->request('POST', 'plugin/soap/api/index/sendFlashMessage', $data);
		$expected_result = 1;
		$expected = '<SOAP-ENV:Body><ns1:sendFlashMessageResponse xmlns:ns1="urn:Api"><result xsi:type="xsd:integer">'.$expected_result.'</result></ns1:sendFlashMessageResponse></SOAP-ENV:Body>';
		$this->_assertStringContainsString($expected, $output);
	}

	/**
	 * @dataProvider database_Provider
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 */
	public function test_api_index_logout($db_engine)
	{
		$dbsetup = new DBSetup([
			'engine' => $db_engine,
		]);
		$dbsetup->setup_config('gammu_no_pbk_kalkun_fresh_install_manual_sql_injection');

		$this->request->setCallablePreConstructor(
			function () {
				if (session_status() === PHP_SESSION_NONE && is_cli() === FALSE)
				{
					session_start();
				}
				$_SESSION['loggedin'] = 'TRUE';
				$_SESSION['id_user'] = '1';
				$_SESSION['level'] = 'admin';
				$_SESSION['username'] = 'kalkun';
			}
		);

		$dbsetup->install_plugin($this, 'soap');
		Pluginss_test::reset_plugins_lib_static_members();

		$message = '<ns9011:logout xmlns:ns9011="urn:Api"></ns9011:logout>';
		$data = '<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"
xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
xmlns:xsd="http://www.w3.org/2001/XMLSchema"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/"
xmlns:ns9011="urn:Api">
<SOAP-ENV:Body>
'.$message.'
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>';
		$this->request->setHeader('Content-Type', 'Content-Type: text/xml;charset=UTF-8');

		// Since there is no session open, because phpunit tests are run in CLI,
		// closing the session will throw an exception.
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('session_destroy(): Trying to destroy uninitialized session');
		// Prevent: "Test code or tested code did not close its own output buffers"
		ob_end_flush();
		ob_get_clean();

		$output = $this->request('POST', 'plugin/soap/api/index/logout', $data);

		/*$expected_result = 1;
		$expected = '<SOAP-ENV:Body><ns1:logoutResponse xmlns:ns1="urn:Api"><result xsi:type="xsd:integer">'.$expected_result.'</result></ns1:logoutResponse></SOAP-ENV:Body>';
		$this->_assertStringContainsString($expected, $output);*/
	}
}
