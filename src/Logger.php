<?php
namespace Icecave\Stump;

use Exception;
use Icecave\Isolator\IsolatorTrait;
use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

/**
 * A very simple PSR-3 logger implementation that writes to STDOUT.
 */
class Logger implements LoggerInterface
{
    use IsolatorTrait;
    use LoggerTrait;

    /**
     * @param string                          $minimumLogLevel   The minimum log level to include in the output.
     * @param boolean|null                    $ansi              True to use ANSI control codes, null to decide automatically based on the terminal.
     * @param string                          $fileName          The target filename.
     * @param string                          $dateFormat        The format specifier to use for outputting dates.
     * @param ExceptionRendererInterface|null $exceptionRenderer The exception renderer to use.
     */
    public function __construct(
        $minimumLogLevel = LogLevel::DEBUG,
        $ansi = null,
        $fileName = 'php://stdout',
        $dateFormat = 'Y-m-d H:i:s',
        ExceptionRendererInterface $exceptionRenderer = null
    ) {
        if (null === $exceptionRenderer) {
            $exceptionRenderer = new ExceptionRenderer();
        }

        $this->minimumLogLevel   = self::$levels[$minimumLogLevel];
        $this->dateFormat        = $dateFormat;
        $this->fileName          = $fileName;
        $this->ansi              = $ansi;
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

        $stream  = $this->stream();
        $message = $this->generateLogMessage(
            $level,
            $message,
            $context
        );

        $this
            ->isolator()
            ->fwrite(
                $stream,
                $message . PHP_EOL
            );

        if (
            isset($context['exception'])
            && $context['exception'] instanceof Exception
        ) {
            $this->logException(
                $context['exception']
            );
        }
    }

    /**
     * Log an exception including the stack trace.
     *
     * @param Exception $exception The exception to log.
     */
    private function logException(Exception $exception)
    {
        // Don't generate any exception logging if DEBUG level is disabled ...
        if (self::$levels[LogLevel::DEBUG] < $this->minimumLogLevel) {
            return;
        }

        $this->exceptionCount++;

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
                'w'
            );

            if (null === $this->ansi) {
                $this->ansi = $iso->function_exists('posix_isatty')
                           && $iso->posix_isatty($this->stream);
            }
        }

        return $this->stream;
    }

    public function generateLogMessage($level, $message, array $context)
    {
        $dateTime = $this
            ->isolator()
            ->date($this->dateFormat);

        $levelText = self::$levelText[$level];

        $message = $this->substitutePlaceholders(
            $message,
            $context
        );

        return sprintf(
            '%s %s %s',
            $this->color(self::ANSI_DARK_GRAY, $dateTime),
            $this->color(self::$levelStyle[$level], $levelText),
            $this->color(self::$messageStyle[$level], $message)
        );
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
            if ($key === 'exception' && $value instanceof Exception) {
                $replacements['{' . $key . '}'] = $this->underline($value->getMessage());
            } else {
                $replacements['{' . $key . '}'] = $this->underline($value);
            }
        }

        return strtr($message, $replacements);
    }

    private function color($code, $text)
    {
        if (!$this->ansi) {
            return $text;
        }

        return $code . $text . self::ANSI_RESET;
    }

    private function underline($text)
    {
        if (!$this->ansi) {
            return $text;
        }

        return self::ANSI_UNDERLINE_ON . $text . self::ANSI_UNDERLINE_OFF;
    }

    const ANSI_RESET         = "\033[39;49;22m";
    const ANSI_RED_INVERSE   = "\033[1;37;41m";
    const ANSI_RED           = "\033[0;31m";
    const ANSI_YELLOW        = "\033[0;33m";
    const ANSI_BLUE          = "\033[0;34m";
    const ANSI_WHITE         = "\033[1;37m";
    const ANSI_GRAY          = "\033[0m";
    const ANSI_DARK_GRAY     = "\033[2;37m";
    const ANSI_UNDERLINE_ON  = "\033[4m";
    const ANSI_UNDERLINE_OFF = "\033[24m";

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

    private static $levelStyle = [
        LogLevel::EMERGENCY => self::ANSI_RED_INVERSE,
        LogLevel::ALERT     => self::ANSI_RED_INVERSE,
        LogLevel::CRITICAL  => self::ANSI_RED_INVERSE,
        LogLevel::ERROR     => self::ANSI_RED,
        LogLevel::WARNING   => self::ANSI_YELLOW,
        LogLevel::NOTICE    => self::ANSI_BLUE,
        LogLevel::INFO      => self::ANSI_WHITE,
        LogLevel::DEBUG     => self::ANSI_GRAY,
    ];

    private static $messageStyle = [
        LogLevel::EMERGENCY => self::ANSI_RED,
        LogLevel::ALERT     => self::ANSI_RED,
        LogLevel::CRITICAL  => self::ANSI_RED,
        LogLevel::ERROR     => self::ANSI_RED,
        LogLevel::WARNING   => self::ANSI_YELLOW,
        LogLevel::NOTICE    => self::ANSI_BLUE,
        LogLevel::INFO      => self::ANSI_RESET,
        LogLevel::DEBUG     => self::ANSI_DARK_GRAY,
    ];

    private $minimumLogLevel;
    private $dateFormat;
    private $fileName;
    private $ansi;
    private $exceptionRenderer;
    private $exceptionCount;
    private $stream;
}
