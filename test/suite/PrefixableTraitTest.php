<?php
namespace Icecave\Stump;

use Phake;
use PHPUnit_Framework_TestCase;

class PrefixableTraitTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->logger = Phake::mock('Icecave\Stump\PrefixLogger');

        Phake::when($this->logger)
            ->prefixWith(Phake::anyParameters())
            ->thenCallParent();
    }

    public function testPrefixWith()
    {
        $logger = $this->logger->prefixWith('foo');

        $this->assertInstanceOf(
            'Icecave\Stump\PrefixLogger',
            $logger
        );

        $this->assertSame(
            'foo.',
            $logger->prefix()
        );

        $this->assertSame(
            $this->logger,
            $logger->logger()
        );
    }
}
