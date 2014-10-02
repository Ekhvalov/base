<?php
namespace WP\Base;

interface IErrorHandler
{
    /**
     * Обработать ошибку PHP.
     */
    public function handlePhpError($type, $error, $file, $line);

    /**
     * Обработать исключение
     */
    public function handleException(\Exception $exception);
}
