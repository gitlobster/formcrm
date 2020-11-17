<?php

namespace Its\Crm;

abstract class Field
{
    /**
     * answerValue
     *
     * @var array
     */
    protected $answerValue;

    abstract protected function getValue();

    /**
     * __construct
     *
     * @param array $answerValue
     *
     * @return
     */
    public function __construct($answerValue)
    {
        $this->answerValue = $answerValue;
    }

    /**
     * mapResult
     *
     * @param mixed $fieldCode
     * @param mixed $fieldTemplate
     *
     * @return mixed
     */
    public function mapResult($fieldCode = null, $fieldTemplate = null)
    {
        return $this->getValue();
    }
}
