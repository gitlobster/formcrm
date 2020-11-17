<?php

function resendLidToBitrix24()
{
    $listOfLidsToSend = \Its\Crm\FailExportLidTable::getList(
        [
            'filter' => [
                '!UF_WAS_SEND' => true
            ],
            'select' => [
                'ID',
                'UF_FORM_ID',
                'UF_RESULT_ID'
            ]
        ]
    )->fetchAll();

    array_walk($listOfLidsToSend, function ($item) {
        $crmForm = new \Its\Crm\Form($item['UF_FORM_ID'], $item['UF_RESULT_ID']);
        $result = $crmForm->sendToB24(true);

        if ($result) {
            \Its\Crm\FailExportLidTable::update(
                $item['ID'],
                [
                    'UF_WAS_SEND' => true,
                    'UF_DATE'  => new \Bitrix\Main\Type\DateTime()
                ]
            );
        }
    });


    return 'resendLidToBitrix24();';
}
