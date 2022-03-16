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
 * MsgIncoming Class
 *
 * @package		Kalkun
 * @subpackage	Plugin
 */
namespace Kalkun\Plugins\StopManager;
defined('BASEPATH') OR exit('No direct script access allowed');

require_once (APPPATH . 'plugins/Plugin_helper.php');

class MsgIncoming {

	const TYPE_NOT_SET = "TYPE_NOT_SET_SO_STOP_ALL";
	const IGNORE_STOP_MANAGER = "IGNORE_STOP_MANAGER";

	private $party = NULL;
	private $cmd = NULL;
	private $type = NULL;
	private $origMsg = NULL;
	private $insertDate = NULL;
	private $config = NULL;
	private $is_lang_loaded = NULL;

	public function __construct(Object $sms)
	{
		$this->origMsg = $sms->TextDecoded;
		$this->party = $sms->SenderNumber;
		$this->config = Config::getInstance();
		$this->parseStopMessage();
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

	public
			function getCmd()
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

	public function setInsertDate($insertDate)
	{
		$this->insertDate = $insertDate;
	}

	public function isStopMessage()
	{
		return ($this->getCmd() !== NULL);
	}

	public function isValidStopMessage()
	{
		return ($this->isOptOut() || $this->isOptIn());
	}

	public function parseStopMessage()
	{
		$ret = NULL;

		$types_reg = implode('|', $this->config->getValidTypes());
		$cmds_reg = implode('|', $this->config->getValidCmds());

		if ($this->config->isTypeEnabled())
		{
			$ret = preg_match('/\b(' . $cmds_reg . ')\s*(' . $types_reg . ')\b/i', $this->origMsg, $matches, PREG_UNMATCHED_AS_NULL);
		}
		else
		{
			$ret = preg_match('/\b(' . $cmds_reg . ')\b/i', $this->origMsg, $matches, PREG_UNMATCHED_AS_NULL);
		}

		if ($ret === 1)
		{
			if ( ! empty($matches[1]))
			{
				$this->setCmd($matches[1]);
			}
			if ( ! empty($matches[2]))
			{
				$this->setType($matches[2]);
			}
			else
			{
				$this->setType("EMPTY?");
			}
		}
	}

	public function isOptOut()
	{
		return in_array($this->getCmd(), $this->config->getKeywordsOptOut());
	}

	public function isOptIn()
	{
		return (in_array($this->getCmd(), $this->config->getKeywordsOptIn()) && $this->config->getConfig('enable_optin'));
	}

	private function getAutoReplyMsgConfirm()
	{
		if ($this->isOptOut())
		{
			return $this->getAutoReplyMsgConfirmOptOut();
		}
		if ($this->isOptIn())
		{
			return $this->getAutoReplyMsgConfirmOptIn();
		}
	}

	private function getAutoReplyMsgConfirmOptIn()
	{
		return tr(
				'{0} taken into account. To opt-out, send "{1}".',
				NULL,
				($this->config->getConfig('enable_type')) ? $this->cmd . ' ' . $this->type : $this->cmd,
				($this->config->getConfig('enable_type')) ? $this->config->getKeywordsOptOut()[0] . ' ' . $this->type : $this->config->getKeywordsOptOut()[0]
		);
	}

	private function getAutoReplyMsgConfirmOptOut()
	{
		if ($this->config->getConfig('enable_optin'))
		{
			return tr(
					'{0} taken into account. To opt-in again, send "{1}".',
					NULL,
					($this->config->getConfig('enable_type')) ? $this->cmd . ' ' . $this->type : $this->cmd,
					($this->config->getConfig('enable_type')) ? $this->config->getKeywordsOptIn()[0] . ' ' . $this->type : $this->config->getKeywordsOptIn()[0]
			);
		}
		else
		{
			return tr(
					'{0} taken into account.',
					NULL,
					($this->config->getConfig('enable_type')) ? $this->cmd . ' ' . $this->type : $this->cmd
			);
		}
	}

	private function getAutoReplyMsgInvalidWithOptInWithType()
	{
		return tr(
				'Request not valid ({0}). Send "{1} or {2} <type>". Possible values for <type> are: {3}. For example "{4}".',
				NULL,
				$this->getOrigMsg(),
				$this->config->getKeywordsOptOut()[0],
				$this->config->getKeywordsOptIn()[0],
				implode(', ', $this->config->getValidTypes()),
				$this->config->getKeywordsOptOut()[0] . ' ' . $this->config->getValidTypes()[0]
		);
	}

	private function getAutoReplyMsgInvalidWithOptInWithoutType()
	{
		return tr(
				'Request not valid ({0}). Send "{1}" or "{2}". For example "{3}".',
				NULL,
				$this->getOrigMsg(),
				$this->config->getKeywordsOptOut()[0],
				$this->config->getKeywordsOptIn()[0],
				$this->config->getKeywordsOptOut()[0]
		);
	}

	private function getAutoReplyMsgInvalidWithoutOptInWithType()
	{
		return tr(
				'Request not valid ({0}). Send "{1} <type>". Possible values for <type> are: {2}. For example "{3}".',
				NULL,
				$this->getOrigMsg(),
				$this->config->getKeywordsOptOut()[0],
				implode(', ', $this->config->getValidTypes()),
				$this->config->getKeywordsOptOut()[0] . ' ' . $this->config->getValidTypes()[0]
		);
	}

	private function getAutoReplyMsgInvalidWithoutOptInWithoutType()
	{
		return tr(
				'Request not valid ({0}). Send "{1}".',
				NULL,
				$this->getOrigMsg(),
				$this->config->getKeywordsOptOut()[0]
		);
	}

	private function getAutoReplyMsgInvalid()
	{
		if ($this->config->getConfig('enable_type'))
		{
			if ($this->config->getConfig('enable_optin'))
			{
				return $this->getAutoReplyMsgInvalidWithOptInWithType();
			}
			else
			{
				return $this->getAutoReplyMsgInvalidWithoutOptInWithType();
			}
		}
		else
		{
			if ($this->config->getConfig('enable_optin'))
			{
				return $this->getAutoReplyMsgInvalidWithOptInWithoutType();
			}
			else
			{
				return $this->getAutoReplyMsgInvalidWithoutOptInWithoutType();
			}
		}
	}

	public function getAutoReplyMsg()
	{
		if ( ! $this->is_lang_loaded)
		{
			// Load Translation helper functions
			$CI = &get_instance();
			$CI->load->helper(['language', 'i18n']);

			// We cannot determine the language of a specific user since this is called on incoming message
			// So the language to use by this robot is read from plugin config
			\Plugin_helper::load_lang('stop_manager', $this->config->getConfig('autoreply_language'));
			$this->is_lang_loaded = TRUE;
		}
		if ($this->isValidStopMessage())
		{
			return $this->getAutoReplyMsgConfirm();
		}
		else
		{
			return $this->getAutoReplyMsgInvalid();
		}
	}

}
