<?php

namespace FedResSdk\BankruptService;

use FedResSdk\Authorization\Authorization;
use FedResSdk\ClientFedRes;
use FedResSdk\Config;
use Request;

class  BankruptServiceClient extends ClientFedRes
{

  public const TYPE = 'BankruptService';
  protected const MAX_MESSAGES_LIMIT = 500;
  protected const MAX_QUERY_LIMIT = 8;

  protected const ROUTE_MESSAGES = 'v1/messages';
  public $sort = "DatePublish:asc";

  public function __construct(Authorization $auth)
  {

    parent::__construct($auth);

    $type = self::TYPE;
    if ($auth->getMode() == 'develop') {
      $type = self::TYPE . 'Test';
    }

    $this->mainUrl = Config::getMainUrl($type);
    $this->setYesterdayDate();
    $this->setMessagesType('ArbitralDecree');
  }

  public function auth()
  {
    if (!$this->isAuthorized()) {

      $this->body = $this->auth->getAuthDataJson();
      $response = $this->apiRequest("POST", 'v1/auth');
      if ($response) {
        $data = json_decode($response, true);
        $this->auth->storeToken($data['jwt']);
        $this->headers['Authorization'] = 'Bearer ' . $data['jwt'];
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

    ($this->offset !== null) ? $url .= '?offset=' . $this->offset : 0;

    ($this->limit !== null) ? $url .= '&limit=' . $this->limit : '';

    ($this->sort !== null) ? $url .= '&sort=' . $this->sort : '';

    ($this->datePublishBegin !== null) ? $url .= '&datePublishBegin=' . $this->datePublishBegin : '';

    ($this->datePublishEnd !== null) ? $url .= '&datePublishEnd=' . $this->datePublishEnd : '';

    ($this->messagesType !== null) ? $url .= '&type=' . $this->messagesType : '';


    return $url;
  }

  public function getMessages()
  {
    $this->route = self::ROUTE_MESSAGES;
    $url = $this->prepareUrl();
    $response = $this->apiRequest("GET", $url);
    $data = json_decode($response, true);
    return $data;
  }

  public function getArbitralDecree($getAll = false)
  {
    $this->type = "ArbitralDecree";
    return $getAll  ? $this->getAllMessages() : $this->getMessages();
  }

  public function getCompletionOfExtrajudicialBankruptcy($getAll = false)
  {
    $this->type = "CompletionOfExtrajudicialBankruptcy";
    return $getAll  ? $this->getAllMessages() : $this->getMessages();
  }

  public function getMessage($id)
  {
    $url = self::ROUTE_MESSAGES . $id;
    $response = $this->apiRequest("GET", $url);
    $data = json_decode($response, true);
    return $data;
  }


  public function getAllMessages()
  {
    $this->setLimit(self::MAX_MESSAGES_LIMIT);
    $this->setOffset(0);

    $data = $this->getMessages();
    $requests = [(new Request('GET', $this->prepareUrl()))];

    for ($offset = self::MAX_MESSAGES_LIMIT; $offset < $data['total']; $offset += self::MAX_MESSAGES_LIMIT) {
      $this->setOffset($offset);
      $url = $this->prepareUrl();
      $requests[] = new Request('GET', $url);
    }
    return $this->poolRequest($requests);
  }

  public function getMessagesWithCards(array $messagesIds, $params = [])
  {

    if (!empty($params)) {
      $this->setParams($params);
    }
    $requests = [];
    $responses = [];
    $countRequest = 0;
    foreach ($messagesIds as $key => $messageId) {
      $url = self::ROUTE_MESSAGES . "/" . $messageId;
      $requests[] = new Request('GET', $url);
      $countRequest++;
      if ($countRequest == self::MAX_QUERY_LIMIT) {
        $responses = array_merge($responses, $this->poolRequest($requests));
        $countRequest = 0;
        $requests = [];
      }
    }
    return $responses;
  }


  public function getFiles($messageId)
  {
    $url = self::ROUTE_MESSAGES . $messageId . '/files/archive';
    $response = $this->apiRequest("GET", $url);
    $data = json_decode($response, true);
    return $data;
  }

  public function setYesterdayDate()
  {
    $this->datePublishBegin = date('Y-m-d', strtotime('-1 day'));
    $this->datePublishEnd = $this->datePublishBegin;
  }
}
