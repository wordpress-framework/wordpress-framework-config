<?php
namespace Wordpress_Framework\Config\v1\Reader;

use  Wordpress_Framework\Config\v1\Config_Interface;
use  Wordpress_Framework\Config\v1\Config;

class Php implements Reader_Interface {
    /**
     * @inheritDoc
     */
    public static function read_from_file(string $filename, bool $allowModifications = false): Config_Interface {
        if (!is_file($filename) || !is_readable($filename)) {
            throw new \RuntimeException(sprintf(
                "File '%s' doesn't exist or not readable",
                $filename
            ));
        }

        return new Config(include $filename, $allowModifications);
    }
}
