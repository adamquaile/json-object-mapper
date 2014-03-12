<?php

namespace AdamQuaile\JsonObjectMapper\Tests\Entity;

/**
 * Dummy book class used to test hydration of json files
 *
 * @package AdamQuaile\JsonObjectMapper\Tests\Entity
 */
class Book
{
    private $title;

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }


}