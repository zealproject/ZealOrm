<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/zealproject
 * @copyright Copyright (c) 2010-2018 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

declare(strict_types=1);

namespace Zeal\Orm\Adapter\Zend\Db;

use Psr\Container\ContainerInterface;
use Zend\Db\Adapter\Adapter as ZendDbAdapter;

class DbAdapterFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $db = $container->get(ZendDbAdapter::class);

        return new DbAdapter($db);
    }
}
