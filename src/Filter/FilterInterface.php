<?php

namespace Devim\Component\DataImporter\Filter;

interface FilterInterface
{
    /**
     * @param mixed $data
     *
     * @return bool
     */
    public function filter($data) : bool;
}
