<?php

namespace Devim\Component\DataImporter\Reader;

use Devim\Component\DataImporter\Dto\ImportParameters;

/**
 * Class AbstractReader
 */
abstract class AbstractReader implements ReaderInterface
{
    /**
     * @var ImportParameters $importParameters
     */
    protected $importParameters;

    /**
     */
    public function beforeRead()
    {
    }

    /**
     * @return void
     */
    public function afterRead()
    {
    }

    /**
     * TODO:
     * @return mixed
     */
    public function getImportParameters()
    {
        return $this->importParameters;
    }

    /**
     * TODO:
     * @param ImportParameters $importParameters
     * @return $this
     */
    public function setImportParameters(ImportParameters $importParameters)
    {
        $this->importParameters = $importParameters;

        return $this;
    }
}
