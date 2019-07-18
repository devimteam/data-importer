<?php

namespace Devim\Component\DataImporter;

use Devim\Component\DataImporter\Converter\ConverterInterface;
use Devim\Component\DataImporter\Exception\UnexpectedTypeException;
use Devim\Component\DataImporter\Filter\FilterInterface;
use Devim\Component\DataImporter\Reader\ReaderInterface;
use Devim\Component\DataImporter\Writer\WriterInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class DataImporter.
 */
class DataImporter
{
    /**
     * @var ReaderInterface
     */
    private $reader;

    /**
     * @var \Traversable|WriterInterface[]
     */
    private $writers = [];

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var ConverterInterface[]
     */
    private $converters = [];

    /**
     * @var \SplPriorityQueue
     */
    private $beforeConvertFilterQueue;

    /**
     * @var \SplPriorityQueue
     */
    private $afterConvertFilterQueue;


    /**
     * Importer constructor.
     *
     * @param ReaderInterface $reader
     * @param array|WriterInterface[] $writers
     * @param array|ConverterInterface[] $converters
     * @param array|FilterInterface[] $filters
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        ReaderInterface $reader,
        array $writers,
        array $converters = [],
        array $filters = [],
        LoggerInterface $logger = null
    ) {
        $this->reader = $reader;
        $this->logger = $logger ?: new NullLogger();
        $this->beforeConvertFilterQueue = new \SplPriorityQueue();
        $this->afterConvertFilterQueue = new \SplPriorityQueue();

        foreach ($writers as $writer) {
            $this->addWriter($writer);
        }

        foreach ($converters as $converter) {
            $this->addConverter($converter);
        }

        foreach ($filters as $filterOptions) {
            list($filter, $priority, $isAfterConvert) = $filterOptions;
            $this->addFilter($filter, $priority, $isAfterConvert);
        }

    }

    /**
     * @param WriterInterface $writer
     *
     * @return $this
     */
    public function addWriter(WriterInterface $writer)
    {
        $this->writers[] = $writer;

        return $this;
    }

    /**
     * @param ConverterInterface $converter
     *
     * @return $this
     */
    public function addConverter(ConverterInterface $converter)
    {
        $this->converters[] = $converter;

        return $this;
    }

    /**
     * @param FilterInterface $filter
     * @param int $priority
     * @param bool $isAfterConvert
     */
    public function addFilter(FilterInterface $filter, int $priority = 0, bool $isAfterConvert = false)
    {
        if ($isAfterConvert) {
            $this->afterConvertFilterQueue->insert($filter, $priority);
        } else {
            $this->beforeConvertFilterQueue->insert($filter, $priority);
        }
    }

    /**
     * @throws \Exception
     */
    public function process()
    {
        $this->doPrepare();

        try {
            $this->reader->beforeRead();

            foreach ($this->reader->read() as $data) {
                if (!$this->filterData($data, $this->beforeConvertFilterQueue)) {
                    continue;
                }

                $convertedData = $this->convertData($data);

                if (!$convertedData) {
                    continue;
                }

                if (!$this->filterData($convertedData, $this->afterConvertFilterQueue)) {
                    continue;
                }

                $this->doWriteData($convertedData);
            }

            $this->reader->afterRead();

        } catch (\Throwable $e) {
           $this->logger->error($e->getMessage(), $e->getTrace());
        }

        $this->doFinish();
    }

    /**
     */
    private function doPrepare()
    {
        foreach ($this->writers as $writer) {
            $writer->prepare();
        }
    }

    /**
     * @param mixed $data
     * @param \SplPriorityQueue|FilterInterface[] $filters
     *
     * @return bool
     */
    protected function filterData($data, \SplPriorityQueue $filters) : bool
    {
        foreach (clone $filters as $filter) {
            if ($this->isBatchRequest($data)) {
                foreach ($data as $item) {
                    if (false == $filter->filter($item)) {
                        return false;
                    }
                }
            }
            else {
                if (false == $filter->filter($data)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param mixed $data
     *
     * @throws UnexpectedTypeException
     *
     * @return mixed
     */
    protected function convertData($data)
    {
        foreach ($this->converters as $converter) {
            $data = $converter->convert($data);
        }

        if ($data && !(is_array($data) || ($data instanceof \ArrayAccess && $data instanceof \Traversable))) {
            throw new UnexpectedTypeException();
        }

        return $data;
    }

    /**
     * @param mixed $data
     */
    private function doWriteData($data)
    {
        foreach ($this->writers as $writer) {
            $writer->writeData($data);
        }
    }

    /**
     */
    private function doFinish()
    {
        foreach ($this->writers as $writer) {
            $writer->finish();
        }
    }

    /**
     * @param array $payload
     *
     * @return bool
     */
    private function isBatchRequest(array $payload) : bool
    {
        return array_keys($payload) === range(0, count($payload) - 1);
    }
}
