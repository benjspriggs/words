<?php
/**
 * Simple PHP Git deploy script
 *
 * Automatically deploy the code using PHP and Git.
 *
 * @version 1.3.2
 * @link    https://github.com/markomarkovic/simple-php-git-deploy/
 */

// =========================================[ Configuration start ]===

/**
 * It's preferable to configure the script using `deploy-config.php` file.
 *
 * Rename `deploy-config.example.php` to `deploy-config.php` and edit the
 * configuration options there instead of here. That way, you won't have to edit
 * the configuration again if you download the new version of `deploy.php`.
 */
if (file_exists(basename(__FILE__, '.php').'-config.php')) {
  define('CONFIG_FILE', basename(__FILE__, '.php').'-config.php');
  require_once CONFIG_FILE;
} else {
  define('CONFIG_FILE', __FILE__);
}

// [ Configuration End ]

require_once "event.php";

ob_start();

$event = new WebUpdateEvent(getallheaders(), file_get_contents('php://input'));

// If there's authorization error, set the correct HTTP header.
if (!$event->IsValid(SECRET_ACCESS_TOKEN)){
  header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden', true, 403);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="robots" content="noindex">
  <title>Deploy from Git</title>
  <style>
body { padding: 0 1em; background: #222; color: #fff; }
h2, .error { color: #c33; }
.prompt { color: #6be234; }
.command { color: #729fcf; }
.output { color: #999; }
  </style>
</head>
<body>
<?php
$event->ValidateSignature(SECRET_ACCESS_TOKEN);
?>
<pre>

Checking the environment ...

Running as <b><?php echo trim(shell_exec('whoami')); ?></b>.

<?php
// Check if the required programs are available
$requiredBinaries = array('git', 'rsync');
if (defined('BACKUP_DIR') && BACKUP_DIR !== false) {
  $requiredBinaries[] = 'tar';
  if (!is_dir(BACKUP_DIR) || !is_writable(BACKUP_DIR)) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    die(sprintf('<div class="error">BACKUP_DIR `%s` does not exists or is not writeable.</div>', BACKUP_DIR));
  }
}
if (defined('USE_COMPOSER') && USE_COMPOSER === true) {
  $requiredBinaries[] = 'composer --no-ansi';
}
foreach ($requiredBinaries as $command) {
  $path = trim(shell_exec('which '.$command));
  if ($path == '') {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    die(sprintf('<div class="error"><b>%s</b> not available. It needs to be installed on the server for this script to work.</div>', $command));
  } else {
    $version = explode("\n", shell_exec($command.' --version'));
    printf('<b>%s</b> : %s'."\n"
      , $path
      , $version[0]
    );
  }
}
?>

Environment OK.

Using configuration defined in <?php echo CONFIG_FILE."\n"; ?>

Deploying <?php echo REMOTE_REPOSITORY; ?> <?php echo BRANCH."\n"; ?>
to        <?php echo TARGET_DIR; ?> ...

<?php
// The commands
$commands = array();

// ========================================[ Pre-Deployment steps ]===

if (!is_dir(TMP_DIR)) {
  // Clone the repository into the TMP_DIR
  $commands[] = sprintf(
    'git clone --depth=1 --branch %s %s %s'
    , BRANCH
    , REMOTE_REPOSITORY
    , TMP_DIR
  );
} else {
  // TMP_DIR exists and hopefully already contains the correct remote origin
  // so we'll fetch the changes and reset the contents.
  $commands[] = sprintf(
    'git --git-dir="%s.git" --work-tree="%s" fetch --tags origin %s'
    , TMP_DIR
    , TMP_DIR
    , BRANCH
  );
  $commands[] = sprintf(
    'git --git-dir="%s.git" --work-tree="%s" reset --hard FETCH_HEAD'
    , TMP_DIR
    , TMP_DIR
  );
}

// Update the submodules
$commands[] = sprintf(
  'git submodule update --init --recursive'
);

// Describe the deployed version
if (defined('VERSION_FILE') && VERSION_FILE !== '') {
  $commands[] = sprintf(
    'git --git-dir="%s.git" --work-tree="%s" describe --always > %s'
    , TMP_DIR
    , TMP_DIR
    , VERSION_FILE
  );
}

// Backup the TARGET_DIR
// without the BACKUP_DIR for the case when it's inside the TARGET_DIR
if (defined('BACKUP_DIR') && BACKUP_DIR !== false) {
  $commands[] = sprintf(
    "tar --exclude='%s*' -czf %s/%s-%s-%s.tar.gz %s*"
    , BACKUP_DIR
    , BACKUP_DIR
    , basename(TARGET_DIR)
    , md5(TARGET_DIR)
    , date('YmdHis')
    , TARGET_DIR // We're backing up this directory into BACKUP_DIR
  );
}

// Invoke composer
if (defined('USE_COMPOSER') && USE_COMPOSER === true) {
  $commands[] = sprintf(
    'composer --no-ansi --no-interaction --no-progress --working-dir=%s install %s'
    , TMP_DIR
    , (defined('COMPOSER_OPTIONS')) ? COMPOSER_OPTIONS : ''
  );
  if (defined('COMPOSER_HOME') && is_dir(COMPOSER_HOME)) {
    putenv('COMPOSER_HOME='.COMPOSER_HOME);
  }
}

// ==================================================[ Deployment ]===

// Compile exclude parameters
$exclude = '';
foreach (unserialize(EXCLUDE) as $exc) {
  $exclude .= ' --exclude='.$exc;
}
// Deployment command
$commands[] = sprintf(
  'rsync -rltgoDzvO %s %s %s %s'
  , TMP_DIR
  , TARGET_DIR
  , (DELETE_FILES) ? '--delete-after' : ''
  , $exclude
);

// =======================================[ Post-Deployment steps ]===

// Remove the TMP_DIR (depends on CLEAN_UP)
if (CLEAN_UP) {
  $commands['cleanup'] = sprintf(
    'rm -rf %s'
    , TMP_DIR
  );
}

// =======================================[ Run the command steps ]===
$output = '';
foreach ($commands as $command) {
  set_time_limit(TIME_LIMIT); // Reset the time limit for each command
  if (file_exists(TMP_DIR) && is_dir(TMP_DIR)) {
    chdir(TMP_DIR); // Ensure that we're in the right directory
  }
  $tmp = array();
  exec($command.' 2>&1', $tmp, $return_code); // Execute the command
  // Output the result
  printf('
<span class="prompt">$</span> <span class="command">%s</span>
<div class="output">%s</div>
'
    , htmlentities(trim($command))
    , htmlentities(trim(implode("\n", $tmp)))
  );
  $output .= ob_get_contents();
  ob_flush(); // Try to output everything as it happens

  // Error handling and cleanup
  if ($return_code !== 0) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    printf('
<div class="error">
Error encountered!
Stopping the script to prevent possible data loss.
CHECK THE DATA IN YOUR TARGET DIR!
</div>
'
    );
    if (CLEAN_UP) {
      $tmp = shell_exec($commands['cleanup']);
      printf('


Cleaning up temporary files ...

<span class="prompt">$</span> <span class="command">%s</span>
<div class="output">%s</div>
'
  , htmlentities(trim($commands['cleanup']))
  , htmlentities(trim($tmp))
      );
    }
    $error = sprintf(
      'Deployment error on %s using %s!'
      , $_SERVER['HTTP_HOST']
      , __FILE__
    );
    error_log($error);
    if (EMAIL_ON_ERROR) {
      $output .= ob_get_contents();
      $headers = array();
      $headers[] = sprintf('From: Simple PHP Git deploy script <simple-php-git-deploy@%s>', $_SERVER['HTTP_HOST']);
      $headers[] = sprintf('X-Mailer: PHP/%s', phpversion());
      mail(EMAIL_ON_ERROR, $error, strip_tags(trim($output)), implode("\r\n", $headers));
    }
    break;
  }
}
?>

Done.
</pre>
</body>
</html>
