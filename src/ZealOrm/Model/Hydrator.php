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
use ZealOrm\DateTime;
use Zend\Exception;

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
        $fieldTypes = $this->getFields();

        $data = array();
        foreach ($fieldTypes as $field => $fieldType) {
            switch ($fieldType) {
                case 'datetime':
                    if ($object->$field) {
                        $data[$field] = $object->$field->format('Y-m-d H:i:s');
                    } else {
                        $data[$field] = null;
                    }
                    break;

                case 'date':
                    if ($object->$field) {
                        $data[$field] = $object->$field->format('Y-m-d');
                    } else {
                        $data[$field] = null;
                    }
                    break;

                case 'serialized':
                    $data[$field] = serialize($object->$field);
                    break;

                case 'ip':
                    if ($object->$field) {
                        $data[$field] = ip2long($object->$field);
                    } else {
                        $data[$field] = null;
                    }
                    break;

                default:
                    $data[$field] = $object->$field;
                    break;
            }
        }

        /*if (!is_callable(array($object, 'getArrayCopy'))) {
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
        });*/

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
                    if ($value instanceof DateTime) {
                        $data[$key] = $value;
                    } else {
                        $data[$key] = new DateTime($value);
                    }
                    break;

                case 'serialized':
                    if (!empty($value) && is_string($value)) {
                        $data[$key] = unserialize($value);
                    }
                    break;

                case 'ip':
                    if (!empty($value)) {
                        $data[$key] = long2ip($value);
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
