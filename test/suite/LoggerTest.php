<?php
namespace Icecave\Stump;

use Exception;
use Icecave\Isolator\Isolator;
use Phake;
use PHPUnit_Framework_TestCase;
use Psr\Log\LogLevel;

class LoggerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->isolator = Phake::mock('Icecave\Isolator\Isolator');

        Phake::when($this->isolator)
            ->fopen(Phake::anyParameters())
            ->thenReturn('<resource>');

        Phake::when($this->isolator)
            ->date(Phake::anyParameters())
            ->thenReturn('<date>');

        $this->logger = new Logger;

        $this->logger->setIsolator($this->isolator);
    }

    /**
     * @dataProvider logTestVectors
     */
    public function testLog($logLevel, $logLevelText)
    {
        $this->logger->log(
            $logLevel,
            'Test message.'
        );

        Phake::verify($this->isolator)->fopen(
            'php://stdout',
            'w'
        );

        Phake::verify($this->isolator)->fwrite(
            '<resource>',
            '<date> ' . $logLevelText . ' Test message.' . PHP_EOL
        );
    }

    public function logTestVectors()
    {
        return [
            [LogLevel::EMERGENCY, 'EMER'],
            [LogLevel::ALERT,     'ALRT'],
            [LogLevel::CRITICAL,  'CRIT'],
            [LogLevel::ERROR,     'ERRO'],
            [LogLevel::WARNING,   'WARN'],
            [LogLevel::NOTICE,    'NOTC'],
            [LogLevel::INFO,      'INFO'],
            [LogLevel::DEBUG,     'DEBG'],
        ];
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
            '<date> INFO Foo: FOO, F: F, Missing: {missing}' . PHP_EOL
        );
    }

    public function testLogIgnoresLowLogLevel()
    {
        $this->logger = new Logger(LogLevel::INFO);

        $this->logger->debug('This should not be logged.');

        Phake::verifyNoInteraction($this->isolator);
    }

    public function testLogOnlyOpensFileOnce()
    {
        $this->logger->info('one');
        $this->logger->info('two');

        Phake::verify($this->isolator, Phake::times(1))->fopen(Phake::anyParameters());
    }

    public function testLogException()
    {
        $exception = new Exception('Test exception.');
        $hash      = spl_object_hash($exception);

        $this->logger->error(
            'Some test error: {exception}',
            [
                'exception' => $exception
            ]
        );

        Phake::verify($this->isolator)->fwrite(
            '<resource>',
            '<date> ERRO Some test error: ' . strval($exception) . PHP_EOL
        );

        $traceLines = explode(
            PHP_EOL,
            $exception->getTraceAsString()
        );
        foreach ($traceLines as $line) {
            Phake::verify($this->isolator)->fwrite(
                '<resource>',
                '<date> ERRO [' . $hash . '] ' . $line . PHP_EOL
            );
        }
    }
}
