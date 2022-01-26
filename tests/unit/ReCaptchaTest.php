<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Incubator\Validation\Tests\Unit;

use Codeception\Test\Unit;
use Phalcon\Incubator\Validation\ReCaptcha;
use Phalcon\Filter\Validation\AbstractValidator;

final class ReCaptchaTest extends Unit
{
    public function testImplementation(): void
    {
        $class = $this->createMock(ReCaptcha::class);

        $this->assertInstanceOf(AbstractValidator::class, $class);
    }
}
