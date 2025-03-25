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
            'fio' => $this->getBankruptFio(),
            'birthDate' => $this->getBirthDate(),
            'birthPlace' => $this->getBirthPlace(),
            'bankruptInn' => $this->getBankruptInn(),
            'bankruptSnils' => $this->getBankruptSnils(),
            'messageType' => $this->getMessageTypeString(),
            'FioHistory' => $this->getBankruptFioHistory(),
            'publisher' => $this->getPublisher(),
            'files' => $this->getFiles() ? 1:0
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