<?php
namespace Wordpress_Framework\Config\v1\Reader;

use  Wordpress_Framework\Config\v1\ConfigInterface;

interface Reader_Interface {
    /**
     * Read from a file and create an Config object that implements ConfigInterface
     *
     * @param  string $filename
     * @return Wordpress_Framework\Config\v1\ConfigInterface object that implements ConfigInterface
     */
    public static function read_from_file(string $filename, bool $allowModifications = false): ConfigInterface;
}