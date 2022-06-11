<?php

namespace Phalcon\Incubator\Validation;

use Phalcon\Messages\Message;
use Phalcon\Validation;
use Phalcon\Validation\AbstractValidator;
use Phalcon\Validation\ValidatorInterface;

/**
 * Validates whether an attribute is a bool type
 *
 * <code>
 * new \Phalcon\Validation\Validator\IsBool([
 *     'message' => {string - validation message},
 *     'allowEmpty' => {bool - allow empty value}
 * ])
 * </code>
 *
 * @package Phalcon\Validation\Validator
 */
class IsBool extends AbstractValidator implements ValidatorInterface
{
    /**
     * @param Validation $validation
     * @param string $attribute
     * @return bool
     */
    public function validate(Validation $validation, $attribute): bool
    {
        $value = $validation->getValue($attribute);

        if (is_bool($value) || $this->isValidEmpty($value)) {
            return true;
        }

        $messageText = $this->getOption('message') ?? "${$attribute} must be of type bool";
        $message = new Message($messageText, $attribute, 'Bool');

        $validation->appendMessage($message);

        return false;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    private function isValidEmpty($value): bool
    {
        return (($this->getOption('allowEmpty') ?? false) && is_null($value));
    }
}
