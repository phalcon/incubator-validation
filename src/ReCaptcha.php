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
  | Authors: Patrick Florek <patrick.florek@gmail.com>                     |
  +------------------------------------------------------------------------+
*/

/**
 * The reCAPTCHA Validator
 *
 * @link https://www.google.com/recaptcha/intro/index.html
 * @link https://developers.google.com/recaptcha/
 *
 * @package Phalcon\Validation\Validator
 */

namespace Phalcon\Incubator\Validation;

use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\ValidatorInterface;
use Phalcon\Messages\Message;
use Phalcon\Filter\Validation\AbstractValidator;

/**
 * Phalcon\Validation\Validator\ReCaptcha
 *
 * Verifies a value to a reCAPTCHA challenge
 *
 * <code>
 * use Phalcon\Validation\Validator;
 *
 * $validator->add(
 *     'g-recaptcha-response',
 *     new Validator(
 *         [
 *             'message' => 'The captcha is not valid',
 *             'secret'  => 'your_site_key',
 *             'score'   => 0.5, //optional score check for ReCaptcha v3
 *             'ip'      => 'optional client ip address override',
 *             'action'  => 'optional action name to verify for ReCaptcha v3',
 *         ],
 *     )
 * );
 * </code>
 *
 * @link https://developers.google.com/recaptcha/intro
 * @package Phalcon\Validation\Validator
 */
class ReCaptcha extends AbstractValidator implements ValidatorInterface
{
    /**
     * API request URL
     */
    public const RECAPTCHA_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * Response error code reference
     *
     * @var array $messages
     */
    protected $messages = [
        'missing-input-secret'   => 'The secret parameter is missing.',
        'invalid-input-secret'   => 'The secret parameter is invalid or malformed.',
        'missing-input-response' => 'The response parameter is missing.',
        'invalid-input-response' => 'The response parameter is invalid or malformed.',
    ];

    /**
     * {@inheritdoc}
     *
     * @param Validation $validator
     * @param string     $attribute
     *
     * @return bool
     */
    public function validate(Validation $validator, $attribute): bool
    {
        $secret   = $this->getOption('secret');
        $value    = $validator->getValue($attribute);
        $request  = $validator->getDI()->get('request');

        if ($this->hasOption('ip')) {
            $remoteIp = $this->getOption('ip');
        } else {
            $remoteIp = $request->getClientAddress(false);
        }

        if (!empty($value)) {
            $curl = curl_init(self::RECAPTCHA_URL);

            curl_setopt_array(
                $curl,
                [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POSTFIELDS     => [
                        'secret'   => $secret,
                        'response' => $value,
                        'remoteip' => $remoteIp,
                    ],
                ]
            );

            $response = curl_exec($curl);
            if (true === is_string($response)) {
                $response = json_decode(
                    $response,
                    true
                );
            }

            curl_close($curl);
        }

        if (
            empty($response['success'])
            || ($this->hasOption('score')
                && $this->getOption('score') > $response['score'])
            || ($this->hasOption('action')
                && $this->getOption('action') !== $response['action'])
        ) {
            $label = $this->getOption('label');
            if (empty($label)) {
                $label = $validator->getLabel($attribute);
            }

            $message      = $this->getOption('message');
            $replacePairs = [':field', $label];

            if (empty($message) && !empty($response['error-codes'])) {
                $message = $this->messages[$response['error-codes']];
            }

            if (empty($message)) {
                $message = $this->getOption('message');
            }

            $validator->appendMessage(
                new Message(
                    strtr($message, $replacePairs),
                    $attribute,
                    'ReCaptcha'
                )
            );

            return false;
        }

        return true;
    }
}
