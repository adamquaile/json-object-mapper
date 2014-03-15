<?php

namespace AdamQuaile\JsonObjectMapper\Filters;

/**
 * Matches an object property based on PCRE pattern
 *
 * @author Adam Quaile <adamquaile@gmail.com>
 */
class PatternMatchesFilter extends PropertyBasedFilter
{
    /**
     * @var mixed
     */
    private $pattern;

    public function __construct($field, $pattern)
    {
        parent::__construct($field);
        
        $this->pattern = $pattern;

    }

    protected function valueMatches($actual)
    {
        return 1 === preg_match($this->pattern, $actual);
    }

}