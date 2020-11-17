<?php

namespace Its\Crm;

class FieldFile extends Field
{
    public function getValue()
    {
        $fileId = $this->answerValue['USER_FILE_ID'];
        $fileName = $this->answerValue['USER_FILE_NAME'];

        if (!$fileId) {
            return false;
        }

        $path = $_SERVER['DOCUMENT_ROOT'] . \CFile::GetPath($fileId);
        $data = file_get_contents($path);
        $base64 = base64_encode($data);
        $name = urldecode($fileName);

        return [['fileData' => [$name, $base64]]];
    }
}
