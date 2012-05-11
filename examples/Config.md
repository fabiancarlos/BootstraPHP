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