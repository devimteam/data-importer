<?php

namespace Devim\Component\DataImporter\Writer;

/**
 * Interface WriterInterface.
 */
interface WriterInterface
{
    /**
     * @return mixed
     */
    public function prepare();

    /**
     * @param $data
     *
     * @return mixed
     */
    public function writeData($data);

    /**
     * @return mixed
     */
    public function finish();
}
