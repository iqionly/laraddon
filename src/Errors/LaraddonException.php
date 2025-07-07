<?php declare(strict_types=1);

namespace Iqionly\Laraddon\Errors;

use Exception;

class LaraddonException extends Exception
{

    /**
     * Report the exception.
     */
    public function report(): bool
    {
        $this->message = 'Laraddon[' . $this->getCode() . ']: ' . $this->getMessage() . PHP_EOL;
        return true;
    }
}