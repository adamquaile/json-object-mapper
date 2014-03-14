<?php

namespace AdamQuaile\JsonObjectMapper;

use Symfony\Component\Finder\Finder;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class to handle searching, retrieving and mapping objects
 * from JSON files.
 *
 * See docs/02-Querying.md for usage
 *
 * @author Adam Quaile <adamquaile@gmail.com>
 */
class EntityManager
{
    /**
     * Base location for all JSON files
     * @var string
     */
    private $location;

    /**
     * @var object[]
     */
    private $stubObjects = [];

    /**
     * @var object[]
     */
    private $fullObjects = [];

    /**
     * @var string[]
     */
    private $incompleteStubIds = [];

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
        if (!isset($this->fullObjects[$id])) {
            $stub = $this->_find($id);

            // Go through stubs until empty..
            while (count($this->stubObjects) > 0) {

                // Get the first
                $keys = array_keys($this->stubObjects);
                $stub = $this->stubObjects[$keys[0]];

                $this->replaceAssociationsWithObjects($stub);
                $this->markComplete($keys[0], $stub);
            }
        }
        return $this->fullObjects[$id];
    }

    private function replaceAssociationsWithObjects($stub)
    {
        foreach ($this->getPropertiesForId($this->accessor->getValue($stub, 'id')) as $property) {

            $parsedValue = $this->parseAssociations($this->accessor->getValue($stub, $property));
            $this->accessor->setValue($stub, $property, $parsedValue);
        }
    }
    private function getPropertiesForId($id)
    {
        return array_keys($this->arrayFromJsonFile($this->getFilename($id)));
    }

    private function markComplete($id, $object)
    {
        unset($this->stubObjects[$id]);
        $this->fullObjects[$id] = $object;
    }

    private function _find($id)
    {
        if (isset($this->fullObjects[$id])) {
            return $this->fullObjects[$id];
        }
        if (isset($this->stubObjects[$id])) {
            return $this->stubObjects[$id];
        }

        return $this->stubObjects[$id] = $this->getStubFromId($id);
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
            $entities[] = $this->find($id);
        }

        return $entities;
    }

    private function getStubFromId($id)
    {
        $data = $this->arrayFromJsonFile($this->getFilename($id));
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

    private function parseAssociations($value)
    {
        switch (true) {
            case is_string($value):
                $ref = $this->isAssociationRef($value);
                return $ref ? $this->_find($ref) : $value;
            case is_array($value):
                return array_map(array($this, 'parseAssociations'), $value);
            default:
                return $value;
        }
    }

    private function isAssociationRef($value)
    {
        return (mb_strlen($value) > 1) && ('@' === mb_substr($value, 0, 1))
            ? mb_substr($value, 1)
            : false
        ;
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