<?php

namespace Devim\Component\DataImporter\Reader;

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
}
