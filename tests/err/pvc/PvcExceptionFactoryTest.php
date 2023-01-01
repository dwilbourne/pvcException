<?php

declare(strict_types=1);

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

namespace tests\err\pvc;

use PHPUnit\Framework\TestCase;
use pvc\err\pvc\_ExceptionFactory;
use pvc\err\pvc\InvalidArrayIndexException;
use pvc\err\pvc\InvalidArrayValueException;
use pvc\err\pvc\InvalidAttributeNameException;
use pvc\err\pvc\InvalidFilenameException;
use pvc\err\pvc\InvalidPHPVersionException;
use pvc\err\pvc\PregMatchFailureException;
use pvc\err\pvc\PregReplaceFailureException;

class PvcExceptionFactoryTest extends TestCase
{
    protected array $params = [
        InvalidArrayIndexException::class => ['(IndexName)'],
        InvalidArrayValueException::class => ['(Value)'],
        InvalidAttributeNameException::class => ['(attribute name)'],
        InvalidFilenameException::class => ['(filename)'],
        InvalidPHPVersionException::class => ['(min php version)'],
        PregMatchFailureException::class => ['some bad regex', 'some subject'],
        PregReplaceFailureException::class => ['some bad regex', 'some subject', 'some replacement'],
    ];

    /**
     * testExceptions
     * @covers _ExceptionFactory::createException
     */
    public function testExceptions(): void
    {
        foreach ($this->params as $classString => $paramArray) {
            $exception = _ExceptionFactory::createException($classString, $paramArray);
            self::assertTrue(0 < $exception->getCode());
            self::assertNotEmpty($exception->getMessage());
        }
    }

	/**
	 * testAwkwardParam
	 * @covers _ExceptionFactory::createException
	 */
    public function testAwkwardParam(): void
    {
        $this->params[InvalidArrayIndexException::class] = new \stdClass();
        $exception = _ExceptionFactory::createException(
            InvalidArrayIndexException::class,
            [$this->params[InvalidArrayIndexException::class]]
        );
        self::assertTrue(0 < $exception->getCode());
        self::assertNotEmpty($exception->getMessage());
    }
}
