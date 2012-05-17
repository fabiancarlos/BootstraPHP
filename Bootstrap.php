<?php
/**
 * Bootstrap your PHP project of desire.
 *
 * @package BootstraPHP
 * @subpackage Bootstrap
 * @copyright 2012 Jan-Marten "Joh Man X" de Boer
 */

// Make sure we have access to the __DIR__ magic constant.
// Constant __DIR__ was introduced in PHP 5.3.
if (@__DIR__ === '__DIR__') {
  define('__DIR__', dirname(__FILE__));
}

// Settings required for the autoloader.
require_once 'Config.php';
Config::getInstance()->parse('settings.ini');

/**
 * Create an auto loader for classes.
 *
 * @param string $class the class to load
 * @return void
 */
function __autoload($class) {
  $config = Config::getInstance();

  if (isset($config->knownClassLocations[$class])) {
    include_once $config->knownClassLocations[$class];
  } elseif($config->fileInPath($class . '.php')) {
    include_once $class . '.php';
  } else {
    trigger_error('Could not automatically load class "' . $class . '"');
  }
}
