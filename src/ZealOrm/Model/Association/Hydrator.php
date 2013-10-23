<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/tfountain
 * @copyright Copyright (c) 2010-2013 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace ZealOrm\Model\Association;

use ZealOrm\Hydrator\AbstractHydrator as AbstractHydrator;

class Hydrator extends AbstractHydrator
{
    public function extract($association)
    {
        if (!$association->isDirty()) {
            return null;
        }

        return array(146);

        $data = $association->loadData();
        if ($data) {

        } else {
            //return $association->get
        }

        var_dump($data);exit;
    }
}
