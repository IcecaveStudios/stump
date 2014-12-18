<?php
namespace Icecave\Stump;

use Psr\Log\LoggerInterface;

interface CompoundLoggerInterface extends LoggerInterface
{
    /**
     * Add a logger to the compound.
     *
     * @param LoggerInterface $logger The logger to add.
     */
    public function add(LoggerInterface $logger);

    /**
     * Remove a logger from the compound.
     *
     * @param LoggerInterface $logger The logger to remove.
     */
    public function remove(LoggerInterface $logger);

    /**
     * Remove all loggers from the compound.
     */
    public function removeAll();
}
