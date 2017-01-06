<?php
namespace Wordpress_Framework\Config\v1\Tests\Reader;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;

use Wordpress_Framework\Config\v1\Config_Interface;
use Wordpress_Framework\Config\v1\Reader\Php as Php_Reader;

class Php_Test extends TestCase {
    
    protected function getTestAssetPath($filename) {
        return __DIR__ . '/TestAssets/Php/' . $filename . '.php';
    }
    /**
     * @expectedException RuntimeException
     */
    public function test_read_from_file() {
        
        Php_Reader::read_from_file('noexist.php');
        $correct_config_object = Php_Reader::read_from_file($this->getTestAssetPath('correct'));
        $this->assertInstanceOf(Config_Interface::class, $correct_config_object);
    }
}