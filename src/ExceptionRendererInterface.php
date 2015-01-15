<?php
namespace Icecave\Stump;

use Exception;

interface ExceptionRendererInterface
{
    /**
     * @param Exception $exception The exception to render.
     *
     * @return string the rendered exception.
     */
    public function render(Exception $exception);
}
