<?php
namespace FedResSdk\Authorization;

Interface TokenStorageInterface{
   
 public function getToken();

 public function storeToken(String $token);

 public function deleteToken();

}