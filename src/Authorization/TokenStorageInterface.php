<?php
namespace FedResSdk\Authorization;

Interface TokenStorageInterface{
   
 public function getToken(): String;

 public function storeToken(String $token): bool;

 public function deleteToken(): bool;

}