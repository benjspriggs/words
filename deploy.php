<?php

chdir(".."); // change to root of the repo

function format_exec($cmd){
  $res = exec($cmd, $output, $retvar);
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

format_exec('./hello.sh');
format_exec('git status');
exit;

require "./config.php";
require "./event.php";

// We should be getting updates from the right agent
$event = new WebUpdateEvent($_POST);

// TODO: COMMENT
// $event->ValidateSignature($config["deploy"]["secret"]);

require "./lib/git-php/Git.php";

// Update the repo, discarding changes
print "Updating the repo from '" . $config["deploy"]["path"] . "'...";
print "<br>";
chdir($config["deploy"]["path"]);

$repo = Git::open(".");

function format_git($repo, $cmd){
  print "<pre>'git ". $cmd ."':". PHP_EOL;
  print htmlspecialchars($repo->run($cmd)) . "</pre>";
}

format_git($repo, "status");
format_git($repo, "pull origin master");
format_git($repo, "reset --hard");
format_git($repo, "clean -f");


?>
