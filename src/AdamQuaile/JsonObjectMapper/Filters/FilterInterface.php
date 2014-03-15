<?php

namespace AdamQuaile\JsonObjectMapper\Filters;

interface FilterInterface
{
    public function matchesFilter($object);
}