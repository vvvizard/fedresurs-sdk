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
    
    public function getName(){
      return (string) $this->getPublisher()->Name;
    }

    public function getMessageType(){
      return (string) $this->getMessageInfo()->MessageType;
    }
    public function getMessageTypeString(){
      $dictionary = new Dictionary();
      return $dictionary->getMessageTypeString($this->getMessageType());
    }

}