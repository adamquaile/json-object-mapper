<?php

namespace AdamQuaile\JsonObjectMapper;

class Entity
{
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }



}