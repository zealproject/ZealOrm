<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/tfountain
 * @copyright Copyright (c) 2010-2018 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace Zeal\Orm\ActiveRecord;

interface ActiveRecordInterface
{
    public static function find($id);

    public static function first();

    public static function all();

    public static function where($params);

    public static function order($order);

    public function create();

    public function update();

    public function save();

    public function delete();
}
