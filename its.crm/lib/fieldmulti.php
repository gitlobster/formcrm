<?php

namespace Its\Crm;

class FieldMulti extends Field
{
    private $type = 'WORK';

    public function getValue()
    {
        $value = $this->answerValue['USER_TEXT'];

        return [['VALUE' => $value, 'VALUE_TYPE' => $this->type]];
    }
}
