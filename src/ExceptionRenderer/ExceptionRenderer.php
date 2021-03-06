<?php

namespace Icecave\Stump\ExceptionRenderer;

use Exception;
use Throwable;

class ExceptionRenderer implements ExceptionRendererInterface
{
    /**
     * @param Throwable|Exception $exception The exception to render.
     *
     * @return string the rendered exception.
     */
    public function render($exception)
    {
        $rendered        = [];
        $renderException = $exception;
        while ($renderException) {
            $rendered[]      = $this->renderException($renderException);
            $renderException = $renderException->getPrevious();
        }

        $string = implode(PHP_EOL, $rendered);

        return trim($string);
    }

    /**
     * @param Throwable|Exception $exception The exception to render.
     *
     * @return string rendered exception.
     */
    private function renderException($exception)
    {
        $string = 'Message: ' . $exception->getMessage() . PHP_EOL;
        $string .= 'Code:    ' . $exception->getCode() . PHP_EOL;
        $string .= 'Type:    ' . get_class($exception) . PHP_EOL;

        $string .= sprintf(
            'Source:  %s:%d' . PHP_EOL,
            $exception->getFile(),
            $exception->getLine()
        );

        $trace = explode(PHP_EOL, $exception->getTraceAsString());
        foreach ($trace as $line) {
            $string .= '    ' . $line . PHP_EOL;
        }

        $string .= PHP_EOL;

        return $string;
    }
}
