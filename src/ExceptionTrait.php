<?php

/**
 * @package pvcErr
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\err;

use pvc\interfaces\err\XDataInterface;
use pvcExamples\err\src\err\ExampleXData;

trait ExceptionTrait
{
    protected XDataInterface $xData;

    protected function getXData() : XDataInterface
    {
        return ($this->xData ?: XLibUtils::discoverXDataFromClassString(get_class));
    }

    /**
     * @function getMessage
     * @param array $params
     * @return string
     */
    public function getMessage(array $params): string
    {
        /**
         * get the message string from the constants file
         */
        $messageRaw = ExampleXData::MESSAGES[get_class()];

        /**
         * convert parameter names into template variables
         */
        foreach ($params as $paramName => $paramValue) {
            $newKey = '${' . $paramName . '}';
            $params[$newKey] = $paramValue;
            unset($params[$paramName]);
        }
        /**
         * replace the template variables with the parameter values and return
         */
        return strtr($messageRaw, $params);
    }

    /**
     * @function getCode
     * @return int
     */
    public function getCode(): int
    {
        $localCode = ExampleXData::LOCALCODES[get_class()];
        $globalPrefix = XCodePrefixes::PREFIXES[__NAMESPACE__];
        return (int) ($globalPrefix . $localCode);
    }
}
