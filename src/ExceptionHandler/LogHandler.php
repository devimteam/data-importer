<?php

namespace Devim\Component\DataImporter\ExceptionHandler;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class LogHandler implements ImportExceptionHandlerInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger ?: new NullLogger();
    }

    public function handle(\Throwable $exception, $data){
        $this->logger->error($exception->getMessage(), $data ?? []);
    }
}
