<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/tfountain
 * @copyright Copyright (c) 2010-2013 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace ZealOrm\Model;

use Zend\Stdlib\Hydrator\AbstractHydrator;

class Hydrator extends AbstractHydrator
{
    protected $fields;


    public function setFields(array $fieldTypes)
    {
        $this->fieldTypes = $fieldTypes;

        return $this;
    }

    public function getFields()
    {
        return $this->fieldTypes;
    }

    public function extract($object)
    {
        if (!is_callable(array($object, 'getArrayCopy'))) {
            throw new Exception\BadMethodCallException(sprintf(
                '%s expects the provided object to implement getArrayCopy()', __METHOD__
            ));
        }

        $self = $this;
        $data = $object->getArrayCopy();
        array_walk($data, function (&$value, $name) use ($self, &$data) {
            if (!$self->getFilter()->filter($name)) {
                unset($data[$name]);
            } else {
                $value = $self->extractValue($name, $value);
            }
        });

        return $data;
    }

    public function hydrate(array $data, $object)
    {
        $fieldTypes = $this->getFields();

        foreach ($data as $key => $value) {
            $fieldType = isset($fieldTypes[$key]) ? $fieldTypes[$key] : 'string';
            switch ($fieldType) {
                case 'integer':
                    $data[$key] = (int)$value;
                    break;

                case 'boolean':
                    $data[$key] = (bool)$value;
                    break;

                case 'datetime':
                    $data[$key] = new \ZealOrm\DateTime($value);
                    break;

                case 'serialized':
                    if (!empty($value)) {
                        $data[$key] = unserialize($value);
                    }
                    break;

                case 'string':
                default:
                    // leave array entry unchanged
                    break;
            }
        }

        $object->populate($data);
    }
}
