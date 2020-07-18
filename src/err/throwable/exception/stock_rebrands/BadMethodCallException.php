<?php declare(strict_types = 1);

namespace pvc\err\throwable\exception\stock_rebrands;

use pvc\err\throwable\ErrorExceptionConstants as ec;
use pvc\msg\ErrorExceptionMsg;
use Throwable;

/**
 *
 * Bad method call exceptions should be thrown when you try to invoke a non-existent method on an object.
 *
 * The PHP library does not use this exception itself (it throws an error instead in the above circumstance),
 * so this exception can only be generated by throwing it explicitly in your code.
 *
 * Example:
 *
 *    class Foo {
 *        function __call($method_name, $args) {
 *            throw new pvc\BadMethodCallException($method_name);
 *        }
 *    }
 *
 *    $f = new Foo();
 *    $f->doSomethingCrazy();
 *
 */
class BadMethodCallException extends Exception
{
    public function __construct(ErrorExceptionMsg $msg, int $code, Throwable $previous = null)
    {
        if ($code == 0) {
            $code = ec::BAD_METHOD_CALL_EXCEPTION;
        }
        parent::__construct($msg, $code, $previous);
    }
}
