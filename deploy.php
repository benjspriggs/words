<?php

chdir("..");

require "./config.php";
require "./event.php";

// We should be getting updates from the right agent
$event = new WebUpdateEvent($_POST);

// $event->ValidateSignature($config["deploy"]["secret"]);

// Update the repo, discarding changes
print "Updating the repo from '" . $config["deploy"]["path"] . "'..." . PHP_EOL;
print "<br>";
chdir($config["deploy"]["path"]);

function exec_print($cmd){
  exec($cmd . " 2>&1", $output);
  return $output;
}

function pexec($cmd){
  $o = exec_print($cmd);
  print "<pre>\n";
  print "'" . $cmd . "':<br>\n";
  foreach(exec_print($cmd) as $line)
  {
    print "\t" . $line;
  }
  print "</pre>\n";
}

pexec("touch testfile");
pexec("git reset --hard");
pexec("git clean -f");
pexec("git pull origin master");

?>
