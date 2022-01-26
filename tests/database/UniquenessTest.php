<?php

namespace Phalcon\Incubator\Validation\Tests\Database;

use Phalcon\Di\Di;
use Phalcon\Incubator\Validation\Test\Fixtures\Migrations\CustomersMigration;
use Phalcon\Filter\Validation;
use Phalcon\Incubator\Validation\Db\Uniqueness;
use Phalcon\Filter\Validation\Exception;

/**
 * \Phalcon\Test\Validation\Validator\Db\UniquenessTest
 * Tests for Phalcon\Validation\Validator\Db\Uniqueness component
 *
 * @copyright (c) 2011-2016 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Tomasz Ślązok <tomek@landingi.com>
 * @package   Phalcon\Test\Validation\Validator\Db
 * @group     DbValidation
 *
 * The contents of this file are subject to the New BSD License that is
 * bundled with this package in the file docs/LICENSE.txt
 *
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@phalconphp.com
 * so that we can send you a copy immediately.
 */
class UniquenessTest extends \Codeception\Test\Unit
{
    /**
     * @var Validation
     */
    protected $validation;

    /**
     * executed before each test
     */
    protected function _before()
    {
        $this->validation = new Validation();
    }

    private function getDbStub()
    {
        codecept_debug('getDbStub');

        $adapter = 'Mysql';
        $class   = 'Phalcon\Db\Adapter\Pdo\\' . $adapter;
        $params  = [
            'host'     => getenv('DATA_MYSQL_HOST'),
            'username' => getenv('DATA_MYSQL_USER'),
            'password' => getenv('DATA_MYSQL_PASS'),
            'dbname'   => getenv('DATA_MYSQL_NAME'),
            'charset'  => getenv('DATA_MYSQL_CHARSET'),
            'port'     => getenv('DATA_MYSQL_PORT')
        ];

        return new $class($params);
    }

    public function testShouldCatchExceptionWhenValidateUniquenessWithoutDbAndDefaultDI()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Validator Uniqueness require connection to database");
        $uniquenessOptions = [
            'table'  => 'co_customers',
            'column' => 'cst_login',
        ];

        new Uniqueness($uniquenessOptions);
    }

    public function testShouldCatchExceptionWhenValidateUniquenessWithoutColumnOption()
    {
        $this->expectExceptionMessage("Validator require column option to be set");
        $this->expectException(Exception::class);

        new Uniqueness(
            [
                'table' => 'co_customers',
            ],
            $this->getDbStub()
        );
    }

    public function testAvailableUniquenessWithDefaultDI()
    {
        $di = Di::getDefault();

        $di->set(
            'db',
            $this->getDbStub()
        );

        $uniquenessOptions = [
            'table'  => 'co_customers',
            'column' => 'cst_login',
        ];

        $uniqueness = new Uniqueness($uniquenessOptions);

        $this->validation->add('login', $uniqueness);

        $messages = $this->validation->validate(
            [
                'cst_login' => 'login_free',
            ]
        );

        $this->assertCount(0, $messages);
    }

    public function testShouldValidateAvailableUniqueness()
    {
        $uniquenessOptions = [
            'table'  => 'co_customers',
            'column' => 'cst_login',
        ];

        $uniqueness = new Uniqueness(
            $uniquenessOptions,
            $this->getDbStub()
        );

        $this->validation->add('cst_login', $uniqueness);

        $messages = $this->validation->validate(
            [
                'cst_login' => 'login_free',
            ]
        );

        $this->assertCount(0, $messages);
    }

    public function testAlreadyTakenUniquenessWithDefaultMessage()
    {
        $uniquenessOptions = [
            'table'  => 'co_customers',
            'column' => 'cst_login',
        ];

        $uniqueness = new Uniqueness(
            $uniquenessOptions,
            $this->getDbStub()
        );

        $this->validation->add('cst_login', $uniqueness);

        $migration = new CustomersMigration($this->getDbStub()->getInternalHandler());
        $migration->insert(1, 1, 'jeremy', 'past', 'login_taken');

        $messages = $this->validation->validate(
            [
                'cst_login' => 'login_taken',
            ]
        );

        $this->assertCount(1, $messages);

        $this->assertEquals(
            'Already taken. Choose another!',
            $messages[0]
        );
    }

    public function testAlreadyTakenUniquenessWithCustomMessage()
    {
        $uniquenessOptions = [
            'table'   => 'co_customers',
            'column'  => 'cst_login',
            'message' => 'Login already taken.'
        ];

        $uniqueness = new Uniqueness(
            $uniquenessOptions,
            $this->getDbStub()
        );

        $this->validation->add('cst_login', $uniqueness);

        $messages = $this->validation->validate(
            [
                'cst_login' => 'login_taken',
            ]
        );

        $this->assertCount(1, $messages);

        $this->assertEquals(
            'Login already taken.',
            $messages[0]
        );
    }
}
