<?php

namespace FedResSdk\BankruptService\XmlParser;

use FedResSdk\BankruptService\XmlParser\XmlCardParser;

class  CompletionOfExtrajudicialBankruptcyXmlParser extends XmlCardParser
{
    public const TYPE = 'CompletionOfExtrajudicialBankruptcy';
   
    public function __construct($xml)
    {
        parent::__construct($xml);
    }  
    public function parse(){
        return [
            'number' => $this->getNumber(),
            'text' => $this->getText(),
            'bankrupt' => $this->getBankrupt(),
            'birthDate' => $this->getBirthDate(),
            'type' => 'CompletionOfExtrajudicialBankruptcy'
        ];
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