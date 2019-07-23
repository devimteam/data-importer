<?php
namespace Devim\Component\DataImporter\Dto;

class ImportParameters
{
    private $startTime;

    /**
     * TODO:
     * @return mixed
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * TODO:
     * @param mixed $startTime
     * @return $this
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;

        return $this;
    }
}
