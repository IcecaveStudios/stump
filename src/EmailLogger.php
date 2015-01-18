<?php
namespace Icecave\Stump;

use Exception;
use Icecave\Isolator\IsolatorTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;

class EmailLogger implements LoggerInterface
{
    use IsolatorTrait;
    use LoggerTrait;

    /**
     * @param string                          $toAddress         The address to send the email to.
     * @param string                          $fromAddress       The email sent from address.
     * @param string                          $subjectTag        The tag to include in the email subject.
     * @param string                          $minimumLogLevel   The minimum log level to include in the output.
     * @param string                          $dateFormat        The format specifier to use for outputting dates.
     * @param ExceptionRendererInterface|null $exceptionRenderer The exception renderer to use.
     */
    public function __construct(
        $toAddress,
        $fromAddress,
        $subjectTag,
        $minimumLogLevel = LogLevel::DEBUG,
        $dateFormat = 'Y-m-d H:i:s',
        ExceptionRendererInterface $exceptionRenderer = null
    ) {
        if (null === $exceptionRenderer) {
            $exceptionRenderer = new ExceptionRenderer();
        }

        $this->minimumLogLevel   = self::$levels[$minimumLogLevel];
        $this->toAddress         = $toAddress;
        $this->fromAddress       = $fromAddress;
        $this->subjectTag        = $subjectTag;
        $this->dateFormat        = $dateFormat;
        $this->exceptionRenderer = $exceptionRenderer;
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

        $substituedMessage = $this->substitutePlaceholders(
            $message,
            $context
        );

        $dateTime = $this
            ->isolator()
            ->date($this->dateFormat);

        $body = sprintf(
            '%s %s %s' . PHP_EOL,
            $dateTime,
            self::$levelText[$level],
            $substituedMessage
        );

        if (
            isset($context['exception'])
            && $context['exception'] instanceof Exception
        ) {
            $body .= PHP_EOL . PHP_EOL;
            $body .= $this->exceptionRenderer->render($context['exception']);
        }

        $this->isolator()->mail(
            $this->toAddress,
            $this->subject($level, $substituedMessage),
            $body,
            $this->headers()
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

    /**
     * @param mixed  $level   The log level.
     * @param string $message The message to log.
     *
     * @return string email subject.
     */
    private function subject($level, $message)
    {
        return sprintf(
            '[%s] %s %s',
            $this->subjectTag,
            self::$levelText[$level],
            $message
        );
    }

    /**
     * @return string email headers.
     */
    private function headers()
    {
        $headers = [
            'From: ' . $this->fromAddress,
            'Content-Type: text/plain',
        ];

        return implode("\r\n", $headers);
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

    private $toAddress;
    private $fromAddress;
    private $subjectTag;
    private $minimumLogLevel;
    private $dateFormat;
    private $exceptionRenderer;
}
