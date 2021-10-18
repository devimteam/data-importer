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

    public function getSelectTime(): float
    {
        if (!count($this->selectTime)) {
            return 0.0;
        }

        return array_sum($this->selectTime) / count($this->selectTime);
    }

    public function getConvertTime(): float
    {
        if (!count($this->convertTime)) {
            return 0.0;
        }

        return array_sum($this->convertTime) / count($this->convertTime);
    }

    public function getInsertTime(): float
    {
        if (!count($this->insertTime)) {
            return 0.0;
        }

        return array_sum($this->insertTime) / count($this->insertTime);
    }
}
