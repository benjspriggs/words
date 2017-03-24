<?php

chdir("..");

require "./config.php";
require "./event.php";

// We should be getting updates from the right agent
$event = new WebUpdateEvent($_POST);

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
