<?php

namespace Phalcon\Incubator\Validation\Tests\Unit;

use Codeception\Test\Unit;
use Phalcon\Incubator\Validation\IsBool;
use Phalcon\Validation;

class IsBoolTest extends Unit
{
    private const ATTRIBUTE = 'attribute';

    public function testItValidatesWhenAllowEmpty(): void
    {
        $validation = new Validation();

        $validation->add(self::ATTRIBUTE, new IsBool(['allowEmpty' => true]));

        $isInvalid = $validation->validate([self::ATTRIBUTE => null]);

        $this->assertFalse($isInvalid->valid());
    }

    /**
     * @dataProvider isBoolDataProvider
     * @param mixed $sut
     * @param bool $expected
     */
    public function testItValidatesTypes($sut, bool $expected): void
    {
        $validation = new Validation();

        $validation->add(self::ATTRIBUTE, new IsBool());

        $isInvalid = $validation->validate([self::ATTRIBUTE => $sut]);

        $this->assertEquals($expected, $isInvalid->valid());
    }

    public static function isBoolDataProvider(): array
    {
        return [
            [true, false],
            [false, false],
            ['string', true],
            [1, true],
            [null, true]
        ];
    }
}