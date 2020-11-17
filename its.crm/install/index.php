<?php

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class its_crm extends CModule
{
    public function __construct()
    {
        $arModuleVersion = array();
        
        include __DIR__ . '/version.php';

        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }
        
        $this->MODULE_ID = 'its.crm';
        $this->MODULE_NAME = Loc::getMessage('BEX_CRM_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('BEX_CRM_MODULE_DESCRIPTION');
        $this->MODULE_GROUP_RIGHTS = 'N';
        $this->PARTNER_NAME = Loc::getMessage('BEX_CRM_MODULE_PARTNER_NAME');
        $this->PARTNER_URI = 'http://its.agency';
    }

    public function doInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);
        $this->installDB();
    }

    public function installDB(){
        Loader::includeModule($this->MODULE_ID);
        $connection = \Bitrix\Main\Application::getConnection();

        foreach($this->getTables() as $tableClass){
            if(!$connection->isTableExists($tableClass::getTableName())) {
                $tableClass::getEntity()->createDbTable();
            }
        }

        return true;
    }

    public function doUninstall()
    {
        $this->uninstallDB();
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }


    public function uninstallDB() {
        Loader::includeModule($this->MODULE_ID);
        $connection = Application::getInstance()->getConnection();

        foreach($this->getTables() as $tableClass){
            if ($connection->isTableExists($tableClass::getTableName())) {
                $connection->dropTable($tableClass::getTableName());
            }
        }

        return true;
    }

    /**
     * @return DataManager[]
     */
    private function getTables():array {
        return [
            \Its\Crm\FailExportLidTable::class
        ];
    }
}
