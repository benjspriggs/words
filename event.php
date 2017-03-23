<?php
class WebUpdateEvent {
  var $event;
  var $signature;
  var $delivery;
  var $body;

  // construct from $_POST
  function __construct($post){
    $event = htmlspecialchars($post["X-GitHub-Event"]);
    $signature = htmlspecialchars($post["X-Hub-Signature"]);
    $delivery = htmlspecialchars($post["X-GitHub-Delivery"]);
    $body = htmlspecialchars(file_get_contents('php://input'));
  }

  function Event(){
    return $this->$event;
  }

  function Signature(){
    return $this->$signature;
  }

  function Delivery(){
    return $this->$signature;
  }

  function Body(){
    return $this->$body;
  }

  function ValidateSignature($app_secret){
    return hash_equals($signature, hash_hmac("sha1", $body, $app_secret));
  }
}

?>
