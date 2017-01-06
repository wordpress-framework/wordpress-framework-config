<?php
namespace Wordpress_Framework\Config\v1;

use Countable;
use Iterator;

interface Config_Interface extends Countable, Iterator {
    /**
     * Constructor
     *
     * @param array $configuration_data
     * @param string $permissions read_and_write || read_only
     */
    public function __construct( array $configuration_data, string $permissions = 'read_only' );
    
    /**
     * Return a value or return $default if element not exist in configuration data 
     *
     * @param  string $name
     * @param  mixed  $default
     * @return mixed
     */
    public function get( string $name, $default = null );

    /**
     * Magic function so that $obj->value will work
     *
     * @param  string $name
     * @return mixed
     */
    public function __get( string $name );
    
    /**
     * Set a value to the config. Magic function so that $obj->value = value will work
     *
     * Only when permissions property was set to read_and_write on construction. Otherwise, throw an exception
     *
     * @param  string $name
     * @param  mixed  $value
     * @return void
     * @throws RuntimeException
     */
    public function __set( string $name, $value );
    
    /**
     * Magic function so that isset( $obj->value ) will work
     *
     * @param  string $name
     * @return bool
     */
    public function __isset( string $name ): bool;
    
    /**
     * unset() overloading
     *
     * @param  string $name
     * @return void
     * @throws InvalidArgumentException
     */
    public function __unset( string $name );
    
    /**
     * Return information whether modifications to data are allowed
     * 
     * @return boolean 
     */
    public function is_read_only(): bool;
    
    /**
     * Change permissions for this instance to read_only
     *
     * @return void
     */
    public function set_permissions_to_read_only();
    
    /**
     * Magic function. Deep clone of this instance to ensure that nested Configs are also cloned
     *
     * @return void
     */
    public function __clone();
    
    /**
     * Returns an array of associative copy of the stored data
     *
     * @return array
     */
    public function to_array(): array;
    
    /**
     * Merge configuration data from another Config object to this one
     *
     * For duplicate keys:
     * - Nested Configs will be recursively merged
     * - Items in $merge with INTEGER keys will be appended
     * - Items in $merge with STRING keys will overwrite current values
     *
     * @param  Config $merge_config_object
     * @return Config
     */
    public function merge( Config $merge_config_object ): Config;
}