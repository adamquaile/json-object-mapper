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
        return file_get_contents($this->getFilename($id));
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

    /**
     * @param $filename
     * @return array
     */
    private function arrayFromJsonFile($filename)
    {
        return json_decode(file_get_contents($filename), true);
    }

    private function getFoldersInHierarchy($id)
    {
        $folders = [$this->location];
        $parts = explode('/', $id);

        for ($i=0;$i<count($parts)-1;$i++) {
            $folders[] = $folders[$i] . DIRECTORY_SEPARATOR . $parts[$i];
        }

        return $folders;
    }

    private function getMetadataForEntity($id)
    {

        $currentMetaData = [];

        foreach ($this->getFoldersInHierarchy($id) as $folder) {
            $metaFile = $folder . DIRECTORY_SEPARATOR . '_meta.json';

            if (file_exists($metaFile)) {
                $currentMetaData = array_merge_recursive(
                    $currentMetaData,
                    $this->arrayFromJsonFile($metaFile)
                );
            }
        }

        $dataFromFile = $this->arrayFromJsonFile($this->getFilename($id));

        if (array_key_exists('_meta', $dataFromFile)) {
            $currentMetaData = array_replace_recursive($currentMetaData, $dataFromFile['_meta']);
        }
        if (!isset($currentMetaData['class'])) {
            $currentMetaData['class'] = '\AdamQuaile\JsonObjectMapper\Entity';
        }

        return $currentMetaData;
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

    /**
     * @param $id
     * @return string
     */
    private function getFilename($id)
    {
        return $this->location . DIRECTORY_SEPARATOR . $id . '.json';
    }

}