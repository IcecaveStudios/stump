<?php

namespace Icecave\Stump;

use Exception;
use Icecave\Isolator\IsolatorTrait;
use Icecave\Stump\ExceptionRenderer\ExceptionRenderer;
use Icecave\Stump\ExceptionRenderer\ExceptionRendererInterface;
use Icecave\Stump\MessageRenderer\AnsiMessageRenderer;
use Icecave\Stump\MessageRenderer\MessageRendererInterface;
use Icecave\Stump\MessageRenderer\PlainMessageRenderer;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;
use Throwable;

/**
 * A very simple PSR-3 logger implementation that writes to STDOUT.
 */
class Logger implements LoggerInterface
{
    use IsolatorTrait;
    use LoggerTrait;

    public function __construct(
        $minimumLogLevel = LogLevel::DEBUG,
        MessageRendererInterface $messageRenderer = null,
        ExceptionRendererInterface $exceptionRenderer = null,
        $fileName = 'php://stdout',
        $dateFormat = 'Y-m-d H:i:s'
    ) {
        if (null === $exceptionRenderer) {
            $exceptionRenderer = new ExceptionRenderer();
        }

        $this->minimumLogLevel   = self::$levels[$minimumLogLevel];
        $this->dateFormat        = $dateFormat;
        $this->fileName          = $fileName;
        $this->messageRenderer   = $messageRenderer;
        $this->exceptionRenderer = $exceptionRenderer;
        $this->exceptionCount    = 0;
    }

    /**
     * Log a message.
     *
     * @param mixed  $level   The log level.
     * @param string $message The message to log.
     * @param array  $context Additional contextual information.
     */
    public function log($level, $message, array $context = [])
    {
        if (self::$levels[$level] < $this->minimumLogLevel) {
            return;
        }

        $iso    = $this->isolator();
        $stream = $this->stream();
        $output = $this->messageRenderer->render(
            $level,
            self::$levelText[$level],
            $iso->date($this->dateFormat),
            $this->substitutePlaceholders(
                $message,
                $context
            )
        );

        $iso->fwrite($stream, $output . PHP_EOL);

        if (
            isset($context['exception']) && (
                $context['exception'] instanceof Exception || // PHP 5
                $context['exception'] instanceof Throwable // PHP 7
            )
        ) {
            $this->logException(
                $context['exception']
            );
        }
    }

    /**
     * Log an exception including the stack trace.
     *
     * @param Throwable|Exception $exception The exception to log.
     */
    private function logException($exception)
    {
        // Don't generate any exception logging if DEBUG level is disabled ...
        if (self::$levels[LogLevel::DEBUG] < $this->minimumLogLevel) {
            return;
        }

        ++$this->exceptionCount;

        $lines = explode(
            PHP_EOL,
            $this->exceptionRenderer->render($exception)
        );

        foreach ($lines as $line) {
            $line = rtrim($line);

            if (empty($line)) {
                continue;
            }

            $this->log(
                LogLevel::DEBUG,
                sprintf(
                    '[exception %s] %s',
                    $this->exceptionCount,
                    $line
                )
            );
        }
    }

    /**
     * @return resource
     */
    private function stream()
    {
        if (!$this->stream) {
            $iso = $this->isolator();

            $this->stream = $iso->fopen(
                $this->fileName,
                'a'
            );

            if (null === $this->messageRenderer) {
                $ansi = $iso->function_exists('posix_isatty')
                     && $iso->posix_isatty($this->stream);

                if ($ansi) {
                    $this->messageRenderer = new AnsiMessageRenderer();
                } else {
                    $this->messageRenderer = new PlainMessageRenderer();
                }
            }
        }

        return $this->stream;
    }

    /**
     * Substitute PSR-3 style placeholders in a message.
     *
     * @param string               $message The message template.
     * @param array<string, mixed> $context The placeholder values.
     *
     * @return string The message template with placeholder values substituted.
     */
    private function substitutePlaceholders($message, array $context)
    {
        if (false === strpos($message, '{')) {
            return $message;
        }

        $replacements = [];
        foreach ($context as $key => $value) {
            if (
                $key === 'exception' && (
                    $value instanceof Exception || // PHP 5
                    $value instanceof Throwable // PHP 7
                )
            ) {
                $replacements['{' . $key . '}'] = $value->getMessage();
            } else {
                $replacements['{' . $key . '}'] = $value;
            }
        }

        return strtr($message, $replacements);
    }

    private static $levels = [
        LogLevel::EMERGENCY => 7,
        LogLevel::ALERT     => 6,
        LogLevel::CRITICAL  => 5,
        LogLevel::ERROR     => 4,
        LogLevel::WARNING   => 3,
        LogLevel::NOTICE    => 2,
        LogLevel::INFO      => 1,
        LogLevel::DEBUG     => 0,
    ];

    private static $levelText = [
        LogLevel::EMERGENCY => 'EMER',
        LogLevel::ALERT     => 'ALRT',
        LogLevel::CRITICAL  => 'CRIT',
        LogLevel::ERROR     => 'ERRO',
        LogLevel::WARNING   => 'WARN',
        LogLevel::NOTICE    => 'NOTC',
        LogLevel::INFO      => 'INFO',
        LogLevel::DEBUG     => 'DEBG',
    ];

    private $minimumLogLevel;
    private $dateFormat;
    private $fileName;
    private $messageRenderer;
    private $exceptionRenderer;
    private $exceptionCount;
    private $stream;
}
