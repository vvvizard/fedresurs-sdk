<?php

namespace FedResSdk\BankruptService;

use FedResSdk\Authorization\Authorization;
use FedResSdk\ClientFedRes;
use FedResSdk\Config;
use FedResSdk\BankruptService\XmlParser\XmlParserFabric;
use FedResSdk\BankruptService\Dictionary;

/**
 * Client for BankruptService
 */
class  BankruptServiceClient extends ClientFedRes
{
  public const TYPE = 'BankruptService';
  protected const MAX_MESSAGES_LIMIT = 500;
  protected const MAX_QUERY_LIMIT = 8;
  protected const MESSAGE_LINK_TPL = 'https://old.bankrot.fedresurs.ru/MessageWindow.aspx?ID=';

  protected const ROUTE_MESSAGES = 'v1/messages';
  protected const ROUTE_AUTH = 'v1/auth';
  public $sort = "DatePublish:asc";
  public $bankruptGuid = null;

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
    $this->checkToken();
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
    ($this->bankruptGuid !== null) ? $url .=  '&bankruptGUID=' . $this->bankruptGuid : '';

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

  /**
   * converting guid to link format
   * @param mixed $guid
   * @return string
   */
  protected function guidToLinkFormat($guid)
  {
    return strtoupper(str_replace('-', '', $guid));
  }

  /**
   * Parsing xml and converting it to array
   * with formatted data
   * @param mixed $xml
   * @param string $type
   */
  protected function parseXml($xml, $type)
  {
    $xmlParser = XmlParserFabric::create($type, $xml);
    return ($xmlParser == ! null) ? $xmlParser->parse() : $xml;
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

  public function getDictionary()
  {
    if ($this->dictionary === null) {
      $this->dictionary = new Dictionary();
    }
    return $this->dictionary;
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

  public function checkToken()
  {

    $url = self::ROUTE_MESSAGES . '?offset=0&limit=1';

    ($this->sort !== null) ? $url .= '&sort=' . $this->sort : '';

    ($this->datePublishBegin !== null) ? $url .= '&datePublishBegin=' . $this->datePublishBegin : '';

    ($this->datePublishEnd !== null) ? $url .= '&datePublishEnd=' . $this->datePublishEnd : '';

    $response = $this->apiRequest("GET", $url);
    if ($response) {
      return true;
    }

    return false;
  }
}
