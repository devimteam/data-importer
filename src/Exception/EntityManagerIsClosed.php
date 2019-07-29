<?php


namespace Devim\Component\DataImporter\Exception;


class EntityManagerIsClosed extends \RuntimeException
{
    /**
     * @var array
     */
    private $data;

    /**
     * TODO:
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * TODO:
     * @param array $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}
