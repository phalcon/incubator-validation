<?php

namespace Phalcon\Incubator\Validation;

use Phalcon\Messages\Message;
use Phalcon\Validation;
use Phalcon\Validation\AbstractValidator;
use Phalcon\Validation\ValidatorInterface;

class ArrayInclusionIn extends AbstractValidator implements ValidatorInterface
{
    /**
     * Executes the validation
     *
     * @param Validation $validator
     * @param string     $attribute
     *
     * @return bool
     *
     */
    public function validate(Validation $validator, $attribute): bool
    {
        $array = $validator->getValue($attribute);
        $domain = $this->getOption('domain');
        $allowEmpty = $this->getOption('allowEmpty');

        if ((empty($array) && !$allowEmpty) || empty($domain) || !is_array($array)) {
            $validator->appendMessage(
                new Message(
                    'Invalid argument supplied',
                    $attribute
                )
            );

            return false;
        }

        foreach ($array as $item) {
            if (!in_array($item, $domain)) {
                $message = $this->getOption(
                    'message',
                    'Values provided not exist in domain'
                );

                $validator->appendMessage(
                    new Message(
                        $message,
                        $attribute
                    )
                );

                return false;
            }
        }

        return true;
    }
}
