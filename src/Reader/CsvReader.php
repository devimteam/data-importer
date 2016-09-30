<?php

namespace Devimteam\Component\DataImporter\Reader;

/**
 * Class CsvReader
 */
class CsvReader extends AbstractReader
{
    const DEFAULT_DELIMITER = ',';
    const DEFAULT_LENGTH = null;

    /**
     * @var string
     */
    private $file;

    /**
     * @var string
     */
    private $delimiter;

    /**
     * @var int
     */
    private $length;

    /**
     * CsvReader constructor.
     * @param string $file
     * @param $delimiter
     * @param $length
     */
    public function __construct(
        string $file,
        string $delimiter = self::DEFAULT_DELIMITER,
        ?int $length = self::DEFAULT_LENGTH
    ) {
        $this->file = $file;
        $this->delimiter = $delimiter;
        $this->length = $length;
    }

    /**
     * @return mixed
     */
    public function read()
    {
        if(!file_exists($this->file)) {
            throw new \RuntimeException(sprintf('File "%s" does\'t exists', $this->file));
        }

        if (($handle = fopen($this->file, 'r')) === false) {
            throw new \RuntimeException(sprintf('File "%s" won\'t open', $this->file));
        }

        while (($data = fgetcsv($handle, $this->length, $this->delimiter)) !== false) {
            yield $data;
        }

        fclose($handle);
    }
}
