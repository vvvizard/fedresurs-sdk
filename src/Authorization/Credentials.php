<?php
namespace FedResSdk\Authorization;

class Credentials implements AuthorizationInterface {

    public $login;
    public $password;
    protected $type;
    
    protected const ENCODING_TYPE = "sha512";

    public function __construct($login,$password,$type)
    {
        $this->login = $login;
        $this->password = $password;
        $this->type = $type;
    }

    public function getEncodingType(){
        return self::ENCODING_TYPE;
    }

    public function getType(){
        return $this->type;
    }

}