<?php
namespace Icecave\Stump;

trait PrefixableTrait
{
    /**
     * Create a logger that logs with the given prefix.
     *
     * @param string $prefix
     * @param string $separator
     *
     * @return LoggerInterface
     */
    public function prefixWith($prefix, $separator = '.')
    {
        return new PrefixLogger($prefix . $separator, $this);
    }
}
