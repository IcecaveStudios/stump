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
        $this->isolator = Phake::mock('Icecave\Isolator\Isolator');
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

    public function testLog()
    {
        $this->logger->log(
            LogLevel::WARNING,
            'Warning message.'
        );

        Phake::verify($this->isolator)->fopen(
            'php://stdout',
            'w'
        );

        Phake::verify($this->isolator)->fwrite(
            '<resource>',
            'WARNING <date>: Warning message.' . PHP_EOL
        );
    }

    public function testLogWithPlaceholderValues()
    {
        $this->logger->log(
            LogLevel::INFO,
            'Foo: {foo}, F: {f}, Missing: {missing}',
            [
                'foo' => 'FOO',
                'f'   => 'F',
            ]
        );

        Phake::verify($this->isolator)->fwrite(
            '<resource>',
            'INFO <date>: Foo: FOO, F: F, Missing: {missing}' . PHP_EOL
        );
    }

    public function testLogIgnoresLowLogLevel()
    {
        $this->logger->debug('This should not be logged.');

        Phake::verifyNoInteraction($this->isolator);
    }

    public function testLogOnlyOpensFileOnce()
    {
        $this->logger->info('one');
        $this->logger->info('two');

        Phake::verify($this->isolator, Phake::times(1))->fopen(Phake::anyParameters());
    }
}
