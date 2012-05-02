<?php
/**
 * Parse configuration files.
 *
 * @package Joh Man X
 * @subpackage Configuration
 * @copyright 2012 Jan-Marten "Joh Man X" de Boer
 */

class Config{
  
  private static $_instance = null;
  private $_config = array();
  
  /**
   * The construct.
   */
  function __construct() {
    if (count($this->_config) === 0) {
      $this->_config = ini_get_all();
      $this->_config['config']['scanned_files'] = array_merge(
        array(php_ini_loaded_file()),
        (array) explode(',', php_ini_scanned_files())
      );
    }
  }
  
  /**
   * Get an instance of self.
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
  private function _fileInPath($file) {
    if (file_exists($file)) {
      return $file;
    } else {
      $paths = $this->get('include_path');
      $global_paths = explode(':', $paths['global_value']);
      $local_paths = explode(':', $paths['local_value']);
      
      $paths = array_unique(array_merge($global_paths, $local_paths));
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
   * @throws exception on parse failure
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
    
    $filename = $this->_fileInPath($file);
    if (is_string($file) && $filename) {
      $config = parse_ini_file($filename, true, INI_SCANNER_NORMAL);
    } elseif (is_string($file) && strrpos($file, ']')) {
      $config = parse_ini_string($file, true, INI_SCANNER_NORMAL);
    }
    
    if ($config === false || !is_array($config)) {
      throw new Exception(
        'Unknown error occured while parsing'
        . ($filename !== false ? ' "' . $filename . '" as' : '') . ' INI.'
      );
    } else {
      $this->_config = array_merge(
        $this->_config,
        array_diff_assoc($this->_config, $config)
      );
      
      if (
        $filename !== false
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
   * Read configuration data.
   *
   * @param mixed $key optional key or keys to read from the config
   * @return mixed $config either an array or a scalar value
   */
  public function get($key = array()) {
    $config = array();
  
    if (is_scalar($key) && array_key_exists($key, $this->_config)) {
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
   */
  public function put(array $config = array()) {
    foreach ($config as $k => $v) {
      if (!empty($k)) {
        $this->_config[$k] = $v;
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
  
}
