<?php
namespace Icecave\Stump;

/**
 * Trait that implements ParentLoggerInterface.
 */
trait ParentLoggerTrait
{
    /**
     * Create a sub-logger that targets this logger.
     *
     * @param string $name The sub-logger name.
     *
     * @return SubLogger A sub-logger with the given name.
     */
    public function createSubLogger($name)
    {
        if ($this instanceof SubLogger) {
            return new SubLogger(
                $this->name() . '.' . $name,
                $this->logger()
            );
        }

        return new SubLogger($name, $this);
    }
}
