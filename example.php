<?php
/**
 * Parse configuration files.
 *
 * @package Joh Man X
 * @subpackage Configuration
 * @copyright 2012 Jan-Marten "Joh Man X" de Boer
 */

require_once 'Config.php';

$config = Config::getInstance();

// Right of the bat, you can use all PHP INI settings.
$include_path = $config->get('include_path');

// Parse an additional INI file, somewhere in the PHP path.
$config->parse('servers.ini');

// Parse an open stream, if you so desire.
$input = fopen('php://input', 'r');
$config->parse($input);

// You could also choose to directly parse an INI string.
$config->parse(
  '[servers]
script = 10.0.0.1
media = 10.0.0.2'
);

// Naturally, it's way more easy to simply put in some PHP.
$config->put(
  array(
    'servers' => array(
      'script' => '10.0.0.1',
      'media' => '10.0.0.2'
    )
  )
);

// Config keeps track of parsed files.
// Let's read that out in JSON.
$parsedFilesJSON = $config->getJSON(array('config' => 'scanned_files'));

// And let's not forget that both getJSON and get can return all the things.
$allTheThings = $config->get();

// So, if you ever want to see what config is available to you.
$availableConfig = array_keys($config->get());

// Now, because INI files should have the inherent nature of overwriting.
// If we want to add data to a deep config setting, we'll do.
$config->put(
  array_merge(
    $this->get('servers'),
    array(
      'servers' => array(
        'script' => '10.0.0.1',
        'media' => '10.0.0.2'
      )
    )
  )
);
