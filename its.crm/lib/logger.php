<?php

namespace Its\Crm;

use Bitrix\Main\Application;

class Logger
{
    public static function add($data, $var)
    {
        $dateTime = new \Bitrix\Main\Type\DateTime();
        \Bitrix\Main\Diag\Debug::WriteToFile($data, $dateTime->format("H:i:s m-d") .  ' ' . $var, "/crm-debug.txt");
    }
}
