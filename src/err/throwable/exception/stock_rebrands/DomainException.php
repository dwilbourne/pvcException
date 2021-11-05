<?php declare(strict_types = 1);

namespace pvc\err\throwable\exception\stock_rebrands;

use pvc\err\throwable\ErrorExceptionConstants as ec;
use pvc\msg\Msg;
use Throwable;

/**
 *
 * Domain exceptions should be thrown if a value does not adhere to a defined valid data domain.
 *
 * The PHP library does not use this exception itself, so this exception can only be generated by throwing it
 * explicitly in your code.
 *
 * Example:
 *
 *    function isPrimaryColor(string $color) {
 *        $primary_colors = array("red", "yellow", "blue");
 *        in_array($color, $primary_colors) ? true : false;
 *    }
 *
 *    class PrimaryColorException extends DomainException {
 *
 *        function __construct($color_arg, Throwable $previous) {
 *            $s = "%s is not a primary color.";
 *            $msg = sprintf($s, json_encode($color_arg));
 *            return new pvc\DomainException($msg, $previous);
 *        }
 *    }
 *
 *    $s = "turtle";
 *    if (!isPrimaryColor($s)) {
 *        throw new PrimaryColorException($s);
 *    }
 *
 */
class DomainException extends Exception
{
    public function __construct(Msg $msg, int $code, Throwable $previous = null)
    {
        if ($code == 0) {
            $code = ec::DOMAIN_EXCEPTION;
        }
        parent::__construct($msg, $code, $previous);
    }
}
