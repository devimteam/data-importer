<?php


namespace Devim\Component\DataImporter\Exception;


class BadPersistsData extends \RuntimeException
{
    private $data;

    /**
     * TODO:
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * TODO:
     * @param mixed $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}
