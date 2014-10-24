<?php
namespace Icecave\Stump;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;

/**
 * A logger that prefixes all messages with a fixed string.
 */
class PrefixLogger implements LoggerInterface, LoggerAwareInterface, PrefixableInterface
{
    use LoggerTrait;
    use LoggerAwareTrait;

    /**
     * @param string $prefix The message prefix.
     * @param LoggerInterface|null $logger The target logger.
     */
    public function __construct(
        $prefix,
        LoggerInterface $logger = null
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
     * @return LoggerInterface|null The target logger.
     */
    public function logger()
    {
        return $this->logger;
    }

    /**
     * Create a logger that logs with the given prefix.
     *
     * @param string $prefix
     *
     * @return LoggerInterface
     */
    public function prefixWith($prefix)
    {
        return new static($prefix, $this);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return null
     */
    public function log($level, $message, array $context = [])
    {
        if ($this->logger) {
            $this->logger->log($level, $this->prefix . $message, $context);
        }
    }

    private $prefix;
}
