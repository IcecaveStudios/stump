<?php

namespace Icecave\Stump\ExceptionRenderer;

use Exception;
use Throwable;

interface ExceptionRendererInterface
{
    /**
     * @param Throwable|Exception $exception The exception to render.
     *
     * @return string the rendered exception.
     */
    public function render($exception);
}
