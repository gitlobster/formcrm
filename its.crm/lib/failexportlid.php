<?php

namespace Its\Crm;

use Bitrix\Main\Entity;

class FailExportLidTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'failedlids';
    }

    public static function getMap()
    {
        return [
            new Entity\IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true
            ]),
            new Entity\StringField('UF_NAME'),
            new Entity\StringField('UF_FORM_ID'),
            new Entity\StringField('UF_RESULT_ID'),
            new Entity\BooleanField('UF_WAS_SEND'),
            new Entity\DateTimeField('UF_DATE')
        ];
    }
}
