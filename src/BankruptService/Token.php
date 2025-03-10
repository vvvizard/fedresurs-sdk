<?php

namespace FedResSdk\BankruptService;

use FedResSdk\Authorization\TokenStorageInterface;
use Stringable;

class Token implements TokenStorageInterface
{
    public function getToken():String
     {
        return file_get_contents('/var/www/html/public/fedres-sdk/token2.txt');
    }

    public function storeToken(String $token): bool 
    {
        return file_put_contents('/var/www/html/public/fedres-sdk/token2.txt', $token);
    }

    public function deleteToken(): bool 
    {
        return file_put_contents('/var/www/html/public/fedres-sdk/token2.txt', '');
    }


}


