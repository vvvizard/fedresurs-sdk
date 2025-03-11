<?php
namespace FedResSdk;

class Config {
   
    protected static $urlToTypes = [
        'MessagesServiceTest' =>  'https://sfact-messages-demo.fedresurs.ru/',
        'BankruptServiceTest' => 'https://bank-publications-demo.fedresurs.ru/',
        'MessagesService' =>  'https://sfact-messages-prod.fedresurs.ru/',
        'BankruptService' => 'https://bank-publications-prod.fedresurs.ru/'
    ];

    public static function getMainUrl($type){
        if( isset(self::$urlToTypes[$type]))
         {
            return self::$urlToTypes[$type];
         }
    }

    public static function getDefaultCredentials(){

    }

    public static function resetMainUrls(array $urlToTypes){
         self::$urlToTypes = $urlToTypes;
    }
    
    
}