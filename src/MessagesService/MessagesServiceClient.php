<?php

namespace FedResSdk\MessagesService;

use FedResSdk\Authorization\Authorization;
use FedResSdk\ClientFedRes;
use FedResSdk\Config;

class  MessagesServiceClient extends ClientFedRes
{

  public const TYPE = 'MessagesService';


  public function __construct(Authorization $auth)
  {

    parent::__construct($auth);

    $this->mainUrl = Config::getMainUrl(self::TYPE);
  }

  public function auth()
  {
    if (!$this->isAuthorized()) {
      $this->body = $this->auth->getAuthDataWithHashJson();
      $this->auth->attempPlus();
      $response = $this->apiRequest("POST", 'v1/auth');

      if ($response) {
        $data = json_decode($response, true);
        $this->auth->storeToken($data['jwt']);
        $this->auth->clearAttemps();
        $this->setAuthHeaders($data['jwt']);
      }
    }
  }

  public function setAuthHeaders($token)
  {
    $this->headers['Authorization'] = 'Bearer ' . $token;
  }

  public function getMessages()
  {
    $url = 'v1/messages?offset='.$this->offset.'&limit='. $this->limit.'&sort='. $this->sort
    .'&dateBegin='.$this->dateBegin.'&dateEnd='.$this->dateEnd;
    
    $response = $this->apiRequest("GET", $url);
    $data = json_decode($response, true);
    var_dump($data);
  }
}
