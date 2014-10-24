<?php
namespace Icecave\Stump;

interface PrefixableInterface
{
    /**
     * Create a logger that logs with the given prefix.
     *
     * @param string $prefix
     *
     * @return LoggerInterface
     */
    public function prefixWith($prefix);
}
