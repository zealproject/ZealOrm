<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/tfountain
 * @copyright Copyright (c) 2010-2013 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace Zeal\Orm;

use Zeal\Orm\Orm;
use Interop\Container\ContainerInterface;

class OrmFactory
{
    /**
     * Create an ORM instance.
     *
     * @param ContainerInterface $container
     * @return Orm
     */
    public function __invoke(ContainerInterface $container)
    {
        // if (! $container->has(RouterInterface::class)) {
        //     throw new Exception\MissingRouterException(sprintf(
        //         '%s requires a %s implementation; none found in container',
        //         UrlHelper::class,
        //         RouterInterface::class
        //     ));
        // }

        // return new UrlHelper($container->get(RouterInterface::class));

        $db = $container->get('Zend\Db\Adapter\Adapter');
        $adapter = new \Zeal\Orm\Adapter\Zend\Db($db);

        Orm::setDefaultAdapter($adapter);

        $orm = new Orm();

        return $orm;
    }
}
