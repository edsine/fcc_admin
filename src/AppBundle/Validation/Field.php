<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 10/7/2016
 * Time: 2:36 AM
 */

namespace AppBundle\Validation;


class Field
{
    private $name;
    private $message = '';
    private $hasError = false;

    public function __construct($name, $message = '') {
        $this->name = $name;
        $this->message = $message;
    }
    public function getName()    { return $this->name; }
    public function getMessage() { return $this->message; }
    public function hasError()    { return $this->hasError; }

    public function setErrorMessage($message) {
        $this->message = $message;
        $this->hasError = true;
    }
    public function clearErrorMessage() {
        $this->message = '';
        $this->hasError = false;
    }

    public function fieldHasError(){
        $this->hasError = true;
    }

    public function getHtml() {
        $message = htmlspecialchars($this->message);
        if ($this->hasError()) {
            return '<div class="validation-error">' . $message . '</div>';
        } else {
            return '';
        }
    }

    public function getErrorText() {
        $message = htmlspecialchars($this->message);
        if ($this->hasError()) {
            return $message;
        } else {
            return '';
        }
    }
}