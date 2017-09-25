<?php

namespace Zeal\Orm;

use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ConfigInterface;

class OrmManager extends AbstractPluginManager
{
    /**
     * Validate the plugin
     *
     * @param  mixed $plugin
     * @return true
     * @throws Exception\InvalidControllerException
     */
    public function validatePlugin($plugin)
    {
        return;
    }
}
