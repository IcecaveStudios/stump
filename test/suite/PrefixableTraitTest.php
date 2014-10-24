<?php
namespace Icecave\Stump;

use Phake;
use PHPUnit_Framework_TestCase;

class PrefixableTraitTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->logger = Phake::mock(PrefixLogger::class);

        Phake::when($this->logger)
            ->prefixWith(Phake::anyParameters())
            ->thenCallParent();
    }

    public function testPrefixWith()
    {
        $logger = $this->logger->prefixWith('foo');

        $this->assertInstanceOf(
            PrefixLogger::class,
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
