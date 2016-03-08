<?php

namespace Icecave\Stump\MessageRenderer;

final class PlainMessageRenderer implements MessageRendererInterface
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
            $dateTime,
            $levelText,
            $message
        );
    }
}
