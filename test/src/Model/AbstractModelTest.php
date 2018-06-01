<?php

declare(strict_types=1);

namespace Zeal\OrmTest;

use Zeal\Orm\Model\AbstractModel;
use PHPUnit\Framework\TestCase;
use Zeal\OrmTest\Model\User;

class AbstractModelTest extends TestCase
{
    public function testGetFunctionIsUsed()
    {
        $user = new User();
        $user->age = 25;

        $this->assertEquals('25 years', $user->age);
    }

    public function testGetFunctionReturnsProperties()
    {
        $user = new User();
        $user->name = 'Joe';

        $this->assertEquals('Joe', $user->name);
    }

    public function testGetNonExistentPropertyException()
    {
        $this->expectException(\Exception::class);

        $user = new User();
        $user->apple;
    }


    public function testDirty()
    {
        $user = new User();
        $user->name = "Joe";

        $this->assertTrue($user->isDirty());
    }
}
