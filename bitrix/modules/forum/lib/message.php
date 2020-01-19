<?php
namespace Bitrix\Forum;

use Bitrix\Forum\Internals\Fabric;
use Bitrix\Main;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\ORM\Data\Result;
use Bitrix\Main\ORM\EntityError;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\EnumField;
use Bitrix\Main\ORM\Fields\FieldError;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Type\DateTime;
use Bitrix\Tasks\Integration;

/**
 * Class MessageTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> FORUM_ID int mandatory
 * <li> TOPIC_ID int mandatory
 * <li> USE_SMILES bool optional default 'Y'
 * <li> NEW_TOPIC bool optional default 'N'
 * <li> APPROVED bool optional default 'Y'
 * <li> SOURCE_ID string(255) mandatory default 'WEB'
 * <li> POST_DATE datetime mandatory
 * <li> POST_MESSAGE string optional
 * <li> POST_MESSAGE_HTML string optional
 * <li> POST_MESSAGE_FILTER string optional
 * <li> POST_MESSAGE_CHECK string(32) optional
 * <li> ATTACH_IMG int optional
 * <li> PARAM1 string(2) optional
 * <li> PARAM2 int optional
 * <li> AUTHOR_ID int optional
 * <li> AUTHOR_NAME string(255) optional
 * <li> AUTHOR_EMAIL string(255) optional
 * <li> AUTHOR_IP string(255) optional
 * <li> AUTHOR_REAL_IP string(128) optional
 * <li> GUEST_ID int optional
 * <li> EDITOR_ID int optional
 * <li> EDITOR_NAME string(255) optional
 * <li> EDITOR_EMAIL string(255) optional
 * <li> EDIT_REASON string optional
 * <li> EDIT_DATE datetime optional
 * <li> XML_ID string(255) optional
 * <li> HTML string optional
 * <li> MAIL_HEADER string optional
 * </ul>
 *
 * @package Bitrix\Forum
 **/
class MessageTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_forum_message';
	}

	public static function getUfId()
	{
		return 'FORUM_MESSAGE';
	}

	private static $post_message_hash = [];
	private static $messageById = [];
	private static $customStorage = [];

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			(new IntegerField("ID", ["primary" => true, "autocomplete" => true])),
			(new IntegerField("FORUM_ID", ["required" => true])),
			(new IntegerField("TOPIC_ID", ["required" => true])),
			(new BooleanField("USE_SMILES", ["values" => ["N", "Y"], "default_value" => "Y"])),
			(new BooleanField("NEW_TOPIC", ["values" => ["N", "Y"], "default_value" => "N"])),
			(new BooleanField("APPROVED", ["values" => ["N", "Y"], "default_value" => "Y"])),
			(new BooleanField("SOURCE_ID", ["values" => ["EMAIL", "WEB"], "default_value" => "WEB"])),
			(new DatetimeField("POST_DATE", ["required" => true, "default_value" => function(){ return new DateTime();}])),
			(new TextField("POST_MESSAGE", ["required" => true])),
			(new TextField("POST_MESSAGE_HTML")),
			(new TextField("POST_MESSAGE_FILTER")),
			(new StringField("POST_MESSAGE_CHECK", ["size" => 32])),
			(new IntegerField("ATTACH_IMG")),
			(new StringField("PARAM1", ["size" => 2])),
			(new IntegerField("PARAM2")),

			(new IntegerField("AUTHOR_ID")),
			(new StringField("AUTHOR_NAME", ["required" => true, "size" => 255])),
			(new StringField("AUTHOR_EMAIL", ["size" => 255])),
			(new StringField("AUTHOR_IP", ["size" => 255])),
			(new StringField("AUTHOR_REAL_IP", ["size" => 255])),
			(new IntegerField("GUEST_ID")),

			(new IntegerField("EDITOR_ID")),
			(new StringField("EDITOR_NAME", ["size" => 255])),
			(new StringField("EDITOR_EMAIL", ["size" => 255])),
			(new TextField("EDIT_REASON")),
			(new DatetimeField("EDIT_DATE", ["default_value" => function(){return new DateTime();}])),

			(new StringField("XML_ID", ["size" => 255])),

			(new TextField("HTML")),
			(new TextField("MAIL_HEADER")),

			(new Reference("TOPIC", TopicTable::class, Join::on("this.TOPIC_ID", "ref.ID")))
		);
	}

	public static function getFilteredFields()
	{
		return [
			"AUTHOR_NAME",
			"AUTHOR_EMAIL",
			"EDITOR_NAME",
			"EDITOR_EMAIL",
			"EDIT_REASON"
		];
	}

	public static function onBeforeAdd(Event $event)
	{
		$result = new \Bitrix\Main\ORM\EventResult();
		/** @var array $data */
		$data = $event->getParameter("fields");
		$strUploadDir = "forum";
		if (($events = GetModuleEvents("forum", "onBeforeMessageAdd", true)) && !empty($events))
		{
			foreach ($events as $ev)
			{
				if (ExecuteModuleEventEx($ev, array(&$data, &$strUploadDir)) === false)
				{
					$result->addError(new EntityError("Error [onBeforeMessageAdd]: ".serialize($ev), "onBeforeMessageAdd"));
					return $result;
				}
			}
		}

		//region Files
		if (array_key_exists("ATTACH_IMG", $data) && !empty($data["ATTACH_IMG"]))
		{
			if (!array_key_exists("FILES", $data))
			{
				$data["FILES"] = [];
			}
			$data["FILES"][] = $data["ATTACH_IMG"];
			unset($data["ATTACH_IMG"]);
			$result->unsetField("ATTACH_IMG");
		}
		if (array_key_exists("FILES", $data))
		{
			$data["FILES"] = is_array($data["FILES"]) ? $data["FILES"] : [$data["FILES"]];
			if (!empty($data["FILES"]))
			{
				$res = File::checkFiles(
					Forum::getById($data["FORUM_ID"]),
					$data["FILES"],
					[
						"FORUM_ID" => $data["FORUM_ID"],
						"TOPIC_ID" => ($data["NEW_TOPIC"] === "Y" ? 0 : $data["TOPIC_ID"]),
						"MESSAGE_ID" => 0,
						"USER_ID" => $data["AUTHOR_ID"]
					]
				);
				if (!$res->isSuccess())
				{
					$result->setErrors($res->getErrors());
				}
				else
				{
					/*@var \Bitrix\Main\ORM\Objectify\EntityObject $object*/
					$object = $event->getParameter("object");
					/*@var \Bitrix\Main\Dictionary $object->customData*/
					$object->sysSetRuntime("FILES", $data["FILES"]);
					$object->sysSetRuntime("UPLOAD_DIR", $strUploadDir);
				}
			}
			$result->unsetField("FILES");
			unset($data["FILES"]);
		}
		//endregion

		$data["POST_MESSAGE_CHECK"] = md5($data["POST_MESSAGE"] . (array_key_exists("FILES", $data) ? serialize($data["FILES"]) : ""));

		//region Deduplication
		$forum = \Bitrix\Forum\Forum::getById($data["FORUM_ID"]);
		$deduplication = null;
		if (array_key_exists("AUX", $data) && $data["AUX"] == "Y")
		{
			$deduplication = false;
			$result->unsetField("AUX");
			unset($data["AUX"]);
		}
		if (array_key_exists("DEDUPLICATION", $data))
		{
			$deduplication = $data["DEDUPLICATION"] == "Y";
			$result->unsetField("DEDUPLICATION");
			unset($data["DEDUPLICATION"]);
		}
		if ($deduplication === null)
		{
			$deduplication = $forum["DEDUPLICATION"] === "Y";
		}
		if ($deduplication && $data["NEW_TOPIC"] !== "Y")
		{
			if (self::$post_message_hash[$data["TOPIC_ID"]] === $data["POST_MESSAGE_CHECK"])
			{
				$result->addError(new EntityError(Loc::getmessage("F_ERR_MESSAGE_ALREADY_EXISTS"), "onBeforeMessageAdd"));
				return $result;
			}
		}
		self::$post_message_hash[$data["TOPIC_ID"]] = $data["POST_MESSAGE_CHECK"];
		//endregion

		$data["POST_MESSAGE"] = \Bitrix\Main\Text\Emoji::encode($data["POST_MESSAGE"]);

		//region Filter
		if (\Bitrix\Main\Config\Option::get("forum", "FILTER", "Y") == "Y")
		{
			$data["POST_MESSAGE_FILTER"] = \CFilterUnquotableWords::Filter($data["POST_MESSAGE"]);
			$filteredFields = self::getFilteredFields();
			$res = [];
			foreach ($filteredFields as $key)
			{
				$res[$key] = array_key_exists($key, $data) ? $data[$key] : "";
				if (!empty($res[$key]))
				{
					$res[$key] = \CFilterUnquotableWords::Filter($res[$key]);
					if (strlen($res[$key]) <= 0)
					{
						$res[$key] = "*";
					}
				}
			}
			$data["HTML"] = serialize($res);
		}
		//endregion


		if ($data != $event->getParameter("fields"))
		{
			$result->modifyFields($data);
		}
		return $result;
	}

	/**
	 * @param \Bitrix\Main\ORM\Event $event
	 * @return \Bitrix\Main\ORM\EventResult
	 */
	public static function onAdd(\Bitrix\Main\ORM\Event $event)
	{
		$result = new \Bitrix\Main\ORM\EventResult();
		if (\Bitrix\Main\Config\Option::get("forum", "MESSAGE_HTML", "N") == "Y")
		{
			$fields = $event->getParameter("fields");
			$object = $event->getParameter("object");

			if ($files = $object->sysGetRuntime("FILES"))
			{
				$result = File::saveFiles(
					$files,
					[
						"FORUM_ID" => $fields["FORUM_ID"],
						"TOPIC_ID" => $fields["TOPIC_ID"],
						"MESSAGE_ID" => 0,
						"USER_ID" => $fields["AUTHOR_ID"],
					],
					($object->sysGetRuntime("UPLOAD_DIR") ?: "forum/upload"));
				$object->sysSetRuntime("FILES", $files);
			}

			$parser = new \forumTextParser(LANGUAGE_ID);
			$allow = \forumTextParser::GetFeatures(\Bitrix\Forum\Forum::getById($fields["FORUM_ID"]));
			$allow["SMILES"] = ($fields["USE_SMILES"] != "Y" ? "N" : $allow["SMILES"]);
			$result->modifyFields([
				"POST_MESSAGE_HTML" => $parser->convert($fields["POST_MESSAGE_FILTER"] ?: $fields["POST_MESSAGE"], $allow, "html", $files)
			]);
		}
		return $result;
	}


	/**
	 * @param \Bitrix\Main\ORM\Event $event
	 * @return void
	 */
	public static function onAfterAdd(\Bitrix\Main\ORM\Event $event)
	{
		$id = $event->getParameter("id");
		$id = is_array($id) && array_key_exists("ID", $id) ? $id["ID"] : $id;
		$fields = $event->getParameter("fields");
		$object = $event->getParameter("object");

		if ($files = $object->sysGetRuntime("FILES"))
		{
			File::saveFiles(
				$files,
				[
					"FORUM_ID" => $fields["FORUM_ID"],
					"TOPIC_ID" => $fields["TOPIC_ID"],
					"MESSAGE_ID" => $id,
					"USER_ID" => $fields["AUTHOR_ID"],
				],
				($object->sysGetRuntime("UPLOAD_DIR") ?: "forum/upload"));
		}

		$message = self::getDataById($id);
		foreach (GetModuleEvents("forum", "onAfterMessageAdd", true) as $event)
			ExecuteModuleEventEx($event, [$id, $message, Forum::getById($message["FORUM_ID"]), Topic::getById($message["TOPIC_ID"]), $fields]);
	}

	public static function getDataById($id, $ttl = 84600)
	{
		if (!array_key_exists($id, self::$messageById))
		{
			self::$messageById[$id] = self::getList([
				"select" => ["*"],
				"filter" => ["ID" => $id],
				"cache" => [
					"ttl" => $ttl
				]
			])->fetch();
		}
		return self::$messageById[$id];
	}

	/**
	 * @param \Bitrix\Main\ORM\Event $event
	 * @return \Bitrix\Main\ORM\EventResult|void
	 * @throws \Bitrix\Main\ObjectException
	 */
	public static function onBeforeUpdate(\Bitrix\Main\ORM\Event $event)
	{
		$result = new \Bitrix\Main\ORM\EventResult();
		/** @var array $data */
		$data = $event->getParameter("fields");
		$id = $event->getParameter("id");
		$id = $id["ID"];
		$strUploadDir = "forum";
		if (($events = GetModuleEvents("forum", "onBeforeMessageUpdate", true)) && !empty($events))
		{
			foreach ($events as $ev)
			{
				if (ExecuteModuleEventEx($ev, array($id, &$data, &$strUploadDir)) === false)
				{
					$result->addError(new EntityError("Error: ".serialize($ev), "onBeforeMessageUpdate"));
					return $result;
				}
			}
		}
		if (\Bitrix\Main\Config\Option::get("forum", "FILTER", "Y") == "Y" &&
			!empty(array_intersect(self::getFilteredFields(), array_keys($data))))
		{
			$forFilter = $data;
			if (
				array_intersect(self::getFilteredFields(), array_keys($data)) !== self::getFilteredFields() &&
				($message = MessageTable::getDataById($id))
			)
			{
				$forFilter = array_merge($message, $forFilter);
			}
			$res = [];
			foreach (self::getFilteredFields() as $key)
			{
				$res[$key] = array_key_exists($key, $forFilter) ? $forFilter[$key] : "";
				if (!empty($res[$key]))
				{
					$res[$key] = \CFilterUnquotableWords::Filter($res[$key]);
					if (strlen($res[$key]) <= 0 )
					{
						$res[$key] = "*";
					}
				}
			}
			$data["HTML"] = serialize($res);
		}
		if (array_key_exists("POST_MESSAGE", $data))
		{
			$data["POST_MESSAGE"] = \Bitrix\Main\Text\Emoji::encode($data["POST_MESSAGE"]);
			if (\Bitrix\Main\Config\Option::get("forum", "FILTER", "Y") == "Y")
			{
				$data["POST_MESSAGE_FILTER"] = \CFilterUnquotableWords::Filter($data["POST_MESSAGE"]);
			}
		}
		$result->unsetField("AUX");
		$result->unsetField("DEDUPLICATION");

		//region Files
		if (array_key_exists("ATTACH_IMG", $data) && !empty($data["ATTACH_IMG"]))
		{
			if (!array_key_exists("FILES", $data))
			{
				$data["FILES"] = [];
			}
			$data["FILES"][] = $data["ATTACH_IMG"];
			unset($data["ATTACH_IMG"]);
			$result->unsetField("ATTACH_IMG");
		}
		if (array_key_exists("FILES", $data))
		{
			$data["FILES"] = is_array($data["FILES"]) ? $data["FILES"] : [$data["FILES"]];
			if (!empty($data["FILES"]))
			{
				$fileFields = $data + MessageTable::getDataById($id);
				$res = File::checkFiles(
					Forum::getById($fileFields["FORUM_ID"]),
					$data["FILES"],
					[
						"FORUM_ID" => $fileFields["FORUM_ID"],
						"TOPIC_ID" => $fileFields["TOPIC_ID"],
						"MESSAGE_ID" => $id,
						"USER_ID" => $fileFields["AUTHOR_ID"]
					]
				);
				if (!$res->isSuccess())
				{
					$result->setErrors($res->getErrors());
				}
				else
				{
					/*@var \Bitrix\Main\ORM\Objectify\EntityObject $object*/
					$object = $event->getParameter("object");
					/*@var \Bitrix\Main\Dictionary $object->customData*/
					$object->sysSetRuntime("FILES", $data["FILES"]);
					$object->sysSetRuntime("UPLOAD_DIR", $strUploadDir);
					$object->sysSetRuntime("FILE_FIELDS", $fileFields);
				}
			}
			$result->unsetField("FILES");
			unset($data["FILES"]);
		}
		//endregion
		if ($data != $event->getParameter("fields"))
		{
			$result->modifyFields($data);
		}

		return $result;
	}
	/**
	 * @param \Bitrix\Main\ORM\Event $event
	 * @return \Bitrix\Main\ORM\EventResult|void
	 */
	public static function onUpdate(\Bitrix\Main\ORM\Event $event)
	{
		$id = $event->getParameter("id");
		$id = $id["ID"];
		$message = self::getDataById($id);

		$fields = $event->getParameter("fields");
		$object = $event->getParameter("object");

		if ($files = $object->sysGetRuntime("FILES"))
		{
			$fileFields = $object->sysGetRuntime("FILE_FIELDS") + $message;
			File::saveFiles(
				$files,
				[
					"FORUM_ID" => $fileFields["FORUM_ID"],
					"TOPIC_ID" => $fileFields["TOPIC_ID"],
					"MESSAGE_ID" => $id,
					"USER_ID" => $fileFields["AUTHOR_ID"],
				],
				($object->sysGetRuntime("UPLOAD_DIR") ?: "forum/upload"));
		}
		if (\Bitrix\Main\Config\Option::get("forum", "MESSAGE_HTML", "N") == "Y")
		{
			$result = new \Bitrix\Main\ORM\EventResult();
			$parser = new \forumTextParser(LANGUAGE_ID);
			$allow = \forumTextParser::GetFeatures(\Bitrix\Forum\Forum::getById($fields["FORUM_ID"]));
			$allow["SMILES"] = ($fields["USE_SMILES"] != "Y" ? "N" : $allow["SMILES"]);
			$result->modifyFields([
				"POST_MESSAGE_HTML" => $parser->convert($fields["POST_MESSAGE_FILTER"] ?: $fields["POST_MESSAGE"], $allow, "html", $files)
			]);
			return $result;
		}
	}

	/**
	 * @param \Bitrix\Main\ORM\Event $event
	 * @return void
	 */
	public static function onAfterUpdate(\Bitrix\Main\ORM\Event $event)
	{
		$id = $event->getParameter("id");
		$id = $id["ID"];
		unset(self::$messageById[$id]);
		$message = self::getDataById($id);
		$fields = $event->getParameter("fields");

		/***************** Event onAfterVoteAdd ****************************/
		foreach (GetModuleEvents("forum", "onAfterMessageUpdate", true) as $event)
			ExecuteModuleEventEx($event, [$id, $fields, $message]);
		/***************** /Event ******************************************/
	}

	/**
	 * @param Result $result
	 * @param mixed $primary
	 * @param array $data
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function checkFields(Result $result, $primary, array $data)
	{
		parent::checkFields($result, $primary, $data);
		if ($result->isSuccess())
		{
			try
			{
				if (array_key_exists("FORUM_ID", $data) && ForumTable::getMainData($data["FORUM_ID"]) === null)
				{
					throw new \Bitrix\Main\ObjectNotFoundException(Loc::getMessage("F_ERR_INVALID_FORUM_ID"));
				}
				if (array_key_exists("TOPIC_ID", $data))
				{
					if (!($topic = TopicTable::getById($data["TOPIC_ID"])->fetch()))
					{
						throw new \Bitrix\Main\ObjectNotFoundException(Loc::getMessage("F_ERR_TOPIC_IS_NOT_EXISTS"));
					}
					if ($topic["STATE"] == Topic::STATE_LINK)
					{
						throw new \Bitrix\Main\ObjectPropertyException(Loc::getMessage("F_ERR_TOPIC_IS_LINK"));
					}
				}
			}
			catch (\Exception $e)
			{
				$result->addError(new Error(
					$e->getMessage()
				));
			}
		}
	}

	/**
	 * Deletes row in entity table by primary key
	 *
	 * @param mixed $primary
	 *
	 * @return Entity\DeleteResult
	 *
	 * @throws \Exception
	 */
	public static function delete($primary)
	{
		self::$messageById = [];
		throw new NotImplementedException;
	}
}

class Message extends Internals\Entity
{
	use \Bitrix\Forum\Internals\EntityFabric;

	public const APPROVED_APPROVED = "Y";
	public const APPROVED_DISAPPROVED = "N";

	protected function init()
	{
		if (!($this->data = MessageTable::getById($this->id)->fetch()))
		{
			throw new \Bitrix\Main\ObjectNotFoundException("Message with id {$this->id} is not found.");
		}
		$this->authorId = intval($this->data["AUTHOR_ID"]);
	}

	public function edit(array $fields)
	{
		$result = self::update($this->getId(), $fields);

		if ($result->isSuccess() )
		{
			$this->data = MessageTable::getById($result->getId())->fetch();

			\Bitrix\Forum\Integration\Search\Message::index(Forum::getById($this->getForumId()), Topic::getById($this->data["TOPIC_ID"]), $this->data);
		}

		return $result;
	}

	/**
	 * @param Topic $parentObject
	 * @param array $fields
	 */
	public static function create($parentObject, array $fields)
	{
		$topic = \Bitrix\Forum\Topic::getInstance($parentObject);
		$result = self::add($topic, $fields);
		if (!$result->isSuccess() )
		{
			return $result;
		}

		$message = MessageTable::getDataById($result->getId());
		$forum = Forum::getById($topic->getForumId());
		//region Update statistic & Seacrh
		User::getById($message["AUTHOR_ID"])->incrementStatistic($message);
		$topic->incrementStatistic($message);
		$forum->incrementStatistic($message);
		\Bitrix\Forum\Integration\Search\Message::index($forum, $topic, $message);
		//endregion

		return $result;
	}

	public static function update($id, array $fields)
	{
		$data = [];

		foreach ([
			"USE_SMILES",
			"POST_MESSAGE",
			"ATTACH_IMG",
			"FILES",
			"AUTHOR_NAME",
			"AUTHOR_EMAIL"
		] as $field)
		{
			if (array_key_exists($field, $fields))
			{
				$data[$field] = $fields[$field];
			}
		}

		if (!empty(array_diff_key($fields, $data)))
		{
			global $USER_FIELD_MANAGER;
			$data += array_intersect_key($fields, $USER_FIELD_MANAGER->getUserFields(MessageTable::getUfId()));
		}

		return MessageTable::update($id, $data);
	}

	/**
	 * @param Topic $topic
	 * @param array $fields
	 */
	public static function add(\Bitrix\Forum\Topic $topic, array $fields)
	{
		$data = [
			"FORUM_ID" => $topic->getForumId(),
			"TOPIC_ID" => $topic->getId(),

			"USE_SMILES" => $fields["USE_SMILES"],
			"NEW_TOPIC" => $fields["NEW_TOPIC"],
			"APPROVED" => $topic["APPROVED"] === Topic::APPROVED_DISAPPROVED ? Topic::APPROVED_DISAPPROVED : $fields["APPROVED"],
			"SOURCE_ID" => $fields["SOURCE_ID"],

			"POST_DATE" => $fields["POST_DATE"] ?: new \Bitrix\Main\Type\DateTime(),
			"POST_MESSAGE" => $fields["POST_MESSAGE"],
			"ATTACH_IMG" => $fields["ATTACH_IMG"],
			"FILES" => $fields["FILES"],

			"AUTHOR_ID" => $fields["AUTHOR_ID"],
			"AUTHOR_NAME" => $fields["AUTHOR_NAME"],
			"AUTHOR_EMAIL" => $fields["AUTHOR_EMAIL"],
			"AUTHOR_IP" => "<no address>",
			"AUTHOR_REAL_IP" => "<no address>",
			"GUEST_ID" => $_SESSION["SESS_GUEST_ID"],

			"PARAM1" => $fields["PARAM1"],
			"PARAM2" => $fields["PARAM2"]
		];
		if ($realIp = \Bitrix\Main\Service\GeoIp\Manager::getRealIp())
		{
			$data["AUTHOR_IP"] = $realIp;
			$data["AUTHOR_REAL_IP"] = $realIp;
			if (\Bitrix\Main\Config\Option::get("forum", "FORUM_GETHOSTBYADDR", "N") == "Y")
			{
				$data["AUTHOR_REAL_IP"] = @gethostbyaddr($realIp);
			}
		}
		if (!empty(array_diff_key($fields, $data)))
    	{
			global $USER_FIELD_MANAGER;
			$data += array_intersect_key($fields, $USER_FIELD_MANAGER->getUserFields(MessageTable::getUfId()));
		}

		return MessageTable::add($data);
	}
}