<?php

namespace AdamQuaile\JsonObjectMapper;

use AdamQuaile\JsonObjectMapper\Filters\CallbackFilter;
use AdamQuaile\JsonObjectMapper\Filters\FieldEqualsFilter;
use AdamQuaile\JsonObjectMapper\Filters\FilterInterface;
use AdamQuaile\JsonObjectMapper\Filters\PatternMatchesFilter;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class Query
{

    /**
     * @var FilterInterface[]
     */
    private $filters = [];

    /**
     * Specify that object property must equal value
     *
     * @param $field
     * @param $value
     * @return $this
     */
    public function equals($field, $value)
    {
        $this->addFilter(new FieldEqualsFilter($field, $value));
        return $this;
    }

    /**
     * Specify that object property must match pattern
     *
     * @param $field
     * @param $pattern
     * @return $this
     */
    public function matches($field, $pattern)
    {
        $this->addFilter(new PatternMatchesFilter($field, $pattern));
        return $this;
    }

    /**
     * Filter entities by a user-defined function
     *
     * @param callable $callback
     * @return $this
     */
    public function callback(callable $callback)
    {
        $this->addFilter(new CallbackFilter($callback));
        return $this;
    }


    /**
     * Add to the list of filters that will be run
     *
     * @param FilterInterface $filterInterface
     */
    private function addFilter(FilterInterface $filterInterface)
    {
        $this->filters[] = $filterInterface;
    }

    /**
     * @param array $objects
     * @return array
     */
    public function getFilteredEntities($objects = [])
    {
        return array_filter($objects, [$this, 'filterObject']);
    }

    /**
     * Does a single object match all filters
     *
     * @param $object
     * @return bool
     */
    private function filterObject($object)
    {
        foreach ($this->filters as $filter) {
            if (!$filter->matchesFilter($object)) {
                return false;
            }
        }

        return true;
    }

}