<?php
namespace Icecave\Stump;

use Exception;
use Icecave\Isolator\Isolator;
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

        $this->exceptionRenderer = Phake::mock('Icecave\Stump\ExceptionRendererInterface');

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
            'php://stdout',
            'Y-m-d H:i:s',
            $this->exceptionRenderer
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
            'w'
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

    /**
     * @dataProvider logTestVectors
     */
    public function testLogWithAnsi($logLevel, $logLevelText, $levelColor, $messageColor)
    {
        Phake::when($this->isolator)
            ->function_exists('posix_isatty')
            ->thenReturn(true);

        Phake::when($this->isolator)
            ->posix_isatty('<resource>')
            ->thenReturn(true);

        $this->logger->log(
            $logLevel,
            'Log message.'
        );

        $expectedMessage = "<ESC>[2;37m<date><ESC>[39;49;22m "
                         . $levelColor
                         . $logLevelText
                         . "<ESC>[39;49;22m "
                         . $messageColor
                         . "Log message.<ESC>[39;49;22m"
                         . PHP_EOL;

        $message = null;

        Phake::verify($this->isolator)->fwrite(
            '<resource>',
            Phake::capture($message)
        );

        $this->assertEquals(
            $expectedMessage,
            str_replace("\033", '<ESC>', $message)
        );
    }

    public function testLogDoesNotLogIfAnsiDisabled()
    {
        Phake::when($this->isolator)
            ->function_exists('posix_isatty')
            ->thenReturn(true);

        Phake::when($this->isolator)
            ->posix_isatty('<resource>')
            ->thenReturn(true);

        $this->logger = new Logger(LogLevel::INFO, false);
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
            [LogLevel::EMERGENCY, 'EMER', "<ESC>[1;37;41m", "<ESC>[0;31m"],
            [LogLevel::ALERT,     'ALRT', "<ESC>[1;37;41m", "<ESC>[0;31m"],
            [LogLevel::CRITICAL,  'CRIT', "<ESC>[1;37;41m", "<ESC>[0;31m"],
            [LogLevel::ERROR,     'ERRO', "<ESC>[0;31m",    "<ESC>[0;31m"],
            [LogLevel::WARNING,   'WARN', "<ESC>[0;33m",    "<ESC>[0;33m"],
            [LogLevel::NOTICE,    'NOTC', "<ESC>[0;34m",    "<ESC>[0;34m"],
            [LogLevel::INFO,      'INFO', "<ESC>[1;37m",    "<ESC>[39;49;22m"],
            [LogLevel::DEBUG,     'DEBG', "<ESC>[0m",       "<ESC>[2;37m"],
        ];
    }
}
