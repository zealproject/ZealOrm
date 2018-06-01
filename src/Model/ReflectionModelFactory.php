<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/zealproject
 * @copyright Copyright (c) 2010-2018 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace Zeal\Orm\Model;

use Psr\Container\ContainerInterface;
use Zeal\Orm\Hydrator\ModelHydrator;
use ReflectionClass;
use ReflectionProperty;

class ReflectionModelFactory
{
    protected $ignoreProperties = ['dirty', 'hydrator', 'associations', 'associationPropertyListeners'];

    public function __invoke(ContainerInterface $container, $requestedName)
    {
        $model = new $requestedName();

        $reflect = new ReflectionClass($model);
        $properties = $reflect->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);

        $fields = [];
        foreach ($properties as $property) {
            $name = $property->getName();
            if (in_array($name, $this->ignoreProperties)) {
                continue;
            }

            $type = $this->determineFieldType($property);
            if ($type) {
                $fields[$name] = $type;
            } else {
                $fields[$name] = 'string';
            }
        }

        $hydrator = new ModelHydrator($fields);
        $model->setHydrator($hydrator);

        // $fieldsMapper = $container->get(\Zeal\Cms\Forms\Mapper\FormFieldMapper::class);
        // $fieldsAssoc = new \Zeal\Orm\Mapper\Association\HasMany($fieldsMapper, []);

        return $model;
    }

    protected function determineFieldType(ReflectionProperty $property)
    {
        $comment = $property->getDocComment();

        $type = false;
        $lines = explode("\n", $comment);
        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }
            if (strpos($line, '@var') !== false) {
                $var = substr($line, strpos($line, '@var') + 4);
                if (strpos($var, ' ')) {
                    $var = substr($var, 0, strpos($var, ' '));
                }
                $type = trim($var);
            }
        }
        if ($type) {
            switch ($type) {
                case 'Zeal\Orm\DateTime':
                    $type = 'datetime';
                    break;
            }
        }

        return $type;
    }
}
