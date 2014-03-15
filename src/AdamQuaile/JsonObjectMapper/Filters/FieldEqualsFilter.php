<?php

namespace AdamQuaile\JsonObjectMapper\Filters;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class FieldEqualsFilter extends PropertyBasedFilter
{
    /**
     * @var mixed
     */
    private $expected;

    public function __construct($field, $expected)
    {
        parent::__construct($field);

        $this->expected = $expected;

    }

    protected function valueMatches($actual)
    {
        return ($this->expected == $actual);
    }

}