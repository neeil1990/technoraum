<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 15.12.2016
 * Time: 0:12
 *
 * @author Pavel Shulaev (http://rover-it.me)
 */

namespace Rover\Fadmin\Inputs;

use Rover\Fadmin\Options;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
/**
 * Class Schedule
 *
 * @package Rover\Fadmin\Inputs
 * @author  Pavel Shulaev (http://rover-it.me)
 */
class Schedule extends Input
{
	/** @var string*/
	protected $periodLabel;

	/** @var int */
	protected $height = 300;

	/** @var int */
	protected $width = 500;

    /** @var array */
	protected $inputValue = array();

    /**
     * Schedule constructor.
     *
     * @param array      $params
     * @param Options    $options
     * @param Input|null $parent
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     */
	public function __construct(array $params, Options $options, Input $parent = null)
	{
		// for automatic serialize/unserialize
		$params['multiple'] = true;

		parent::__construct($params, $options, $parent);

		$this->periodLabel = isset($params['periodLabel'])
			? $params['periodLabel']
			: Loc::getMessage('rover-fa__schedule-default-period');

		if (isset($params['width']) && intval($params['width']))
			$this->width = $params['width'];

		if (isset($params['height']) && intval($params['height']))
			$this->height = $params['height'];
	}

    /**
     * @param $value
     * @return bool|mixed
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function beforeSaveRequest(&$value)
	{
		$periods    = json_decode($value, true);

		if (is_array($periods)){

			$value = $this->preparePeriodsDates($periods);
			$value = $this->pastePeriodsTogether($value);
			$value = $this->markWeekDays($value);

		} else
			$value = array();

		return true;
	}

	/**
	 * @param $periods
	 * @return array
	 * @author Pavel Shulaev (http://rover-it.me)
	 */
	protected function markWeekDays($periods)
	{
		$result = array();

		foreach ($periods as $period)
		{
		    $dateStartObj   = new \DateTime();
		    $dateEndObj     = new \DateTime();

			$dateStart  = $dateStartObj->setTimestamp($period['start']);
			$dateEnd    = $dateEndObj->setTimestamp($period['end']);

			$result[] = array(
				'startWeekDay'  => $dateStart->format('l'),
				'startTime'     => $dateStart->format('H:i:s'),
				'endWeekDay'    => $dateEnd->format('l'),
				'endTime'       => $dateEnd->format('H:i:s'),
            );
		}

		return $result;
	}

	/**
	 * make timestamps from periods` dates, remove invalid periods
	 * @param array $periods
	 * @return array
	 * @author Pavel Shulaev (http://rover-it.me)
	 */
	protected function preparePeriodsDates(array $periods)
	{
		$result = array();

		$minTimestamp = $this->getMinTimestamp();
		$maxTimestamp = $this->getMaxTimestamp();

		foreach ($periods as $period)
		{
			$period['start']    = $this->createTimestamp($period['start']);
			$period['end']      = $this->createTimestamp($period['end']);

			if ($period['start'] < $minTimestamp)
				$period['start'] = $minTimestamp;

			if ($period['end'] > $maxTimestamp)
				$period['end'] = $maxTimestamp;

			if (intval($period['start']) && intval($period['end'])
				&& ($period['start'] < $period['end']))
				$result[] = $period;
		}

		return $result;
	}

	/**
	 * @param $periods
	 * @return array
	 * @author Pavel Shulaev (http://rover-it.me)
	 */
	protected function pastePeriodsTogether(array $periods)
	{
		do {
			$result = array();
			$pasted = false;

			foreach ($periods as $periodNum => $period){

				// first value
				if (!count($result)) {
					$result[] = $period;
					continue;
				}

				$periodInResult = false;

				foreach ($result as &$resultPeriod){

					if (($period['start'] >= $resultPeriod['start'])
						&& ($period['start'] <= $resultPeriod['end']))
					{
						if ($period['end'] > $resultPeriod['end']){
							$resultPeriod['end'] = $period['end'];
							$pasted = true;
						}

						$periodInResult = true;

						break;
					}

					if (($period['end'] <= $resultPeriod['end'])
						&& ($period['end'] >= $resultPeriod['start']))
					{
						if ($period['start'] < $resultPeriod['start']){
							$resultPeriod['start'] = $period['start'];
							$pasted = true;
						}

						$periodInResult = true;

						break;
					}
				}

				if (!$periodInResult)
					$result[] = $period;
			}

			$periods = $result;

		} while ($pasted);

		return $result;
	}

	/**
	 * @param $time
	 * @return int|null
	 * @author Pavel Shulaev (http://rover-it.me)
	 */
	protected function createTimestamp($time)
	{
		$dateTime = \DateTime::createFromFormat('Y-m-d\TH:i:s', $time);

		if (false === $dateTime instanceof \DateTime)
			return null;

		return $dateTime->getTimestamp();
	}

	/**
	 * @return int
	 * @author Pavel Shulaev (http://rover-it.me)
	 */
	protected function getMinTimestamp()
	{
	    $dateTime = new \DateTime('Monday this week');

		return $dateTime->getTimestamp();
	}

	/**
	 * @return int
	 * @author Pavel Shulaev (http://rover-it.me)
	 */
	protected function getMaxTimestamp()
	{
	    $dateTime = new \DateTime('Monday next week');

		return $dateTime->getTimestamp() - 1;
	}

    /**
     * @param $value
     * @author Pavel Shulaev (https://rover-it.me)
     * @internal
     */
	public function afterLoadValue(&$value)
	{
		foreach ($value as &$period)
		{
			$period['start']    = $this->getDateByWeekDayTime($period['startWeekDay'], $period['startTime']);
			$period['end']      = $this->getDateByWeekDayTime($period['endWeekDay'], $period['endTime']);

			$period['jqwStartMonth']    = $period['start']->format('m') - 1;
			$period['jqwEndMonth']      = $period['end']->format('m') - 1;

			$this->inputValue[] = array(
				'start' => $period['start']->format('Y-m-d\TH:i:s'),
				'end'   => $period['end']->format('Y-m-d\TH:i:s')
            );
		}
	}

	/**
	 * @param $weekDay
	 * @param $time
	 * @return \DateTime
	 * @author Pavel Shulaev (http://rover-it.me)
	 */
	protected function getDateByWeekDayTime($weekDay, $time)
	{
		$date = new \DateTime($weekDay . ' this week');
		$time = explode(':', $time);
		$date->setTime($time[0], $time[1], $time[2]);

		return $date;
	}

    /**
     * @return string
     */
    public function getPeriodLabel()
    {
        return $this->periodLabel;
    }

    /**
     * @param string $periodLabel
     */
    public function setPeriodLabel($periodLabel)
    {
        $this->periodLabel = $periodLabel;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param int $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param int $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * @return array
     */
    public function getInputValue()
    {
        return $this->inputValue;
    }

    /**
     * @param array $inputValue
     */
    public function setInputValue($inputValue)
    {
        $this->inputValue = $inputValue;
    }
}