<?php

/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2016 Phalcon Team (https://www.phalconphp.com)      |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file LICENSE.txt.                             |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: Anton Kornilov <kachit@yandex.ru>                             |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Incubator\Validation;

use MongoId as Id;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\AbstractValidator;
use Phalcon\Filter\Validation\Validator;
use Phalcon\Messages\Message;
use Phalcon\Filter\Validation\Exception as ValidationException;
use Phalcon\Filter\Validation\ValidatorInterface;

/**
 * MongoId validator
 *
 * @package Phalcon\Validation\Validator
 */
class MongoId extends AbstractValidator implements ValidatorInterface
{
    /**
     * @param Validation $validator
     * @param string     $attribute
     *
     * @return bool
     * @throws ValidationException
     */
    public function validate(Validation $validator, $attribute): bool
    {
        if (!extension_loaded('mongo')) {
            throw new ValidationException('Mongo extension is not available');
        }

        $value = $validator->getValue($attribute);
        $allowEmpty = $this->hasOption('allowEmpty');
        $result = ($allowEmpty && empty($value)) ? true : Id::isValid($value);

        if (!$result) {
            $message = ($this->hasOption('message')) ? $this->getOption('message') : 'MongoId is not valid';

            $validator->appendMessage(
                new Message(
                    $message,
                    $attribute,
                    'MongoId'
                )
            );
        }

        return $result;
    }
}
