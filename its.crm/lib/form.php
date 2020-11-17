<?php

namespace Its\Crm;

use CForm;
use Bitrix\Main;
use Bitrix\Main\Entity;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

class Form
{
    /**
     * answerId
     *
     * @var int|null
     */
    private $answerId;
    /**
     * formId
     *
     * @var int
     */
    private $formId;
    /**
     * answers
     *
     * @var array
     */
    private $answers;

    /**
     * Form constructor.
     * @param int $formId
     * @param int $answerId
     * @throws Main\LoaderException
     */
    public function __construct($formId, $answerId)
    {
        Loader::includeModule('form');

        $this->answerId = $answerId;
        $this->formId = $formId;
        $this->answers = [];
    }


    /**
     * Main method for send data for b24. Get data from webform answer
     * and map it to form template
     *
     * @param bool $isResend
     *
     * @return Main\Result|false
     * @throws Main\ArgumentNullException
     * @throws Main\ArgumentOutOfRangeException|Main\LoaderException
     */
    public function sendToB24($isResend = false)
    {
        $fields = $this->getFieldsForSend();

        array_walk($fields, function (string &$fieldValue) {
            $this->mapDataToField($fieldValue);
        });

        if ($fields) {
            $sender = new Sender();
            $result = $sender->postToCrmInternal($this->getAddLidUrl(), $fields);

            if ($result->isSuccess()) {
                if (Option::get('its.crm', 'debug', '') === 'Y') {
                    Logger::add($result->getData(), 'Б24 ответил');
                }

                return $result;
            }

            if (!$isResend) {
                $this->addFailedSend($fields);
            }

            if (Option::get('its.crm', 'debug', '') === 'Y') {
                Logger::add($result->getErrors(), 'Ошибка отправки');
            }
        }

        return false;
    }


    /**
     * Read value template string and map values to it
     *
     * @param string $fieldValue
     * @return void
     */
    private function mapDataToField(&$fieldValue)
    {
        preg_match_all('/#(.*)#/U', $fieldValue, $matches);

        if ($matches) {
            array_walk($matches[1], function (string $fieldCode) use (&$fieldValue) {
                list($code, $specialType) = explode('|', $fieldCode);

                $answerValue = $this->getAnswerValue($code);

                if ($answerValue) {
                    $fieldType = $specialType ? $specialType : $answerValue['FIELD_TYPE'];
                    
                    switch ($fieldType) {
                        case 'file':
                            $fieldClass = FieldFile::class;
                            break;
                        case 'Multi':
                            $fieldClass = FieldMulti::class;
                            break;
                        case 'text':
                        case 'textarea':
                            $fieldClass = FieldText::class;
                            break;
                        default:
                            $fieldClass = FieldText::class;
                            break;
                    }

                    if (class_exists($fieldClass)) {
                        $field = new $fieldClass($answerValue);
                        $fieldValue = $field->mapResult($fieldCode, $fieldValue);
                    }
                } else {
                    $fieldValue = '';
                }
            });
        }
    }

    /**
     * Get value by SID from webform result database
     *
     * @param string $fieldCode
     *
     * @return array|Bool
     */
    public function getAnswerValue($fieldCode)
    {
        if (!$this->answers) {
            CForm::GetResultAnswerArray(
                $this->formId,
                $arrColumns,
                $arrAnswers,
                $arrAnswersVarname,
                ['RESULT_ID' => $this->answerId]
            );
            unset($arrColumns);
            unset($arrAnswers);

            array_walk($arrAnswersVarname[$this->answerId], function ($value, $answerCode) {
                $this->answers[$answerCode] = $value[0];
            });
        }

        if (isset($this->answers[$fieldCode])) {
            return $this->answers[$fieldCode];
        }

        return false;
    }

    /**
     * Add failed form lid send to database for resend later
     *
     * @param array $fields
     * @return Entity\AddResult
     * @throws \Exception
     */
    private function addFailedSend($fields)
    {
        return FailExportLidTable::add([
            'UF_NAME' => $fields['TITLE'],
            'UF_FORM_ID' => $this->formId,
            'UF_RESULT_ID' => $this->answerId,
            'UF_WAS_SEND' => false,
            'UF_DATE' => new Main\Type\DateTime()
        ]);
    }

    /**
     * Load array fields where key is field name in b24
     * and value is template value or default field value
     *
     * @return array
     * @throws Main\LoaderException
     */
    private function getFieldsForSend()
    {
        $baseFields = [
            'SOURCE_ID' => 'WEB',
            'OPENED' => 'Y',
            'NAME' => '#Name#',
            'PHONE' => '#Phone|Multi#',
            'EMAIL' => '#Email|Multi#',
            'ASSIGNED_BY_ID' => $this->getAssignedBy(),
        ];

        $fromSettings = $this->loadFromSettings();
        $utmFields = $this->getUtm();

        return array_merge($baseFields, $utmFields, $fromSettings);
    }

    /**
     * Get utm methods for analitic from its.utm module
     *
     * @return array
     * @throws Main\LoaderException
     */
    private function getUtm()
    {
        $result = [];
        if (Loader::includeModule('its.utm')) {
            $listNames = [
                'utm_campaign',
                'utm_content',
                'utm_medium',
                'utm_source',
                'utm_term'
            ];

            $store = new \Its\Utm\SessionStore();
            $utmList = new \Its\Utm\Ulist($listNames, $store);
            $utmList->initFromStore();

            $result = $utmList->getArList();
        }

        return $result;
    }

    /**
     * Get fields template from webform description
     *
     * @return array
     */
    private function loadFromSettings()
    {
        $formInfo = $this->getFormInfo();
        $description = $formInfo['DESCRIPTION'];

        $fields = explode("\n", $description);

        $result = [];
        foreach ($fields as $field) {
            list($code, $value) = explode('=', $field);
            $result[$code] = trim($value);
        }

        return $result;
    }

    /**
     * Get webform fields from base
     *
     * @return array
     */
    private function getFormInfo()
    {
        $res = CForm::GetByID($this->formId);
        $form = $res->Fetch();

        return $form;
    }

    /**
     * Get url from module options for add lead to b24
     *
     * @return string
     * @throws Main\ArgumentNullException
     * @throws Main\ArgumentOutOfRangeException
     */
    private function getAddLidUrl()
    {
        return Option::get('its.crm', 'b24url', '');
    }

    /**
     * Get assigned user id from module option
     *
     * @return string
     * @throws Main\ArgumentNullException
     * @throws Main\ArgumentOutOfRangeException
     */
    private function getAssignedBy()
    {
        return Option::get('its.crm', 'assigned_by', '');
    }
}
