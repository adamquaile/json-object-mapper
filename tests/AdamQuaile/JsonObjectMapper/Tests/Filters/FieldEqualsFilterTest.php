<?php

namespace AdamQuaile\JsonObjectMapper\Tests\Filters;

use AdamQuaile\JsonObjectMapper\Filters\FieldEqualsFilter;

class FieldEqualsFilterTest extends \PHPUnit_Framework_TestCase
{

    private $object;


    public function setUp()
    {
        $this->object = (object) [
            'key' => 'value',
            'nested' => (object) [
                'key' => 'another value'
            ]
        ];
    }

    public function testEqualFieldSucceeds()
    {
        $filter = new FieldEqualsFilter('key', 'value');
        $this->assertTrue($filter->matchesFilter($this->object));
    }

    public function testEqualFieldFails()
    {
        $filter = new FieldEqualsFilter('key', 'incorrect value');
        $this->assertFalse($filter->matchesFilter($this->object));
    }

}