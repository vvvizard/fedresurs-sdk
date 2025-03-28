<?php

namespace FedResSdk\Authorization;

use FedResSdk\Authorization\AuthorizationInterface;
use FedResSdk\Authorization\TokenStorageInterface;

class Authorization
{

    protected $token = null;
    protected $mode = "production";
    protected $tokenStorage;

    protected $credentials;
    protected $type;

    protected $attemps = 0;


    public function __construct(AuthorizationInterface $credentials, ?TokenStorageInterface $tokenStorage = null)
    {
        $this->credentials = $credentials;
        $this->type = $credentials->getType();
        $this->tokenStorage = $tokenStorage;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function issetToken()
    {
        if ($this->token != null) {
            return true;
        }
        return false;
    }

    public function setMode($mode){
        $this->mode = $mode;
    }

    public function getMode()
    {
        return $this->mode;
    }

    public function setToken($token)
    {
        return $this->token = $token;
    }

    public function unsetToken()
    {
        $this->setToken(NULL);
    }

    public function deleteToken(){
        $this->unsetToken();
        if ($this->tokenStorage !== null) {
            return $token = $this->tokenStorage->deleteToken();
        } else {
            $this->token = null;
            return $this->token;
        }

    }

    public function storeToken($token)
    {
        $this->setToken($token);
        if ($this->tokenStorage !== null) {
            return $token = $this->tokenStorage->storeToken($token);
        } else {
            $this->token = $token;
            return $this->token;
         }
    }

    public function loadToken()
    {
        if ($this->tokenStorage !== null) {
            $token = $this->tokenStorage->getToken();
        } else {
            $token = file_get_contents('/var/www/html/public/fedres-sdk/token.txt');
        }
        if ($token) {
            $this->setToken($token);
            return true;
        }
        return false;
    }

    public function attempPlus(){
        $this->attemps += 1;
    }

    public function getAttemps(){
        return $this->attemps;
    }   

    public function clearAttemps(){
        $this->attemps = 0;
    }


    public function getAuthData()
    {
        return [
            'login' => $this->credentials->login,
            'password' => $this->credentials->password
        ];
    }

    public function getAuthDataJson()
    {
        return json_encode([
            'login' => $this->credentials->login,
            'password' => $this->credentials->password
        ]);
    }

    public function getAuthDataWithHash()
    {
        return [
            'login' => $this->credentials->login,
            'passwordHash' => hash($this->credentials->getEncodingType(), $this->credentials->password)
        ];
    }

    public function getAuthDataWithHashJson()
    {
        return json_encode([
            'login' => $this->credentials->login,
            'passwordHash' => hash($this->credentials->getEncodingType(), $this->credentials->password)
        ]);
    }
}
