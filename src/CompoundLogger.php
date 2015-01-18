<?php
namespace Icecave\Stump;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use SplObjectStorage;

class CompoundLogger implements CompoundLoggerInterface
{
    use LoggerTrait;

    public function __construct()
    {
        $this->loggers = new SplObjectStorage();
    }

    /**
     * Add a logger to the compound.
     *
     * @param LoggerInterface $logger The logger to add.
     */
    public function add(LoggerInterface $logger)
    {
        $this->loggers->attach($logger);
    }

    /**
     * Remove a logger from the compound.
     *
     * @param LoggerInterface $logger The logger to remove.
     */
    public function remove(LoggerInterface $logger)
    {
        $this->loggers->detach($logger);
    }

    /**
     * Remove all loggers from the compound.
     */
    public function removeAll()
    {
        $this->loggers->removeAll(
            $this->loggers
        );
    }

    /**
     * Log a message.
     *
     * @param mixed  $level   The log level.
     * @param string $message The message to log.
     * @param array  $context Additional contextual information.
     */
    public function log($level, $message, array $context = [])
    {
        foreach ($this->loggers as $logger) {
            $logger->log($level, $message, $context);
        }
    }

    private $loggers;
}
