<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/tfountain
 * @copyright Copyright (c) 2010-2018 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace Zeal\Orm\Hydrator;

use Zeal\Orm\Hydrator\AbstractHydrator;
use Zend\Hydrator\Strategy\BooleanStrategy;
use Zeal\Orm\Hydrator\Strategy\IntegerStrategy;
use Zeal\Orm\Hydrator\Strategy\DateTimeStrategy;
use Zeal\Orm\Hydrator\Strategy\SerializeStrategy;
use Zeal\Orm\Hydrator\Strategy\IpStrategy;

class ModelHydrator extends AbstractHydrator
{
    protected $fields;

    public function __construct($fields)
    {
        parent::__construct();

        if (count($fields) == 0) {
            throw new \Exception('Unable to create ModelHydrator without fields');
        }

        $this->fields = $fields;
        $this->addStrategies();
    }

    protected function addStrategies()
    {
        foreach ($this->fields as $fieldName => $fieldType) {
            switch ($fieldType) {
                case 'boolean':
                    $this->addStrategy($fieldName, new BooleanStrategy('1', '0'));
                    break;

                case 'integer':
                    $this->addStrategy($fieldName, new IntegerStrategy());
                    break;

                case 'datetime':
                    $this->addStrategy($fieldName, new DateTimeStrategy());
                    break;

                case 'serialized':
                    $this->addStrategy($fieldName, new SerializeStrategy());
                    break;

                case 'ip':
                    $this->addStrategy($fieldName, new IpStrategy());
                    break;
            }
        }
    }

    /*
     * Extract values from an object
     *
     * @param  object $object
     * @return array
     */
    public function extract($object)
    {
        // if (!is_object($object)) {
        //     throw new Exception\BadMethodCallException(
        //         sprintf('%s expects the provided $object to be a PHP object)', __METHOD__)
        //     );
        // }
        $filter = $this->getFilter();

        $data = [];
        foreach ($this->fields as $fieldName => $fieldType) {
            // Filter keys, removing any we don't want
            if (!$filter->filter($fieldName)) {
                continue;
            }

            // Replace name if extracted differ
            $fieldName = $this->extractName($fieldName, $object);

            $data[$fieldName] = $this->extractValue($fieldName, $object->$fieldName, $object);
        }

        return $data;
    }

    /**
     * Hydrate a model
     *
     * @param  array  $data
     * @param  object $object
     * @return object
     */
    public function hydrate(array $data, $object)
    {
        // if (!is_object($object)) {
        //     throw new Exception\BadMethodCallException(
        //         sprintf('%s expects the provided $object to be a PHP object)', __METHOD__)
        //     );
        // }

        foreach ($data as $rawName => $rawValue) {
            $name = $this->hydrateName($rawName, $data);
            $value = $this->hydrateValue($name, $rawValue, $data);

            $object->$name = $value;
        }

        return $object;
    }
}
