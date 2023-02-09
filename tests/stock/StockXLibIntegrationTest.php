<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvcTests\err\stock;

use PHPUnit\Framework\TestCase;
use pvc\err\XFactory;
use pvc\err\XCodePrefixes;
use pvc\err\stock\BadFunctionCallException;
use pvc\err\stock\BadMethodCallException;
use pvc\err\stock\ClosedGeneratorException;
use pvc\err\stock\DomainException;
use pvc\err\stock\DOMArgumentException;
use pvc\err\stock\DOMException;
use pvc\err\stock\DOMFunctionException;
use pvc\err\stock\ErrorException;
use pvc\err\stock\Exception;
use pvc\err\stock\IntlException;
use pvc\err\stock\InvalidArgumentException;
use pvc\err\stock\InvalidDataTypeException;
use pvc\err\stock\LengthException;
use pvc\err\stock\LogicException;
use pvc\err\stock\OutOfBoundsException;
use pvc\err\stock\OutOfRangeException;
use pvc\err\stock\OverflowException;
use pvc\err\stock\PDOException;
use pvc\err\stock\PharException;
use pvc\err\stock\RangeException;
use pvc\err\stock\ReflectionException;
use pvc\err\stock\RuntimeException;
use pvc\err\stock\SodiumException;
use pvc\err\stock\UnderflowException;
use pvc\err\stock\UnexpectedValueException;

use function PHPUnit\Framework\assertNotEmpty;

/**
 * Class ExceptionLibraryTest
 */
class StockXLibIntegrationTest extends TestCase
{
    /**
     * @var array<class-string, array<mixed>> $params
     */
    protected array $params = [
            Exception::class => [],
            BadFunctionCallException::class => [],
            BadMethodCallException::class => [],
            ClosedGeneratorException::class => [],
            DomainException::class => [],
            DOMArgumentException::class => [],
            DOMException::class => [],
            DOMFunctionException::class => [],
            ErrorException::class => [],
            IntlException::class => [],
            InvalidArgumentException::class => [],
            InvalidDataTypeException::class => [],
            LengthException::class => [],
            LogicException::class => [],
            OutOfBoundsException::class => [],
            OutOfRangeException::class => [],
            OverflowException::class => [],
            RangeException::class => [],
            ReflectionException::class => [],
            PharException::class => [],
            RuntimeException::class => [],
            PDOException::class => [],
            UnderflowException::class => [],
            UnexpectedValueException::class => [],
            SodiumException::class => [],
        ];

    /**
     * testExceptions
     * @covers \pvc\err\stock\_StockXLibData::getLocalXMessages
     * @covers \pvc\err\stock\_StockXLibData::getLocalXCodes
     */
    public function testExceptions(): void
    {
        $libraryCodes = new XCodePrefixes();
        $factory = new XFactory($libraryCodes);

        foreach ($this->params as $classString => $paramArray) {
            $exception = $factory->createException($classString, $paramArray);
            self::assertTrue(0 < $exception->getCode());
            self:assertNotEmpty($exception->getMessage());
        }
    }
}
