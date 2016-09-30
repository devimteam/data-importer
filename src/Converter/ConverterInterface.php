<?php

namespace Devimteam\Component\DataImporter\Converter;

interface ConverterInterface
{
    /**
     * @param mixed $data
     *
     * @return mixed
     */
    public function convert($data);
}
