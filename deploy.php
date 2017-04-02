<?php

chdir(".."); // change to root of the repo
require "./config.php";
require "./event.php";

function format_exec($cmd){
  $res = exec("su $(stat -c '%U') -c '$cmd' 2>&1", $output, $retvar);
  print "<pre>"
    . "Result of command '" . $cmd . "': " 
    . $retvar . PHP_EOL;
  foreach ($output as $index => $line){
    print $index 
      . "\t". $line . ""
      . PHP_EOL;
  }
  print "</pre>";
}

// We should be getting updates from the right agent
$event = new WebUpdateEvent($_POST);

// TODO: COMMENT
// $event->ValidateSignature($config["deploy"]["secret"]);

// Update the repo, discarding changes
print "Updating the repo from '" . $config["deploy"]["path"] . "'...";
print "<br>";
chdir($config["deploy"]["path"]);

format_exec('./deploy.sh');

?>
