<?php
namespace Icecave\Stump;

use Exception;
use Icecave\Isolator\Isolator;
use Phake;
use PHPUnit_Framework_TestCase;
use Psr\Log\LogLevel;

class CompoundLoggerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->logger1 = Phake::mock('Psr\Log\LoggerInterface');
        $this->logger2 = Phake::mock('Psr\Log\LoggerInterface');

        $this->compoundLogger = new CompoundLogger();

        $this->compoundLogger->add(
            $this->logger1
        );
    }

    public function testAdd()
    {
        $this->compoundLogger->add(
            $this->logger2
        );

        $this->compoundLogger->log(
            LogLevel::INFO,
            'Test message.'
        );

        Phake::verify($this->logger1)->log(
            LogLevel::INFO,
            'Test message.'
        );

        Phake::verify($this->logger2)->log(
            LogLevel::INFO,
            'Test message.'
        );
    }

    public function testRemove()
    {
        $this->compoundLogger->remove(
            $this->logger2
        );

        $this->compoundLogger->log(
            LogLevel::INFO,
            'Test message.'
        );

        Phake::verify($this->logger1)->log(
            LogLevel::INFO,
            'Test message.'
        );

        Phake::verifyNoInteraction($this->logger2);
    }

    public function testRemoveAll()
    {
        $this->compoundLogger->removeAll();

        $this->compoundLogger->log(
            LogLevel::INFO,
            'Test message.'
        );

        Phake::verifyNoInteraction($this->logger1);
        Phake::verifyNoInteraction($this->logger2);
    }

    /**
     * @dataProvider logTestVectors
     */
    public function testLog($logLevel, $logLevelText)
    {
        $this->compoundLogger->log(
            $logLevel,
            $logLevelText . ' Test message.',
            []
        );

        Phake::verify($this->logger1)->log(
            LogLevel::INFO,
            $logLevelText . ' Test message.',
            []
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
