<?php

declare(strict_types=1);

namespace Zeal\OrmTest;

use PHPUnit\Framework\TestCase;
use Zeal\Orm\DateTime;

class DateTimeTest extends TestCase
{
    public function testBasicFunctionality()
    {
        $datetime = new DateTime('2010-03-05 12:03:45');

        $this->assertEquals($datetime->format('Y'), 2010);
        $this->assertEquals('2010-03-05', $datetime->format('Y-m-d'));
    }

    public function testToString()
    {
        $datetime = new DateTime('2010-03-05 12:03:45');

        $this->assertEquals('12:03, 5/3/2010', $datetime->__toString());
    }

    public function testToStringCustomFormat()
    {
        DateTime::$defaultFormat = DateTime::RFC2822;

        $datetime = new DateTime('2010-03-05 12:03:45');

        $this->assertEquals('Fri, 05 Mar 2010 12:03:45 +0000', $datetime->__toString());
    }
}
