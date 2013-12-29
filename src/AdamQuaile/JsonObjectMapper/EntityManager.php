<?php

namespace AdamQuaile\JsonObjectMapper;

use Symfony\Component\Finder\Finder;

class EntityManager
{
    private $location;

    public function __construct($location)
    {
        $this->location = $location;
    }

    public function find($id)
    {
        $filename = $this->location . DIRECTORY_SEPARATOR . $id . '.json';

        return self::fromJsonWithId($id, file_get_contents($filename));
    }
    public function findAll($namespace)
    {
        $finder = new Finder();
        $finder->files()->in($this->location . '/' . $namespace)->name('*.json');

        $entities = [];
        foreach ($finder as $file) {
            $realPath = $file->getRealPath();
            $id = str_replace($this->location, '', $this->location);
            $entities[] = self::fromJson(file_get_contents($realPath));
        }

        return $entities;
    }

    public static function fromJsonWithId($id, $jsonString)
    {
        return new Entity($id, json_decode($jsonString, true));
    }

    public function __call($key, $args)
    {
        return $this->data[$key];
    }
}