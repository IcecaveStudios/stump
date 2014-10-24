<?php
namespace Icecave\Stump;

/**
 * Interface for loggers that support creation of sub-loggers.
 */
interface ParentLoggerInterface
{
    /**
     * Create a sub-logger that targets this logger.
     *
     * @param string $name The sub-logger name.
     *
     * @return SubLogger A sub-logger with the given name.
     */
    public function createSubLogger($name);
}
