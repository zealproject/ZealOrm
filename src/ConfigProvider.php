<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/tfountain
 * @copyright Copyright (c) 2010-2018 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace Zeal\Orm;

class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * @return array
     */
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    /**
     * Returns the container dependencies
     *
     * @return array
     */
    public function getDependencies()
    {
        return [
            'invokables' => [
                Model\Hydrator::class => Model\Hydrator::class,
            ],
            'factories' => [
                // Orm::class => OrmFactory::class,
                Adapter\Zend\Db::class => Adapter\Zend\Db\DbAdapterFactory::class,
                Mapper\Association\HasMany::class => Mapper\Association\AssociationFactory::class,
                Middleware\OrmInitializerMiddleware::class => Middleware\OrmInitializerMiddlewareFactory::class,
            ],
            'shared' => [
                Adapter\Zend\Db::class => false
            ]
        ];
    }
}
