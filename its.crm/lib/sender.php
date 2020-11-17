<?php

namespace Its\Crm;

use \Its\Crm\Logger;
use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Bitrix\Main\Config\Option;

class Sender
{
    public function __construct()
    {
    }

    public function postToCrmInternal($url, $fields)
    {
        return $this->send($url, $fields);
    }

    private function send($actionUrl, $fields)
    {
        $result = new Result();

        if (Option::get('its.crm', 'debug', '') === 'Y') {
            Logger::add($fields, "Поля, что отправятся");
        }

        if (Option::get('its.crm', 'notsend', '') === 'Y') {
            if (Option::get('its.crm', 'debug', '') === 'Y') {
                Logger::add("Отключена отправка в б24 в настройках модуля", "");
            }

            $result->setData(['message' => 'not send by module option']);
            return $result;
        }

        $queryData = http_build_query(array(
            'fields' => $fields,
        ));

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $actionUrl,
            CURLOPT_POSTFIELDS => $queryData,
        ));
        $curlResult = curl_exec($curl);

        if (curl_error($curl)) {
            $result->addError(new Error(
                curl_error($curl),
                'curl'
            ));

            if (Option::get('its.crm', 'debug', '') === 'Y') {
                Logger::add(curl_error($curl), 'Б24 ошибка');
            }
        }
        curl_close($curl);

        if ($result->isSuccess()) {
            $result->setData(json_decode($curlResult, true));
        }

        return $result;
    }
}
