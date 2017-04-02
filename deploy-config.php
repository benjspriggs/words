<?php

require_once "./lib/spyc/Spyc.php";

// .yml config file
const CONFIG_FILENAME = "./app.config.yml";
// mapping of values to keys as they appear in the YAML file
const CONFIG_MAP = array(
  "required" => array(
    "SECRET_ACCESS_TOKEN" => "secret",
    "REMOTE_REPOSITORY" => "remote_url",
    "BRANCH" => "branch",
    "TARGET_DIR" => "path",
    "DELETE_FILES" => "make-clean",
    "EXCLUDE" => "exclude",
    "TMP_DIR" => "staging",
    "CLEAN_UP" => "cleanup-after-stage",
    "TIME_LIMIT" => "timeout",
  ),
  "optional" => array(
    "BACKUP_DIR" => "backup",
    "USE_COMPOSER" => "composer",
    "COMPOSER_HOME" => "composer-home",
    "EMAIL_ON_ERROR" => "email",
  )
);


// Read settings from the config
function read_config($filename)
{
  return spyc_load_file(CONFIG_FILENAME);
}

// attempt to get a value, fill with a default
function get(&$value, $default = null){
  return isset($value) ? $value : $default;
}

function map_to_configs($config_mapping, $deploy_config, $required = false){
  foreach($config_mapping as $config_val => $yaml_key){
    if ($required && !array_key_exists($yaml_key, $deploy_config)){
      die("Missing required key '$yaml_key' in deploy configuration.");
    }
    define($config_val, get($deploy_config[$yaml_key], false));
  }
}

// define all of the constants used in the deploy.php script
function setup_deploy_config($filename)
{
  $config = read_config($filename);
  $deploy_config = $config['deploy'];
  map_to_configs(CONFIG_MAP["required"], $deploy_config, true);
  map_to_configs(CONFIG_MAP["optional"], $deploy_config, false);
}


// DOCUMENTATION BEGIN
/** SECRET_ACCESS_TOKEN
 **Protect the script from unauthorized access by using a secret access token.
 **If it's not present in the access URL as a GET variable named `sat`
 **e.g. deploy.php?sat=Bett...s the script is not going to deploy.
 **
 **@var string
 **/

/** REMOTE_REPOSITORY
 **The address of the remote Git repository that contains the code that's being
 **deployed.
 **If the repository is private, you'll need to use the SSH address.
 **
 **@var string
 **/
/** BRANCH
 **The branch that's being deployed.
 **Must be present in the remote repository.
 **
 **@var string
 **/
/** TARGET_DIR
 **The location that the code is going to be deployed to.
 **Don't forget the trailing slash!
 **
 **@var string Full path including the trailing slash
 **/
/** DELETE_FILES
 **Whether to delete the files that are not in the repository but are on the
 **local (server) machine.
 **
 **!!! WARNING !!! This can lead to a serious loss of data if you're not
 **careful. All files that are not in the repository are going to be deleted,
 **except the ones defined in EXCLUDE section.
 **BE CAREFUL!
 **
 **@var boolean
 **/
/** EXCLUDE
 **The directories and files that are to be excluded when updating the code.
 **Normally, these are the directories containing files that are not part of
 **code base, for example user uploads or server-specific configuration files.
 **Use rsync exclude pattern syntax for each element.
 **
 **@var serialized array of strings
 **/
/** TMP_DIR
 **Temporary directory we'll use to stage the code before the update. If it
 **already exists, script assumes that it contains an already cloned copy of the
 **repository with the correct remote origin and only fetches changes instead of
 **cloning the entire thing.
 **
 **@var string Full path including the trailing slash
 **/
/** CLEAN_UP
 **Whether to remove the TMP_DIR after the deployment.
 **It's useful NOT to clean up in order to only fetch changes on the next
 **deployment.
 **/
/** VERSION_FILE
 **Output the version of the deployed code.
 **
 **@var string Full path to the file name
 **/
/** TIME_LIMIT
 **Time limit for each command.
 **
 **@var int Time in seconds
 **/
/** BACKUP_DIR
 **OPTIONAL
 **Backup the TARGET_DIR into BACKUP_DIR before deployment.
 **
 **@var string Full backup directory path e.g. `/tmp/`
 **/
/** USE_COMPOSER
 **OPTIONAL
 **Whether to invoke composer after the repository is cloned or changes are
 **fetched. Composer needs to be available on the server machine, installed
 **globaly (as `composer`). See http://getcomposer.org/doc/00-intro.md#globally
 **
 **@var boolean Whether to use composer or not
 **@link http://getcomposer.org/
 **/
/** COMPOSER_OPTIONS
 **OPTIONAL
 **The options that the composer is going to use.
 **
 **@var string Composer options
 **@link http://getcomposer.org/doc/03-cli.md#install
 **/
/** COMPOSER_HOME
 **OPTIONAL
 **The COMPOSER_HOME environment variable is needed only if the script is
 **executed by a system user that has no HOME defined, e.g. `www-data`.
 **
 **@var string Path to the COMPOSER_HOME e.g. `/tmp/composer`
 **@link https://getcomposer.org/doc/03-cli.md#composer-home
 **/
/** EMAIL_ON_ERROR
 **OPTIONAL
 **Email address to be notified on deployment failure.
 **
 **@var string A single email address, or comma separated list of email addresses
 **e.g. 'someone@example.com' or 'someone@example.com, someone-else@example.com, ...'
 **/
?>
