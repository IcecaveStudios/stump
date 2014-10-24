<?php
namespace Icecave\Stump;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

/**
 * A logger that prepends its name to all messages.
 */
class SubLogger implements
    LoggerAwareInterface,
    LoggerInterface,
    ParentLoggerInterface
{
    use LoggerAwareTrait;
    use LoggerTrait;
    use ParentLoggerTrait;

    /**
     * @param string          $name   The sub-logger name.
     * @param LoggerInterface $logger The target logger.
     */
    public function __construct(
        $name,
        LoggerInterface $logger
    ) {
        $this->name = $name;

        $this->setLogger($logger);
    }

    /**
     * Get the sub-logger name.
     *
     * @return string The sub-logger name.
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Get the target logger.
     *
     * @return LoggerInterface The target logger.
     */
    public function logger()
    {
        return $this->logger;
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
        $this->logger->log($level, $this->name . ': ' . $message, $context);
    }

    private $name;
}
