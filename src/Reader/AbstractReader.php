<?php

namespace Devim\Component\DataImporter\Reader;

/**
 * Class AbstractReader
 */
abstract class AbstractReader implements ReaderInterface
{
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
}
