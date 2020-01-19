<?php
namespace Bitrix\Forum;

use Bitrix\Main;
use Bitrix\Main\Entity;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ORM\EntityError;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\EnumField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Type\DateTime;

/**
 * Class MessageTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> FORUM_ID int mandatory
 * <li> TOPIC_ID int
 * <li> TITLE string(255) mandatory
 * <li> TITLE_SEO string(255)
 * <li> TAGS string(255)
 * <li> DESCRIPTION string(255)
 * <li> ICON string(255)
 * <li> STATE bool optional default 'Y'
 * <li> APPROVED bool optional default 'Y'
 * <li> SORT int mandatory default '150'
 * <li> VIEWS mandatory default '0'
 * <li> USER_START_ID int
 * <li> USER_START_NAME string(255),
 * <li> START_DATE datetime mandatory
 * <li> POSTS int mandatory default '0'
 * <li> LAST_POSTER_ID int(10)
 * <li> LAST_POSTER_NAME string(255) mandatory
 * <li> LAST_POST_DATE datetime mandatory
 * <li> LAST_MESSAGE_ID int
 * <li> POSTS_UNAPPROVED int mandatory default '0'
 * <li> ABS_LAST_POSTER_ID int
 * <li> ABS_LAST_POSTER_NAME string(255)
 * <li> ABS_LAST_POST_DATE datetime
 * <li> ABS_LAST_MESSAGE_ID int
 * <li> XML_ID string(255)
 * <li> HTML text
 * <li> SOCNET_GROUP_ID int
 * <li> OWNER_ID int
 * </ul>
 *
 * @package Bitrix\Forum
 **/
class TopicTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_forum_topic';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			(new IntegerField("ID", ["primary" => true, "autocomplete" => true])),
			(new IntegerField("FORUM_ID", ["required" => true])),
			(new IntegerField("TOPIC_ID")),
			(new StringField("TITLE", ["required" => true, "size" => 255])),
			(new StringField("TITLE_SEO", ["size" => 255])),
			(new StringField("TAGS", ["size" => 255])),
			(new StringField("DESCRIPTION", ["size" => 255])),
			(new StringField("ICON", ["size" => 255])),
			(new EnumField("STATE", ["values" => [Topic::STATE_OPENED, Topic::STATE_CLOSED, Topic::STATE_LINK], "default_value" => Topic::STATE_OPENED])),
			(new BooleanField("APPROVED", ["values" => [Topic::APPROVED_DISAPPROVED, Topic::APPROVED_APPROVED], "default_value" => Topic::APPROVED_APPROVED])),
			(new IntegerField("SORT", ["default_value" => 150])),
			(new IntegerField("VIEWS")),
			(new IntegerField("USER_START_ID")),
			(new StringField("USER_START_NAME", ["required" => true, "size" => 255])),
			(new DatetimeField("START_DATE", ["required" => true, "default_value" => function(){return new DateTime();}])),
			(new IntegerField("POSTS")),
			(new IntegerField("LAST_POSTER_ID")),
			(new StringField("LAST_POSTER_NAME", ["required" => true, "size" => 255])),
			(new DatetimeField("LAST_POST_DATE", ["required" => true, "default_value" => function(){return new DateTime();}])),
			(new IntegerField("LAST_MESSAGE_ID")),
			(new IntegerField("POSTS_UNAPPROVED", ["default_value" => 0])),
			(new IntegerField("ABS_LAST_POSTER_ID")),
			(new StringField("ABS_LAST_POSTER_NAME", ["size" => 255])),
			(new DatetimeField("ABS_LAST_POST_DATE", ["required" => true, "default_value" => function(){return new DateTime();}])),
			(new IntegerField("ABS_LAST_MESSAGE_ID")),
			(new StringField("XML_ID", ["size" => 255])),
			(new TextField("HTML")),
			(new IntegerField("SOCNET_GROUP_ID")),
			(new IntegerField("OWNER_ID")),
			(new Reference("FORUM", ForumTable::class, Join::on("this.FORUM_ID", "ref.ID")))
		];
	}

	public static function getFilteredFields()
	{
		return [
			"TITLE",
			"TAGS",
			"DESCRIPTION",
			"USER_START_NAME",
			"LAST_POSTER_NAME",
			"ABS_LAST_POSTER_NAME"
		];
	}

	public static function onBeforeAdd(Event $event)
	{
		$result = new \Bitrix\Main\ORM\EventResult();
		/** @var array $data */
		$data = $event->getParameter("fields");
		if (($events = GetModuleEvents("forum", "Error onBeforeTopicAdd", true)) && !empty($events))
		{
			foreach ($events as $ev)
			{
				if (ExecuteModuleEventEx($ev, array(&$data)) === false)
				{
					$result->addError(new EntityError("onBeforeTopicAdd: ".serialize($ev), "event"));
					return $result;
				}
			}
		}
		if (\Bitrix\Main\Config\Option::get("forum", "FILTER", "Y") == "Y")
		{
			$filteredFields = self::getFilteredFields();
			foreach ($filteredFields as $key)
			{
				$res[$key] = $val = array_key_exists($key, $data) ? $data[$key] : "";
				if (!empty($val))
				{
					$res[$key] = \CFilterUnquotableWords::Filter($val);
					if (empty($res[$key]))
					{
						$res[$key] = "*";
					}
				}
			}
			$data["HTML"] = serialize($res);
		}

		$data["TITLE_SEO"] = array_key_exists("TITLE_SEO", $data) ? trim($data["TITLE_SEO"], " -") : "";
		if (empty($data["TITLE_SEO"]))
		{
			$data["TITLE_SEO"] = \CUtil::translit($data["TITLE"], LANGUAGE_ID, array("max_len"=>255, "safe_chars"=>".", "replace_space" => '-'));
		}

		if ($data != $event->getParameter("fields"))
		{
			$result->modifyFields($data);
		}

		return self::modifyData($event, $result);
	}

	/**
	 * @param \Bitrix\Main\ORM\Event $event
	 * @return void
	 */
	public static function onAfterAdd(\Bitrix\Main\ORM\Event $event)
	{
		$id = $event->getParameter("id");
		$id = $id["ID"];
		$fields = $event->getParameter("fields");
		/***************** Event onAfterVoteAdd ****************************/
		foreach (GetModuleEvents("forum", "onAfterTopicAdd", true) as $event)
			ExecuteModuleEventEx($event, [$id, $fields]);
		/***************** /Event ******************************************/
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
		$topic = TopicTable::getById($id)->fetch();
		if (($events = GetModuleEvents("forum", "onBeforeTopicUpdate", true)) && !empty($events))
		{
			foreach ($events as $ev)
			{
				if (ExecuteModuleEventEx($ev, array($id, &$data, $topic)) === false)
				{
					$result->addError(new EntityError("Error onBeforeTopicUpdate: ".serialize($ev), "event"));
					return $result;
				}
			}
		}
		if (\Bitrix\Main\Config\Option::get("forum", "FILTER", "Y") == "Y")
		{
			$filteredFields = self::getFilteredFields();
			if (!empty(array_intersect($filteredFields, array_keys($data))))
			{
				$res = [];
				foreach ($filteredFields as $key)
				{
					$res[$key] = $val = array_key_exists($key, $data) ? $data[$key] : $topic[$key];
					if (!empty($val))
					{
						$res[$key] = \CFilterUnquotableWords::Filter($val);
						if (empty($res[$key]))
						{
							$res[$key] = "*";
						}
					}
				}
				$data["HTML"] = serialize($res);
			}
		}
		if (array_key_exists("TITLE_SEO", $data) || array_key_exists("TITLE", $data))
		{
			$data["TITLE_SEO"] = trim($data["TITLE_SEO"], " -");
			if (strlen($data["TITLE_SEO"]) <= 0)
			{
				$title = array_key_exists("TITLE", $data) ? $data["TITLE"] : $topic["TITLE"];
				$data["TITLE_SEO"] = \CUtil::translit($title, LANGUAGE_ID, array("max_len"=>255, "safe_chars"=>".", "replace_space" => '-'));
			}
		}
		if ($data != $event->getParameter("fields"))
		{
			$result->modifyFields($data);
		}
		return self::modifyData($event, $result);
	}

	/**
	 * @param \Bitrix\Main\ORM\Event $event
	 * @return void
	 */
	public static function onAfterUpdate(\Bitrix\Main\ORM\Event $event)
	{
		$id = $event->getParameter("id");
		$id = $id["ID"];
		$fields = $event->getParameter("fields");
		if (array_key_exists("FORUM_ID", $fields))
		{
			$connection = \Bitrix\Main\Application::getInstance()->getConnection();
			$connection->queryExecute("UPDATE " . FileTable::getTableName() . " SET FORUM_ID={$fields["FORUM_ID"]} WHERE TOPIC_ID={$id}");
			$connection->queryExecute("UPDATE " . MessageTable::getTableName() . " SET FORUM_ID={$fields["FORUM_ID"]} WHERE TOPIC_ID={$id}");
			$connection->queryExecute("UPDATE " . SubscribeTable::getTableName() . " SET FORUM_ID={$fields["FORUM_ID"]} WHERE TOPIC_ID={$id}");
		}
		/***************** Event onAfterVoteAdd ****************************/
		foreach (GetModuleEvents("forum", "onAfterTopicUpdate", true) as $event)
			ExecuteModuleEventEx($event, [$id, $fields, []]);
		/***************** /Event ******************************************/
	}
	/**
	 * @param \Bitrix\Main\ORM\Event $event
	 * @param \Bitrix\Main\ORM\EventResult $result
	 * @return \Bitrix\Main\ORM\EventResult
	 * @throws \Bitrix\Main\ObjectException
	 */
	private static function modifyData(\Bitrix\Main\ORM\Event $event, \Bitrix\Main\ORM\EventResult $result)
	{
		$data = array_merge($event->getParameter("fields"), $result->getModified());
		$fields = [];

		//region check image
		$key = array_key_exists("VIEWS", $data) ? "VIEWS" : (array_key_exists("=VIEWS", $data) ? "=VIEWS" : null);
		if ($key !== null && str_replace(" ", "", $data[$key]) === "VIEWS+1")
		{
			unset($data[$key]);
			$fields["VIEWS"] = new \Bitrix\Main\DB\SqlExpression('?# + 1', 'VIEWS');
		}
		//endregion
		if (!empty($fields))
		{
			$result->modifyFields(array_merge($result->getModified(), $fields));
		}
		return $result;
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
		throw new NotImplementedException;
	}
}

class Topic extends \Bitrix\Forum\Internals\Entity
{
	use \Bitrix\Forum\Internals\EntityFabric;

	public const STATE_LINK = "L";
	public const STATE_CLOSED = "N";
	public const STATE_OPENED = "Y";
	public const APPROVED_APPROVED = "Y";
	public const APPROVED_DISAPPROVED = "N";

	protected function init()
	{
		if (!($this->data = TopicTable::getById($this->id)->fetch()))
		{
			throw new \Bitrix\Main\ObjectNotFoundException("Topic with id {$this->id} is not found.");
		}
		$this->authorId = intval($this->data["USER_START_ID"]);
	}

	public function moveToForum(int $forumId)
	{
		$result = new \Bitrix\Main\Result();
		if ($forumId == $this->forum->getId())
		{
			return $result;
		}
		$newForum = \Bitrix\Forum\Forum::getById($forumId);
		TopicTable::update($this->getId(), ["FORUM_ID" => $newForum->getId()]);
		$this->forum->calcStat();
		$newForum->calcStat();

		\Bitrix\Forum\Integration\Search\Topic::reindex($this->getId());
		return $result;
	}

	public function update(array $fields)
	{
		unset($fields["FORUM_ID"]);

		$result = TopicTable::update($this->getId(), $fields);

		if (
			$result->isSuccess() &&
			$this->forum["INDEXATION"] === "Y" &&
			($data = array_intersect_key($result->getData(), ["TITLE" => null, "TITLE_SEO" => null, "TAGS" => null, "DESCRIPTION" => null])) &&
			!empty($data) &&
			IsModuleInstalled("search"))
		{
			if (empty(array_intersect_key($data, ["TITLE" => null, "TITLE_SEO" => null])))
			{
				\Bitrix\Forum\Integration\Search\Topic::reindexFirstMessage($this->getId());
			}
			else
			{
				\Bitrix\Forum\Integration\Search\Topic::reindex($this->getId());
			}
		}
		return $result;
	}

	public function open()
	{
		$result = new \Bitrix\Main\Result();
		if ($this->data["STATE"] === self::STATE_CLOSED)
		{
			$res = \CForumTopic::Update($this->getId(), ["STATE" => self::STATE_OPENED], True); // TODO replace this
			if ($res === false)
			{
				$result->addError(new \Bitrix\Main\Error("Topic is not opened."));
			}
			else
			{
				$this->data["STATE"] = self::STATE_OPENED;
				\CForumEventLog::Log("topic", "open", $this->getId(), serialize($this->data));
				$result->setData(["STATE" => self::STATE_OPENED]);
			}
		}
		return $result;
	}

	public function close()
	{
		$result = new \Bitrix\Main\Result();
		if ($this->data["STATE"] === self::STATE_OPENED)
		{
			$res = \CForumTopic::Update($this->getId(), ["STATE" => self::STATE_CLOSED], true); // TODO replace this
			if ($res === false)
			{
				$result->addError(new \Bitrix\Main\Error("Topic is not closed."));
			}
			else
			{
				$this->data["STATE"] = self::STATE_CLOSED;
				\CForumEventLog::Log("topic", "close", $this->getId(), serialize($this->data));
				$result->setData(["STATE" => self::STATE_CLOSED]);
			}
		}
		return $result;
	}

	public function disapprove()
	{
		$result = new \Bitrix\Main\Result();
		if ($this->data["APPROVED"] === self::APPROVED_APPROVED)
		{
			$this->data["APPROVED"] = self::APPROVED_DISAPPROVED;
			TopicTable::update($this->getId(), ["APPROVED" => self::APPROVED_DISAPPROVED]);

			// region 1. Change rights for search indexes
			if (\Bitrix\Main\Loader::includeModule("search") && $this->forum["INDEXATION"] == "Y")
			{
				$res = $this->forum->getPermissions();
				$groups = [1];
				foreach ($res as $group => $permission)
				{
					if ($permission >= Permission::CAN_MODERATE)
					{
						$groups[] = $group;
					}
				}
				\CSearch::ChangePermission("forum", $groups, false, $this->forum["ID"], $this->getId());
			}
			//endregion
			//region 2. Update MessageTable & Forum Statistic
			$connection = \Bitrix\Main\Application::getInstance()->getConnection();
			$connection->queryExecute("UPDATE " . MessageTable::getTableName() . " SET APPROVED='" . Message::APPROVED_DISAPPROVED . "' WHERE TOPIC_ID={$this->getId()}");
			$this->forum->calcStat();
			\Bitrix\Forum\Statistic\TopicMembersStepper::calc($this->getId());
			//endregion\

			\CForumEventLog::Log("topic", "disapprove", $this->getId(), serialize($this->data));
			$event = new \Bitrix\Main\Event("forum", "onTopicModerate", [$this->getId(), $this->data]);
			$event->send();
			$result->setData(["APPROVED" => self::APPROVED_DISAPPROVED]);
		}
		return $result;
	}

	public function approve()
	{
		$result = new \Bitrix\Main\Result();
		if ($this->data["APPROVED"] !== self::APPROVED_APPROVED)
		{
			$this->data["APPROVED"] = self::APPROVED_APPROVED;
			TopicTable::update($this->getId(), ["APPROVED" => self::APPROVED_APPROVED]);

			// region 1. Change rights for search indexes
			if (\Bitrix\Main\Loader::includeModule("search") && $this->forum["INDEXATION"] == "Y")
			{
				$res = $this->forum->getPermissions();
				$groups = [];
				foreach ($res as $group => $permission)
				{
					if ($permission > Permission::ACCESS_DENIED)
					{
						$groups[] = $group;
					}
				}
				\CSearch::ChangePermission("forum", $groups, false, $this->forum["ID"], $this->getId());
			}
			//endregion
			//region 2. Update MessageTable & Forum Statistic
			$connection = \Bitrix\Main\Application::getInstance()->getConnection();
			$connection->queryExecute("UPDATE " . MessageTable::getTableName() . " SET APPROVED='" . Message::APPROVED_APPROVED . "' WHERE TOPIC_ID={$this->getId()}");
			$this->forum->calcStat();
			\Bitrix\Forum\Statistic\TopicMembersStepper::calc($this->getId());
			//endregion\

			\CForumEventLog::Log("topic", "approve", $this->getId(), serialize($this->data));
			$event = new \Bitrix\Main\Event("forum", "onTopicModerate", [$this->getId(), $this->data]);
			$event->send();
			$result->setData(["APPROVED" => self::APPROVED_APPROVED]);
		}
		return $result;
	}

	/**
	 * @param Forum $parentObject
	 * @param array $fields
	 */
	public static function create($parentObject, array $fields)
	{
		global $USER_FIELD_MANAGER;

		$forum = \Bitrix\Forum\Forum::getInstance($parentObject);
		$date = new \Bitrix\Main\Type\DateTime($fields["START_DATE"] ?: $fields["POST_DATE"]);
		$author = [
			"ID" => $fields["USER_START_ID"] ?: $fields["AUTHOR_ID"],
			"NAME" => $fields["USER_START_NAME"] ?: $fields["AUTHOR_NAME"]
		];

		$topicData = [
			"FORUM_ID" => $forum->getId(),
			"TITLE" => $fields["TITLE"],
			"TITLE_SEO" => (array_key_exists("TITLE_SEO", $fields) ? $fields["TITLE_SEO"] : ""),
			"TAGS" => $fields["TAGS"],
			"DESCRIPTION" => $fields["DESCRIPTION"],
			"ICON" => $fields["ICON"],
			"STATE" => $fields["STATE"] ?: Topic::STATE_OPENED,
			"APPROVED" => $fields["APPROVED"],

			"POSTS" => 0,
			"POSTS_UNAPPROVED" => 0,

			"USER_START_ID" => $author["ID"],
			"USER_START_NAME" => $author["NAME"],
			"START_DATE" => $date,

			"LAST_POSTER_ID" =>  $author["ID"],
			"LAST_POSTER_NAME" => $author["NAME"],
			"LAST_POST_DATE" => $date,
			"LAST_MESSAGE_ID" => 0,

			"ABS_LAST_POSTER_ID" => $author["ID"],
			"ABS_LAST_POSTER_NAME" => $author["NAME"],
			"ABS_LAST_POST_DATE" => $date,
			"ABS_LAST_MESSAGE_ID" => 0,

			"XML_ID" => $fields["TOPIC_XML_ID"],

			"OWNER_ID" => $fields["OWNER_ID"] ?: null,
			"SOCNET_GROUP_ID" => $fields["SOCNET_GROUP_ID"] ?: null
		];
		$result = TopicTable::add($topicData);
		if ($result->isSuccess())
		{
			$messageData = array(
				"NEW_TOPIC" => "Y",
				"APPROVED" => $topicData["APPROVED"],

				"USE_SMILES" => $fields["USE_SMILES"],
				"POST_DATE" => $date,
				"POST_MESSAGE" => $fields["POST_MESSAGE"],

				"ATTACH_IMG" => $fields["ATTACH_IMG"],
				"FILES" => $fields["FILES"],

				"PARAM1" => $fields["PARAM1"],
				"PARAM2" => $fields["PARAM2"],

				"AUTHOR_ID" => $author["ID"],
				"AUTHOR_NAME" => $author["NAME"],
				"AUTHOR_EMAIL" => $fields["AUTHOR_EMAIL"],
			);

			$messageData += array_intersect_key($fields, $USER_FIELD_MANAGER->getUserFields(MessageTable::getUfId()));

			$topic = Topic::getById($result->getId());
			$result = Message::add($topic, $messageData);
			if ($result->isSuccess())
			{
				$result->setData(["MESSAGE_ID" => $result->getId(), "TOPIC_ID" => $topic->getId()]);
				$message = MessageTable::getDataById($result->getId());
				//region Update statistic & Seacrh
				User::getById($message["AUTHOR_ID"])->incrementStatistic($message);
				$forum->incrementStatistic($message);
				\Bitrix\Forum\Integration\Search\Message::index($forum, $topic, $message);
				//endregion
			}
			else
			{
				TopicTable::delete($topic->getId());
				$topic->destroy();
			}
		}

		return $result;
	}

	/**
	 * @param array $fields
	 */
	public function edit(array $fields)
	{
		if (!$m = MessageTable::getList([
			"select" => ["ID"],
			"filter" => ["TOPIC_ID" => $this->getId(), "NEW_TOPIC" => "Y"],
			"limit" => 1
		])->fetch())
		{
			throw new Main\ObjectException("Главное сообщение темы не найдено. ");
		}
		$result = Message::update($m["ID"], $fields);
		if (!$result->isSuccess())
		{
			return $result;
		}

		$topicData = [];
		foreach ([
			"TITLE",
			"TITLE_SEO",
			"TAGS",
			"DESCRIPTION",
			"ICON",
			"USER_START_NAME"
		] as $field)
		{
			if (array_key_exists($field, $fields))
			{
				$topicData[$field] = $fields[$field];
			}
		}
		if (array_key_exists("AUTHOR_NAME", $fields))
		{
			$topicData["USER_START_NAME"] = $fields["AUTHOR_NAME"];
		}
		$result = TopicTable::update($this->getId(), $topicData);
		if ($result->isSuccess())
		{
			$this->data = TopicTable::getById($this->getId())->fetch();

			\Bitrix\Forum\Integration\Search\Message::index(Forum::getById($this->getForumId()), $this, MessageTable::getById($m["ID"])->fetch());

			$result->setPrimary(["ID" => $m["ID"]]);
			$result->setData(["MESSAGE_ID" => $m["ID"], "TOPIC_ID" => $this->getId()]);
		}
		return $result;
	}

	public function incrementStatistic(array $message)
	{
		$fields = array(
			"ABS_LAST_POSTER_ID" => $message["AUTHOR_ID"],
			"ABS_LAST_POSTER_NAME" => $message["AUTHOR_NAME"],
			"ABS_LAST_POST_DATE" => $message["POST_DATE"],
			"ABS_LAST_MESSAGE_ID" => $message["ID"]
		);
		$this->data = array_merge($this->data, $fields);
		if ($message["APPROVED"] == "Y")
		{
			$fields += [
				"APPROVED" => "Y",
				"LAST_POSTER_ID" => $message["AUTHOR_ID"],
				"LAST_POSTER_NAME" => $message["AUTHOR_NAME"],
				"LAST_POST_DATE" => $message["POST_DATE"],
				"LAST_MESSAGE_ID" => $message["ID"]
			];
			$this->data = array_merge($this->data, $fields);
			if ($message["NEW_TOPIC"] != "Y")
			{
				$fields["POSTS"] = new \Bitrix\Main\DB\SqlExpression('?# + 1', "POSTS");
				$this->data["POSTS"]++;
			}
		}
		else
		{
			$fields["POSTS_UNAPPROVED"] = new \Bitrix\Main\DB\SqlExpression('?# + 1', "POSTS_UNAPPROVED");
			$this->data["POSTS_UNAPPROVED"]++;
		}
		return TopicTable::update($this->getId(), $fields);
	}
}