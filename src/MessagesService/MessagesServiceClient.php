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
       
    $type = self::TYPE;
    if($auth->getMode() == 'develop'){
     $type = self::TYPE . 'Test';
    }

    $this->mainUrl = Config::getMainUrl($type); 
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

  
  public function prepareUrl()
  {
      $url = $this->route;

      ($this->offset !== null) ? $url .= '?offset=' . $this->offset : 0 ;

      ($this->limit !== null) ? $url .= '&limit=' . $this->limit : '';

      ($this->sort !== null) ? $url .= '&sort=' . $this->sort : '';

      ($this->messagesType !== null) ? $url .= '&type=' . $this->messagesType : '';
      
      ($this->dateBegin !== null) ? $url .= '&dateBegin=' . $this->dateBegin : '';
      
      ($this->dateEnd !== null) ? $url .= '&dateEnd=' . $this->dateEnd : '';


      return $url;
  }


  public function getMessages()
  {
    $url = $this->prepareUrl();
    
    $response = $this->apiRequest("GET", $url);
    $data = json_decode($response, true);
    return $data;
  }

  public function getAllMessages(bool $getAll = false){
    $this->setLimit(100); 
    return $this->getMessages();
  }
}
