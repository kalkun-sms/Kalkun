<?php


class CI_Jsonrpc {	
	var $server;
	var $client;
	
	function CI_Jsonrpc() {
		
	}
	
	function get_server() {
		if(!isset($this->server)) {
			$this->server = new JSON_RPC_Server();
		}
		return $this->server;
	}
	
	function get_client() {
		if(!isset($this->client)) {
			$this->client = new JSON_RPC_Client();
		}
		return $this->client;
	}
}

class JSON_RPC_Message {
	var $JSON_RPC_VERSION		= '1.1';
	var $JSON_RPC_ID;	// for backwards compatibility
	
	var $CONTENT_LENGTH_KEY 	= 'Content-Length';
	var $CONTENT_TYPE_KEY 		= 'Content-Type';
	var $CONNECTION_KEY 		= 'Connection';
	
	var $content_length;
	var $content_type			= 'application/json';
	var $connection				= 'Close';
	
	var $error_code				= '';
	var $error_message			= '';

	var $raw_data;
	var $data_object;
	
	var $parser;
	var $VALUE_MAPPINGS = array();
	
	function JSON_RPC_Message() {
		$this->parser = new JSON_RPC_Parser();		
		$this->JSON_RPC_ID = 'ID_'.rand();
		
		$this->VALUE_MAPPINGS = array(
				$this->CONTENT_LENGTH_KEY	=> 'content_length',
				$this->CONTENT_TYPE_KEY		=> 'content_type',
				$this->CONNECTION_KEY		=> 'connection',
			);
	}
	
	function create_header($key, $value) {
		return "$key: $value\r\n";
	}
	function parse_header($header) {		
		if(preg_match('/(.+):\s+(.+)/', $header, $matches)) {
			return array($matches[1],$matches[2]);			
		}
		return false;
	}
}

class JSON_RPC_Request extends JSON_RPC_Message {
	var $HOST_KEY		= 'Host';
	var $USER_AGENT_KEY = 'User-Agent';
	var $ACCEPT_KEY		= 'Accept';
	
	var $host;
	var $user_agent		= 'CodeIgniter JSON RPC Client';
	var $accept			= 'application/json,application/javascript,text/javascript';
	
	var $path;
	var $remote_method;
	var $request_method;
	
	function JSON_RPC_Request() {
		parent::JSON_RPC_Message();
		
		$this->VALUE_MAPPINGS[$this->HOST_KEY]			= 'host';
		$this->VALUE_MAPPINGS[$this->USER_AGENT_KEY]	= 'user_agent';
		$this->VALUE_MAPPINGS[$this->ACCEPT_KEY]		= 'accept';
	}
	
	function create_request() {
		$req = '';
		
		if($this->request_method == 'POST') {
			$data = array();
			$data['version']	= $this->JSON_RPC_VERSION;
			$data['id']			= $this->JSON_RPC_ID;
			$data['method']		= $this->remote_method;
			if(isset($this->data_object)) {
				$data['params'] = $this->data_object;				
			}
			
			$data = $this->parser->encode($data);
			$this->content_length = strlen($data);
			
			$req .= "POST {$this->path} HTTP/1.1\r\n";
			$req .= $this->create_header($this->USER_AGENT_KEY, $this->user_agent);
			$req .= $this->create_header($this->HOST_KEY, $this->host);
			$req .= $this->create_header($this->CONTENT_TYPE_KEY, $this->content_type);
			$req .= $this->create_header($this->CONTENT_LENGTH_KEY, $this->content_length);
			$req .= $this->create_header($this->ACCEPT_KEY, $this->accept);
			$req .= $this->create_header($this->CONNECTION_KEY, $this->connection);
			$req .= "\r\n";
			$req .= $data;
		} else {
			$req .= 'GET ';
			$req .= ($this->path == '/') ? '' : $this->path;
			$req .= "/{$this->remote_method}?";

			if(isset($this->data_object)) {
				foreach($this->data_object as $param_key=>$param_value) {
					if(!is_array($param_value) && !is_object($param_value)) {
						$req .= "$param_key=$param_value&";					
					}
				}				
			}
			
			$req = substr($req, 0, -1); // rip off trailing ? or &
			$req .= " HTTP/1.1\r\n";
			
			$req .= $this->create_header($this->USER_AGENT_KEY, $this->user_agent);
			$req .= $this->create_header($this->HOST_KEY, $this->host);
			$req .= $this->create_header($this->ACCEPT_KEY, $this->accept);
			$req .= $this->create_header($this->CONNECTION_KEY, $this->connection);
			$req .= "\r\n";
		}
		
		$this->raw_data = $req;
		
		return $req;
	}
}

class JSON_RPC_Response extends JSON_RPC_Message {
	var $ERROR_CODES = array(
			'invalid_json'			=> 1,
			'response_not_ok'		=> 2,
			'response_malformed'	=> 3,
		);
	var $ERROR_MESSAGES = array(
			'invalid_json' 			=> 'The server responded with an invalid JSON object',
			'response_not_ok' 		=> '...',
			'response_malformed' 	=> 'The server responded with a malformed HTTP request'
		);
	
	var $SERVER_KEY = 'Server';
	var $CACHE_CONTROL_KEY = 'Cache-Control';
	
	var $server;
	var $cache_control;
	
	var $http_version;
	var $response_code;
	
	var $error_code = '';
	var $error_message = '';
	
	function JSON_RPC_Response($message = '', $error_code = '', $error_message = '') {
		parent::JSON_RPC_Message();
		
		$this->raw_data = $message;
		$this->error_code = $error_code;
		$this->error_message = $error_message;
		
		$this->VALUE_MAPPINGS[$this->SERVER_KEY]		= 'server';
		$this->VALUE_MAPPINGS[$this->CACHE_CONTROL_KEY]	= 'cache_control';		
	}
	
	function has_errors() {
		return (strlen($this->error_code) > 0);
	}
		
	function parse_response() {
		if(strncmp($this->raw_data, 'HTTP', 4) == 0) {
			preg_match('/^HTTP\/([0-9\.]+) (\d+) /', $this->raw_data, $response);
			
			$this->http_version  = $response[1];
			$this->response_code = $response[2];
			
			if($this->response_code != '200') {
				$this->error_code = $this->ERROR_CODES['response_not_ok'];
				$this->error_message = substr($this->raw_data, 0, strpos($this->raw_data, "\n")-1);
				return false;
			}
		} else {
			$this->error_code = $this->ERROR_CODES['response_malformed'];
			$this->error_message = $this->ERROR_MESSAGES['response_malformed'];
			return false;
		}
		
		
		$lines = explode("\r\n", $this->raw_data);
		array_shift($lines); // remove first line, as it's not technically a header

		while (($line = array_shift($lines))) {
			if(strlen($line) < 1) { break; }

			$header = $this->parse_header($line);
			
			//echo $this->VALUE_MAPPINGS[$header[0]];
			//$k = $this->VALUE_MAPPINGS[$header[0]];
			//echo $this->$k;
			
			if(isset($this->VALUE_MAPPINGS[$header[0]])) {
				$k = $this->VALUE_MAPPINGS[$header[0]];

				$this->$k = $header[1];
			}				

		}
		$data = implode("\r\n", $lines);

		$this->data_object = $this->parser->decode($data);

		if(!is_object($this->data_object) || is_null($this->data_object)) {
			$this->error_code = $this->ERROR_CODES['invalid_json'];
			$this->error_message = $this->ERROR_MESSAGES['invalid_json'];
			return false;
		}
		
		return true;
	}
}

class JSON_RPC_Server_Response extends JSON_RPC_Message {
	var $SERVER_KEY = 'Server';
	
	var $server = 'CodeIgniter JSON RPC Server';
	
	var $id;
	var $error;
	
	var $ERROR_CODES = array(
		'bad_call'=>array(
			'code'=> 000,
			'name'=>'Bad call',
			'message'=> 'The procedure call is not valid.'
			),
		'parse_error'=>array(
			'code'=> 000,
			'name'=> 'Parse error',
			'message'=> 'An error occurred on the server while parsing the JSON text comprising the procedure call.'
			),
		'procedure_not_found'=>array(
			'code'=> 000,
			'name'=> 'Procedure not found',
			'message'=> 'The call is valid but the procedure identified by the call could not be located on the service.'
			),
		'service_error'=>array(
			'code'=> 000,
			'name'=> 'Service error',
			'message'=> 'The call is valid, but a general error occurred during the procedure invocation.'
			)
		);
	
	function JSON_RPC_Server_Response($data_object = null) {
		parent::JSON_RPC_Message();
		
		if($data_object != null) {
			$this->data_object = $data_object;			
		}
	}
	
	function set_error($error) {
		if(is_string($error)) {
			$this->error = $this->ERROR_CODES[$error];			
		} else if(is_array($error)) {
			$this->error = $error;
		}
	}
	
	function create_server_response() {
		$data = array();
		$data['version']	= $this->JSON_RPC_VERSION;

		if(isset($this->id)) { $data['id'] = $this->id; }
		if(isset($this->error)) { $data['error'] = $this->error; }
		else { $data['result'] = $this->data_object; }
		
		$data = $this->parser->encode($data);
		$this->content_length = strlen($data);
		
		header("HTTP/1.1 200 OK\r\n");
		header($this->create_header($this->SERVER_KEY, $this->server));
		header($this->create_header($this->CONNECTION_KEY, $this->connection));
//		header($this->create_header($this->CONTENT_TYPE_KEY, $this->content_type));
		header($this->create_header($this->CONTENT_LENGTH_KEY, $this->content_length));
		
		$this->raw_data = $data;
		
		return $data;
	}
}

class JSON_RPC_Server_Request extends JSON_RPC_Message {
	var $ERROR_CODES = array(
			'invalid_json' => 1
		);
	var $ERROR_MESSAGES = array(
			'invalid_json' => 'The server responded with an invalid JSON object'
		);
	
	var $error_code = '';
	var $error_message = '';
	
	function JSON_RPC_Server_Request($message = '', $error_code = '', $error_message = '') {
		parent::JSON_RPC_Message();
		
		$this->raw_data = $message;
		$this->error_code = $error_code;
		$this->error_message = $error_message;	
	}
	
	function has_errors() {
		return (strlen($this->error_code) > 0);
	}
		
	function parse_response() {
		$this->data_object = $this->parser->decode($this->raw_data);

		if(!is_object($this->data_object) || is_null($this->data_object)) {
			$this->error_code = $this->ERROR_CODES['invalid_json'];
			$this->error_message = $this->ERROR_MESSAGES['invalid_json'];
			return false;
		}
		
		return true;
	}
}

class JSON_RPC_Client {
	var $request;
	var $response;

	var $port		= 80;
	var $timeout	= 5;
	
	function JSON_RPC_Client() {
		$this->request = new JSON_RPC_Request();
	}
	function server($url, $request_method = 'POST', $port = 80) {
		if (substr($url, 0, 4) != "http") {
			$url = "http://".$url;
		}
		
		$parts = parse_url($url);
		
		$path = ( ! isset($parts['path'])) ? '/' : $parts['path'];
		
		if (isset($parts['query']) && $parts['query'] != '') {
			$path .= '?'.$parts['query'];
		}
		
		$this->request->path = $path;
		$this->request->host = $parts['host'];
		$this->request->request_method = $request_method;
		$this->port = $port;
	}
	function method($remote_method) {
		$this->request->remote_method = $remote_method;
	}
	function request($request_parameters) {
		$this->request->data_object = $request_parameters;
	}
	function timeout($timeout = 5) {
		$this->timeout = $timeout;
	}
	function send_request() {
		$request = $this->request->create_request();
		
		$fp = @fsockopen($this->request->host, $this->port, $errno, $errstr, $this->timeout);

		if(!$fp) {
			$this->response = new JSON_RPC_Response('', $errno, $errstr);
			return false;
		}

	    fwrite($fp, $request);
		$response_text = '';
		while (!feof($fp)) {
	        $response_text .= fgets($fp, 128);
	    }
	
		$this->response = new JSON_RPC_Response($response_text);
		fclose($fp);

		return $this->response->parse_response();		
	}
	function get_response() {
		return $this->response;
	}
	function get_response_object() {
		return $this->response->data_object;
	}
}

class JSON_RPC_Server {
	var $php_types_to_jsonrpc_types = array(
		'Boolean'=>'bit',
		'Number'=>'num',
		'String'=>'str',
		'Array'=>'arr',
		'Object'=>'obj'
		);
	
	var $methods = array();
	var $object = false;
	
	var $service_name 		= 'CodeIgniter JSON RPC Server';
	var $service_sd_version = '1.0';
	var $service_id			= '';
	var $service_version	= '1.0';
	var $service_summary	= 'A JSON RPC Server for CodeIgniter. Written by Nick Husher (nhusher@bear-code.com)';
	var $service_help		= '';
	var $service_address	= '';
	
	function JSON_RPC_Server() {
		$this->methods['system.describe'] = array(
			'function'=>'this.describe',
			'summary'=>'Display relevant information about the JSON RPC server.',
			'help'=>'http://json-rpc.org',
			'return'=>array('type'=>'obj')
			);
			
		$CI =& get_instance();
		$CI->load->helper('url');
		
		$this->service_address = current_url();
		$this->service_id = current_url();
	}
	
	function define_methods($methods) {
		
		foreach($methods as $methodName=>$methodProperties) {
			$this->methods[$methodName] = $methodProperties;
		}
	}
	function set_object($object) {
		if(is_object($object)) {
			$this->object =& $object;
		}
	}
	function serve() {
		global $HTTP_RAW_POST_DATA;
		
		$incoming = new JSON_RPC_Server_Request($HTTP_RAW_POST_DATA);
		
		if(!$incoming->parse_response()) {
			$response = $this->send_error('parse_error');
			echo $response->create_server_response();
			return;
		}
		
		$response = $this->_execute($incoming->data_object);
		
		echo $response->create_server_response();
	}
	
	function send_response($object) {
		return new JSON_RPC_Server_Response($object);
	}
	function send_error($error) {
		$r = new JSON_RPC_Server_Response();
		$r->set_error($error);
		
		return $r;
	}
	
	function _execute($request_object) {
		// check if the method is defined on the server
		if(!isset($this->methods[$request_object->method])) {
			return $this->send_error('procedure_not_found');
		}
		$method_definition = $this->methods[$request_object->method];
		
		// check if we have a function definition
		if(!isset($method_definition['function'])) {
			return $this->send_error('procedure_not_found');			
		}
		
		$function_name = explode('.',$method_definition['function']);
		$is_system_call = ($function_name[0] == 'this');
		
		// check if the function/object is callable
		if($is_system_call) {
			if(!isset($function_name[1]) || !is_callable(array($this, $function_name[1]))) {
				$r = $this->send_error('service_error');
//				$r->error['code'] = 001;
				return $r;
			}
		} else {
			if(!isset($function_name[1]) || !is_callable(array($function_name[0], $function_name[1]))) {
				$r = $this->send_error('service_error');
//				$r->error['code'] = 002;
				return $r;
			}
		}
		
		// check parameters
		if(isset($request_object->params)) {
			$parameters = $request_object->params;
		} else {
			$parameters = array();
		}
			
		if(isset($method_definition['parameters']) && is_array($method_definition['parameters'])) {
			$parameters = $method_definition['parameters'];

			for($i = 0; $i < count($parameters); $i++) {
				$current_parameter = $parameters[$i];
				if(!isset($current_parameter['name'])) {
					$r = $this->send_error('service_error');
//					$r->error['code'] = 003;
					return $r;
				}

				if(!isset($parameters->$current_parameter['name'])) {
					return $this->send_error('bad_call');
				}

				if(isset($current_parameter['type']) && 
					gettype($parameters->$current_parameter['name']) != $current_parameter['type'])
				{
					return $this->send_error('bad_call');	
				}
			}
		}
				
		// call the function
		if($is_system_call) {
			$response = $this->$function_name[1]($parameters);
		} else {
			if(is_object($this->object)) {
				$response = $this->object->$function_name[1]($parameters);				
			} else {
				$r = $this->send_error('service_error');
//				$r->error['code'] = 003;
				return $r;
			}
		}
		
		if(isset($request_object->id)) {
			$response->id = $request_object->id;			
		}
		
		return $response;
	}
	
	// system functions
	function describe() {
		$method_property_names = array(
			'parameters'=>'params',
			'summary'=>'summary',
			'help'=>'help',
			'return'=>'return'
			);
		
		$description = array();
		
		$description['sdversion']	= $this->service_sd_version;
		$description['name']		= $this->service_name;
		$description['id']			= $this->service_id;
		$description['version']		= $this->service_version;
		$description['summary']		= $this->service_summary;
		$description['help']		= $this->service_help;
		$description['address']		= $this->service_address;
		
		
		$description['procs'] = array();
		foreach($this->methods as $method_name=>$method_properties) {
			$method = array();
			$method['name'] = $method_name;
		
			foreach($method_property_names as $name=>$property_name) {
				if(isset($method_properties[$property_name])) {
					$method[$property_name] = $method_properties[$name];					
				} else if($name == 'parameters' || $name == 'return') {
					$method[$property_name] = 'any';
				}
			}
			
			$description['procs'][] = $method;
		}
		
		return $this->send_response($description);
	}
}

class JSON_RPC_Parser {
	function encode($val) {
		return json_encode($val);
	}
	function decode($val) {
		return json_decode($val);
	}
}

?>