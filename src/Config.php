<?php
namespace FedResSdk;

class Config {
   
    protected static $urlToTypes = [
        'MessagesService' =>  'https://sfact-messages-demo.fedresurs.ru/',
        'BankruptService' => 'https://bank-publications-demo.fedresurs.ru/'
    ];

    public static function getMainUrl($type){
        if( isset(self::$urlToTypes[$type]))
         {
            return self::$urlToTypes[$type];
         }
    }

    public static function getDefaultCredentials(){

    }
    
    
}