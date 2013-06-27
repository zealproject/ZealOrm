<?php

namespace ZealOrm\ActiveRecord;

class ActiveRecordInterface
{
    public static function find($id);

    public static function first();

    public static function all();

    public static function where($params);

    public static function create(array $data);

    public function update();

    public function save();

    public function delete();
}
