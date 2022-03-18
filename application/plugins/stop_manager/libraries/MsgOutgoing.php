<?php

/**
 * Kalkun
 * An open source web based SMS Management
 *
 * @package		Kalkun
 * @author		Kalkun Dev Team
 * @license		https://spdx.org/licenses/GPL-3.0-or-later.html
 * @link		https://kalkun.sourceforge.io/
 */
// ------------------------------------------------------------------------

/**
 * MsgOutgoing Class
 *
 * @package		Kalkun
 * @subpackage	Plugin
 */
namespace Kalkun\Plugins\StopManager;
defined('BASEPATH') OR exit('No direct script access allowed');

class MsgOutgoing {

	const TYPE_NOT_SET = MsgIncoming::TYPE_NOT_SET;
	const IGNORE_STOP_MANAGER = MsgIncoming::IGNORE_STOP_MANAGER;

	private $party = NULL;
	private $cmd = NULL;
	private $type = NULL;
	private $origMsg = NULL;
	private $cleanedMsg = NULL;
	private $config = NULL;

	public function __construct(array $sms)
	{
		$this->origMsg = $sms[1]['message'];
		$this->party = $sms[0];
		$this->config = Config::getInstance();
		$this->parseOutgoingMessage();
	}

	public function getParty()
	{
		return $this->party;
	}

	public function getType()
	{
		return $this->type;
	}

	public function getOrigMsg()
	{
		return $this->origMsg;
	}

	public function getInsertDate()
	{
		return $this->insertDate;
	}

	public function setParty($party)
	{
		$this->party = $party;
	}

	public function getCmd()
	{
		return $this->cmd;
	}

	public function setCmd($cmd)
	{
		$this->cmd = strtoupper($cmd);
	}

	public function setType($type)
	{
		$this->type = ($this->config->isTypeEnabled()) ? strtolower($type) : self::TYPE_NOT_SET;
	}

	public function setOrigMsg($origMsg)
	{
		$this->origMsg = $origMsg;
	}

	public function getCleanedMsg()
	{
		return $this->cleanedMsg;
	}

	public function parseOutgoingMessage()
	{
		// Parse the outgoing message to find its Type
		// Be careful! Kalkun may append $config['append_username_message'] to all messages.
		$CI = &get_instance();
		$ret_match = NULL;
		if ($CI->config->item('append_username'))
		{
			$ret_match = preg_match('/^(.*)~(.+)~.*/', $this->origMsg, $matches);
		}
		else
		{
			$ret_match = preg_match('/^(.*)~(.+)~$/', $this->origMsg, $matches);
		}

		// Get the type of the SMS (rappel, annul...)
		if ($ret_match)
		{
			if ($this->config->isTypeEnabled()
				&& (! empty($matches[2]))
				&& in_array($matches[2], $this->config->getKeywordsType()))
			{
				$this->type = $matches[2];
			}


			$this->cleanedMsg = (! empty($matches[1])) ? trim($matches[1]) : $this->origMsg;
		}

		if (is_null($this->type))
		{
			// type of SMS (for filtering) is not set yet.
			// The message is sent    if we enabled  the use of type ($config['enable_type'])
			// The message is dropped if we disabled the use of type ($config['enable_type']) and if it is in blacklist
			if ( ! $this->config->isTypeEnabled())
			{
				// Will drop all numbers that are in stop_manager whatever the value of type
				//$type = "%";
				// Will drop all numbers that are in stop_manager having been recorded as TYPE_NOT_SET_SO_STOP_ALL
				$this->type = MsgIncoming::TYPE_NOT_SET;
			}
			else
			{
				// IGNORE_STOP_MANAGER is just a fake value that should never match something in the table,
				// this is to keep the message
				$this->type = MsgIncoming::IGNORE_STOP_MANAGER;
			}
		}

		// Build the cleaned message = Message without the "tag" to identify the
		// type of the outgoing message.
		// eg. "~rappel~" at the end of the message
		if ($ret_match
			&& (! empty($matches[1]))
			&& $this->config->isTypeEnabled()
			&& (! empty($matches[2]))
			&& in_array($matches[2], $this->config->getKeywordsType()))
		{
			$this->cleanedMsg = trim($matches[1]);
		}
		else
		{
			$this->cleanedMsg = $this->origMsg;
		}
	}

}
