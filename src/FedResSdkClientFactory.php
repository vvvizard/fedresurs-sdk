<?php

namespace FedResSdk;

use FedResSdk\Authorization\Authorization;
use FedResSdk\MessagesService\MessagesServiceClient;
use FedResSdk\BankruptService\BankruptServiceClient;
use FedResSdk\BankruptService\BankruptMessages;

class FedResSdkClientFactory
{

    public static function createSdk(Authorization $auth)
    {
        switch ($auth->getType()) {
            case 'MessageService':
                return new MessagesServiceClient($auth);
            case 'BankruptService':
                return new BankruptMessages($auth);
            case 'BankruptSearch':
                return new BankruptSearch($auth);
            case 'MessageServiceTest':
                $client = new MessagesServiceClient($auth);
                $client->setMode('develop');
                return $client;
            case 'BankruptServiceTest':
                $client = new BankruptServiceClient($auth);
                $client->setMode('develop');
                return $client;
            default:
                throw new \Exception("Invalid type");
        }
    }
}
