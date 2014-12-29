<?php
namespace Icecave\Stump;

use Exception;
use Icecave\Isolator\Isolator;
use Phake;
use PHPUnit_Framework_TestCase;
use Psr\Log\LogLevel;

class EmailLoggerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->exception = new Exception();

        $this->isolator = Phake::mock('Icecave\Isolator\Isolator');

        Phake::when($this->isolator)
            ->mail(Phake::anyParameters())
            ->thenReturn(true);

        Phake::when($this->isolator)
            ->date(Phake::anyParameters())
            ->thenReturn('2014-10-20 11:22:33');

        $this->exceptionRenderer = Phake::mock('Icecave\Stump\ExceptionRendererInterface');

        Phake::when($this->exceptionRenderer)
            ->render(Phake::anyParameters())
            ->thenReturn('<the-rendered-exception>');

        $this->emailLogger = new EmailLogger(
            'to@test.address',
            'from@test.address',
            'SUBJECT-TAG',
            LogLevel::ERROR,
            'Y-m-d H:i:s',
            $this->exceptionRenderer
        );

        $this->emailLogger->setIsolator(
            $this->isolator
        );
    }

    public function testConstructorDefaults()
    {
        $this->emailLogger = new EmailLogger(
            'to@test.address',
            'from@test.address',
            'SUBJECT-TAG'
        );

        $this->emailLogger->setIsolator(
            $this->isolator
        );

        $this->emailLogger->log(
            LogLevel::INFO,
            'Test logging.',
            []
        );

        $expectedSubject = '[SUBJECT-TAG] INFO Test logging.';

        $expectedBody = '2014-10-20 11:22:33 INFO Test logging.' . PHP_EOL;

        $expectedHeaders = [
            'From: from@test.address',
            'Content-Type: text/plain',
        ];
        $expectedHeaders = implode("\r\n", $expectedHeaders);

        Phake::verify($this->isolator)->mail(
            'to@test.address',
            $expectedSubject,
            $expectedBody,
            $expectedHeaders
        );
    }

    /**
     * @dataProvider logTestVectors
     */
    public function testLog($logLevel, $levelText, array $context, $expectMail)
    {
        $this
            ->emailLogger
            ->log(
                $logLevel,
                'Test logging with log level {level}',
                $context
            );

        if ($expectMail) {
            $expectedSubject = '[SUBJECT-TAG] ' . $levelText . ' Test logging with log level ' . $logLevel;

            $expectedBody = '2014-10-20 11:22:33 ' . $levelText . ' Test logging with log level ' . $logLevel . PHP_EOL;

            $expectedHeaders = [
                'From: from@test.address',
                'Content-Type: text/plain',
            ];
            $expectedHeaders = implode("\r\n", $expectedHeaders);

            Phake::verify($this->isolator)->mail(
                'to@test.address',
                $expectedSubject,
                $expectedBody,
                $expectedHeaders
            );
        } else {
            Phake::verifyNoInteraction($this->isolator);
        }
    }

    /**
     * @dataProvider logTestVectors
     */
    public function testLogWithContextException($logLevel, $levelText, array $context, $expectMail)
    {
        $context['exception'] = $this->exception;

        $this
            ->emailLogger
            ->log(
                $logLevel,
                'Test logging with log level {level}',
                $context
            );

        if ($expectMail) {
            $expectedSubject = '[SUBJECT-TAG] ' . $levelText . ' Test logging with log level ' . $logLevel;

            $expectedBody  = '2014-10-20 11:22:33 ' . $levelText . ' Test logging with log level ' . $logLevel . PHP_EOL;
            $expectedBody .= PHP_EOL . PHP_EOL;
            $expectedBody .= '<the-rendered-exception>';

            $expectedHeaders = [
                'From: from@test.address',
                'Content-Type: text/plain',
            ];
            $expectedHeaders = implode("\r\n", $expectedHeaders);

            Phake::verify($this->isolator)->mail(
                'to@test.address',
                $expectedSubject,
                $expectedBody,
                $expectedHeaders
            );
        } else {
            Phake::verifyNoInteraction($this->isolator);
        }
    }

    public function logTestVectors()
    {
        // This data is based on the EmailLogger being constructed with a minimum log level of LogLevel::ERROR.
        return [
            [LogLevel::EMERGENCY, 'EMER', ['level' => LogLevel::EMERGENCY], true],
            [LogLevel::ALERT,     'ALRT', ['level' => LogLevel::ALERT],     true],
            [LogLevel::CRITICAL,  'CRIT', ['level' => LogLevel::CRITICAL],  true],
            [LogLevel::ERROR,     'ERRO', ['level' => LogLevel::ERROR],     true],
            [LogLevel::WARNING,   'WARN', ['level' => LogLevel::WARNING],   false],
            [LogLevel::NOTICE,    'NOTC', ['level' => LogLevel::NOTICE],    false],
            [LogLevel::INFO,      'INFO', ['level' => LogLevel::INFO],      false],
            [LogLevel::DEBUG,     'DEBG', ['level' => LogLevel::DEBUG],     false],
        ];
    }
}
