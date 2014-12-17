<?php
namespace Icecave\Stump;

use Exception;

class ExceptionRenderer implements ExceptionRendererInterface
{
    /**
     * @param Exception $exception The exception to render.
     *
     * @return string the rendered exception.
     */
    public function render(Exception $exception)
    {
        $rendered        = [];
        $renderException = $exception;
        while ($renderException) {
            $rendered[]      = $this->renderException($renderException);
            $rootCause       = $renderException->getMessage();
            $renderException = $renderException->getPrevious();
        }

        $string  = 'MESSAGE: ' . $exception->getMessage() . PHP_EOL;
        $string .= 'CAUSE: ' . $rootCause . PHP_EOL;
        $string .= PHP_EOL . PHP_EOL;
        $string .= implode(PHP_EOL, $rendered);

        return trim($string);
    }

    /**
     * @param Exception $exception The exception to render.
     *
     * @return string rendered exception.
     */
    private function renderException(Exception $exception)
    {
        $string  = 'TYPE: ' . get_class($exception) . PHP_EOL;
        $string .= 'MESSAGE: ' . $exception->getMessage() . PHP_EOL;
        $string .= 'FILE: ' . $exception->getFile() . PHP_EOL;
        $string .= 'LINE: ' . $exception->getLine() . PHP_EOL;
        $string .= 'CODE: ' . $exception->getCode() . PHP_EOL;
        $string .= 'TRACE:' . PHP_EOL;
        $string .= $exception->getTraceAsString() . PHP_EOL;
        $string .= PHP_EOL;

        return $string;
    }
}
