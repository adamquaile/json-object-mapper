<?php

namespace AdamQuaile\JsonObjectMapper\Filters;

use Symfony\Component\PropertyAccess\PropertyAccess;

abstract class PropertyBasedFilter implements FilterInterface
{
    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    private $accessor;

    /**
     * @var string
     */
    private $field;

    public function __construct($field)
    {
        $this->field = $field;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    protected abstract function valueMatches($actual);

    public function matchesFilter($object)
    {
        return $this->valueMatches($this->accessor->getValue($object, $this->field, false));
    }

}