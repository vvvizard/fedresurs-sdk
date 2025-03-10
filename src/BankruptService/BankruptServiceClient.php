<?php
namespace FedResSdk\BankruptService;

use FedResSdk\Authorization\Authorization;
use FedResSdk\ClientFedRes;
use FedResSdk\Config;

class  BankruptServiceClient extends ClientFedRes {
    
    public const TYPE = 'BankruptService';

//protected $messagesType = ArbitralDecree
    public function __construct(Authorization $auth){
   
         parent::__construct($auth);

         $this->mainUrl = Config::getMainUrl(self::TYPE);     
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
}