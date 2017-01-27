<?php
namespace Wordpress_Framework\Config\v1;

use RuntimeException;
use InvalidArgumentException;

class Config implements Config_Interface {
    /**
     * Flag specifying whether modifications to data are allowed
     *
     * @var string
     */
    protected $permissions;

    /**
     * Container for configuration data
     *
     * @var array
     */
    protected $configuration_data = [];

    /**
     * Flag for ensure we do not skip the next element when unsetting values during iteration
     * (used by in functions defined by Iterator Interface)
     *
     * @var bool
     */
    protected $skip_next_iteration;

    /**
     * Constructor
     *
     * @param array $configuration_data
     * @param string $permissions read_and_write || read_only
     */
    public function __construct( array $configuration_data = [], string $permissions = 'read_only' ) {
        $this->permissions = $permissions;

        foreach ( $configuration_data as $conf_key => $conf_value ) {
            if ( is_array( $conf_value ) ) {
                $this->configuration_data[$conf_key] = new static( $conf_value, $this->permissions );
            } else {
                $this->configuration_data[$conf_key] = $conf_value;
            }
        }
    }

    /**
     * Return a value or return $default if element not exist in configuration data
     *
     * @param  string $name
     * @param  mixed  $default
     * @return mixed
     */
    public function get( string $name, $default = null ) {
        if ( array_key_exists( $name, $this->configuration_data ) ) {
            return $this->configuration_data[$name];
        }

        return $default;
    }

    /**
     * Magic function so that $obj->value will work
     *
     * @param  string $name
     * @return mixed
     */
    public function __get( string $name ) {
        return $this->get( $name );
    }

    /**
     * Set a value to the config
     *
     * Only when permissions property was set to read_and_write on construction. Otherwise, throw an exception
     *
     * @param  string $name
     * @param  mixed  $value
     * @return void
     * @throws RuntimeException
     */
    public function set( string $name, $value ) {
        if ( ! $this->is_read_only() ) {
            if ( is_array( $value ) ) {
                $value = new static( $value, true );
            }

            $this->configuration_data[$name] = $value;
        } else {
            throw new RuntimeException( 'This Config is read only' );
        }
    }

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
    public function __set( string $name, $value ) {
        $this->set( $name, $value );
    }

    /**
     * Magic function so that isset( $obj->value ) will work
     *
     * @param  string $name
     * @return bool
     */
    public function __isset( string $name ): bool {
        return isset( $this->configuration_data[$name] );
    }

    /**
     * unset() overloading
     *
     * @param  string $name
     * @return void
     * @throws InvalidArgumentException
     */
    public function __unset( string $name ) {
        if ( $this->is_read_only()) {
            throw new RuntimeException( 'This Config is read only' );
        } elseif ( isset( $this->configuration_data[$name] ) ) {
            unset( $this->configuration_data[$name] );
            $this->skip_next_iteration = true;
        }
    }

    /**
     * Function defined by Countable interface
     *
     * @see    Countable::count()
     * @return int
     */
    public function count(): int {
        return count( $this->configuration_data );
    }

    /**
     * Function defined by Iterator interface
     *
     * @see    Iterator::current()
     * @return mixed
     */
    public function current() {
        $this->skip_next_iteration = false;
        return current( $this->configuration_data );
    }

    /**
     * Function defined by Iterator interface
     *
     * @see    Iterator::key()
     * @return mixed
     */
    public function key() {
        return key( $this->configuration_data );
    }

    /**
     * Function defined by Iterator interface
     *
     * @see    Iterator::next()
     * @return void
     */
    public function next() {
        if ( $this->skip_next_iteration ) {
            $this->skip_next_iteration = false;
            return;
        }

        next( $this->configuration_data );
    }

    /**
     * Function defined by Iterator interface
     *
     * @see    Iterator::rewind()
     * @return void
     */
    public function rewind()
    {
        $this->skip_next_iteration = false;
        reset( $this->configuration_data );
    }

    /**
     * Function defined by Iterator interface
     *
     * @see    Iterator::valid()
     * @return bool
     */
    public function valid(): bool {
        return ( $this->key() !== null );
    }

    /**
     * Return information whether modifications to data are allowed
     *
     * @return boolean
     */
    public function is_read_only(): bool {
        return $this->permissions === 'read_and_write' ? false : true;
    }

    /**
     * Change permissions for this instance to read_only
     *
     * @return void
     */
    public function set_permissions_to_read_only() {
        $this->allowModifications = 'read_only';

        foreach ( $this->configuration_data  as $conf_value ) {
            if ( $conf_value instanceof self ) {
                $conf_value->set_permissions_to_read_only();
            }
        }
    }

    /**
     * Magic function. Deep clone of this instance to ensure that nested Configs are also cloned
     *
     * @return void
     */
    public function __clone() {
        $clone_configuration_data = [];

        foreach ( $this->configuration_data as $conf_key => $conf_value ) {
            if ( $conf_value instanceof self ) {
                $clone_configuration_data[$conf_key] = clone $conf_value;
            } else {
                $clone_configuration_data[$conf_key] = $conf_value;
            }
        }

        $this->configuration_data = $clone_configuration_data;
    }

    /**
     * Returns an array of associative copy of the stored data
     *
     * @return array
     */
    public function to_array(): array {
        $configuration_data_array = [];

        foreach ( $this->configuration_data as $conf_key => $conf_value ) {
            if ( $conf_value instanceof self ) {
                $configuration_data_array[$conf_key] = $conf_value->to_array();
            } else {
                $configuration_data_array[$conf_key] = $conf_value;
            }
        }

        return $configuration_data_array;
    }

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
    public function merge( Config $merge_config_object ): Config {
        if ( $this->is_read_only()) {
            throw new RuntimeException( 'This Config is read only' );
        } else {
            foreach ( $merge_config_object as $merge_conf_key => $merge_conf_value ) {
                if ( array_key_exists( $merge_conf_key, $this->configuration_data ) ) {
                    if ( is_int( $merge_conf_key ) ) {
                        $this->configuration_data[] = $merge_conf_value;
                    } elseif ( $merge_conf_value instanceof self && $this->configuration_data[$merge_conf_key] instanceof self ) {
                        $this->configuration_data[$merge_conf_key]->merge( $merge_conf_value );
                    } else {
                        if ( $merge_conf_value instanceof self ) {
                            $this->configuration_data[$merge_conf_key] = new static( $merge_conf_value->to_array(), $this->permissions );
                        } else {
                            $this->configuration_data[$merge_conf_key] = $merge_conf_value;
                        }
                    }
                } else {
                    if ( $merge_conf_value instanceof self ) {
                        $this->configuration_data[$merge_conf_key] = new static( $merge_conf_value->to_array(), $this->permissions );
                    } else {
                        $this->configuration_data[$merge_conf_key] = $merge_conf_value;
                    }
                }
            }
        }

        return $this;
    }
}
