<?php

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
