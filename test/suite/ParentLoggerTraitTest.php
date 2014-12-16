<?php
namespace Icecave\Stump;

use Phake;
use PHPUnit_Framework_TestCase;

class ParentLoggerTraitTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->targetLogger = Phake::mock('Icecave\Stump\Logger');

        Phake::when($this->targetLogger)
            ->createSubLogger(Phake::anyParameters())
            ->thenCallParent();
    }

    public function testCreateSubLogger()
    {
        $logger = $this
            ->targetLogger
            ->createSubLogger('foo');

        $this->assertInstanceOf(
            'Icecave\Stump\SubLogger',
            $logger
        );

        $this->assertSame(
            'foo',
            $logger->name()
        );

        $this->assertSame(
            $this->targetLogger,
            $logger->logger()
        );
    }

    public function testSubLoggerChaning()
    {
        $logger = $this
            ->targetLogger
            ->createSubLogger('foo')
            ->createSubLogger('bar');

        $this->assertInstanceOf(
            'Icecave\Stump\SubLogger',
            $logger
        );

        $this->assertSame(
            'foo.bar',
            $logger->name()
        );

        $this->assertSame(
            $this->targetLogger,
            $logger->logger()
        );
    }
}
