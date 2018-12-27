<?php

namespace Bitrix\Mail\ImapCommands;

use Bitrix\Mail\Helper\MessageFolder;
use Bitrix\Main;
use Bitrix\Mail;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class SyncInternalManager
 * @package Bitrix\Mail\ImapCommands
 */
class SyncInternalManager
{
	const FLAG_UNSEEN = 'unseen';
	const FLAG_SEEN = 'seen';

	protected $userId;
	protected $mailbox;
	protected $mailboxId;
	protected $messagesIds;
	protected $messages;
	private $isInit;
	/** @var Repository */
	protected $repository;

	public function __construct($mailboxId, $messagesIds, $userId = null)
	{
		$this->mailboxId = $mailboxId;
		if (!is_array($messagesIds))
		{
			$messagesIds = [$messagesIds];
		}
		$this->messagesIds = $messagesIds;
		$this->userId = $userId;
		$this->repository = $this->getRepository();
	}

	public function setUserId($userId)
	{
		$this->userId = $userId;
	}

	protected function getRepository()
	{
		return new Repository($this->mailboxId, $this->messagesIds);
	}

	protected function getMailClientHelper()
	{
		return Mail\Helper\Mailbox::createInstance($this->mailboxId);
	}

	protected function initData($folderType = null)
	{
		if ($this->isInit)
		{
			return new Main\Result();
		}
		$this->isInit = true;
		$result = new Main\Result();
		foreach ($this->messagesIds as $index => $messagesId)
		{
			if (strlen($messagesId) !== 32)
			{
				return $result->addError(new Main\Error(Loc::getMessage('MAIL_CLIENT_WRONG_PARAMETERS'),
					'MAIL_CLIENT_WRONG_PARAMETERS'));
			}
		}
		if (!is_numeric($this->mailboxId))
		{
			return $result->addError(new Main\Error(Loc::getMessage('MAIL_CLIENT_WRONG_PARAMETERS'),
				'MAIL_CLIENT_WRONG_PARAMETERS'));
		}

		$this->mailbox = $this->repository->getMailbox();
		if (!$this->mailbox)
		{
			return $result->addError(new Main\Error(Loc::getMessage('MAIL_CLIENT_MAILBOX_NOT_FOUND'),
				'MAIL_CLIENT_MAILBOX_NOT_FOUND'));
		}

		if ($folderType)
		{
			$folder = $this->getFolderNameByType($folderType);
			if (!$folder)
			{
				$errorCode = 'MAIL_CLIENT_' . ($folderType == MessageFolder::TRASH ? 'TRASH' : 'SPAM') . '_FOLDER_NOT_SELECTED_ERROR';
				return $result->addError(new Main\Error(
					Loc::getMessage($errorCode),
					$errorCode));
			}
		}
		if (is_null($this->messages))
		{
			$this->messages = $this->repository->getMessages();
		}

		if (empty($this->messages))
		{
			return $result->addError(new Main\Error(Loc::getMessage('MAIL_CLIENT_MESSAGES_NOT_FOUND'),
				'MAIL_CLIENT_MESSAGES_NOT_FOUND'));
		}
		$this->fillMessagesEmails();

		$folders = [];
		foreach ($this->messages as $index => $message)
		{
			if (in_array($message['ID'], $this->messagesIds, true))
			{
				$folders[$message['DIR_MD5']] = $message['DIR_MD5'];
			}
		}
		if (count($folders) > 1)
		{
			return $result->addError(new Main\Error(Loc::getMessage('MAIL_CLIENT_MESSAGES_MULTIPLE_FOLDERS'),
				'MAIL_CLIENT_MESSAGES_MULTIPLE_FOLDERS'));
		}
		return $result;
	}

	protected function getFolderNameByType($folderType)
	{
		return MessageFolder::getFolderNameByType($folderType, $this->mailbox['OPTIONS']);
	}

	protected function fillMessagesEmails()
	{
		foreach ($this->messages as $index => $message)
		{
			$address = new Main\Mail\Address($message['FIELD_FROM']);
			$this->messages[$index]['EMAIL'] = $address->getEmail();
		}
	}
}