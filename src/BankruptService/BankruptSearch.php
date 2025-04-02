<?php

namespace FedResSdk\BankruptService;

use FedResSdk\BankruptService\BankruptServiceClient;
use FedResSdk\Authorization\Authorization;

/**
 * Client for getting Bankrupt persons information
 */
class  BankruptSearch extends  BankruptServiceClient
{

    protected const ROUTE_BANKRUPTS = 'v1/bankrupts';

    protected $bankruptType; // Company|Person
    protected $bankruptGuid; // Bankrupt ID
    protected $bankruptName; // Bankrupt name

    protected $ogrnip;
    protected $ogrn;
    protected $snils;
    protected $inn;
    protected $birthdate;

    public function __construct(Authorization $auth)
    {
        parent::__construct($auth);
    }

    public function getBankrupt()
    {

        $this->route = self::ROUTE_BANKRUPTS;
        $url = $this->prepareUrl();
        $response = $this->apiRequest("GET", $url);
        $data = json_decode($response, true);
        return $data;
    }

    public function prepareUrl()
    {
        $url = $this->route;

        ($this->offset !== null) ? $url .= '?offset=' . $this->offset : 0;
        ($this->limit !== null) ? $url .= '&limit=' . $this->limit : '';
        ($this->sort !== null) ? $url .= '&sort=' . $this->sort : '';
        ($this->bankruptGuid !== null) ? $url .=  '&guid=' . $this->bankruptGuid : '';
        ($this->bankruptName !== null) ? $url .=  '&name=' . $this->bankruptName : '';
        ($this->bankruptType !== null) ? $url .=  '&type=' . $this->bankruptType : '';
        ($this->ogrnip !== null) ? $url .=  '&ogrnip=' . $this->ogrnip : '';
        ($this->ogrn !== null) ? $url .=  '&ogrn=' . $this->ogrn : '';
        ($this->snils !== null) ? $url .=  '&snils=' . $this->snils : '';
        ($this->inn !== null) ? $url .=  '&inn=' . $this->inn : '';
        ($this->birthdate !== null) ? $url .=  '&birthdate=' . $this->birthdate : '';

        return $url;
    }
}
