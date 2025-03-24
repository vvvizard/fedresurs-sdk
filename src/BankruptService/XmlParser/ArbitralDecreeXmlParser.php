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
        return $this->getCourtDecision()->Text;
    }

    public function getCourtName()
    {
        return $this->getCourtDecision()->CourtDecree->CourtName;
    }

    public function getCourtId()
    {
        return $this->getCourtDecision()->CourtDecree->CourtId;
    }

    public function getCourtFileNumber()
    {
        return $this->getCourtDecision()->CourtDecree->FileNumber;
    }
    public function getCourtDecisionDate()
    {
        return $this->getCourtDecision()->CourtDecree->DecisionDate;
    }

    public function parse()
    {
        return [
            'type' => self::TYPE,
            'fio' => $this->getBankruptFio(),
            'birthDate' => $this->getBirthDate(),
            'birthPlace' => $this->getBirthPlace(),
            'bankruptInn' => $this->getBankruptInn(),
            'bankruptSnils' => $this->getBankruptSnils(),
            'courtFileNumber' => $this->getCourtFileNumber(),
            'courtDecisionDate' => $this->getCourtDecisionDate(),
            'courtName' => $this->getCourtName(),
            'courtId' => $this->getCourtId(),
            'courtDecisionText' => $this->getCourtDecisionText(),
            'messageType' => $this->getMessageTypeString(),
            'publisher' => $this->getPublisher(),
            'files' => $this->getFiles()
        ];
    }
}
