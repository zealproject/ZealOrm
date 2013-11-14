<?php

namespace ZealOrm\Model\Association;

interface AssociationInterface
{
    const BELONGS_TO = 1;
    const HAS_ONE = 2;
    const HAS_MANY = 3;
    const HAS_AND_BELONGS_TO_MANY = 4;

    public function loadData();

    public function saveData($object, $adapter);

    public function buildQuery();

    public function getType();

    public function setShortname($shortname);

    public function getShortname();

    public function setListenerProperty($var, $value);

    public function getListenerProperty($var);

    //public function setSource($sourceModel);

    public function setTargetClassName($className);

    public function getTargetClassName();

    public function hasOption($key);

    public function getOption($key, $default);

    public function getOptions();
}
