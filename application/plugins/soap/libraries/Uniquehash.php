<?php
/**
 *	@Author: bullshit "oskar@biglan.at"
 *	@Copyright: bullshit, 2010
 *	@License: GNU General Public License
*/

class Uniquehash {
	private $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVEXYZ-_0123456789";
	private $len;
 
	public function __construct($length)
	{
		$this->setLength($length);
	}

	public function setLength($length)
	{
		$this->len = (int)$length;
	}
	 
	public function getLength()
	{
		return $this->len;
	}
	 
	public function getChars()
	{
		return $this->chars;
	}
	
	public function setChars($chars)
	{
		$this->chars = (string)$chars;
	}
 
	public function getHash()
	{
		$hash = "";
		for($i = 0; $i < $this->len; $i++)
		{
			$hash .= $this->chars{mt_rand(0, strlen($this->chars)-1)};
		}
		return $hash;
	}
}
?>
