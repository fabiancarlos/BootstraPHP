Config
======

Easily manage all your PHP configuration in a single location that prevents redundancy of data.

Example usage
=============

Initiate the wonders.

```php
<?php
require_once 'Config.php';

$config = Config::getInstance();
```


Right of the bat, you can use all PHP INI settings.

```php
<?php
$include_path = $config->get('include_path');
```


Parse an additional INI file, somewhere in the PHP path.

```php
<?php
$config->parse('servers.ini');
```


Parse an open stream, if you so desire.

```php
<?php
$input = fopen('php://input', 'r');
$config->parse($input);
```


You could also choose to directly parse an INI string.

```php
<?php
$config->parse(
  '[servers]
script = 10.0.0.1
media = 10.0.0.2'
);
```


Naturally, it's way more easy to simply put in some PHP.
```php
<?php
$config->put(
  array(
    'servers' => array(
      'script' => '10.0.0.1',
      'media' => '10.0.0.2'
    )
  )
);
```


Config keeps track of parsed files.
Let's read that out in JSON.

```php
<?php
$parsedFilesJSON = $config->getJSON(array('config' => 'scanned_files'));
```


And let's not forget that both getJSON and get can return all the things.

```php
<?php
$allTheThings = $config->get();
```


So, if you ever want to see what config is available to you.

```php
<?php
$availableConfig = array_keys($config->get());
```


Now, because INI files should have the inherent nature of overwriting.
If we want to add data to a deep config setting, we'll do:

```php
<?php
$config->put(
  array(
    'servers' => array_merge(
      $config->get('servers'),
      array(
        'script' => '10.0.0.1',
        'media' => '10.0.0.2'
      )
    )
  )
);
```
