<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/tfountain
 * @copyright Copyright (c) 2010-2013 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace Zeal\Orm\Form;

use Zeal\Orm\Hydrator\AbstractHydrator;

class Hydrator extends AbstractHydrator
{
    protected $formElements;

    public function setFormElements($formElements)
    {
        $this->formElements = $formElements;
    }

    public function getFormElements()
    {
        return $this->formElements;
    }

    public function extract($object)
    {
        $modelHydrator = $object->getHydrator();

        $modelData = $modelHydrator->extract($object);

        $data = array();
        foreach ($this->getFormElements() as $name => $element) {
            if (array_key_exists($name, $modelData)) {
                $data[$name] = $modelData[$name];
            } else if ($object->isAssociation($name) || $object->isAssociationPropertyListener($name)) {
                $data[$name] = $object->$name;
            }
        }

        return $data;
    }
}
