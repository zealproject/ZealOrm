<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/tfountain
 * @copyright Copyright (c) 2010-2018 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

declare(strict_types=1);

namespace Zeal\Orm\Middleware;

use Psr\Container\ContainerInterface;
use Zeal\Orm\Adapter\Zend\Db as DbAdapter;
use Zeal\Orm\Orm;

class OrmInitializerMiddlewareFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $adapter = $container->get(DbAdapter::class);

        Orm::setAdapter(DbAdapter::class, $adapter);

        return new OrmInitializerMiddleware();
    }
}
