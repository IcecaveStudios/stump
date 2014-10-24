<?php
namespace Icecave\Stump;

use Icecave\Isolator\Isolator;
use Phake;
use PHPUnit_Framework_TestCase;
use Psr\Log\LogLevel;

class LoggerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->isolator = Phake::mock(Isolator::class);
        $this->minimumLogLevel = LogLevel::INFO;

        Phake::when($this->isolator)
            ->fopen(Phake::anyParameters())
            ->thenReturn('<resource>');

        Phake::when($this->isolator)
            ->date(Phake::anyParameters())
            ->thenReturn('<date>');

        $this->logger = new Logger($this->minimumLogLevel);
        $this->logger->setIsolator($this->isolator);
    }

    public function testPrefixWith()
    {
        $logger = $this->logger->prefixWith('foo');

        $this->assertInstanceOf(
            PrefixLogger::class,
            $logger
        );

        $this->assertSame(
            'foo',
            $logger->prefix()
        );

        $this->assertSame(
            $this->logger,
            $logger->logger()
        );
    }

    public function testLog()
    {
        $this->logger->log(
            LogLevel::INFO,
            'Foo: {foo}, F: {f}, Missing: {missing}',
            [
                'foo' => 'FOO',
                'f'   => 'F',
            ]
        );

        $this->logger->log(
            LogLevel::WARNING,
            'Warning message.'
        );

        $dateVerifier = Phake::verify($this->isolator, Phake::times(2))->date('Y-m-d H:i:s');

        Phake::inOrder(
            Phake::verify($this->isolator)->fopen('php://stdout', 'w'),
            $dateVerifier,
            Phake::verify($this->isolator)->fwrite(
                '<resource>',
                'INFO <date>: Foo: FOO, F: F, Missing: {missing}' . PHP_EOL
            ),
            $dateVerifier,
            Phake::verify($this->isolator)->fwrite(
                '<resource>',
                'WARNING <date>: Warning message.' . PHP_EOL
            )
        );
    }

    public function testBelowLevel()
    {
        $this->logger->log(LogLevel::DEBUG, 'Debug message.');

        Phake::verifyNoInteraction($this->isolator);
    }
}
