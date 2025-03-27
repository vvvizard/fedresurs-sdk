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

    public function getCourtDecision()
    {
        return $this->getMessageInfo()->CourtDecision;
    }

    public function getCourtDecisionText()
    {
        return (string) $this->getCourtDecision()->Text;
    }

    public function getJudicalActType(){
        return $this->getCourtDecision()->DecisionType['Name'];
    }
    public function getCourtName()
    {
        return (string) $this->getCourtDecision()->CourtDecree->CourtName;
    }

    public function getCourtId()
    {
        return (string) $this->getCourtDecision()->CourtDecree->CourtId;
    }

    public function getCourtFileNumber()
    {
        return (string) $this->getCourtDecision()->CourtDecree->FileNumber;
    }
    public function getCourtDecisionDate()
    {
        return (string) $this->getCourtDecision()->CourtDecree->DecisionDate;
    }

    public function parse()
    {
        return [
            'type' => self::TYPE,
            'fio' => $this->getBankruptFio(),
            'category' => $this->getCategory(),
            'birthDate' => $this->getBirthDate(),
            'birthPlace' => $this->getBirthPlace(),
            'address' => $this->getBankruptAddress(),
            'bankruptInn' => $this->getBankruptInn(),
            'bankruptSnils' => $this->getBankruptSnils(),
            'fioHistory' => $this->getBankruptFioHistory(),
            'courtFileNumber' => $this->getCourtFileNumber(),
            'courtDecisionDate' => $this->getCourtDecisionDate(),
            'courtName' => $this->getCourtName(),
            'courtId' => $this->getCourtId(),
            'courtDecisionText' => $this->getCourtDecisionText(),
            'messageType' => $this->getMessageTypeString(),
            'publisher' => $this->getPublisher(),
            'files' => $this->getFiles() ? 1 : 0
        ];
    }
}
