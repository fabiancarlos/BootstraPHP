<?php
/**
 * Parse configuration files.
 *
 * @package BootstraPHP
 * @subpackage Configuration
 * @copyright 2012 Jan-Marten "Joh Man X" de Boer
 */

/**
 * The configuration class.
 */
class Config {

  /**
   * An instance of Config.
   *
   * @var $_instance
   */
  private static $_instance = null;

  /**
   * The configuration data.
   *
   * @var $_config
   */
  private $_config = array();

  /**
   * The construct.
   */
  private function __construct() {
    $this->_doInternalCall();
    $this->_config['config']['checkOnAddClass'] = false;
    $this->_config['config']['non_constants'] = $this->peek();
  }

  /**
   * Perform a magic check on the existence of a config.
   *
   * @param string $key the key to check
   * @return boolean
   */
  public function __isset($key) {
    return $this->defined($key);
  }

  /**
   * Do magical unsetting.
   *
   * @param string $key the key to unset
   * @return void
   */
  public function __unset($key) {
    unset($this->_config[$key]);
  }

  /**
   * Do magical declaration of data.
   *
   * @param string $key the key to set data to
   * @param mixed $value the value to set
   * @return void
   */
  public function __set($key, $value) {
    $data = array();
    $data[$key] = $value;
    $this->put($data);
  }

  /**
   * Do magical getting of data.
   *
   * @param string $key the key to fetch data with.
   * @return mixed
   */
  public function __get($key) {
    return $this->get($key);
  }

  /**
   * Make the config available as a serialized string.
   *
   * @return string $config the config data.
   */
  public function __toString() {
    return serialize($this->get());
  }

  /**
   * Restrict what part of the class can be serialized.
   *
   * @return array $config the config variable.
   */
  public function __sleep() {
    return array('_config');
  }

  /**
   * Make sure the config is up to date.
   *
   * @return void
   */
  public function __wakeup() {
    $this->_doInternalCall();
  }

  /**
   * Get an instance of self.
   *
   * Kudos to @doenietzomoeilijk
   *
   * @return object $_instance
   */
  public static function getInstance() {
    if (!isset(self::$_instance)) {
      $classname = __CLASS__;
      self::$_instance = new $classname;
    }

    return self::$_instance;
  }

  /**
   * Checks if a file exists in the current path.
   *
   * @param string $file the file name
   * @return boolean|string $file either the path or false
   */
  public function fileInPath($file) {
    if (file_exists($file)) {
      return $file;
    } else {
      $paths = $this->get('include_path');
      $globalPaths = explode(':', $paths['global_value']);
      $localPaths = explode(':', $paths['local_value']);

      $paths = array_unique(array_merge($globalPaths, $localPaths));
      $paths = is_array($paths) ? $paths : array($paths);

      foreach ($paths as $path) {
        if (file_exists($path . $file)) {
          return $path . $file;
        }
      }
    }

    return false;
  }

  /**
   * Parse an INI file.
   *
   * @param string|resource $file the file name or handler or content
   * @param boolean $return optional: return the parsed config
   * @throws Exception on parse failure
   * @return void|array
   */
  public function parse($file, $return = false) {
    if (is_resource($file)) {
      @fseek($file, 0);
      $tmp = @tempnam('/tmp', 'Config_parse_tmp_');
      $stream = @fopen($tmp, 'w');
      $realSize = @stream_copy_to_stream($file, $stream);

      if ($realSize) {
        $file = $tmp;
      } else {
        throw new Exception(
          'Unknown error occured while trying to read given resource handler.'
        );
      }
    }

    $filename = $this->fileInPath($file);
    if (is_string($file) && $filename) {
      $config = parse_ini_file($filename, true, INI_SCANNER_NORMAL);
    } elseif (is_string($file) && strrpos($file, ']')) {
      $config = parse_ini_string($file, true, INI_SCANNER_NORMAL);
    }

    if (!isset($config) || $config === false || !is_array($config)) {
      throw new Exception(
        'Unknown error occured while parsing'
        . ($filename !== false ? ' "' . $filename . '" as' : '') . ' INI.'
      );
    } else {
      $this->put($config);

      if ($filename !== false
          && !in_array($filename, $this->_config['config']['scanned_files'])
      ) {
        $this->_config['config']['scanned_files'][] = $filename;
      }
    }

    if ($return === true) {
      return $config;
    }
  }

  /**
   * Do internal operations when a getter is called.
   *
   * @return void
   */
  private function _doInternalCall() {
    // Get ini settings.
    if (count($this->_config) === 0) {
      $this->_config = ini_get_all();
    }

    // Get loaded configs.
    if (!isset($this->_config['config'])
        || !isset($this->_config['config']['scanned_files'])
    ) {
      $this->_config['config']['scanned_files'] = array_merge(
        array(php_ini_loaded_file()),
        (array) explode(',', php_ini_scanned_files())
      );
    }

    // Get constants.
    $this->_config['constants'] = get_defined_constants(true);

    // Get extensions.
    $this->_config['extensions'] = array_fill_keys(
      get_loaded_extensions(),
      array()
    );

    foreach ($this->_config['extensions'] as $ext => $funcs) {
      $this->_config['extensions'][$ext] = get_extension_funcs($ext);
    }

    // Get included files.
    // Note: Files included using the auto_prepend_file directive
    // are not included in the returned array.
    $this->_config['config']['included_files'] = get_included_files();
  }

  /**
   * Read configuration data.
   *
   * @param mixed $key optional key or keys to read from the config
   * @return mixed $config either an array or a scalar value
   */
  public function get($key = array()) {
    $this->_doInternalCall();
    $config = array();

    if (is_scalar($key) && $this->defined($key)) {
      $config = $this->_config[$key];
    } elseif (is_array($key) && count($key)) {
      foreach ($key as $i => $k) {
        if (is_string($i)) {
          $config[$k] = $this->_config[$i][$k];
        } else {
          $config[$k] = $this->_config[$k];
        }
      }
    } elseif (is_array($key)) {
      $config = $this->_config;
    }

    return $config;
  }

  /**
   * Put in specific configuration.
   *
   * @param array $config the configuration, expected in key => value pairs
   * @return void
   */
  public function put(array $config = array()) {
    foreach ($config as $k => $v) {
      if (!empty($k)) {
        $cv = $this->get($k);
        if (is_array($cv) && is_array($v)) {
          $this->_config[$k] = array_merge($cv, $v);
        } else {
          $this->_config[$k] = $v;
        }
      }
    }
  }

  /**
   * Get all config or a specific set in JSON.
   *
   * @param mixed $key optional key or keys to read from the config
   * @return string $config the config in JSON format
   */
  public function getJSON($key = array()) {
    return json_encode($this->get($key));
  }

  /**
   * Check if a setting is defined.
   *
   * @param mixed $key the key or keys that will be checked
   * @return boolean $defined
   */
  public function defined($key) {
    $this->_doInternalCall();
    if (is_array($key)) {
      foreach ($key as $k => $v) {
        if (!array_key_exists($k, $this->_config)
            || !array_key_exists($v, $this->_config[$k])
        ) {
          return false;
        }
      }
    } else {
      return array_key_exists($key, $this->_config);
    }

    return true;
  }

  /**
   * Define any scalar config as a constant.
   *
   * @return void
   */
  public function defineScalarConstants() {
    $config = $this->get();

    foreach ($config as $key => $value) {
      if (is_scalar($value)
          && !defined($key)
          && !in_array($key, $this->_config['config']['non_constants'])
      ) {
        define($key, $value);
      }
    }
  }

  /**
   * List all the available config.
   *
   * @return array $config the defined config
   */
  public function peek() {
    $this->_doInternalCall();
    $config = array_keys($this->_config);
    return $config;
  }

  /**
   * Add a class file to the known locations.
   *
   * @param string $name the name for the class
   * @param string $file the file for the class
   * @param boolean $checkfs wether to check if the file exists or not
   * @return boolean $added when the file was added.
   */
  public function addClass($name, $file, $checkfs = false) {
    $args = func_get_args();

    // Default value for $checkfs assumed.
    if (!isset($args[2])) {
      $checkfs = $this->checkOnAddClass;
    }

    if (!$checkfs || $this->fileInPath($file)) {
      $this->put(
        array(
          'knownClassLocations' => array($name => $file)
        )
      );
      return true;
    }

    return false;
  }

}
