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
        $this->location = realpath($location);
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    private function getFileContents($id)
    {
        return file_get_contents($this->location . DIRECTORY_SEPARATOR . $id . '.json');
    }



    public function find($id)
    {
        return self::fromJsonWithId($id, $this->getFileContents($id));
    }

    public function findAll($namespace)
    {
        $finder = new Finder();
        $finder->files()->in($this->location . '/' . $namespace)->name('*.json');

        $entities = [];
        foreach ($finder as $file) {
            $realPath = $file->getRealPath();
            $id = str_replace($this->location, '', $realPath);
            $id = ltrim($id, '/');
            $id = preg_replace('/\.json$/', '', $id);
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
        $dataFromFile = json_decode($this->getFileContents($id), true);
        if (array_key_exists('_meta', $dataFromFile)) {
            if (array_key_exists('class', $dataFromFile['_meta'])) {
                $metaClass = $dataFromFile['_meta']['class'];
            }
        }
        if (!isset($metaClass)) {
            $metaClass = '\AdamQuaile\JsonObjectMapper\Entity';
        }

        return ['class' => $metaClass];
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