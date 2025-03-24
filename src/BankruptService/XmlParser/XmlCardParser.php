<?php

namespace FedResSdk\BankruptService\XmlParser;

use SimpleXMLElement;
use FedResSdk\BankruptService\Dictionary;
/**
 * Base class for all XML parsers
 */
abstract class  XmlCardParser 
{

  protected $xml;
  protected $dictionary;

     
    public function __construct($xml)
    {
      $this->xml = simplexml_load_string($xml);
      $this->dictionary = new Dictionary();
    }

    abstract public function parse();

    public function getPublisher()
    {
        return $this->xml->Publisher;
    }
    
    public function getMessageInfo()
    {
        return $this->xml->MessageInfo;
    }

    public function getBankrupt()
    {
        return $this->xml->Bankrupt;  
    }
    
    public function getCategory()
    {
         return (string) $this->getBankrupt()->Desctipition;
    }

    public function getBirthDate(){
        return (string) $this->getBankrupt()->BirthDate;
    }

    public function getBirthPlace(){
        return (string) $this->getBankrupt()->BirthPlace;
    } 

    public function getBankruptFio(){
      return [
        'LastName' => (string)$this->getBankrupt()->FIO->LastName,
        'MiddleName' => (string)$this->getBankrupt()->FIO->MiddleName,
        'FirstName' => (string)$this->getBankrupt()->FIO->FirstName,
      ];
    }

    public function getBankruptFioHistory()
    {
      return (array) $this->getBankrupt()->FioHistory;
    }
    public function getBankruptInn(){
      return (string) $this->getBankrupt()->Inn;
    }

    public function getBankruptSnils(){
      return (string) $this->getBankrupt()->Snils;
    }

    public function getBankruptAddress(){
      return (string) $this->getBankrupt()->Address;
    }

    public function getBankruptFioString(){
      return $this->getBankruptFio()['LastName'] . ' ' . $this->getBankruptFio()['FirstName'] . ' ' . $this->getBankruptFio()['MiddleName'];
    }

    public function getPublisherInn(){
      return (string) $this->getPublisher()->Inn;
    }
    public function getPublisherName(){
      return (string) $this->getPublisher()->Name;
    }

    public function getMessageType(){
      return (string) $this->getMessageInfo()->MessageType;
    }
    public function getMessageTypeString(){
      $dictionary = new Dictionary();
      return $dictionary->getMessageTypeString($this->getMessageType());
    }

    public function getFiles(){
      if (property_exists($this, 'FileInfoList')){
        return true;
      }
      return false;
    }

}