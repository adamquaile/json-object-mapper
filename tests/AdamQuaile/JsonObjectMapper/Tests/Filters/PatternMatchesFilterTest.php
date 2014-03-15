<?php

namespace AdamQuaile\JsonObjectMapper\Tests\Filters;

use AdamQuaile\JsonObjectMapper\Filters\FieldEqualsFilter;
use AdamQuaile\JsonObjectMapper\Filters\PatternMatchesFilter;

class PatternMatchesFilterTest extends \PHPUnit_Framework_TestCase
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
        $filter = new PatternMatchesFilter('key', '/v...e/');
        $this->assertTrue($filter->matchesFilter($this->object));
    }

    public function testEqualFieldFails()
    {
        $filter = new FieldEqualsFilter('key', '/[0-9]/');
        $this->assertFalse($filter->matchesFilter($this->object));
    }

}