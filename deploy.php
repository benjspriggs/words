<?php

chdir("..");

require "./config.php";
require "./event.php";

// We should be getting updates from the right agent
$event = new WebUpdateEvent($_POST);

$event->ValidateSignature($config["deploy"]["secret"]);

// Update the repo, discarding changes
print "Updating the repo from '" . $config["deploy"]["path"] . "'..." . PHP_EOL;
print "<br>";
chdir($config["deploy"]["path"]);

echo exec("git reset --hard");
print "<br>";
echo exec("git clean -f");
print "<br>";
echo exec("git pull origin master");
print "<br>";

?>
