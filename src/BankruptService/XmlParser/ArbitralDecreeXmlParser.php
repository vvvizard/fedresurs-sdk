<?php

namespace FedResSdk\BankruptService\XmlParser;

use FedResSdk\BankruptService\XmlParser\XmlCardParser;

class  ArbitralDecreeXmlParser extends XmlCardParser
{
    public const TYPE = 'ArbitralDecree';

    public function __construct($xml)
    {
        parent::__construct($xml);
    }

    public function parse()
    {
        return [
            'type' => self::TYPE,
            'messageInfo' => $this->getMessageInfo(),
            'publisher' => $this->getPublisher(),
            'files' => $this->getFiles()
        ];
    }
}