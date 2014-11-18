<?php
namespace Icecave\Stump;

use Icecave\Isolator\IsolatorTrait;
use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

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
     * @param string $minimumLogLevel The minimum log level to include in the output.
     * @param string $dateFormat      The format specifier to use for outputting dates.
     * @param string $fileName        The target filename.
     */
    public function __construct(
        $minimumLogLevel = LogLevel::DEBUG,
        $fileName = 'php://stdout',
        $dateFormat = 'Y-m-d H:i:s'
    ) {
        $this->minimumLogLevel = self::$levels[$minimumLogLevel];
        $this->dateFormat      = $dateFormat;
        $this->fileName        = $fileName;
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
        LogLevel::ALERT     =>     'ALRT',
        LogLevel::CRITICAL  =>  'CRIT',
        LogLevel::ERROR     =>     'ERRO',
        LogLevel::WARNING   =>   'WARN',
        LogLevel::NOTICE    =>    'NOTC',
        LogLevel::INFO      =>      'INFO',
        LogLevel::DEBUG     =>     'DEBG',
    ];

    private $minimumLogLevel;
    private $dateFormat;
    private $fileName;
    private $stream;
}
