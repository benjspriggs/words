<?php

require "./config.php";
require "./event.php";

// We should be getting updates from the right agent
$event = new WebUpdateEvent($_POST);

if (!$event->ValidateSignature($config["deploy"]["secret"]))
{
  die('Invalid signature');
}

// Update the repo, discarding changes
chdir($config["deploy"]["path"]);

exec("git reset --hard");
exec("git clean -f");
exec("git pull origin master");

?>
