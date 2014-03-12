<?php

namespace AdamQuaile\JsonObjectMapper;

use Symfony\Component\Finder\Finder;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;

class EntityManager
{
    private $location;


    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    private $accessor;

    public function __construct($location)
    {
        $this->location = $location;
        $this->accessor = PropertyAccess::createPropertyAccessor();
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
            $entities[] = $this->fromJsonWithId($id, file_get_contents($realPath));
        }

        return $entities;
    }

    public function fromJsonWithId($id, $jsonString)
    {
        $data = json_decode($jsonString, true);
        $data['id'] = $id;


        $metadata = $this->getMetadataForEntity($id);
        $classToHydrate = $metadata['class'];

        return $this->hydrateObject($classToHydrate, $data);

    }

    private function getMetadataForEntity($id)
    {
        return ['class' => '\AdamQuaile\JsonObjectMapper\Entity'];
    }

    public function hydrateObject($className, $data)
    {
        $reflectionClass = new \ReflectionClass($className);
        $object = $reflectionClass->newInstanceWithoutConstructor();

        foreach ($data as $key => $value) {
            try {
                $this->accessor->setValue($object, $key, $value);
            } catch (NoSuchPropertyException $e) {
                $object->$key = $value;
            }
        }

        return $object;


    }

}