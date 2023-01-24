<?php

/**
 * @package pvcErr
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\err\stock;

/**
 *
 * Overflow exceptions should be thrown when you try "adding an element to a full container."
 *
 * The PHP library does not use this exception itself.  This exception can only be generated by throwing it
 * explicitly in your code.
 *
 * @package pvcErr
 *
 */
class OverflowException extends RuntimeException
{
}
