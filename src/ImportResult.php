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
}
