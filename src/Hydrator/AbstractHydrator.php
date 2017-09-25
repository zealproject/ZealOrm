<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/tfountain
 * @copyright Copyright (c) 2010-2013 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace Zeal\Orm\Hydrator;

use Zend\Hydrator\AbstractHydrator as ZendAbstractHydrator;
use Zeal\Orm\DateTime;
use Zend\Exception;

abstract class AbstractHydrator extends ZendAbstractHydrator
{
    /**
     * @var array
     */
    protected $fields;

    /**
     * Setter for field types array
     *
     * This should be an array of field types where the key is the
     * name of the field, and the value is the field type
     *
     * @param array $fields
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Getter for the field types array
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Extract values from an object based on the fields array
     *
     * @param  object $object
     * @return array
     */
    public function extractFieldData($object)
    {
        $fields = $this->getFields();

        $data = array();
        foreach ($fields as $field => $fieldType) {
            switch ($fieldType) {
                case 'integer':
                    if ($object->$field !== null) {
                        $data[$field] = (int)$object->$field;
                    }
                    break;

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

        return $data;
    }

    /**
     * Extract values from an object
     *
     * @param  object $object
     * @return array
     */
    public function extract($object)
    {
        if (method_exists($object, 'getArrayCopy')) {
            $data = $object->getArrayCopy();

            if (!is_array($data)) {
                throw new \Exception(get_class($object).'::getArrayCopy() must return an array');
            }

            return $data;
        }

        return $this->extractFieldData($object);
    }

    public function hydrate(array $data, $object)
    {
        $fieldTypes = $this->getFields();

        foreach ($data as $key => $value) {
            $fieldType = isset($fieldTypes[$key]) ? $fieldTypes[$key] : 'string';
            switch ($fieldType) {
                case 'integer':
                    if ($value === null || $value === '') {
                        $data[$key] = null;
                    } else {
                        $data[$key] = (int)$value;
                    }
                    break;

                case 'boolean':
                    $data[$key] = (bool)$value;
                    break;

                case 'datetime':
                    if (!empty($value)) {
                        if ($value instanceof DateTime) {
                            $data[$key] = $value;
                        } else {
                            $data[$key] = new DateTime($value);
                        }
                    }
                    break;

                case 'date':
                    if (!empty($value)) {
                        if ($value instanceof DateTime) {
                            $data[$key] = $value;
                        } else {
                            $data[$key] = new DateTime($value);
                        }
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

        foreach ($data as $var => $value) {
            $object->$var = $value;
        }

        return $object;
    }
}
