<?php

namespace AdamQuaile\JsonObjectMapper\Filters;

/**
 * Filter objects by a callback to allow very
 * expressive queries
 *
 * @author Adam Quaile <adamquaile@gmail.com>
 *
 */
class CallbackFilter implements FilterInterface
{
    /**
     * @var callable
     */
    private $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @param $object
     * @return mixed
     */
    public function matchesFilter($object)
    {
        return call_user_func($this->callback, $object);
    }

}