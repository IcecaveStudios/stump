<?php
namespace Icecave\Stump;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

/**
 * A logger that prefixes all messages with a fixed string.
 */
class PrefixLogger implements
    LoggerAwareInterface,
    LoggerInterface,
    PrefixableInterface
{
    use LoggerAwareTrait;
    use LoggerTrait;
    use PrefixableTrait;

    /**
     * @param string          $prefix The message prefix.
     * @param LoggerInterface $logger The target logger.
     */
    public function __construct(
        $prefix,
        LoggerInterface $logger
    ) {
        $this->prefix = $prefix;

        $this->setLogger($logger);
    }

    /**
     * @return string The message prefix
     */
    public function prefix()
    {
        return $this->prefix;
    }

    /**
     * @return LoggerInterface The target logger.
     */
    public function logger()
    {
        return $this->logger;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     */
    public function log($level, $message, array $context = [])
    {
        $this->logger->log($level, $this->prefix . $message, $context);
    }

    private $prefix;
}
