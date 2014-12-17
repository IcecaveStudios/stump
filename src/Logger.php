<?php
namespace Icecave\Stump;

use Exception;
use Icecave\Isolator\IsolatorTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;

/**
 * A very simple PSR-3 logger implementation that writes to STDOUT.
 */
class Logger implements
    LoggerInterface,
    ParentLoggerInterface
{
    use IsolatorTrait;
    use LoggerTrait;
    use ParentLoggerTrait;

    /**
     * @param string                          $minimumLogLevel   The minimum log level to include in the output.
     * @param string                          $dateFormat        The format specifier to use for outputting dates.
     * @param string                          $fileName          The target filename.
     * @param ExceptionRendererInterface|null $exceptionRenderer The exception renderer to use.
     */
    public function __construct(
        $minimumLogLevel = LogLevel::DEBUG,
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

        if (!$this->stream) {
            $this->stream = $this
                ->isolator()
                ->fopen($this->fileName, 'w');
        }

        $dateTime = $this
            ->isolator()
            ->date($this->dateFormat);

        $this
            ->isolator()
            ->fwrite(
                $this->stream,
                sprintf(
                    '%s %s %s' . PHP_EOL,
                    $dateTime,
                    self::$levelText[$level],
                    $this->substitutePlaceholders(
                        $message,
                        $context
                    )
                )
            );

        if (isset($context['exception'])
            && $context['exception'] instanceof Exception
        ) {
            $this->logException(
                $context['exception']
            );
        }
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
            $replacements['{' . $key . '}'] = $value;
        }

        return strtr($message, $replacements);
    }

    /**
     * Log an exception including the stack trace.
     *
     * @param Exception $exception The exception to log.
     */
    private function logException(Exception $exception)
    {
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
    private $exceptionRenderer;
    private $exceptionCount;
    private $stream;
}
