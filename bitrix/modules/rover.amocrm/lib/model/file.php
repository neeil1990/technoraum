<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 17.10.2017
 * Time: 8:02
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */

namespace Rover\AmoCRM\Model;

/**
 * Class File
 *
 * @package Rover\AmoCRM\Model
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class File
{
    /**
     * @param $id
     * @return null|string
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getValueById($id)
    {
        $id = intval($id);

        if (empty($id))
            return null;

        $file = \CFile::GetFileArray($id);
        if (empty($file['SRC']))
            return null;

        // external file
        if (empty($file['~src'])){
            // internal file
            $protocol = \CMain::IsHTTPS()
                ? 'https://'
                : 'http://';

            // use first site domain
            $site = Site::getFirst();
            $domain = isset($site['SERVER_NAME'])
                ? $site['SERVER_NAME']
                : '<not defined>';

            $path = $protocol . str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR,
                DIRECTORY_SEPARATOR, $domain . $file['SRC']);
        } else {
            $path = $file['~src'];
        }

        $result = isset($file['ORIGINAL_NAME'])
            ? $file['ORIGINAL_NAME'] . ' (' . $path . ')'
            : $path;

        return $result;
    }
}