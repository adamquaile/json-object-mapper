<?php

namespace AdamQuaile\JsonObjectMapper\Tests\Filters;

use PHPUnit_Framework_MockObject_MockObject;

class PropertyBasedFilterTest extends \PHPUnit_Framework_TestCase
{

    public function testMatchesFirstLevel()
    {
        $filter = $this->getMockForAbstractClass(
            'AdamQuaile\JsonObjectMapper\Filters\PropertyBasedFilter',
            [
                'key'
            ]
        );
        $object = (object) ['key' => 'value', 'nested' => (object) ['key' => 'nested value']];

        $filter->expects($this->once())->method('valueMatches')->with('value');
        $filter->matchesFilter($object);
    }

    public function testMatchesNestedValue()
    {
        $filter = $this->getMockForAbstractClass(
            'AdamQuaile\JsonObjectMapper\Filters\PropertyBasedFilter',
            [
                'nested.key'
            ]
        );
        $object = (object) ['key' => 'value', 'nested' => (object) ['key' => 'nested value']];

        $filter->expects($this->once())->method('valueMatches')->with('nested value');
        $filter->matchesFilter($object);
    }

}