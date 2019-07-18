<?php

namespace Devim\Component\DataImporter\ExceptionHandler;

/**
 * Обработчик исключений, которые происходят при импорте
 * Interface ImportExceptionHandlerInterface
 */
interface ImportExceptionHandlerInterface
{
    public function handle(\Throwable $exception, $data);
}
