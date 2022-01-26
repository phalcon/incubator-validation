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
  | Authors: Tomasz Ślązok <tomek@sabaki.pl>                               |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Incubator\Validation\Db;

use Phalcon\Di\DiInterface;
use Phalcon\Messages\Message;
use Phalcon\Filter\Validation\AbstractValidator;
use Phalcon\Db\Adapter\Pdo\AbstractPdo as DbConnection;
use Phalcon\Filter\Validation\Exception as ValidationException;
use Phalcon\Db\Enum as Db;
use Phalcon\Di\Di;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\ValidatorInterface;

/**
 * Phalcon\Validation\Validator\Db\Uniqueness
 *
 * Validator for checking uniqueness of field in database
 *
 * <code>
 * $uniqueness = new Uniqueness(
 *     [
 *         'table'   => 'users',
 *         'column'  => 'login',
 *         'message' => 'already taken',
 *         'exclude' => [
 *             'column' => 'id',
 *             'value' => 1 // Some ID to exclude
 *         ],
 *     ],
 *     $di->get('db');
 * );
 * </code>
 *
 * Exclude option is optional.
 *
 * If second parameter will be null (omitted) than validator will try to get
 * database connection from default DI instance with
 * \Phalcon\Di::getDefault()->get('db');
 */

class Uniqueness extends AbstractValidator implements ValidatorInterface
{
    /**
     * Database connection
     * @var \Phalcon\Db\Adapter\Pdo\AbstractPdo
     */
    private $db;

    /**
     * Class constructor.
     *
     * @param array $options
     * @param DbConnection|null $db
     * @throws ValidationException
     */
    public function __construct(array $options = [], DbConnection $db = null)
    {
        parent::__construct($options);

        if (!empty($options['message'])) {
            $this->setOption('message', $options['message']);
        }

        if (!$db) {
            // try to get db instance from default Dependency Injection
            $di = Di::getDefault();

            if ($di instanceof DiInterface && $di->has('db')) {
                $db = $di->get('db');
            }
        }
        if (!$db instanceof DbConnection) {
            throw new ValidationException(
                'Validator Uniqueness require connection to database'
            );
        }

        if (!$this->hasOption('table')) {
            throw new ValidationException(
                'Validator require table option to be set'
            );
        }

        if (!$this->hasOption('column')) {
            throw new ValidationException(
                'Validator require column option to be set'
            );
        }

        if ($this->hasOption('exclude')) {
            $exclude = $this->getOption('exclude');

            if (!isset($exclude['column']) || empty($exclude['column'])) {
                throw new ValidationException(
                    'Validator with "exclude" option require column option to be set'
                );
            }

            if (!isset($exclude['value']) || empty($exclude['value'])) {
                throw new ValidationException(
                    'Validator with "exclude" option require value option to be set'
                );
            }
        }

        $this->db = $db;
    }

    /**
     * Executes the uniqueness validation
     *
     * @param  Validation $validator
     * @param  string $attribute
     * @return boolean
     */
    public function validate(Validation $validator, $attribute): bool
    {
        $table = $this->db->escapeIdentifier(
            $this->getOption('table')
        );

        $column = $this->db->escapeIdentifier(
            $this->getOption('column')
        );

        if ($this->hasOption('exclude')) {
            $exclude = $this->getOption('exclude');

            $result = $this->db->fetchOne(
                sprintf(
                    'SELECT COUNT(*) AS count FROM %s WHERE %s = ? AND %s != ?',
                    $table,
                    $column,
                    $this->db->escapeIdentifier($exclude['column'])
                ),
                Db::FETCH_ASSOC,
                [
                    $validator->getValue($attribute),
                    $exclude['value'],
                ]
            );
        } else {
            $result = $this->db->fetchOne(
                sprintf(
                    'SELECT COUNT(*) AS count FROM %s WHERE %s = ?',
                    $table,
                    $column
                ),
                Db::FETCH_ASSOC,
                [
                    $validator->getValue($attribute),
                ]
            );
        }

        if ($result['count']) {
            $message = $this->getOption(
                'message',
                'Already taken. Choose another!'
            );

            $validator->appendMessage(
                new Message(
                    $message,
                    $attribute,
                    'Uniqueness'
                )
            );

            return false;
        }

        return true;
    }
}
