<?php

namespace Phalcon\Incubator\Validation\Unit;

use Phalcon\Filter\Validation;
use Phalcon\Incubator\Validation\ArrayInclusionIn;

class ArrayInclusionInTest extends \Codeception\Test\Unit
{
    protected $domain = ['A', 'B', 'C', 'D'];

    public function testArrayInclusionInValidatorOk()
    {
        $values = ['A', 'B'];

        $validation = new Validation();

        $validation->add(
            'field',
            new ArrayInclusionIn(
                [
                    'domain'     => $this->domain,
                    'allowEmpty' => false,
                ]
            )
        );

        $messages = $validation->validate(
            [
                'field' => $values,
            ]
        );

        $this->assertCount(
            0,
            $messages
        );
    }

    public function testArrayInclusionInWithInvalidInput()
    {
        $values = ['A', 'E'];

        $validation = new Validation();

        $validation->add(
            'field',
            new ArrayInclusionIn(
                [
                    'domain'     => $this->domain,
                    'allowEmpty' => false,
                ]
            )
        );

        $messages = $validation->validate(
            [
                'field' => $values,
            ]
        );

        $this->assertCount(
            1,
            $messages
        );
    }

    public function testArrayInclusionInWithInvalidArgument()
    {
        $values = 'A';

        $validation = new Validation();

        $validation->add(
            'field',
            new ArrayInclusionIn(
                [
                    'domain'     => $this->domain,
                    'allowEmpty' => false,
                ]
            )
        );

        $messages = $validation->validate(
            [
                'field' => $values,
            ]
        );

        $this->assertCount(
            1,
            $messages
        );
    }

    public function testArrayInclusionInWithAllowEmptyTrue()
    {
        $values = null;

        $validation = new Validation();

        $validation->add(
            'field',
            new ArrayInclusionIn(
                [
                    'domain'     => $this->domain,
                    'allowEmpty' => true,
                ]
            )
        );

        $messages = $validation->validate(
            [
                'field' => $values,
            ]
        );

        $this->assertCount(
            0,
            $messages
        );
    }
}
