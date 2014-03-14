<?php

namespace AdamQuaile\JsonObjectMapper;

use Symfony\Component\Finder\Finder;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class to handle searching, retrieving and mapping objects
 * from JSON files.
 *
 * See docs/02-Querying.md for usage.
 *
 *
 * A note on stubs / full objects:
 *
 * When searching for an object, in order to avoid issues with
 * cyclic associations and infinite recursion, all objects are
 * created as stubs. Each association followed will get another
 * stub object.
 *
 * The code then iterates through each stub and replaces associations
 * with reference to the new object. It is then considered complete.
 *
 * At the end of this process, all the objects are full, with
 * cyclic relationships.
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
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    private $accessor;

    /**
     * Create an entity manager for files in a certain directory
     *
     * @param $location
     */
    public function __construct($location)
    {
        $this->location = realpath($location);
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * Find all objects within a directory / namespace
     *
     * @param $namespace
     * @return array
     */
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

    /**
     * Find a single object by its ID.
     *
     * @param $id
     * @return object
     */
    public function find($id)
    {
        // Return if we already have it
        if (!isset($this->fullObjects[$id])) {

            $this->_find($id);

            // Go through stubs until empty..
            while (count($this->stubObjects) > 0) {

                // Get the first
                $stubID = array_keys($this->stubObjects)[0];
                $stub = $this->stubObjects[$stubID];

                // Iterate, linking associations..
                $this->replaceAssociationsWithObjects($stub);

                // ... and marking as full object
                unset($this->stubObjects[$stubID]);
                $this->fullObjects[$stubID] = $stub;
            }
        }
        return $this->fullObjects[$id];
    }

    /**
     * Iterate through each of the properties loaded from JSON file
     * and replace any relevant values with the objects they
     * should be associated with (either stub or full)
     *
     * @param $stub
     */
    private function replaceAssociationsWithObjects($stub)
    {
        $id = $this->accessor->getValue($stub, 'id');
        $stubProperties = array_keys($this->arrayFromJsonFile($this->getFilename($id)));

        foreach ($stubProperties as $property) {

            $parsedValue = $this->parseAssociations($this->accessor->getValue($stub, $property));
            $this->accessor->setValue($stub, $property, $parsedValue);
        }
    }

    /**
     * Used internally, either the stub object or the full one
     * if we have it.
     *
     * @param $id
     * @return object
     */
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

    /**
     * Load a stub object from an ID
     * @param $id
     * @return object
     */
    private function getStubFromId($id)
    {
        $data = $this->arrayFromJsonFile($this->getFilename($id));
        $data['id'] = $id;


        $metadata = $this->getMetadataForEntity($id);
        $classToHydrate = $metadata['class'];

        return $this->hydrateObject($classToHydrate, $data);

    }

    /**
     * Create an object of the given class and set its properties.
     *
     * Uses Reflection and Symfony's PropertyAccess component
     *
     * @param $className    string
     * @param $data         array
     *
     * @return object
     */
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
     * Get associative array of the JSON file
     * @param $filename
     * @return array
     */
    private function arrayFromJsonFile($filename)
    {
        return json_decode(file_get_contents($filename), true);
    }

    /**
     * Get a list of folders between the base location and the object's
     * directory where potential meta-files may reside.
     *
     * Returns upper-level folders first
     *
     * @param $id
     * @return array
     */
    private function getFoldersInHierarchy($id)
    {
        $folders = [$this->location];
        $parts = explode('/', $id);

        for ($i=0;$i<count($parts)-1;$i++) {
            $folders[] = $folders[$i] . DIRECTORY_SEPARATOR . $parts[$i];
        }

        return $folders;
    }

    /**
     * Get fully merged metadata (currently only class to hydrate)
     * for an object
     *
     * @param $id
     * @return array
     */
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

    /**
     * Replace any matching values with their associations
     *
     * @param $value
     * @return array|object
     */
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

    /**
     * Does a scalar string look like an association reference?
     *
     * @param $value string
     * @return bool|string
     */
    private function isAssociationRef($value)
    {
        return (mb_strlen($value) > 1) && ('@' === mb_substr($value, 0, 1))
            ? mb_substr($value, 1)
            : false
        ;
    }

    /**
     * Get the full filename for an ID
     *
     * @param $id
     * @return string
     */
    private function getFilename($id)
    {
        return $this->location . DIRECTORY_SEPARATOR . $id . '.json';
    }

}