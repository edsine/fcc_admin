<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 10/7/2016
 * Time: 2:41 AM
 */

namespace AppBundle\Validation;


use Psr\Log\LoggerInterface;
use \DateTime;

class Validator
{
    private $fields;

    public function __construct()
    {
        $this->fields = new Fields();
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function required($name, $value)
    {

        // add and return/get Field object
        //$field = $this->fields->addField($name, $message);

        // Get Field object
        $field = $this->fields->getField($name);

        // Check field and set or clear error message
        if (empty($value)) {
            //$field->setErrorMessage('Required.');
            $field->fieldHasError();
        } else {
            $field->clearErrorMessage();
        }
    }

    public function textRequired2($name, $value, $message)
    {

        // add and return/get Field object
        //$field = $this->fields->addField($name, $message);

        // Get Field object
        $field = $this->fields->getField($name);
        if ($field) {
            $field = $this->fields->addAndReturnField($name, $message);
        }

        // Check field and set or clear error message
        if (empty($value)) {
            //$field->setErrorMessage('Required.');
            $field->fieldHasError();
        } else {
            $field->clearErrorMessage();
        }
    }

    // Validate a generic text field
    public function textRequiredMax($name, $value, $required = true, $min = 1, $max = 255)
    {

        // add and return/get Field object
        //$field = $this->fields->addField($name, $message);

        // Get Field object
        $field = $this->fields->getField($name);

        // If field is not required and empty, remove errors and exit
        if (!$required && empty($value)) {
            $field->clearErrorMessage();
            return;
        }

        // Check field and set or clear error message
        if ($required && empty($value)) {
            //$field->setErrorMessage('Required.');
            $field->fieldHasError();
        } else if (strlen($value) < $min) {
            $field->fieldHasError();
            $field->setErrorMessage("Cannot be less than $min characters");
        } else if (strlen($value) > $max) {
            $field->fieldHasError();
            $field->setErrorMessage("Cannot be more than $max characters");
        } else {
            $field->clearErrorMessage();
        }
    }

    public function integer($name, $value, $allowZero = false)
    {
        // add and return/get Field object
        //$field = $this->fields->addField($name, $message);

        // Get Field object
        $field = $this->fields->getField($name);

        // Check field and set or clear error message
        if (empty($value)) {
            //$field->setErrorMessage('Email is Required.');
            $field->fieldHasError();
            if($allowZero && $value==0){
                $field->clearErrorMessage();
            }
        } else if (filter_var(str_replace(',', '', $value), FILTER_VALIDATE_INT) === false) {
            //$field->setErrorMessage('Invalid input');
            $field->fieldHasError();
        } else {
            $field->clearErrorMessage();
        }
    }

    public function number($name, $value)
    {
        // add and return/get Field object
        //$field = $this->fields->addField($name, $message);

        // Get Field object
        $field = $this->fields->getField($name);

        // Check field and set or clear error message
        if (empty($value)) {
            //$field->setErrorMessage('Email is Required.');
            $field->fieldHasError();
        } else if (is_numeric($value)===false) {
            $field->fieldHasError();
            $field->setErrorMessage('Invalid');
        } else {
            $field->clearErrorMessage();
        }
    }

    public function totalChars($name, $value, $totalCharacters)
    {

        // add and return/get Field object
        //$field = $this->fields->addField($name, $message);

        // Get Field object
        $field = $this->fields->getField($name);

        // Check field and set or clear error message
        if (empty($value)) {
            //$field->setErrorMessage('Required.');
            $field->fieldHasError();
        } else if (strlen($value) != $totalCharacters) {
            $field->fieldHasError();
            $field->setErrorMessage("Must be $totalCharacters characters.");
        } else {
            $field->clearErrorMessage();
        }
    }

    public function totalDigits($name, $value, $totalDigits)
    {

        // add and return/get Field object
        //$field = $this->fields->addField($name, $message);

        // Get Field object
        $field = $this->fields->getField($name);

        // Check field and set or clear error message
        if (empty($value)) {
            //$field->setErrorMessage('Required.');
            $field->fieldHasError();
        } else if (strlen($value) != $totalDigits) {
            $field->fieldHasError();
            $field->setErrorMessage("Must be $totalDigits digits.");
        } else {
            $field->clearErrorMessage();
        }
    }

    public function email($name, $value)
    {
        // add and return/get Field object
        //$field = $this->fields->addField($name, $message);

        // Get Field object
        $field = $this->fields->getField($name);

        // Check field and set or clear error message
        if (empty($value)) {
            //$field->setErrorMessage('Email is Required.');
            $field->fieldHasError();
        } else if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            $field->setErrorMessage('Invalid email address');
            $field->fieldHasError();
        } else {
            $field->clearErrorMessage();
        }
    }

    public function phone($name, $value)
    {
        // add and return/get Field object
        //$field = $this->fields->addField($name, $message);

        // Get Field object
        $field = $this->fields->getField($name);

        $gsmPrefix = substr($value, 0, 3);
        $prefixArray = array('070', '080', '081', '090');


        if (empty($value)) {
            //$field->setErrorMessage('Email is Required.');
            $field->fieldHasError();
        } else if (is_numeric($value)===false) {
            $field->fieldHasError();
            $field->setErrorMessage('Invalid phone number');
        }else if (strlen($value) !== 11) {
            $field->fieldHasError();
            $field->setErrorMessage('Phone number must be Nigerian GSM');
        }else if (!in_array($gsmPrefix, $prefixArray)) {
            $field->fieldHasError();
            $field->setErrorMessage('Phone number must be Nigerian GSM.');
        } else {
            $field->clearErrorMessage();
        }

    }

    public function datePattern($name, $value, $format, $message = 'Invalid input format.')
    {
        // add and return/get Field object
        //$field = $this->fields->addField($name, $message);

        // Get Field object
        $field = $this->fields->getField($name);

        // Check field and set or clear error message
        if (empty($value)) {
            //$field->setErrorMessage('Email is Required.');
            $field->fieldHasError();
        } else if (!DateTime::createFromFormat($format, $value)) {
            $field->setErrorMessage($message);
            $field->fieldHasError();
        } else {
            $field->clearErrorMessage();
        }
    }

    public function matches($name, $firstValue, $secondValue)
    {
        // add and return/get Field object
        //$field = $this->fields->addField($name, $message);

        // Get Field object
        $field = $this->fields->getField($name);

        // Check field and set or clear error message
        if ($firstValue && $secondValue && ($firstValue !== $secondValue)) {
            //$field->setErrorMessage('Email is Required.');
            $field->fieldHasError();
        } else {
            $field->clearErrorMessage();
        }
    }
}