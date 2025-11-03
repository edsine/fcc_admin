<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 10/7/2016
 * Time: 2:38 AM
 */

namespace AppBundle\Validation;


class Fields
{
    private $fields = array();

    public function addField($name, $message = '') {
        $field = new Field($name, $message);
        $this->fields[$field->getName()] = $field;
    }

    public function addFieldObject($field) {
        $this->fields[$field->getName()] = $field;
    }

    public function addAndReturnField($name, $message = '') {
        $field = new Field($name, $message);
        $this->fields[$field->getName()] = $field;

        return $field;
    }

    public function getField($name) {
        return $this->fields[$name];
    }

    public function getFields() {
        return $this->fields;
    }

    public function hasErrors() {
        foreach ($this->fields as $field) {
            if ($field->hasError()) return true;
        }
        return false;
    }
}