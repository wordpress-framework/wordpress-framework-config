<?php
namespace Wordpress_Framework\Config\v1\Tests;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;

use Wordpress_Framework\Config\v1\Config;

class Config_Test extends TestCase {
    
    public function setUp() {
        $this->configuration_data = [
            'one' => 'one_data',
            'two' => [
                'two_one' => 'two_one_data',
                'two_two' => [
                    'two_two_one' => 'two_two_one_data'
                ]
            ]
        ];
    }
        
    public function test_load() {
        $config = new Config( $this->configuration_data );
        
        $this->assertEquals( 'one_data', $config->one );
        $this->assertEquals( 'two_one_data', $config->two->two_one );
        $this->assertEquals( 'two_two_one_data', $config->two->two_two->two_two_one );
        $this->assertNull( $config->nonexistent );
    }
    
    public function test_modification() {
        $config = new Config( $this->configuration_data, 'read_and_write' );
        
        $config->one = 'one_data_modificated';
        $this->assertEquals( 'one_data_modificated', $config->one );
        
        $config->two->two_one = 'two_one_data_modificated';
        $this->assertEquals( 'two_one_data_modificated', $config->two->two_one );
        
        $config->two->two_two = [ 'two_two_one' => 'two_two_one_data_modificated' ];
        $this->assertEquals( 'two_two_one_data_modificated', $config->two->two_two->two_two_one );
        
        $config->nonexistent = 'nonexistent_data';
        $this->assertEquals( 'nonexistent_data', $config->nonexistent );
    }
    
    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage This Config is read only
     */
    public function test_no_modification() {
        $config = new Config( $this->configuration_data );
        
        $config->one = 'one_data_modificated';
        $this->assertEquals( 'one_data', $config->one );
        
        $config->two->two_one = 'two_one_data_modificated';
        $this->assertEquals( 'two_one_data', $config->two->two_one );
        
        $config->two->two_two = [ 'two_two_one' => 'two_two_one_data_modificated' ];
        $this->assertEquals( 'two_two_one_data', $config->two->two_two->two_two_one );
        
        $config->nonexistent = 'nonexistent_data';
        $this->assertNull( $config->nonexistent );
    }
    
    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage This Config is read only
     */
    public function test_set_permissions_to_read_only() {
        $config = new Config( $this->configuration_data, 'read_and_write' );
        
        $this->assertFalse( $config->is_read_only() );
        
        $config->one = 'one_data_modificated';
        $this->assertEquals( 'one_data_modificated', $config->one );
        
        $config->two->two_one = 'two_one_data_modificated';
        $this->assertEquals( 'two_one_data_modificated', $config->two->two_one );
        
        $config->two->two_two = [ 'two_two_one' => 'two_two_one_data_modificated' ];
        $this->assertEquals( 'two_two_one_data_modificated', $config->two->two_two->two_two_one );
        
        $config->nonexistent = 'nonexistent_data';
        $this->assertEquals( 'nonexistent_data', $config->nonexistent );
        
        $config->set_permissions_to_read_only();
        
        $this->assertTrue( $config->is_read_only() );
        
        $config->one = 'one_data_modificated_2';
        $this->assertEquals( 'one_data_modificated', $config->one );
        
        $config->two->two_one = 'two_one_data_modificated_2';
        $this->assertEquals( 'two_one_data_modificated', $config->two->two_one );
        
        $config->two->two_two = [ 'two_two_one' => 'two_two_one_data_modificated_3' ];
        $this->assertEquals( 'two_two_one_data_modificated', $config->two->two_two->two_two_one );
        
        $config->nonexistent = 'nonexistent_data_2';
        $this->assertEquals( 'nonexistent_data', $config->nonexistent );
    }
    
    public function test_isset() {
        $config = new Config( $this->configuration_data );
        
        $this->assertTrue( isset( $config->one ) );
        $this->assertTrue( isset( $config->two->two_one ) );
        $this->assertTrue( isset( $config->two->two_two->two_two_one ) );        
        $this->assertFalse( isset( $config->nonexistent ) );
    }
    
    public function test_unset() {
        $config = new Config( $this->configuration_data, 'read_and_write' );
        
        unset( $config->one );
        $this->assertNull( $config->one );
        
        unset( $config->two_one );
        $this->assertNull( $config->two_one );
        
        unset( $config->two->two_two->two_two_one );
        $this->assertNull( $config->two->two_two->two_two_one );
    }
    
    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage This Config is read only
     */
    public function test_no_unset() {
        $config = new Config( $this->configuration_data );
        
        unset( $config->one );
        $this->assertEquals( 'one_data', $config->one );
        
        unset( $config->two_one );
        $this->assertEquals( 'two_one_data', $config->two->two_one );
        
        unset( $config->two->two_two->two_two_one );
        $this->assertEquals( 'two_two_one_data', $config->two->two_two->two_two_one );
    }
    
    /**
     * @group count_tests
     */
    public function test_count() {
        $config = new Config( $this->configuration_data, 'read_and_write' );
        $this->assertEquals( 2, $config->count() );
        $config->three = 'thre_data';
        $this->assertEquals( 3, $config->count() );
    }
    
    /**
     * @group iterator_tests
     */
    public function test_iterator() {
        //top level
        $config = new Config( $this->configuration_data );
        $var = '';
        foreach ( $config as $config_key => $config_value ) {
            if ( is_string( $config_value ) ) {
                $var .= "\nkey = $config_key, value = $config_value";
            }
            
            $this->assertEquals( $config->key(), $config_key );
            $this->assertEquals( $config->current(), $config_value );
        }
        
        $this->assertContains( 'key = one, value = one_data', $var );
        
        //1 nest
        $var = '';
        foreach ( $config->two as $config_key => $config_value ) {
            if ( is_string( $config_value ) ) {
                $var .= "\nkey = $config_key, value = $config_value";
            }
            
            $this->assertEquals( $config->two->key(), $config_key );
            $this->assertEquals( $config->two->current(), $config_value );
        }
        
        $this->assertContains( 'key = two_one, value = two_one_data', $var );
        
        //2 nest
        $var = '';
        foreach ( $config->two->two_two as $config_key => $config_value ) {
            if ( is_string( $config_value ) ) {
                $var .= "\nkey = $config_key, value = $config_value";
            }
            
            $this->assertEquals( $config->two->two_two->key(), $config_key );
            $this->assertEquals( $config->two->two_two->current(), $config_value );
        }
        
        $this->assertContains( 'key = two_two_one, value = two_two_one_data', $var );
    }

    /**
     * @group iterator_tests
     */
    public function test_unset_first_element_during_foreach_does_not_skip_an_element() {
        $config = new Config( [
            'first'  => [1],
            'second' => [2],
            'third'  => [3]
        ], 'read_and_write' );
        
        $key_list = [];
        foreach ( $config as $config_key => $config_value ) {
            $key_list[] = $config_key;
            
            if ($config_key === 'first') {
                unset( $config->$config_key );
            }
        }
        
        $this->assertEquals('first', $key_list[0]);
        $this->assertEquals('second', $key_list[1]);
        $this->assertEquals('third', $key_list[2]);
    }
    
    /**
     * @group iterator_tests
     */
    public function test_unset_middle_element_during_foreach_does_not_skip_an_element() {
        $config = new Config( [
            'first'  => [1],
            'second' => [2],
            'third'  => [3]
        ], 'read_and_write' );
        
        $key_list = [];
        foreach ( $config as $config_key => $config_value ) {
            $key_list[] = $config_key;
            
            if ($config_key === 'second') {
                unset( $config->$config_key );
            }
        }
        
        $this->assertEquals('first', $key_list[0]);
        $this->assertEquals('second', $key_list[1]);
        $this->assertEquals('third', $key_list[2]);
    }
    
    /**
     * @group iterator_tests
     */
    public function test_unset_last_element_during_foreach_does_not_skip_an_element() {
        $config = new Config( [
            'first'  => [1],
            'second' => [2],
            'third'  => [3]
        ], 'read_and_write' );
        
        $key_list = [];
        foreach ( $config as $config_key => $config_value ) {
            $key_list[] = $config_key;
            
            if ($config_key === 'third') {
                unset( $config->$config_key );
            }
        }
        
        $this->assertEquals('first', $key_list[0]);
        $this->assertEquals('second', $key_list[1]);
        $this->assertEquals('third', $key_list[2]);
    }
    
    public function test_clone() {
        $config = new Config( $this->configuration_data, 'read_and_write' );
        $clone_of_config = clone $config;
        
        $clone_of_config->one = 'one_data_modificated';
        $this->assertEquals( 'one_data_modificated', $clone_of_config->one );
        $this->assertEquals( 'one_data', $config->one );
        
        $clone_of_config->two->two_one = 'two_one_data_modificated';
        $this->assertEquals( 'two_one_data_modificated', $clone_of_config->two->two_one );
        $this->assertEquals( 'two_one_data', $config->two->two_one );
        
        $clone_of_config->two->two_two = [ 'two_two_one' => 'two_two_one_data_modificated' ];
        $this->assertEquals( 'two_two_one_data_modificated', $clone_of_config->two->two_two->two_two_one );
        $this->assertEquals( 'two_two_one_data', $config->two->two_two->two_two_one );
        
        $clone_of_config->nonexistent = 'nonexistent_data';
        $this->assertEquals( 'nonexistent_data', $clone_of_config->nonexistent );
        $this->assertNull( $config->nonexistent );
    }
    
    /**
     * @group to_array_tests
     */
    public function test_to_array() {
        $config = new Config( $this->configuration_data );
        $config_array = $config->to_array();
        
        $this->assertEquals( $config_array, $this->configuration_data );
        $this->assertEquals( 'one_data', $config_array['one'] );
        $this->assertEquals( 'two_one_data', $config_array['two']['two_one'] );
        $this->assertEquals( 'two_two_one_data', $config_array['two']['two_two']['two_two_one'] );
    }
    
    /**
     * @group to_array_tests
     */
    public function test_to_array_supports_objects() {
        $configData = [
            'one' => new \stdClass(),
            'two' => [
                'two_one' => new \stdClass(),
                'two_two' => new \stdClass()
                ]
        ];
        
        $config = new Config( $configData );
        $this->assertEquals( $config->to_array(), $configData );
        $this->assertInstanceOf( 'stdClass', $config->one );
        $this->assertInstanceOf( 'stdClass', $config->two->two_one );
        $this->assertInstanceOf( 'stdClass', $config->two->two_two );
    }
    
    /**
     * @group to_array_tests
     */
    public function test_to_array_does_not_disturb_internal_iterator() {
        $config = new Config( range( 1, 12 ) );
        $config->rewind();
        
        $this->assertEquals( 1, $config->current() );
        $config->to_array();
        $this->assertEquals( 1, $config->current() );
    }
    
    /**
     * @group to_array_tests
     */
    public function test_load_invalid_key() {
        $config = new Config( [ 
            ' ' => 'invalid_key_1', 
            ''=>'invalid_key_2'
        ] );
        
        $config_array = $config->to_array();
        $this->assertContains( 'invalid_key_1', $config_array[' '] );
        $this->assertContains( 'invalid_key_2', $config_array[''] );
    }
    
    /**
     * @group merge_tests
     * @expectedException RuntimeException
     * @expectedExceptionMessage This Config is read only
     */
    public function test_merge_no_modification() {
        $merge_config_a = [
            'one' => 1,
            'two' => 2,
        ];
        
        $merge_config_b = [
            'one' => 3,
            'two' => 4,
        ];
        
        $config_a = new Config( $merge_config_a );
        $config_b = new Config( $merge_config_b );
        $config_a->merge( $config_b );
        
        $this->assertEquals( 1, $config_a->one );
        $this->assertEquals( 2, $config_a->two );
    }
    
    /**
     * @group merge_tests
     */
    public function test_merge() {
        $merge_config_a = [
            'one' => 1,
            'two' => 2,
            'three' => 'three_data',
            'numerical' => [
                'numerical_first',
                'numerical_second',
                [
                    'numerical_third_first'
                ]
            ],
            'misaligned' => [
                2 => 'misaligned_2',
                3 => 'misaligned_3'
            ],
            'mixed' => [
                'mixed_one' => 'mixed_one_data'
            ],
            'replace_assoc' => [
                'replace_assoc_one' => 'replace_assoc_one_data'
            ],
            'replace_numerical' => [
                'replace_numerical_one'
            ]
        ];
        $merge_config_b = [
            'one' => 3,
            'three' => 'three_data_modificated',
            'numerical' => [
                'numerical_fourth',
                'numerical_fifth',
                [
                    'numerical_sixth_first'
                ]
            ],
            'misaligned' => [
                3 => 'misaligned_3_modificated'
            ],
            'mixed' => [
                false
            ],
            'replace_assoc' => null,
            'replace_numerical' => true
        ];
        
        $config_a = new Config( $merge_config_a, 'read_and_write' );
        $config_b = new Config( $merge_config_b, 'read_and_write' );
        $config_a->merge( $config_b );
        // config->
        $this->assertEquals( 3, $config_a->one );
        $this->assertEquals( 2, $config_a->two );
        $this->assertEquals( 'three_data_modificated', $config_a->three );
        
        // config->numerical->
        $this->assertInstanceOf( 'Wordpress_Framework\Config\v1\Config', $config_a->numerical);
        $this->assertEquals( 'numerical_first', $config_a->numerical->{0} );
        $this->assertEquals( 'numerical_second', $config_a->numerical->{1} );
        
        // config->numerical->{2}->
        $this->assertInstanceOf( 'Wordpress_Framework\Config\v1\Config', $config_a->numerical->{2} );
        $this->assertEquals( 'numerical_third_first', $config_a->numerical->{2}->{0} );
        $this->assertEquals( null, $config_a->numerical->{2}->{1} );
        
        // config->numerical-> 
        $this->assertEquals( 'numerical_fourth', $config_a->numerical->{3} );
        $this->assertEquals( 'numerical_fifth', $config_a->numerical->{4} );
        
        // config->numerical->{5}
        $this->assertInstanceOf( 'Wordpress_Framework\Config\v1\Config', $config_a->numerical->{5} );
        $this->assertEquals( 'numerical_sixth_first', $config_a->numerical->{5}->{0} );
        $this->assertEquals( null, $config_a->numerical->{5}->{1} );
        // config->misaligned
        $this->assertInstanceOf( 'Wordpress_Framework\Config\v1\Config', $config_a->misaligned );
        $this->assertEquals( 'misaligned_2', $config_a->misaligned->{2} );
        $this->assertEquals( 'misaligned_3', $config_a->misaligned->{3} );
        $this->assertEquals( 'misaligned_3_modificated', $config_a->misaligned->{4} );
        
        $this->assertEquals( null, $config_a->misaligned->{0} );
        // config->mixed
        $this->assertInstanceOf( 'Wordpress_Framework\Config\v1\Config', $config_a->mixed );
        $this->assertEquals( 'mixed_one_data', $config_a->mixed->mixed_one );
        $this->assertFalse( $config_a->mixed->{0} );
        $this->assertEquals( null, $config_a->mixed->{1} );
        
        // config->replace_assoc
        $this->assertEquals( null, $config_a->replace_assoc );
        
        // config->replace_numerical
        $this->assertTrue( $config_a->replace_numerical );
    }
    
    /**
     * @group merge_tests
     */
    public function test_merge_set_permission_flag_at_all_levels() {
        $config_a = new Config( [ 'one' => [ 'one_one' => 'yes' ], 'two' => 'yes' ] );
        $config_b = new Config( [], 'read_and_write' );
        $config_b->merge( $config_a );
        
        $config_b->two = 'no';
        $this->assertEquals( 'no', $config_b->two );
        $config_b->one->one_one = 'no';
        $this->assertEquals( 'no', $config_b->one->one_one );
    }
    
    /**
     * @group merge_tests
     */
    public function test_merge_replacing_unnamed_config_data() {
        $configuration_data_a = [
            'one' => true,
            'two' => 'two_data',
            'list' => [ 'a', 'b', 'c' ],
            'specific_one' => 1
        ];
        $configuration_data_b = [
            'one' => false,
            'two' => 'two_data_modificated',
            'list' => [ 'd', 'e' ],
            'specific_two' => 2
        ];
        $configuration_data_result = [
            'one' => false,
            'two' => 'two_data_modificated',
            'list' => [ 'a', 'b', 'c', 'd', 'e' ],
            'specific_one' => 1,
            'specific_two' => 2
        ];
        $config_a = new Config( $configuration_data_a, 'read_and_write');
        $config_b = new Config( $configuration_data_b, 'read_and_write');
        $config_a->merge( $config_b );
        
        $this->assertEquals( $configuration_data_result, $config_a->to_array() );
    }
    
    public function test_ensure_clone_does_not_keep_nested_references() {
        $config_a = new Config( [ 'one' => [ 'nested' => 'nested_value' ] ], 'read_and_write' );
        $config_b = clone $config_a;
        $config_b->merge( new Config( ['one' => [ 'nested' => 'nested_value_override'] ], 'read_and_write' ) );
        
        $this->assertEquals( 'nested_value_override', $config_b->one->nested );
        $this->assertEquals( 'nested_value', $config_a->one->nested );
    }
    
    /**
     * @group count_tests
     * @depends test_count
     */
    public function test_count_after_merge() {
        $configuration_data_a = [
            'one' => true,
            'two' => 'two_data',
            'list' => [ 'a', 'b', 'c' ],
            'specific_one' => 1
        ];
        
        $configuration_data_b = [
            'one' => false,
            'two' => 'two_data_modificated',
            'list' => [ 'd', 'e' ],
            'specific_two' => 2
        ];
        
        $config = new Config( $configuration_data_a , 'read_and_write' );
        $this->assertEquals( 4, $config->count() );
        
        $config->merge( new Config( $configuration_data_b ) );
        $this->assertEquals( 5, $config->count() );
    }
}