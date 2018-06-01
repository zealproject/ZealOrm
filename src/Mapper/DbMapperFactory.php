<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/tfountain
 * @copyright Copyright (c) 2010-2018 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

declare(strict_types=1);

namespace Zeal\Orm\Mapper;

use Psr\Container\ContainerInterface;
use Zeal\Orm\Adapter\Zend\Db as DbAdapter;
use Zeal\Orm\Model\Hydrator as ModelHydrator;

class DbMapperFactory
{
    public function __invoke(ContainerInterface $container, $requestedName)
    {
        $adapter = $container->get(DbAdapter::class);

        if (!class_exists($requestedName)) {
            throw new \Exception("Invalid class '$requestedName'");
        }

        $mapper = new $requestedName();
        $adapter->setOptions($mapper->getAdapterOptions());
        $mapper->setAdapter($adapter);

        // $hydrator = $container->get(ModelHydrator::class);
        // $hydrator->setFields($mapper->getFields());
        // $mapper->setHydrator($hydrator);

        $mapper->setContainer($container);

        return $mapper;
    }
}
