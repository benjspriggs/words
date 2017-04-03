<?php
class WebUpdateEvent {
  var $event;
  var $signature;
  var $delivery;
  var $body;
  var $algo;

  // construct from $_POST
  function __construct($post){
    $this->event = htmlspecialchars($post["X-GitHub-Event"]);
    $this->signature = htmlspecialchars($post["X-Hub-Signature"]);
    $this->delivery = htmlspecialchars($post["X-GitHub-Delivery"]);
    $this->body = htmlspecialchars(file_get_contents('php://input'));
    list($this->algo, $this->signature) = 
      explode('=', $this->signature, 2) + array('', '');
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
    return $this->signature;
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
