<?php


namespace Devim\Component\DataImporter;


class ImportResult
{
    /**
     * @var int
     */
    private $successDataCounter = 0;

    /**
     * @var array
     */
    private $errorData = [];

    private $selectTime = [];

    private $convertTime = [];

    private $insertTime = [];

    /**
     *
     * @return $this
     */
    public function incrementSuccessCount()
    {
        $this->successDataCounter++;

        return $this;
    }

    /**
     *
     * @param array|string $errors
     * @return $this
     */
    public function addErrors($errors)
    {
        array_push($this->errorData, ...$errors);

        return $this;
    }

    /**
     * TODO:
     * @return int
     */
    public function getSuccessDataCounter(): int
    {
        return $this->successDataCounter;
    }

    /**
     * TODO:
     * @return array
     */
    public function getErrorData(): array
    {
        return $this->errorData;
    }

    public function addSelectTime(float $time): self
    {
        $this->selectTime[] = $time;
        return $this;
    }

    public function addConvertTime(float $time): self
    {
        $this->convertTime[] = $time;
        return $this;
    }

    public function addInsertTime(float $time): self
    {
        $this->insertTime[] = $time;

        return $this;
    }

    public function getSelectTime(): array
    {
        return $this->selectTime;
    }

    public function getConvertTime(): array
    {
        return $this->convertTime;
    }

    public function getInsertTime(): array
    {
        return $this->insertTime;
    }
}
