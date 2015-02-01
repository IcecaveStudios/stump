<?php
namespace Icecave\Stump\ExceptionRenderer;

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
