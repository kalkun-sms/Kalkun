<?php
class Tools extends Controller {

	function index()
	{
		//echo "IT WORKS\n";
		//print "DOCUMENT_ROOT: ".$_SERVER['DOCUMENT_ROOT']."\n";
		//print "SCRIPT_FILENAME: ".$_SERVER['SCRIPT_FILENAME'];
		//print "REQUEST_URI: ".$_SERVER['REQUEST_URI'];
		//print "QUERY_STRING: ".$_SERVER['QUERY_STRING'];
		//print "REQUEST_METHOD: ".$_SERVER['REQUEST_METHOD'];
		//print "SCRIPT_NAME: ".$_SERVER['SCRIPT_NAME'];
		
		echo site_url();
		//echo $this->uri->uri_string();
	}

	public function message($to = 'World')
	{
		echo "Hello {$to}!".PHP_EOL;
		echo $this->uri->uri_string();
	}
}
?>