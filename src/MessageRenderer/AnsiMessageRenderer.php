<?php
namespace Icecave\Stump\MessageRenderer;

use Psr\Log\LogLevel;

final class AnsiMessageRenderer implements MessageRendererInterface
{
    /**
     * Render a log message.
     *
     * @param string $level     The PSR log level.
     * @param string $levelText The human-readable log level string.
     * @param string $dateTime  The human-readable date string.
     * @param string $message   The log message.
     *
     * @return string The rendered log message.
     */
    public function render(
        $level,
        $levelText,
        $dateTime,
        $message
    ) {
        return sprintf(
            '%s %s %s',
            $this->apply(self::ANSI_DARK_GRAY, $dateTime),
            $this->apply(self::$levelStyle[$level], $levelText),
            $this->apply(self::$messageStyle[$level], $message)
        );
    }

    private function apply($style, $text)
    {
        return $style . $text . self::ANSI_RESET;
    }

    const ANSI_RESET         = "\033[39;49;22m";
    const ANSI_RED_INVERSE   = "\033[1;37;41m";
    const ANSI_RED           = "\033[0;31m";
    const ANSI_YELLOW        = "\033[0;33m";
    const ANSI_BLUE          = "\033[0;34m";
    const ANSI_WHITE         = "\033[1;37m";
    const ANSI_GRAY          = "\033[0m";
    const ANSI_DARK_GRAY     = "\033[2;37m";

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
}
