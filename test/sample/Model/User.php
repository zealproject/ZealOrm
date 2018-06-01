<?php
namespace Zeal\OrmTest\Model;

use Zeal\Orm\Model\AbstractModel;

class User extends AbstractModel
{
    protected $name;

    protected $age;

    public function getAge()
    {
        return $this->age.' years';
    }
}
