<?php

namespace Its\Crm;

class FieldText extends Field
{
    /**
     * getValue
     *
     *
     * @return
     */
    public function getValue()
    {
        $this->answerValue['USER_TEXT'];
        return $this->answerValue['USER_TEXT'];
    }

    /**
     * mapResult
     *
     * @param string $fieldCode
     * @param string $fieldTemplate
     *
     * @return string
     */
    public function mapResult($fieldCode = null, $fieldTemplate = null)
    {
        $fieldValue = str_replace('#'.$fieldCode.'#', $this->getValue(), $fieldTemplate);

        return $fieldValue;
    }
}
