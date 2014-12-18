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
        $this->isolator = Phake::mock('Icecave\Isolator\Isolator');
    }
}
