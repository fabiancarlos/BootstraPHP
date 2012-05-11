<?php
/**
 * Parse configuration files.
 *
 * @package BootstraPHP
 * @subpackage Bootstrap
 * @copyright 2012 Jan-Marten "Joh Man X" de Boer
 */

// Make sure we have access to the __DIR__ magic constant.
// Constant __DIR__ was introduced in PHP 5.3.
if (!defined('__DIR__')) {
  define('__DIR__', dirname(__FILE__));
}

// Settings required for the autoloader.
require_once 'Config.php';
Config::getInstance()->put(
  array(
    'knownClassLocations' => array(
      'Config' => __DIR__ . 'Config.php';
    )
  )
);

/**
 * Create an auto loader for classes.
 *
 * @param string $class the class to load
 * @throws Exception when not able to find the class file
 * @return void
 */
function __autoload($class) {
  $config = Config::getInstance();

  if ($config->defined(array('knownClassLocations' => $class))) {
    include_once $config->get(array('knownClassLocations' => $class));
  } elseif($config->fileInPath($class . '.php')) {
    include_once $class . '.php';
  } else {
    throw new Exception('Could not automatically load class "' . $class . '"');
  }
}
