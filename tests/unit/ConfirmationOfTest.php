<?php

/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2016 Phalcon Team (http://www.phalconphp.com)       |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file docs/LICENSE.txt.                        |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: David Hubner <david.hubner@gmail.com>                         |
  +------------------------------------------------------------------------+
 */

namespace Phalcon\Incubator\Validation\Tests\Unit;

use Codeception\Util\Stub;
use Phalcon\Incubator\Validation\ConfirmationOf;
use Phalcon\Filter\Validation;

class ConfirmationOfTest extends \Codeception\Test\Unit
{
    public function testValidateExceptionWithoutOrigField()
    {
        $validation = Stub::make('Phalcon\Filter\Validation');

        $validator = new ConfirmationOf();
        $this->expectException('Phalcon\Filter\Validation\Exception');

        $validator->validate($validation, 'confirmation');
    }

    public function testValidateSameAsOrig()
    {
        $validation = Stub::make(
            'Phalcon\Filter\Validation',
            [
                'getValue' => 'value',
            ]
        );

        $validator = new ConfirmationOf(
            [
                'origField' => 'original',
            ]
        );

        $this->assertTrue(
            $validator->validate($validation, 'confirmation')
        );
    }

    public function testValidateNotSameAsOrig()
    {
        $validation = Stub::make(
            Validation::class,
            [
                'getValue'      => Stub::consecutive('val1', 'val2'),
            ]
        );

        $validator = new ConfirmationOf(
            [
                'origField' => 'original',
            ]
        );

        $this->assertFalse(
            $validator->validate($validation, 'confirmation')
        );
    }

    public function testValidateAllowEmpty()
    {
        $validation = Stub::make(
            'Phalcon\Filter\Validation',
            [
                'getValue' => Stub::consecutive('', 'val2'),
            ]
        );

        $validator = new ConfirmationOf(
            [
                'origField'  => 'original',
                'allowEmpty' => true,
            ]
        );

        $this->assertTrue(
            $validator->validate($validation, 'confirmation')
        );
    }

    public function testValidateNotAllowEmpty()
    {
        $validation = Stub::make(
            'Phalcon\Filter\Validation',
            [
                'getValue'      => Stub::consecutive('', 'val2'),
            ]
        );

        $validator = new ConfirmationOf(
            [
                'origField'  => 'original',
                'allowEmpty' => false,
            ]
        );

        $this->assertFalse(
            $validator->validate($validation, 'confirmation')
        );
    }

    public function testValidateInvalidValue()
    {
        $validation = Stub::make(
            'Phalcon\Filter\Validation',
            [
                'getValue'      => ['value', 'value'],
            ]
        );

        $validator = new ConfirmationOf(
            [
                'origField' => 'original',
            ]
        );

        $this->assertFalse(
            $validator->validate($validation, 'confirmation')
        );
    }
}
