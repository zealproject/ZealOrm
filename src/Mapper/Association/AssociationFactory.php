<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/zealproject
 * @copyright Copyright (c) 2010-2018 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

declare(strict_types=1);

namespace Zeal\Orm\Mapper\Association;

use Psr\Container\ContainerInterface;

class AssociationFactory
{
    public function __invoke(ContainerInterface $container, $requestedName)
    {
        $association = new $requestedName();

        return $association;
    }
}
