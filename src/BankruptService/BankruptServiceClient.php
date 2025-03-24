<?php

namespace FedResSdk\BankruptService;

use FedResSdk\Authorization\Authorization;
use FedResSdk\ClientFedRes;
use FedResSdk\Config;
use GuzzleHttp\Psr7\Request;
use FedResSdk\BankruptService\XmlParser\XmlParserFabric;

/**
 * Client for BankruptService
 */
class  BankruptServiceClient extends ClientFedRes
{
  public const TYPE = 'BankruptService';
  protected const MAX_MESSAGES_LIMIT = 500;
  protected const MAX_QUERY_LIMIT = 8;

  protected const ROUTE_MESSAGES = 'v1/messages';
  protected const ROUTE_AUTH = 'v1/auth';
  public $sort = "DatePublish:asc";

  protected $downloadDir = "";

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
    $this->setYesterdayDate();
    $this->setMessagesType('ArbitralDecree');
  }

  public function auth()
  {
    if (!$this->isAuthorized()) {

      $this->body = $this->auth->getAuthDataJson();
      $response = $this->apiRequest("POST", self::ROUTE_AUTH);
      if ($response) {
        $data = json_decode($response, true);
        $this->auth->storeToken($data['jwt']);
        $this->headers['Authorization'] = 'Bearer ' . $data['jwt'];
      }
    }
  }
  
  /**
   * setting headers for auth
   * @param mixed $token
   * @return void
   */
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

  public function getMessagesIds()
  {
    $data = $this->getMessages();
    return array_column($data['pageData'], 'guid');
  }

  /**
   * getting messages with ArbitralDecree type
   * @param mixed $getAll 
   */
  public function getArbitralDecree($getAll = false)
  {
    $this->messagesType= "ArbitralDecree";
    return $getAll  ? $this->getAllMessages() : $this->getMessages();
  }

  /**
   * getting messages with completionOfExtrajudicialBankruptcy type
   * @param mixed $getAll
   */
  public function getCompletionOfExtrajudicialBankruptcy($getAll = false)
  {
    $this->messagesType = "CompletionOfExtrajudicialBankruptcy";
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

  /**
   * Gets list of messages with all data from inner cards 
   * @param array $messagesIds
   * @param mixed $params
   * @return array
   */
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
      $requests[] = new Request('GET', $this->mainUrl . $url,  $this->headers, $this->body);
      $countRequest++;
      if ($countRequest == self::MAX_QUERY_LIMIT || $countRequest >= count($messagesIds)) {
        $response = $this->poolRequest($requests);
        $response = $this->createMessagesWithCards($response);
        $responses = array_merge($responses, $response);
        $countRequest = 0;
        $requests = [];
      }
    }
    return $responses;
  }

  /**
   * creates message card 
   * @param array $messages
   * @return array
   */
  protected function createMessagesWithCards(array $messages)
  {
    $resultMessages = [];
    foreach ($messages as $message) {
      $cardData = $this->parseXml($message['content']);
      if($cardData['files']){
        $fileName  = $this->downloadDir . "/" . $this->messagesType ."/". $message['guid'] . '.zip';
        $message['files'] = $this->getFiles($message['guid'], $fileName);
      }
      $message['content'] = $cardData;
      $message['linked'] = $this->getLinkedMessages($message['guid']);
      $resultMessages[] = $message;
    }
    return $resultMessages;
  }

  /**
   * Parsing xml and converting it to array
   * with formatted data
   * @param mixed $xml
   */
  protected function parseXml($xml){
    $xmlParser = new XmlParserFabric($this->messagesType, $xml);
    return $xmlParser ? $xmlParser->parse() : $xml;
  }
 
  /**
   * getting files from message card
   * @param mixed $messageId
   */
  public function getFiles($messageId, $saveTo)
  {
    $url = self::ROUTE_MESSAGES . "/" . $messageId . '/files/archive';
    $this->getZip($url, $saveTo);
    return $saveTo;
  }

  /**
   * returning linked messages noted in message card
   * @param mixed $messageId
   */
  public function getLinkedMessages($messageId)
  {
    $url = self::ROUTE_MESSAGES . "/" . $messageId . '/linked';
    $response = $this->apiRequest("GET", $url);
    $data = json_decode($response, true);
    return $data;
  }

  public function setDownloadDir($dir)
  {
    $this->downloadDir = $dir;
  }
  public function setYesterdayDate()
  {
    $this->datePublishBegin = date('Y-m-d', strtotime('-1 day'));
    $this->datePublishEnd = $this->datePublishBegin;
  }
}
