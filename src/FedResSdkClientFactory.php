<?php
namespace FedResSdk;

use FedResSdk\Authorization\Authorization;
use FedResSdk\MessagesService\MessagesServiceClient;
use FedResSdk\BankruptService\BankruptServiceClient;

class FedResSdkClientFactory {

    public static function createSdk(Authorization $auth) {
        switch ($auth->getType()) {
            case 'MessageService':
                return new MessagesServiceClient($auth);
            case 'BankruptService':
                    return new BankruptServiceClient($auth);
            default:
                throw new \Exception("Invalid type");
        }
    }
    
   
}