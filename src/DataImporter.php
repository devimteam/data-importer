<?php

namespace Devim\Component\DataImporter;

use Devim\Component\DataImporter\Converter\ConverterInterface;
use Devim\Component\DataImporter\Exception\BadPersistsData;
use Devim\Component\DataImporter\Exception\EntityManagerIsClosed;
use Devim\Component\DataImporter\Exception\UnexpectedTypeException;
use Devim\Component\DataImporter\Filter\FilterInterface;
use Devim\Component\DataImporter\Reader\ReaderInterface;
use Devim\Component\DataImporter\Writer\WriterInterface;
use Devim\Component\DataImporter\ExceptionHandler\ImportExceptionHandlerInterface;
use Devim\Component\DataImporter\ExceptionHandler\LogHandler;
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
     * @var ImportExceptionHandlerInterface
     */
    private $exceptionHandler;

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
     * @param ImportExceptionHandlerInterface|null $handler
     */
    public function __construct(
        ReaderInterface $reader,
        array $writers,
        array $converters = [],
        array $filters = [],
        ImportExceptionHandlerInterface $handler = null
    ) {
        $this->reader                   = $reader;
        $this->exceptionHandler         = $handler ?: new LogHandler();
        $this->beforeConvertFilterQueue = new \SplPriorityQueue();
        $this->afterConvertFilterQueue  = new \SplPriorityQueue();

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

        $this->reader->beforeRead();

        foreach ($this->reader->read() as $data) {
            try {
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
            //Если EntityManager закрыт сделать ничего нельзя, выходим из программы
            catch (EntityManagerIsClosed $exception){
                $this->exceptionHandler->handle($exception, $exception->getData());

                throw $exception;
            }catch (BadPersistsData $persistsDataException){
                $this->exceptionHandler->handle($persistsDataException, $persistsDataException->getData());
            }
            //Сталкивался с type error, когда в арчи кривые данные лежали
            catch (\Throwable $e) {
                $this->exceptionHandler->handle($e, $data);
            }
        }

        $this->reader->afterRead();

        try {
            $this->doFinish();
        }catch (EntityManagerIsClosed $exception){
            $this->exceptionHandler->handle($exception, $exception->getData());

            throw $exception;
        }catch (BadPersistsData $exception){
            $this->exceptionHandler->handle($exception, $exception->getData());
        }
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
    protected function filterData($data, \SplPriorityQueue $filters): bool
    {
        foreach (clone $filters as $filter) {
            if ($this->isBatchRequest($data)) {
                foreach ($data as $item) {
                    if (false == $filter->filter($item)) {
                        return false;
                    }
                }
            } else {
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
     * @return mixed
     * @throws UnexpectedTypeException
     *
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
    private function isBatchRequest(array $payload): bool
    {
        return array_keys($payload) === range(0, count($payload) - 1);
    }
}
