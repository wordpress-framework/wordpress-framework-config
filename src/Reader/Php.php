<?php
namespace Wordpress_Framework\Config\v1\Reader;

use  Wordpress_Framework\Config\v1\ConfigInterface;
use  Wordpress_Framework\Config\v1\Config;

class Php implements Reader_Interface {
    /**
     * Read from a file and create an Config object that implements ConfigInterface
     *
     * @param  string $filename
     * @return Wordpress_Framework\Config\v1\ConfigInterface object that implements ConfigInterface
     */
    public static function read_from_file(string $filename, bool $allowModifications = false): ConfigInterface {
        if (!is_file($filename) || !is_readable($filename)) {
            throw new \RuntimeException(sprintf(
                "File '%s' doesn't exist or not readable",
                $filename
            ));
        }
        
        return new Config(include $filename, $allowModifications);
    }
}