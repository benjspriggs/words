<?php
class WebUpdateEvent {
  var $event;
  var $signature;
  var $delivery;
  var $body;

  // construct from $_POST
  function __construct($post){
    $this->event = htmlspecialchars($post["X-GitHub-Event"]);
    $this->signature = htmlspecialchars($post["X-Hub-Signature"]);
    $this->delivery = htmlspecialchars($post["X-GitHub-Delivery"]);
    $this->body = htmlspecialchars(file_get_contents('php://input'));
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
    return hash_equals($this->signature, hash_hmac("sha1", $this->body, $app_secret));
  }

  function ValidateSignature($app_secret){
    if (!$this->IsValid($app_secret))
      die("Invalid signature");
  }
}
?>
