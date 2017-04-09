<?php
class WebUpdateEvent {
  var $event;
  var $signature;
  var $delivery;
  var $body;
  var $algo;

  function __construct($headers, $body){
    $this->event = htmlspecialchars($headers["X-GitHub-Event"]);
    $this->signature = htmlspecialchars($headers["X-Hub-Signature"]);
    $this->delivery = htmlspecialchars($headers["X-GitHub-Delivery"]);
    $this->body = $body;
    list($this->algo, $this->signature) = explode('=', $this->signature, 2);
    if (!in_array($this->algo, hash_algos(), true)){
      throw new Exception("Hashing algorithm unexpected - ". $this->algo);
    }
  }

  function Event(){
    return $this->event;
  }

  function Signature(){
    return $this->signature;
  }

  function Delivery(){
    return $this->delivery;
  }

  function Body(){
    return $this->body;
  }

  function IsValid($app_secret){
    return hash_equals($this->signature, hash_hmac($this->algo, $this->body, $app_secret));
  }

  function ValidateSignature($app_secret){
    if (!$this->IsValid($app_secret))
      die("Invalid signature");
  }
}
?>
