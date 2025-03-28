<?php

namespace FedResSdk\MessagesService;

use FedResSdk\Authorization\Authorization;
use FedResSdk\ClientFedRes;
use FedResSdk\Config;

/**
 * Client for MessagesService 
 * Message-Stats
 */
class  MessagesServiceClient extends ClientFedRes
{

  public const TYPE = 'MessagesService';

  protected const ROUTE_MESSAGES = 'v1/messages';
  protected const ROUTE_AUTH = 'v1/auth';

  /**
   * @param \FedResSdk\Authorization\Authorization $auth
   */
  public function __construct(Authorization $auth)
  {
    parent::__construct($auth);

    $type = self::TYPE;
    if ($auth->getMode() == 'develop') {
      $type = self::TYPE . 'Test';
    }

    $this->mainUrl = Config::getMainUrl($type);
  }

  public function auth()
  {
    $this->checkToken();
    if (!$this->isAuthorized()) {
      $this->body = $this->auth->getAuthDataWithHashJson();
      $this->auth->attempPlus();
      $response = $this->apiRequest("POST", self::ROUTE_AUTH);

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

  /**
   * preparing url for request
   */
  public function prepareUrl()
  {
    $url = $this->route;

    ($this->offset !== null) ? $url .= '?offset=' . $this->offset : 0;

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

  public function getAllMessages(bool $getAll = false)
  {
    $this->setLimit(100);
    return $this->getMessages();
  }

  public function checkToken()
  {

    $url = self::ROUTE_MESSAGES . '?offset=0&limit=1';

    ($this->sort !== null) ? $url .= '&sort=' . $this->sort : '';
    ($this->dateBegin !== null) ? $url .= '&dateBegin=' . $this->dateBegin : '';
    ($this->dateEnd !== null) ? $url .= '&dateEnd=' . $this->dateEnd : '';

    $response = $this->apiRequest("GET", $url);
    $data = json_decode($response, true);
    if (!empty($data)) {
      return true;
    }

    return false;
  }
}
