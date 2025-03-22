<?php
namespace FedResSdk\Authorization;

/**
 * Interfase for token storage in your application
 */
Interface TokenStorageInterface{
   
 public function getToken();

 public function storeToken(String $token);

 public function deleteToken();

}