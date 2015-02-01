<?php
namespace Icecave\Stump;

use Exception;
use Icecave\Isolator\Isolator;
use Icecave\Stump\MessageRenderer\PlainMessageRenderer;
use PHPUnit_Framework_TestCase;
use Phake;
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

        $this->exceptionRenderer = Phake::mock('Icecave\Stump\ExceptionRenderer\ExceptionRendererInterface');

        Phake::when($this->exceptionRenderer)
            ->render(Phake::anyParameters())
            ->thenReturn(
                '<the-rendered-exception-line-1>' . PHP_EOL .
                PHP_EOL .
                '<the-rendered-exception-line-2>' . PHP_EOL
            );

        $this->logger = new Logger(
            LogLevel::DEBUG,
            null,
            $this->exceptionRenderer,
            'php://stdout',
            'Y-m-d H:i:s'
        );

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
            'a'
        );

        Phake::verify($this->isolator)->fwrite(
            '<resource>',
            '<date> ' . $logLevelText . ' Test message.' . PHP_EOL
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
            '<date> INFO Foo: FOO, F: F, Missing: {missing}' . PHP_EOL
        );
    }

    public function testLogIgnoresLowLogLevel()
    {
        $this->logger = new Logger(LogLevel::INFO);
        $this->logger->setIsolator($this->isolator);
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
        $exception = new Exception('This is the exception message.');

        $this->logger->error(
            'Some test error. Exception: {exception}',
            ['exception' => $exception]
        );

        Phake::verify($this->isolator)->fwrite(
            '<resource>',
            '<date> ERRO Some test error. Exception: This is the exception message.' . PHP_EOL
        );

        Phake::verify($this->isolator)->fwrite(
            '<resource>',
            '<date> DEBG [exception 1] <the-rendered-exception-line-1>' . PHP_EOL
        );

        Phake::verify($this->isolator)->fwrite(
            '<resource>',
            '<date> DEBG [exception 1] <the-rendered-exception-line-2>' . PHP_EOL
        );

        Phake::verify($this->exceptionRenderer)->render(
            $exception
        );
    }

    public function testExceptionRendererIsNotUsedIfDebugIsDisabled()
    {
        $exception = new Exception('This is the exception message.');

        $this->logger = new Logger(LogLevel::INFO);
        $this->logger->setIsolator($this->isolator);
        $this->logger->debug('This should not be logged.');

        $this->logger->error(
            'Log message.',
            ['exception' => $exception]
        );

        Phake::verifyNoInteraction($this->exceptionRenderer);
    }

    public function testLogWithAnsi()
    {
        Phake::when($this->isolator)
            ->function_exists('posix_isatty')
            ->thenReturn(true);

        Phake::when($this->isolator)
            ->posix_isatty('<resource>')
            ->thenReturn(true);

        $this->logger->log(
            LogLevel::INFO,
            'Log message.'
        );

        Phake::verify($this->isolator)->fwrite(
            '<resource>',
            Phake::capture($message)
        );

        $this->assertContains(
            '<ESC>',
            str_replace("\033", '<ESC>', $message)
        );
    }

    public function testLogDoesNotCreatedNewMessageRendererIfOneIsInjected()
    {
        Phake::when($this->isolator)
            ->function_exists('posix_isatty')
            ->thenReturn(true);

        Phake::when($this->isolator)
            ->posix_isatty('<resource>')
            ->thenReturn(true);

        $this->logger = new Logger(
            LogLevel::INFO,
            new PlainMessageRenderer
        );
        $this->logger->setIsolator($this->isolator);

        $this->logger->info('Test message.');

        Phake::verify($this->isolator)->fwrite(
            '<resource>',
            '<date> INFO Test message.' . PHP_EOL
        );
    }

    public function testLogDoesNotUseAnsiIfPosixExtensionNotAvailable()
    {
        Phake::when($this->isolator)
            ->function_exists('posix_isatty')
            ->thenReturn(false);

        Phake::when($this->isolator)
            ->posix_isatty('<resource>')
            ->thenReturn(true);

        $this->logger->info('Test message.');

        Phake::verify($this->isolator)->fwrite(
            '<resource>',
            '<date> INFO Test message.' . PHP_EOL
        );
    }

    public function testLogDoesNotUseAnsiIfNotATTY()
    {
        Phake::when($this->isolator)
            ->function_exists('posix_isatty')
            ->thenReturn(true);

        Phake::when($this->isolator)
            ->posix_isatty('<resource>')
            ->thenReturn(false);

        $this->logger->info('Test message.');

        Phake::verify($this->isolator)->fwrite(
            '<resource>',
            '<date> INFO Test message.' . PHP_EOL
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
}
