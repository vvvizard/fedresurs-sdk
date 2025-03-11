<?php
namespace FedResSdk\BankruptService;

use FedResSdk\Authorization\Authorization;
use FedResSdk\ClientFedRes;
use FedResSdk\Config;

class  BankruptServiceClient extends ClientFedRes {
    
    public const TYPE = 'BankruptService';
    public $sort = "DatePublish:asc";

    public function __construct(Authorization $auth){
   
         parent::__construct($auth);
         
         $type = self::TYPE;
         if($auth->getMode() == 'develop'){
          $type = self::TYPE . 'Test';
         }

         $this->mainUrl = Config::getMainUrl($type);     
         $this->setYesterdayDate();
         $this->setMessagesType('ArbitralDecree');
    }
    
    public function auth(){
      if(!$this->isAuthorized()){

        $this->body = $this->auth->getAuthDataJson();
        $response = $this->apiRequest("POST", 'v1/auth');
        if($response){
            $data = json_decode($response, true);
            $this->auth->storeToken($data['jwt']);
            $this->headers['Authorization'] = 'Bearer ' . $data['jwt'];
         }
      }
    }

    public function setAuthHeaders($token){
      $this->headers['Authorization'] = 'Bearer ' . $token;
    }
    public function getMessages()
    {
      $url = 'v1/messages?offset='.$this->offset.'&limit='. $this->limit.'&sort='. $this->sort
      .'&datePublishBegin='.$this->datePublishBegin.'&datePublishEnd='.$this->datePublishEnd;
      
      $response = $this->apiRequest("GET", $url);
      $data = json_decode($response, true);
      return $data;
    }

    public function getMessage($id)
    {
      $url = 'v1/messages/'.$id;
      $response = $this->apiRequest("GET", $url);
      $data = json_decode($response, true);
      return $data;
    }

    public function getFiles($messageId)
    {
      $url = 'v1/messages/'.$messageId.'/files/archive';
      $response = $this->apiRequest("GET", $url);
      $data = json_decode($response, true);
      return $data;
    }

    public function setYesterdayDate(){
      $this->datePublishBegin = date('Y-m-d', strtotime('-1 day'));
      $this->datePublishEnd = date('Y-m-d');
    }
}