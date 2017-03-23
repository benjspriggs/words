<?php

// Read settings from the config
$config_filename = "app.config.yml";
$config_file = fopen($config_filename, "r") or die("Unable to load app config");
$config = yaml_parse($config_file);
fclose($config_file);

?>
