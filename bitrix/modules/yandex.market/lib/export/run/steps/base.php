<?php

namespace Yandex\Market\Export\Run\Steps;

use Bitrix\Main;
use Yandex\Market;

Main\Localization\Loc::loadMessages(__FILE__);

abstract class Base
{
	const STORAGE_STATUS_FAIL = 1;
	const STORAGE_STATUS_SUCCESS = 2;
	const STORAGE_STATUS_INVALID = 3;
	const STORAGE_STATUS_DUPLICATE = 4;
	const STORAGE_STATUS_DELETE = 5;

	/** @var Market\Export\Run\Processor */
	protected $processor = null;
	/** @var Market\Export\Xml\Tag\Base */
	protected $tag = null;
	/** @var Market\Export\Xml\Tag\Base[] */
	protected $typedTagList = [];
	/** @var string|null */
	protected $tagParentName = null;
	/** @var array */
	protected $tagPath = null;
	/** @var bool */
	protected $isAllowCopyPublic = false;
	/** @var string */
	protected $runAction = null;
	/** @var int|null*/
	protected $totalCount;

	public static function getStorageStatusTitle($status)
	{
		return Market\Config::getLang('EXPORT_RUN_STEP_STORAGE_STATUS_' . $status);
	}

	public function __construct(Market\Export\Run\Processor $processor)
	{
		$this->processor = $processor;
	}

	public function destroy()
	{
		$this->processor = null;
		$this->tag = null;
		$this->tagParentName = null;
	}

	/**
	 * Название шага для событий
	 *
	 * @return mixed
	 */
	abstract public function getName();

	/**
	 * Не работает с файлом выгрузки
	 *
	 * @return bool
	 */
	public function isVirtual()
	{
		return false;
	}

	/**
	 * Инвалидируем лог и хранилище шага по изменениям
	 */
	public function invalidate()
	{
		$context = $this->getContext();
		$changes = $this->getChanges();

		$this->invalidateDataStorage($changes, $context);
	}

	/**
	 * Очищаем лог и хранилище шага полностью
	 *
	 * @param $isStrict bool
	 */
	public function clear($isStrict = false)
	{
		$context = $this->getContext();

		$this->clearDataLog($context);
		$this->clearDataStorage($context);
	}

	public function getReadyCount()
	{
		return null;
	}

	public function getTotalCount()
	{
		return $this->totalCount;
	}

	public function setTotalCount($count)
	{
		$this->totalCount = ($count !== null ? (int)$count : null);
	}

	/**
	 * Установить текущий режим выгрузки
	 *
	 * @param $action
	 */
	protected function setRunAction($action)
	{
		$this->runAction = $action;
	}

	/**
	 * Текущий режим выгрузки
	 *
	 * @return string
	 */
	public function getRunAction()
	{
		return $this->runAction;
	}

	/**
	 * Необходимо ли запускать процесс
	 *
	 * @param $action
	 *
	 * @return bool
	 */
	public function validateAction($action)
	{
		$result = true;
		$initTime = $this->getParameter('initTime');
		$isValidInitTime = ($initTime && $initTime instanceof Main\Type\Date);

		switch ($action)
		{
			case 'change':
				$changes = $this->getChanges();
				$result = ($isValidInitTime && !empty($changes));
			break;

			case 'refresh':
				$result = $isValidInitTime;
			break;
		}

		return $result;
	}

	/**
	 * Запускаем шаг
	 *
	 * @param $offset
	 *
	 * @return Market\Result\Step
	 */
	abstract public function run($action, $offset = null);

	/**
	 * Записываем данные шага
	 *
	 * @param $tagValuesList Market\Result\XmlValue[]
	 * @param $elementList array
	 * @param $context array
	 * @param $data array|null
	 * @param $limit int|null
	 *
	 * @return array
	 */
	protected function writeData($tagValuesList, $elementList, array $context = [], array $data = null, $limit = null)
	{
		$this->extendData($tagValuesList, $elementList, $context, $data);

		$tagResultList = $this->buildTagList($tagValuesList, $context);

		$this->writeDataUserEvent($tagResultList, $elementList, $context, $data);

		$storageResultList = $this->writeDataStorage($tagResultList, $tagValuesList, $elementList, $context, $data, $limit);

		if (!$this->isVirtual())
		{
			$this->writeDataFile($tagResultList, $storageResultList, $context);
			$this->writeDataCopyPublic($tagResultList, $context);
		}

		$this->writeDataLog($tagResultList, $context);

		return $storageResultList;
	}

	/**
	 * Расширяем данные шага через $tagValuesList
	 *
	 * @param Market\Result\XmlValue[] $tagValuesList
	 * @param array                    $elementList
	 * @param array                    $context
	 * @param array|null               $data
	 */
	protected function extendData($tagValuesList, $elementList, array $context = [], array $data = null)
	{
		$this->extendDataUserEvent($tagValuesList, $elementList, $context, $data);
	}

	/**
	 * Пользовательское событие для расширения через $tagValuesList
	 *
	 * @param Market\Result\XmlValue[] $tagValuesList
	 * @param array                    $elementList
	 * @param array                    $context
	 * @param array|null               $data
	 */
	protected function extendDataUserEvent($tagValuesList, $elementList, array $context = [], array $data = null)
	{
		$stepName = $this->getName();
		$moduleName = Market\Config::getModuleName();
		$eventName = 'onExport' . ucfirst($stepName) . 'ExtendData';
		$eventData = [
			'TAG_VALUE_LIST' => $tagValuesList,
			'ELEMENT_LIST' => $elementList,
			'CONTEXT' => $context
		];

		if (isset($data))
		{
			$eventData += $data;
		}

		$event = new Main\Event($moduleName, $eventName, $eventData);
		$event->send();
	}

	/**
	 * Генерируем теги
	 *
	 * @param Market\Result\XmlValue[] $tagValuesList
	 * @param array                    $context
	 *
	 * @return Market\Result\XmlNode[]
	 */
	protected function buildTagList($tagValuesList, array $context = [])
	{
		$document = null;
		$isTypedTag = $this->isTypedTag();
		$result = [];

		foreach ($tagValuesList as $elementId => $tagValue)
		{
			$tagType = ($isTypedTag ? $tagValue->getType() : null);
			$tagData = $tagValue->getTagData();
			$tag = $this->getTag($tagType);

			if ($document === null)
			{
				$document = $tag->exportDocument();
			}

			$result[$elementId] = $tag->exportTag($tagData, $context, $document);
		}

		return $result;
	}

	/**
	 * Список изменений
	 *
	 * @return array|null
	 */
	protected function getChanges()
	{
		$result = $this->getParameter('changes');

		return $this->filterChanges($result);
	}

	/**
	 * Фильтруем список изменений по правилам шага
	 *
	 * @param $changes
	 *
	 * @return array|null
	 */
	protected function filterChanges($changes)
	{
		$result = $changes;
		$ignoredTypeList = $this->getIgnoredTypeChanges();

		if ($result !== null && $ignoredTypeList !== null)
		{
			foreach ($result as $changeType => $entityIds)
			{
				if (isset($ignoredTypeList[$changeType]))
				{
					unset($result[$changeType]);
				}
			}
		}

		return $result;
	}

	/**
	 * Список типов изменений, которые игнорируются внутри шага
	 *
	 * @return array|null
	 */
	protected function getIgnoredTypeChanges()
	{
		return null;
	}

	/**
	 * Пользовательское события для модификации результата шага через $tagResultList
	 *
	 * @param $dataList
	 * @param $tagResultList Market\Result\XmlNode[]
	 */
	protected function writeDataUserEvent($tagResultList, $elementList, array $context = [], array $data = null)
	{
		$stepName = $this->getName();
		$moduleName = Market\Config::getModuleName();
		$eventName = 'onExport' . ucfirst($stepName) . 'WriteData';
		$eventData = [
			'TAG_RESULT_LIST' => $tagResultList,
			'ELEMENT_LIST' => $elementList,
			'CONTEXT' => $context
		];

		if (isset($data))
		{
			$eventData += $data;
		}

		$event = new Main\Event($moduleName, $eventName, $eventData);
		$event->send();
	}

	/**
	 * Класс хранилища результовов шага
	 *
	 * @return Market\Reference\Storage\Table
	 */
	protected function getStorageDataClass()
	{
		return null;
	}

	/**
	 * Основной ключ строки в хранилище результатов выгрузки
	 *
	 * @return array
	 */
	protected function getStoragePrimaryList()
	{
		return [
			'SETUP_ID',
			'ELEMENT_ID'
		];
	}

	/**
	 * Секция runtime для запросов к хранилищю результатов
	 *
	 * @return array
	 */
	protected function getStorageRuntime()
	{
		return [];
	}

	/**
	 * Инвалидириуем результаты выгрузки по изменениям
	 *
	 * @param $changes
	 * @param $context
	 *
	 * @throws \Bitrix\Main\ArgumentException
	 */
	protected function invalidateDataStorage($changes, $context)
	{
		$dataClass = $this->getStorageDataClass();
		$invalidateFilter = $this->getStorageChangesFilter($changes, $context);

		if ($dataClass && $invalidateFilter !== null)
		{
			/** @var \Bitrix\Main\Type\DateTime $initTime */
			$initTime = $this->getParameter('initTime');

			// filter

			$filter = [
				'=SETUP_ID' => $context['SETUP_ID'],
				'!=STATUS' => static::STORAGE_STATUS_DELETE
			];

			if (!empty($invalidateFilter))
			{
				$filter[] = $invalidateFilter;
			}

			// fields

			$fields = [
				'STATUS' => static::STORAGE_STATUS_INVALID
			];

			if ($initTime)
			{
				$updateTime = clone $initTime;
				$updateTime->add('-PT1S');

				$fields['TIMESTAMP_X'] = $updateTime;
			}

			$dataClass::updateBatch(
				[ 'filter' => $filter, 'runtime' => $this->getStorageRuntime() ],
				$fields
			);
		}
	}

	/**
	 * Очищаем хранилище результатов выгрузки полностью
	 *
	 * @param $context
	 *
	 * @throws \Bitrix\Main\ArgumentException
	 */
	protected function clearDataStorage($context)
	{
		$dataClass = $this->getStorageDataClass();

		if ($dataClass)
		{
			$dataClass::deleteBatch([
				'filter' => [ '=SETUP_ID' => $context['SETUP_ID'] ]
			]);
		}
	}

	/**
	 * Фильтр по изменениям для хранилища результатов
	 *
	 * @param $changes
	 * @param $context
	 *
	 * @return array|null
	 */
	protected function getStorageChangesFilter($changes, $context)
	{
		return []; // invalidate all by default
	}

	/**
	 * Записываем результат выгрузки в постоянное хранилище
	 *
	 * @param $tagResultList Market\Result\XmlNode[]
	 * @param $tagValuesList Market\Result\XmlValue[]
	 * @param $elementList
	 * @param $context
	 * @param $data
	 * @param $limit
	 *
	 * @return array
	 */
	protected function writeDataStorage($tagResultList, $tagValuesList, $elementList, array $context = [], array $data = null, $limit = null)
	{
		$result = [];
		$dataClass = $this->getStorageDataClass();
		$useHashCollision = $this->useHashCollision();

		if ($dataClass)
		{
			$timestamp = new Main\Type\DateTime();
			$existMap = [];
			$hashList = [];
			$hashListMap = [];
			$needCheckHashList = [];
			$successCount = 0;
			$isVirtual = $this->isVirtual();

			// query exists

			$queryExistsFilter = $this->getExistDataStorageFilter($tagResultList, $tagValuesList, $elementList, $context, $data);

			$queryExists = $dataClass::getList([
				'filter' => $queryExistsFilter,
				'select' => [
					'ELEMENT_ID',
					'HASH'
				]
			]);

			while ($row = $queryExists->fetch())
			{
				$existMap[$row['ELEMENT_ID']] = $row['HASH'];
			}

			// hash list

			foreach ($tagResultList as $elementId => $tagResult)
			{
				if ($tagResult->isSuccess())
				{
					$hash = $this->getTagResultHash($tagResult, $useHashCollision);

					$hashList[$elementId] = $hash;

					if ($useHashCollision && !isset($hashListMap[$hash]))
					{
						$hashListMap[$hash] = $elementId;
					}
				}
			}

			// make update

			$fieldsList = [];

			foreach ($tagResultList as $elementId => $tagResult)
			{
				$rowId = null;
				$writeAction = null;
				$element = isset($elementList[$elementId]) ? $elementList[$elementId] : null;
				$tagValues = isset($tagValuesList[$elementId]) ? $tagValuesList[$elementId] : null;

				$fields = [
					'SETUP_ID' => $context['SETUP_ID'],
					'ELEMENT_ID' => $elementId, // not int, maybe currency
					'STATUS' => static::STORAGE_STATUS_FAIL,
					'HASH' => '',
					'TIMESTAMP_X' => $timestamp
				];

				if ($isVirtual)
				{
					$fields['CONTENTS'] = '';
				}

				$additionalData = $this->getStorageAdditionalData($tagResult, $tagValues, $element, $context, $data);

				if (!empty($additionalData))
				{
					$fields += $additionalData;
				}

				if ($tagResult->isSuccess())
				{
					$hash = $hashList[$elementId];

					if (!$useHashCollision)
					{
						$fields['STATUS'] = static::STORAGE_STATUS_SUCCESS;
						$fields['HASH'] = $hash;

						if ($isVirtual)
						{
							$fields['CONTENTS'] = $tagResult->getXmlContents();
						}
					}
					else if ($hashListMap[$hash] === $elementId) // hash is unique
					{
						$needCheckHashList[$hash] = $fields['ELEMENT_ID'];

						$fields['STATUS'] = static::STORAGE_STATUS_SUCCESS;
						$fields['HASH'] = $hash;

						if ($isVirtual)
						{
							$fields['CONTENTS'] = $tagResult->getXmlContents();
						}
					}
					else // match another hash
					{
						$fields['STATUS'] = static::STORAGE_STATUS_DUPLICATE;
						$fields['HASH'] = '';

						$tagResult->addError(new Market\Error\XmlNode(
							Market\Config::getLang('EXPORT_RUN_STEP_BASE_HASH_COLLISION', [
								'#ELEMENT_ID#' => $hashList[$hash]
							]),
							Market\Error\XmlNode::XML_NODE_HASH_COLLISION
						));
					}
				}

				$fieldsList[] = $fields;
			}

			// check hash collision from already stored data

			if ($useHashCollision && !empty($needCheckHashList))
			{
				$duplicateHashList = $this->checkHashCollision($needCheckHashList, $context);

				if (!empty($duplicateHashList))
				{
					foreach ($fieldsList as &$fields)
					{
						if (
							$fields['STATUS'] === static::STORAGE_STATUS_SUCCESS
							&& isset($duplicateHashList[$fields['HASH']])
						)
						{
							$elementId = $fields['ELEMENT_ID'];
							$tagResult = $tagResultList[$elementId];

							$fields['STATUS'] = static::STORAGE_STATUS_DUPLICATE;
							$fields['HASH'] = '';

							if ($isVirtual)
							{
								$fields['CONTENTS'] = '';
							}

							$tagResult->addError(new Market\Error\XmlNode(
								Market\Config::getLang('EXPORT_RUN_STEP_BASE_HASH_COLLISION', [
									'#ELEMENT_ID#' => $duplicateHashList[$fields['HASH']]
								]),
								Market\Error\XmlNode::XML_NODE_HASH_COLLISION
							));
						}
					}
					unset($fields);
				}
			}

			// write to db and build actions

			$fieldsCount = count($fieldsList);
			$writeChunkSize = $this->getWriteStorageChunkSize();

			for ($writeOffset = 0; $writeOffset < $fieldsCount; $writeOffset += $writeChunkSize)
			{
				$writeList = array_slice($fieldsList, $writeOffset, $writeChunkSize);

				// over limit check

				if ($limit !== null)
				{
					$loopSuccessCount = $successCount;

					foreach ($writeList as &$fields)
					{
						if ($fields['STATUS'] === static::STORAGE_STATUS_SUCCESS)
						{
							$loopSuccessCount++;

							if ($loopSuccessCount > $limit)
							{
								$fields['STATUS'] = static::STORAGE_STATUS_FAIL;
								$fields['HASH'] = '';

								if ($isVirtual)
								{
									$fields['CONTENTS'] = '';
								}
							}
						}
					}
					unset($fields);
				}

				// write

				$writeResult = $dataClass::addBatch($writeList, true);

				// process write result

				$isSuccessWrite = $writeResult->isSuccess();

				foreach ($writeList as $fields)
				{
					$elementId = $fields['ELEMENT_ID'];
					$prevHash = (isset($existMap[$elementId]) ? $existMap[$elementId] : '');
					$fileAction = null;

					if (
						!$isSuccessWrite // fail write to db
						&& $fields['STATUS'] === static::STORAGE_STATUS_SUCCESS // and going write to file
					)
					{
						$fields['STATUS'] = static::STORAGE_STATUS_FAIL;
						$fields['HASH'] = '';
					}

					// write action

					if ($fields['HASH'] !== $prevHash)
					{
						$prevFileAction = ($prevHash !== '' ? 'add' : 'delete');
						$newFileAction = ($fields['HASH'] !== '' ? 'add' : 'delete');

						if ($prevFileAction !== $newFileAction)
						{
							$fileAction = $newFileAction;
						}
						else if ($newFileAction === 'add')
						{
							$fileAction = 'update';
						}
					}

					if ($fields['STATUS'] === static::STORAGE_STATUS_SUCCESS)
					{
						$successCount++;
					}

					$result[$elementId] = [
						'ID' => $elementId,
						'STATUS' => $fields['STATUS'],
						'ACTION' => $fileAction
					];
				}
			}
		}

		return $result;
	}

	/**
	 * Размер пакета для записи в результаты выгрузки
	 *
	 * @return int
	 */
	protected function getWriteStorageChunkSize()
	{
		return (int)Market\Config::getOption('export_write_storage_chunk_size') ?: 50;
	}

	/**
	 * Фильтр по уже выгруженным результатам для выбранных элементов
	 *
	 * @param $tagResultList
	 * @param $tagValuesList
	 * @param $elementList
	 * @param array $context
	 * @param array $data
	 *
	 * @return array
	 */
	protected function getExistDataStorageFilter($tagResultList, $tagValuesList, $elementList, array $context, array $data = null)
	{
		return [
			'=SETUP_ID' => $context['SETUP_ID'],
			'=ELEMENT_ID' => array_keys($tagResultList)
		];
	}

	/**
	 * Есть ли успешно выгруженные элементы данного типа
	 *
	 * @param array|null $context
	 *
	 * @return bool
	 */
	protected function hasDataStorageSuccess($context = null)
	{
		$dataClass = $this->getStorageDataClass();
		$result = false;

		if ($dataClass)
		{
			if ($context === null) { $context = $this->getContext(); }

			$readyFilter = $this->getStorageReadyFilter($context, true);
			$readyFilter['=STATUS'] = static::STORAGE_STATUS_SUCCESS;

			$query = $dataClass::getList([
				'filter' => $readyFilter,
				'limit' => 1
			]);

			if ($row = $query->fetch())
			{
				$result = true;
			}
		}

		return $result;
	}

	/**
	 * Фильтр по готовым элементам
	 *
	 * @param $queryContext array
	 * @param $isNeedFull bool
	 *
	 * @return array
	 */
	protected function getStorageReadyFilter($queryContext, $isNeedFull = false)
	{
		$filter = [
			'=SETUP_ID' => $queryContext['SETUP_ID']
		];

		if (!$isNeedFull)
		{
			switch ($this->getRunAction())
			{
				case 'change':
				case 'refresh':
					$filter['>=TIMESTAMP_X'] = $this->getParameter('initTime');
				break;
			}
		}

		return $filter;
	}

	/**
	 * Дополнительная информация для сохранения в таблице результатов выгрузки
	 *
	 * @param $tagResult Market\Result\XmlNode
	 * @param $tagValues Market\Result\XmlValue
	 * @param $element array|null
	 * @param $context array
	 * @param $data array
	 *
	 * @return array
	 */
	protected function getStorageAdditionalData($tagResult, $tagValues, $element, $context, $data)
	{
		return null;
	}

	/**
	 * Проверять совпадение хешей при экспорте
	 *
	 * @return bool
	 */
	protected function useHashCollision()
	{
		return false;
	}

	/**
	 * Проверяем наличие выгруженных элементов с указанными хешами
	 *
	 * @param $hashList
	 * @param $context
	 *
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	protected function checkHashCollision($hashList, $context)
	{
		$result = [];
		$dataClass = $this->getStorageDataClass();

		if ($dataClass && !empty($hashList))
		{
			$filter = [
				'=SETUP_ID' => $context['SETUP_ID'],
				'=STATUS' => static::STORAGE_STATUS_SUCCESS,
				'=HASH' => array_keys($hashList)
			];

			switch ($this->getRunAction())
			{
				case 'refresh':
					$filter['>=TIMESTAMP_X'] = $this->getParameter('initTime');
				break;
			}

			$query = $dataClass::getList([
				'filter' => $filter,
				'select' => [
					'ELEMENT_ID',
					'HASH'
				]
			]);

			while ($row = $query->fetch())
			{
				$hash = $row['HASH'];

				if (isset($hashList[$hash]) && $hashList[$hash] != $row['ELEMENT_ID'])
				{
					$result[$hash] = $row['ELEMENT_ID'];
				}
			}
		}

		return $result;
	}

	/**
	 * Хэш результата
	 *
	 * @param $tagResult Market\Result\XmlNode
	 * @param $useHashCollision bool
	 *
	 * @return string
	 */
	protected function getTagResultHash($tagResult, $useHashCollision = false)
	{
		$result = '';
		$xmlContents = $tagResult->getXmlContents();

		if ($xmlContents !== null)
		{
			if ($useHashCollision) // remove id attr for check tag contents
			{
				$xmlContents = preg_replace('/^(<[^ ]+) id="[^"]*?"/', '$1', $xmlContents);
			}

			$result = md5($xmlContents);
		}

		return $result;
	}

	/**
	 * Записываем изменения в файл экспорта
	 *
	 * @param $tagResultList Market\Result\XmlNode[]
	 * @param $storageResultList array
	 * @param $context array
	 */
	protected function writeDataFile($tagResultList, $storageResultList, $context)
	{
		$writer = $this->getWriter();
		$isOnlyDelete = true;
		$actionDataList = [];

		foreach ($storageResultList as $elementId => $storageResult)
		{
			$actionType = null;
			$actionContents = null;

			switch ($storageResult['ACTION'])
			{
				case 'add':
				case 'update':
					$isOnlyDelete = false;

					$actionType = $storageResult['ACTION'];
					$actionContents = $tagResultList[$elementId]->getXmlContents();
				break;

				case 'delete':
					$actionType = 'update';
					$actionContents = '';
				break;
			}

			if (isset($actionType))
			{
				if (!isset($actionDataList[$actionType]))
				{
					$actionDataList[$actionType] = [];
				}

				$actionDataList[$actionType][$elementId] = $actionContents;
			}
		}

		foreach ($actionDataList as $action => $actionData)
		{
			switch ($action)
			{
				case 'add':
					$tagParentName = $this->getTagParentName();

					$writeResultList = $writer->writeTagList($actionData, $tagParentName);

					if (empty($writeResultList) && $this->isAllowDeleteParent()) // failed write to file, then hasn't parent tag
					{
						$parentPath = $this->getTagPath();
						$chainContents = '<' . $tagParentName . '>' . implode('', $actionData) . '</' . $tagParentName . '>';

						foreach ($parentPath as $parentName => $parentPosition)
						{
							$parentWriteResult = $writer->writeTag($chainContents, $parentName, $parentPosition);

							if ($parentWriteResult)
							{
								break;
							}

							if (
								$parentPosition === Market\Export\Run\Writer\Base::POSITION_APPEND
								|| $parentPosition === Market\Export\Run\Writer\Base::POSITION_PREPEND
							)
							{
								$chainContents = '<' . $parentName . '>' . $chainContents . '</' . $parentName . '>';
							}
						}
					}
				break;

				case 'update':
					$tag = $this->getTag();
					$tagName = $tag->getName();
					$tagParentName = $this->getTagParentName();
					$isTagSelfClosed = $tag->isSelfClosed();
					$runAction = $this->getRunAction();

					$writer->updateTagList($tagName, $actionData, 'id', $isTagSelfClosed);

					if ($isOnlyDelete && ($runAction === 'change' || $runAction === 'refresh'))
					{
						$isNeedDeleteParent = ($tagParentName !== null && $this->isAllowDeleteParent() && !$this->hasDataStorageSuccess($context));

						if ($isNeedDeleteParent)
						{
							$writer->updateTag($tagParentName, null, '');
						}
					}
				break;
			}
		}
	}

	/**
	 * Записываем изменения в публичный файл экспорта
	 *
	 * @param $tagResultList Market\Result\XmlNode[]
	 * @param $context array
	 */
	protected function writeDataCopyPublic($tagResultList, $context)
	{
		if (
			$this->getRunAction() === 'change'
			&& ($writer = $this->getPublicWriter())
		)
		{
			$updateList = [];
			$isAllowDelete = $this->isAllowPublicDelete();

			foreach ($tagResultList as $elementId => $tagResult)
			{
				if ($tagResult->isSuccess())
				{
					$updateList[$elementId] = $tagResult->getXmlContents();
				}
				else if ($isAllowDelete)
				{
					$updateList[$elementId] = '';
				}
			}

			if (!empty($updateList))
			{
				$tag = $this->getTag();
				$tagName = $tag->getName();
				$isTagSelfClosed = $tag->isSelfClosed();

				$writer->lock(true);

				// update

				$updateResult = $writer->updateTagList($tagName, $updateList, 'id', $isTagSelfClosed);

				// add

				$addList = [];

				foreach ($updateList as $elementId => $contents)
				{
					if ($contents && !isset($updateResult[$elementId]))
					{
						$addList[$elementId] = $contents;
					}
				}

				if (!empty($addList))
				{
					$parentName = $this->getTagParentName();

					$writer->writeTagList($addList, $parentName);
				}

				$writer->unlock();
			}
		}
	}

	/**
	 * Разрешно ли удалять родительский тег
	 *
	 * @return bool
	 */
	protected function isAllowDeleteParent()
	{
		return false;
	}

	/**
	 * Разрешено ли удалять элементы из публичного файла (используется при внесении изменений)
	 *
	 * @return bool
	 */
	protected function isAllowPublicDelete()
	{
		return false;
	}

	/**
	 * Очищаем лог
	 *
	 * @param $context
	 *
	 * @throws \Bitrix\Main\ArgumentException
	 */
	protected function clearDataLog($context)
	{
		$entityType = $this->getDataLogEntityType();

		if ($entityType)
		{
			Market\Logger\Table::deleteBatch([
				'filter' => [
					'=ENTITY_TYPE' => $entityType,
					'=ENTITY_PARENT' => $context['SETUP_ID'],
				]
			]);
		}
	}

	/**
	 * Записываем ошибки и warning в таблицу логов
	 *
	 * @param $tagResultList Market\Result\XmlNode[]
	 * @param $context array
	 */
	protected function writeDataLog($tagResultList, $context)
	{
		$entityType = $this->getDataLogEntityType();

		if ($entityType && !empty($tagResultList))
		{
			$runAction = $this->getRunAction();

			$logger = new Market\Logger\Logger();
			$logger->allowBatch();

			if ($runAction === 'change' || $runAction === 'refresh')
			{
				$logger->allowCheckExists();
				$logger->allowRelease();
			}

			foreach ($tagResultList as $elementId => $tagResult)
			{
				$logContext = [
					'ENTITY_TYPE' => $entityType,
					'ENTITY_PARENT' => $context['SETUP_ID'],
					'ENTITY_ID' => $elementId
				];
				$errorGroupList = [
					Market\Psr\Log\LogLevel::CRITICAL => $tagResult->getErrors(),
					Market\Psr\Log\LogLevel::WARNING => $tagResult->getWarnings()
				];

				foreach ($errorGroupList as $logLevel => $errorGroup)
				{
					/** @var \Yandex\Market\Error\Base $error */
					foreach ($errorGroup as $error)
					{
						$errorContext = $logContext;
						$message = $error->getMessage();

						if ($messageCode = $error->getCode())
						{
							$errorContext['ERROR_CODE'] = $messageCode;
						}

						$logger->log($logLevel, $message, $errorContext);
					}
				}

				$logger->registerElement($logContext['ENTITY_TYPE'], $logContext['ENTITY_PARENT'], $logContext['ENTITY_ID']);
			}

			$logger->flush();
		}
	}

	/**
	 * Тип сущности для логов
	 *
	 * @return string|null
	 */
	protected function getDataLogEntityType()
	{
		return null;
	}

	/**
	 * Связь таблицы логов с таблицей результатов выгрузки
	 *
	 * @return array
	 */
	protected function getDataLogEntityReference()
	{
		return [
			'=this.ENTITY_PARENT' => 'ref.SETUP_ID',
			'=this.ENTITY_ID' => 'ref.ELEMENT_ID',
		];
	}

	/**
	 * Удаляем инвалидированные элементы, которые не попали в выгрузку по изменениям
	 *
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function removeInvalid()
	{
		$context = $this->getContext();
		$filter = [
			'=SETUP_ID' => $context['SETUP_ID'],
			'=STATUS' => static::STORAGE_STATUS_INVALID,
			'<TIMESTAMP_X' => $this->getParameter('initTime')
		];

		$this->removeByFilter($filter, $context);
	}

	/**
	 * Удаляем необработанные элементы
	 *
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function removeOld()
	{
		$context = $this->getContext();
		$filter = [
			'=SETUP_ID' => $context['SETUP_ID'],
			'!=STATUS' => static::STORAGE_STATUS_DELETE,
			'<TIMESTAMP_X' => $this->getParameter('initTime')
		];

		$this->removeByFilter($filter, $context);
	}

	/**
	 * Удаляем элементы по фильтру
	 *
	 * @param $filter
	 * @param $context
	 *
	 * @throws \Bitrix\Main\ArgumentException
	 */
	protected function removeByFilter($filter, $context)
	{
		$dataClass = $this->getStorageDataClass();
		$isVirtual = $this->isVirtual();
		$logEntityType = $this->getDataLogEntityType();
		$hasUpdateStorage = false;
		$writeList = [];

		// remove from storage and prepare file array

		if ($dataClass)
		{
			$timestamp = new Main\Type\DateTime();
			$storagePrimaryList = $this->getStoragePrimaryList();
			$updateFields = [
				'STATUS' => static::STORAGE_STATUS_DELETE,
				'HASH' => '',
				'TIMESTAMP_X' => $timestamp
			];

			if ($isVirtual)
			{
				$updateFields['CONTENTS'] = '';
			}
			else // get ids exist in file
			{
				$query = $dataClass::getList([
					'filter' => $filter + [ '!=HASH' => false ],
					'select' => $storagePrimaryList + [ 'HASH' ]
				]);

				while ($item = $query->fetch())
				{
					$writeList[$item['ELEMENT_ID']] = '';
				}
			}

			$updateResult = $dataClass::updateBatch([ 'filter' => $filter ], $updateFields);

			if ($updateResult->isSuccess() && $updateResult->getAffectedRowsCount() > 0)
			{
				$hasUpdateStorage = true;
			}
		}

		// log

		if ($logEntityType && $hasUpdateStorage)
		{
			Market\Logger\Table::deleteBatch([
				'filter' => [
					'=ENTITY_TYPE' => $logEntityType,
					'=ENTITY_PARENT' => $context['SETUP_ID'],
					'=RUN_STORAGE.STATUS' => static::STORAGE_STATUS_DELETE
				],
				'runtime' => [
					new Main\Entity\ReferenceField(
						'RUN_STORAGE',
						$dataClass,
						$this->getDataLogEntityReference()
					)
				]
			]);
		}

		// write to file

		if (!empty($writeList))
		{
			$tag = $this->getTag();
			$tagName = $tag->getName();
			$isTagSelfClosed = $tag->isSelfClosed();
			$writer = $this->getWriter();
			$parentName = $this->getTagParentName();
			$isNeedDeleteParent = ($parentName !== null && $this->isAllowDeleteParent() && !$this->hasDataStorageSuccess($context));

			$writer->updateTagList($tagName, $writeList, 'id', $isTagSelfClosed);

			if ($isNeedDeleteParent)
			{
				$writer->updateTag($parentName, null, '');
			}

			// remove from public

			if ($this->getRunAction() === 'change' && $this->isAllowPublicDelete())
			{
				$publicWriter = $this->getPublicWriter();

				if ($publicWriter)
				{
					$publicWriter->updateTagList($tagName, $writeList, 'id', $isTagSelfClosed);

					if ($isNeedDeleteParent)
					{
						$publicWriter->updateTag($parentName, null, '');
					}
				}
			}
		}
	}

	/**
	 * Контроллер выгрузки
	 *
	 * @return \Yandex\Market\Export\Run\Processor
	 */
	protected function getProcessor()
	{
		return $this->processor;
	}

	/**
	 * Модель настройки выгрузки
	 *
	 * @return \Yandex\Market\Export\Setup\Model
	 */
	protected function getSetup()
	{
		return $this->getProcessor()->getSetup();
	}

	/**
	 * Писатель файла экспорта
	 *
	 * @return \Yandex\Market\Export\Run\Writer\Base
	 */
	protected function getWriter()
	{
		return $this->getProcessor()->getWriter();
	}

	/**
	 * Писатель в публичный файл
	 *
	 * @return \Yandex\Market\Export\Run\Writer\Base|null
	 */
	protected function getPublicWriter()
	{
		return $this->getProcessor()->getPublicWriter();
	}

	/**
	 * Параметр выполнения
	 *
	 * @param $name
	 *
	 * @return mixed|null
	 */
	protected function getParameter($name)
	{
		return $this->getProcessor()->getParameter($name);
	}

	/**
	 * Контекст выполнения
	 *
	 * @return array
	 */
	protected function getContext()
	{
		return $this->getSetup()->getContext();
	}

	protected function getFormat()
	{
		return $this->getSetup()->getFormat();
	}

	/**
	 * Зависит формат тега от типа данных
	 *
	 * @return bool
	 */
	public function isTypedTag()
	{
		return false;
	}

	/**
	 * Выгружаемый тег
	 *
	 * @param $type string|null
	 *
	 * @return \Yandex\Market\Export\Xml\Tag\Base
	 */
	public function getTag($type = null)
	{
		$result = null;

		if ($type !== null)
		{
			if (isset($this->typedTagList[$type]))
			{
				$result = $this->typedTagList[$type];
			}
			else
			{
				$format = $this->getFormat();
				$result = $this->getFormatTag($format, $type);

				$this->typedTagList[$type] = $result;
			}
		}
		else
		{
			if ($this->tag !== null)
			{
				$result = $this->tag;
			}
			else
			{
				$format = $this->getFormat();
				$result = $this->getFormatTag($format);

				$this->tag = $result;
			}
		}

		return $result;
	}

	/**
	 * Название родительского тега
	 *
	 * @return null|string
	 */
	public function getTagParentName()
	{
		if (!isset($this->tagParentName))
		{
			$format = $this->getFormat();

			$this->tagParentName = $this->getFormatTagParentName($format);
		}

		return $this->tagParentName;
	}

	/**
	 * Путь к родительскому тегу
	 *
	 * @return array
	 *
	 * @throws Main\SystemException
	 */
	public function getTagPath()
	{
		if ($this->tagPath === null)
		{
			$format = $this->getFormat();
			$parentName = $this->getTagParentName();
			$rootTag = $format->getRoot();
			$path = $this->findTagPath($rootTag, $parentName);

			if ($path === null)
			{
				throw new Main\SystemException('not found tag path for ' . $parentName);
			}

			$this->tagPath = $path;
		}

		return $this->tagPath;
	}

	/**
	 * Поиск пути к тегу
	 *
	 * @param Market\Export\Xml\Tag\Base $tag
	 * @param $findName
	 *
	 * @return array|null
	 */
	protected function findTagPath(Market\Export\Xml\Tag\Base $tag, $findName)
	{
		$result = null;
		$afterTagNameList = [];

		/** @var Market\Export\Xml\Tag\Base $child */
		foreach (array_reverse($tag->getChildren()) as $child) // because gifts require promos, categories and currencies requires offers
		{
			$childName = $child->getName();
			$childResult = null;
			$isFoundSelf = false;

			if ($childName === $findName)
			{
				$isFoundSelf = true;
			}
			else
			{
				$childResult = $this->findTagPath($child, $findName);
			}

			if ($isFoundSelf || $childResult !== null)
			{
				if ($isFoundSelf)
				{
					foreach (array_reverse($afterTagNameList) as $afterTagName)
					{
						$result[$afterTagName] = Market\Export\Run\Writer\Base::POSITION_BEFORE;
					}
				}
				else
				{
					foreach ($childResult as $childName => $childPosition)
					{
						$result[$childName] = $childPosition;
					}
				}

				$result[$tag->getName()] = Market\Export\Run\Writer\Base::POSITION_APPEND;

				break;
			}

			$afterTagNameList[] = $childName;
		}

		return $result;
	}

	/**
	 * Выгружаемый тег из формата настройки
	 *
	 * @param Market\Export\Xml\Format\Reference\Base $format
	 * @param $type string|null
	 *
	 * @return \Yandex\Market\Export\Xml\Tag\Base|null
	 */
	public function getFormatTag(Market\Export\Xml\Format\Reference\Base $format, $type = null)
	{
		return null;
	}

	/**
	 * Название родительского тега из формата настройки
	 *
	 * @return string|null
	 * */
	public function getFormatTagParentName(Market\Export\Xml\Format\Reference\Base $format)
	{
		return null;
	}

	/**
	 * @param $tagDescriptionList
	 * @param $sourceValuesList
	 * @param $context
	 *
	 * @return Market\Result\XmlValue[]
	 */
	protected function buildTagValuesList($tagDescriptionList, $sourceValuesList, $context)
	{
		$result = [];

		foreach ($sourceValuesList as $elementId => $sourceValues)
		{
			$result[$elementId] = $this->buildTagValues($elementId, $tagDescriptionList, $sourceValues, $context);
		}

		return $result;
	}

	/**
	 * @param $elementId
	 * @param $tagDescriptionList
	 * @param $sourceValues
	 * @param $context
	 *
	 * @return Market\Result\XmlValue
	 */
	protected function buildTagValues($elementId, $tagDescriptionList, $sourceValues, $context)
	{
		$result = new Market\Result\XmlValue();

		if (isset($sourceValues['TYPE']) && $this->isTypedTag())
		{
			$result->setType($sourceValues['TYPE']);
		}

		foreach ($tagDescriptionList as $tagDescription)
		{
			$tagName = $tagDescription['TAG'];

			// get values list

			$tagValues = [];

			if (isset($tagDescription['VALUE']))
			{
				$tagValue = $this->getSourceValue($tagDescription['VALUE'], $sourceValues);

				if (is_array($tagValue))
				{
					$tagValues = $tagValue;
				}
				else
				{
					$tagValues[] = $tagValue;
				}
			}
			else
			{
				$tagValues[] = null;
			}

			// settings

			$tagSettings = isset($tagDescription['SETTINGS']) ? $tagDescription['SETTINGS'] : null;

			if ($tagSettings !== null && is_array($tagSettings))
			{
				foreach ($tagSettings as $settingName => $setting)
				{
					if (isset($setting['TYPE'], $setting['FIELD']))
					{
						if ($setting['TYPE'] === Market\Export\Entity\Manager::TYPE_TEXT)
						{
							$tagSettings[$settingName] = $setting['FIELD'];
						}
						else
						{
							$tagSettings[$settingName] = $this->getSourceValue($setting, $sourceValues);
						}
					}
				}
			}

			// fill available keys and load attributes

			$valueKeys = array_flip(array_keys($tagValues));
			$attributeValues = [];

			if (isset($tagDescription['ATTRIBUTES']))
			{
				foreach ($tagDescription['ATTRIBUTES'] as $attributeName => $attributeSourceMap)
				{
					$attributeValue = $this->getSourceValue($attributeSourceMap, $sourceValues);

					if (is_array($attributeValue))
					{
						foreach ($attributeValue as $valueKey => $value)
						{
							if (!isset($valueKeys[$valueKey]))
							{
								$valueKeys[$valueKey] = true;
							}
						}
					}

					$attributeValues[$attributeName] = $attributeValue;
				}
			}

			// export values

			foreach ($valueKeys as $valueKey => $dummy)
			{
				$tagValue = isset($tagValues[$valueKey]) ? $tagValues[$valueKey] : null;
				$isEmptyTagValue = $this->isEmptyXmlValue($tagValue); // is empty
				$tagAttributeList = [];

				foreach ($attributeValues as $attributeName => $attributeValue)
				{
					if (is_array($attributeValue))
					{
						$attributeValue = isset($attributeValue[$valueKey]) ? $attributeValue[$valueKey] : null;
					}

					$tagAttributeList[$attributeName] = $attributeValue;

					if (!$this->isEmptyXmlValue($attributeValue)) // is not empty
					{
						$isEmptyTagValue = false;
					}
				}

				if (!$isEmptyTagValue && !$result->hasTag($tagName, $tagValue, $tagAttributeList))
				{
					$result->addTag($tagName, $tagValue, $tagAttributeList, $tagSettings);
				}
			}
		}

		return $result;
	}

	protected function getSourceValue($sourceMap, $sourceValues)
	{
		$result = null;

		if (isset($sourceMap['VALUE']))
		{
			$result = $sourceMap['VALUE'];
		}
		else if (isset($sourceValues[$sourceMap['TYPE']][$sourceMap['FIELD']]))
		{
			$result = $sourceValues[$sourceMap['TYPE']][$sourceMap['FIELD']];
		}

		return $result;
	}

	protected function isEmptyXmlValue($value)
	{
		if ($value === null)
		{
			$result = true;
		}
		else if (is_scalar($value))
		{
			$result = (trim($value) === '');
		}
		else
		{
			$result = empty($value);
		}

		return $result;
	}

	protected function applyValueConflict($elementValue, $conflictAction)
	{
		switch ($conflictAction['TYPE'])
		{
			case 'INCREMENT':
				$result = $elementValue + $conflictAction['VALUE'];
			break;

			default:
				$result = $elementValue;
			break;
		}

		return $result;
	}
}