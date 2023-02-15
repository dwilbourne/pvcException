<?php

/**
 * @package pvcErr
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\err\stock;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use pvc\err\PhpParserNodeVisitorClassName;
use pvc\err\XCodePrefixes;
use pvc\interfaces\err\XDataInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Throwable;

/**
 * Class Exception
 */
class Exception extends \Exception
{
    protected ?Throwable $previous = null;

    /**
     * This method uses nikic's PhpParser to parse each file in the exception library (directory) and
     * extract the class string or, if the class is not namespaced, the class name.
     *
     * This is implemented with a "node visitor".  The PhpParserNodeVisitorClassName object gets the class name
     * and namespacing within the file.  The other significant feature of the PhpParserNodeVisitorClassName object
     * is that it stops traversal of the tree (AST) as soon as the class name is obtained.
     *
     * @function getClassStringFromFileContents
     * @param string $fileContents
     * @return string
     */
    protected function getClassStringFromFileContents(string $fileContents): string
    {
        /**
         * create the parser and parse the file.  Result is an array of nodes, which is the AST
         */
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        /** @var Node[] $nodes */
        $nodes = $parser->parse($fileContents);

        /**
         * PhpParser object which traverses the AST.  Add a visitor to the traverser.  This visitor
         * gets namespace and class name strings and stops traversal of the AST after it finds a class name.
         */
        $traverser = new NodeTraverser();
        $classVisitor = new PhpParserNodeVisitorClassName();
        $traverser->addVisitor($classVisitor);
        $traverser->traverse($nodes);

        /**
         * two parts to the class string: the namespace and the class name.  Concatenate the two properly depending
         * on whether there was a class declaration present.
         */
        if ($classString = $classVisitor->getClassname()) {
            if ($namespaceName = $classVisitor->getNamespaceName()) {
                $classString = $namespaceName . '\\' . $classString;
            }
        }
        return $classString;
    }

    /**
     * getXDataFromClassString is a clumsy but effective method that tries to find the
     * XData object for a given (exception) class string. It does this by searching for a php file that, when
     * reflected, implements XDataInterface.  When it finds it, the class is instantiated and returned.  Returns null
     * if it does not find it, meaning that there is no XData object in the library directory that contains
     * the object referred to by the classString argument.  If there is more than one XData file in the directory,
     * this returns the first one it comes across.
     *
     * @param class-string $classString
     * @return XDataInterface|null
     * @throws ReflectionException
     */
    protected function getXDataFromClassString(string $classString): ?XDataInterface
    {
        /**
         * reflect the classString
         */
        $reflected = new ReflectionClass($classString);

        /**
         * get the filename (including path) from the reflection.  false is only returned from getFileName if the
         * class is part of php core or defined in an extension, so it should be ok to typehint $fileName.
         * @var string $fileName
         */
        $fileName = $reflected->getFileName();

        /**
         * get the directory portion of the filename and scan it for files.
         * @var string $dir
         */
        $dir = pathinfo($fileName, PATHINFO_DIRNAME);

        /**
         * phpstan does know that scandir cannot return false in this case, so typehint the $files variable
         * @var array<string> $files
         */
        $files = scandir($dir);
        $files = array_diff($files, array('.', '..'));

        /**
         * iterate through the list of files, trying to reflect each one and test it for XDataInterface
         */
        foreach ($files as $file) {
            $filePath = $dir . DIRECTORY_SEPARATOR . $file;

            if (is_readable($filePath)) {
                $fileContents = file_get_contents($filePath) ?: '';

                /** @var class-string $className */
                $className = self::getClassStringFromFileContents($fileContents);

                if ($className) {
                    $reflected = new ReflectionClass($className);
                    /**
                     * if it implements the right interface, return a new instance.
                     */
                    if ($reflected->implementsInterface(XDataInterface::class)) {
                        /** @var XDataInterface $xData */
                        $xData = new $className();
                        return $xData;
                    }
                }
            }
        }
        /**
         * if we got to here, we've iterated through the directory without finding a file that has the right interface.
         */
        return null;
    }

    /**
     * @function parseParams
     * @param array<mixed> $paramValues
     * @param int $countOfMessageVariables
     * @return array<mixed>
     */
    protected function parseParams(array $paramValues, int $countOfMessageVariables): array
    {
        $reflected = new ReflectionClass($this);
        /** @var ReflectionMethod $constructor */
        $constructor = $reflected->getConstructor();
        $paramNames = $constructor->getParameters();

        /**
         * put the parameters into an associative array using the arguments' variable names (which should be the same
         * as the template names in the message template) as the indices.  For example, if the raw message looks
         * like 'Index ${index} is greater than ${limit}', then the argument list in this exception's signature
         * should be $index and $limit.  The array produced by this method would be ['index' => value1, 'limit' =>
         * value2].
         */
        for ($i = 0, $messageParams = []; $i < $countOfMessageVariables; $i++) {
            $templateVariable = '${' . $paramNames[$i]->name . '}';
            $messageParams[$templateVariable] = $this->sanitizeParameterValue($paramValues[$i]);
        }

        /**
         * There should be one more argument to process (the previous exception, which could be null).  BUT, php
         * allows you to call a function with extra arguments and will not really complain. So in order to be
         * defensive about this, we take the next argument, if any, and test it for null or Throwable and if it fits
         * the criterion, then we set it up as $previous.  Remaining arguments, if any, are discarded.
         */
        $paramValue = $paramValues[$i] ?? null;
        if (($paramValue instanceof Throwable) || (is_null($paramValue))) {
            $this->previous = $paramValue;
        }

        return $messageParams;
    }

    /**
     * @function sanitizeParameterValue
     * @param $value
     * @return string
     */
    protected function sanitizeParameterValue($value): string
    {
        switch ($type = gettype($value)) {
            case 'string':
                $result = $value;
                break;
            case 'integer':
                $result = (string)$value;
                break;
            case 'boolean':
                $result = ($value ? 'true' : 'false');
                break;
            default:
                $result = $type;
                break;
        }
        return $result;
    }

    /**
     * @param mixed ...$allParams
     * @throws ReflectionException
     */
    public function __construct(...$allParams)
    {
        /**
         * initialize some things that we need to create the message and the code
         */
        $myClassString = get_class($this);
        $reflected = new ReflectionClass($myClassString);
        $xData = $this->getXDataFromClassString($myClassString);

        /**
         * of course this should never happen if the library has been tested.......
         */
        if (is_null($xData)) {
            $msg = 'No exception data file found for exception $myClassString!';
            $code = 0;
            throw new \Exception($msg, $code);
        }

        /**
         * exception code is code prefix concatenated to local code.
         */
        $localCode = $xData->getLocalXCode($myClassString);
        $globalPrefix = XCodePrefixes::getXCodePrefix($reflected->getNamespaceName());
        $code = (int)($globalPrefix . $localCode);

        /**
         * get the message template and variables and do the string substitution
         */
        $messageTemplate = $xData->getXMessageTemplate($myClassString);
        /** parsing the parameters sets $this->previous as a by-product */
        $messageParams = $this->parseParams($allParams, $xData->countXMessageVariables($messageTemplate));
        $message = strtr($messageTemplate, $messageParams);

        parent::__construct($message, $code, $this->previous);
    }
}