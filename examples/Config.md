The basics
==========

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

It also supports the use of magical methods, so keeping it simple is a breeze.

```php
<?php
$globalPath = $config->include_path['global_value'];
```

That also means we can do easy declarations.

```php
<?php
$config->HOST_TYPE = 'development';

if (isset($config->HOST_TYPE) && $config->HOST_TYPE === 'development') {
  // Debug all the things!
  var_dump($config);
}
```

Parsing settings
================

Parse an additional INI file, somewhere in the PHP path.

```php
<?php
$config->parse('servers.ini');
```


There's no need to fret, when simply wanting the content of the INI

```php
<?php
$servers = $config->parse('servers.ini', true);
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


Native methods
==============

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


But we prefer a cleaner method.

```php
<?php
// Sorry, guys. list is reserved.
$availableConfig = $config->peek();
```


Ofcourse this results in us also wanting to check individual config.

```php
<?php
// Could also be done by using isset($config->servers).
if ($config->defined('servers')) {
  // Could also be done by using $config->servers.
  var_dump($config->get('servers'));
}
```

And when we, for some obscure reason, want to rid ourselves of config.

```php
<?php
unset($config->servers);
```

Using constants
===============

Now, what if we actually wanted to use constants throughout our code? That's possible, but you should be wary of using this behaviour, since it potentially creates a lot of constants.
For security, we exclude all values that are not scalar and were set when the Config automatically gathered data.

Here's an example of a settings.ini file:

```
HOST_TYPE = development
DEFAULT_LANG = nl
DEFAULT_LANG_FALLBACK = en
```

Now let's use this data.

```php
<?php
$config = Config::getInstance();
$config->parse('settings.ini');
$config->defineScalarConstants();

if (HOST_TYPE === 'development') {
  // Debug all the things.
  var_dump($config);
}
```

And to see what damage that actually did.

```php
<?php
var_dump(array_keys($config->constants['user']));
```

```
array(4) {
  [0]=>
  string(12) "PCNTL_EXISTS"
  [1]=>
  string(9) "HOST_TYPE"
  [2]=>
  string(12) "DEFAULT_LANG"
  [3]=>
  string(21) "DEFAULT_LANG_FALLBACK"
}
```

Extending the autoloader
========================

Because we want to use the autoloader, waisting as little resources as possible, it's been made really easy to extend the known class locations.

This can be done at any point in the script, if at least before loading the class in question.

```php
<?php
$config->put(
  array(
    'knownClassLocations' => array(
      'AlmightyClass' => __DIR__ . '/EpicGoodness.php'
    )
  )
);
```

As per the behavior of `Config::put()`, the settings will be merged, rather than overwritten.

Now, it would be more simple to have a dedicated method for this, so here goes.

```php
<?php
$config->addClass('AlmightyClass', 'EpicGoodness.php');
```

That one can check if the file exists somewhere in the PHP path before adding it to the list, preventing all sorts of mayhem later on.

```php
<?php
// Only add if the file really exists.
$config->addClass('AlmightyClass', 'EpicGoodness.php', true);
```

Public methods
==============

- `Config::getInstance(void)` Get an instance of self, preventing redundant data.
- `Config::fileInPath(string $file)` If the file is somewhere in the current PHP path, return that. Otherwise, fail by returning boolean false.
- `Config::parse(mixed $file[, boolean $return = false])` Parse an ini file, blob or open file stream, optionally returning the INI settings as an associative array.
- `Config::get(mixed $key = array())` Return any or all config data.
- `Config::put(array $config = array())` Add, update or extend config data.
- `Config::getJSON(mixed $key = array())` A JSON wrapper around the `get` method.
- `Config::defined(mixed $key)` Check if a certain key is defined.
- `Config::defineScalarConstants(void)` Automatically convert config to constants.
- `Config::peek(void)` Lists all available config.
- `Config::addClass(string $name, string $file[, boolean $checkfs = false])` Add a file location for a specific clas to the autoloader.

Magic methods
=============

- `Config::__isset(string $key)` A wrapper around `Config::defined`, allowing `if (isset($config->someConfigThingy)) { ... }`.
- `Config::__unset(string $key)` Enable the user to remove config through `unset($config->someConfigThingy)`.
- `Config::__set(string $key, mixed $value)` Allow for `$config->someConfigThingy = 'some value';`.
- `Config::__get(string $key)` Allow for `echo $config->someConfigThingy; // 'some value'`.