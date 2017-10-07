<?php

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
            // 'invokables' => [
            //     Orm::class => Orm::class,
            // ],
            'factories' => [
                Orm::class => OrmFactory::class,
            ],
        ];
    }
}
