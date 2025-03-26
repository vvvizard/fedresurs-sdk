<?php

namespace FedResSdk\BankruptService\XmlParser;

/**
 * Fabric for XmlParsers, 
 * type of xml parser depends from message type
 */
class XmlParserFabric
{
    public static function create($type, $xml)
    {
        $xmlParser = __NAMESPACE__ . $type;

        return class_exists($xmlParser) ? new $xmlParser($xml) : null;
    }
}
