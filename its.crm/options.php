<?php

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

defined('ADMIN_MODULE_NAME') or define('ADMIN_MODULE_NAME', 'its.crm');

if (!$USER->isAdmin()) {
    $APPLICATION->authForm('Nope');
}

$app = Application::getInstance();
$context = $app->getContext();
$request = $context->getRequest();

Loc::loadMessages($context->getServer()->getDocumentRoot()."/bitrix/modules/main/options.php");
Loc::loadMessages(__FILE__);

$tabControl = new CAdminTabControl("tabControl", array(
    array(
        "DIV" => "edit1",
        "TAB" => Loc::getMessage("MAIN_TAB_SET"),
        "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_SET"),
    ),
));

if ((!empty($save) || !empty($restore)) && $request->isPost() && check_bitrix_sessid()) {
    if (!empty($restore)) {
        Option::delete(ADMIN_MODULE_NAME);
        CAdminMessage::showMessage(array(
            "MESSAGE" => Loc::getMessage("REFERENCES_OPTIONS_RESTORED"),
            "TYPE" => "OK",
        ));
    } elseif (true) {
        Option::set(
            ADMIN_MODULE_NAME,
            'debug',
            $request->getPost('debug')
        );
        Option::set(
            ADMIN_MODULE_NAME,
            'notsend',
            $request->getPost('notsend')
        );
        Option::set(
            ADMIN_MODULE_NAME,
            'b24url',
            $request->getPost('b24url')
        );
        Option::set(
            ADMIN_MODULE_NAME,
            'assigned_by',
            $request->getPost('assigned_by')
        );

        CAdminMessage::showMessage(array(
            "MESSAGE" => Loc::getMessage("REFERENCES_OPTIONS_SAVED"),
            "TYPE" => "OK",
        ));
    } else {
        CAdminMessage::showMessage(Loc::getMessage("REFERENCES_INVALID_VALUE"));
    }
}

$tabControl->begin();
?>

<form method="post" action="<?=sprintf('%s?mid=%s&lang=%s', $request->getRequestedPage(), urlencode($mid), LANGUAGE_ID)?>">
    <?php
    echo bitrix_sessid_post();
    $tabControl->beginNextTab();
    ?>
    <tr>
        <td width="40%">
            <label for="sections_sort"><?=Loc::getMessage("BEX_CRM_DEBUG_MODE") ?>:</label>
        <td width="60%">
            <input type="checkbox"
                   name="debug"
                    <?=Option::get(ADMIN_MODULE_NAME, 'debug', '') === 'Y' ? 'checked' : '';?>
                   value="Y"
                   />
        </td>
    </tr>
    <tr>
        <td width="40%">
            <label for="sections_sort"><?=Loc::getMessage("BEX_CRM_NOT_SEND") ?>:</label>
        <td width="60%">
            <input type="checkbox"
                   name="notsend"
                    <?=Option::get(ADMIN_MODULE_NAME, 'notsend', '') === 'Y' ? 'checked' : '';?>
                   value="Y"
                   />
        </td>
    </tr>
    <tr>
        <td width="40%">
            <label for="sections_sort"><?=Loc::getMessage("BEX_CRM_B24_LID_URL") ?>:</label>
        </td>
        <td width="60%">
            <input type="text" name="b24url"  value="<?=Option::get(ADMIN_MODULE_NAME, 'b24url', '');?>" style="width: 100%;"/>
            <div style="margin-top: 10px; opacity: 0.5;">
                Похож на такой: https://portal.supersite.ru/rest/137/e339v0t5i2kgf2vv/crm.lead.add.json
            </div>
        </td>
    </tr>
    <tr>
        <td width="40%">
            <label for="sections_sort"><?=Loc::getMessage("BEX_CRM_ASSIGNED_BY") ?>:</label>
        </td>
        <td width="60%">
            <input type="text" name="assigned_by"  value="<?=Option::get(ADMIN_MODULE_NAME, 'assigned_by', '');?>"/>
            <div style="margin-top: 10px; opacity: 0.5;">
                Число. Например: 183. Если не указан в настройках формы, то возьмет этот
            </div>
        </td>
    </tr>

    <?php
    $tabControl->buttons();
    ?>
    <input type="submit"
           name="save"
           value="<?=Loc::getMessage("MAIN_SAVE") ?>"
           title="<?=Loc::getMessage("MAIN_OPT_SAVE_TITLE") ?>"
           class="adm-btn-save"
           />
    <input type="submit"
           name="restore"
           title="<?=Loc::getMessage("MAIN_HINT_RESTORE_DEFAULTS") ?>"
           onclick="return confirm('<?=AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING")) ?>')"
           value="<?=Loc::getMessage("MAIN_RESTORE_DEFAULTS") ?>"
           />
    <?php
    $tabControl->end();
    ?>
</form>
