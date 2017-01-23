<?php
namespace Wordpress_Framework\Config\v1\Reader;

use  Wordpress_Framework\Config\v1\Config_Interface;

interface Reader_Interface {
    /**
     * Read from a file and create an Config object that implements Config_Interface
     *
     * @param  string $filename
     * @return Wordpress_Framework\Config\v1\Config_Interface object that implements Config_Interface
     */
    public static function read_from_file(string $filename, bool $allowModifications = false): Config_Interface;
}