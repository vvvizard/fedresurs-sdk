<?php

namespace FedResSdk\BankruptService;

use FedResSdk\BankruptService\XmlCardParser;

class  ExtrajudicialBankruptcyXmlParser extends XmlCardParser
{

    public function __construct($xml)
    {
        parent::__construct($xml);
    }   
    
}