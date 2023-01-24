<?php

/**
 * @package pvcErr
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\err\stock;

/**
 *
 * Bad function call exceptions should be thrown when you try to invoke a callback where the callback
 * function is not defined or some arguments are bad / missing.
 *
 * The PHP library does not use this exception itself (it throws an error instead in the above circumstance),
 * so this exception can only be generated by throwing it explicitly in your code.
 *
 * Example:
 *
 * function my_array_walk($array, $callback) {
 *        if (!is_callable($callback)) {
 *            throw new pvc\BadFunctionCallException($callback);
 *        }
 *        return array_walk($array, $callback);
 *    }
 *
 * @package pvcErr
 */
class BadFunctionCallException extends Exception
{
}
