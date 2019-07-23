<?php

namespace Devim\Component\DataImporter\Reader;

use Devim\Component\DataImporter\Dto\ImportParameters;

interface ReaderInterface
{
    /**
     * @return void
     */
    public function beforeRead();

    /**
     * @return void
     */
    public function afterRead();

    /**
     * @return mixed
     */
    public function read();

    /**
     *
     * @param ImportParameters $importParameters
     * @return mixed
     */
    public function setImportParameters(ImportParameters $importParameters);
}
