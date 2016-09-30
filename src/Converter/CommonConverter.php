<?php

namespace Devimteam\Component\DataImporter\Converter;

/**
 * Class CommonConverter
 */
class CommonConverter implements ConverterInterface
{
    /**
     * @param mixed $data
     *
     * @return mixed
     */
    public function convert($data)
    {
        return array_map(function ($value) {
            if (!is_scalar($value)) {
                return $value;
            }

            return trim(str_replace(array("\s"), '', $value));
        }, $data);
    }
}
