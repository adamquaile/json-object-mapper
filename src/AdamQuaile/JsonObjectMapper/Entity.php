<?php

namespace AdamQuaile\JsonObjectMapper;

class Entity
{
    private $id;
    private $data;

    public function __construct($id, $data)
    {
        $this->id = $id;
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }




}