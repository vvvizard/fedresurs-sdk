<?php

namespace FedResSdk\BankruptService;

use FedResSdk\BankruptService\XmlCardParser;

class  ExtrajudicialBankruptcyXmlParser extends XmlCardParser
{
    public const TYPE = 'CompletionOfExtrajudicialBankruptcy';
   
    public function __construct($xml)
    {
        parent::__construct($xml);
    }  
    
    public function getNumber(){
        return (string) $this->getMessageInfo()->CompletionOfExtrajudicialBankruptcy->StartOfExtrajudicialBankruptcyMessageNumber;
    }

    public function getText(){
        return (string) $this->getMessageInfo()->CompletionOfExtrajudicialBankruptcy->Text;
    }

    public function getBankrupt(){
        return  $this->xml->Bankrupt;
    }

    public function getBirthDate(){
        return (string) $this->getMessageInfo()->BirthDate;
    }
}