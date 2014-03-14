<?php

namespace AdamQuaile\JsonObjectMapper;

/**
 * Default class used for mapping if none is specified.
 *
 * Used as stdObject except with the always known property of
 * id. This ID is determined by the filename.
 *
 * @author Adam Quaile <adamquaile@gmail.com>
 */
class Entity
{
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

}