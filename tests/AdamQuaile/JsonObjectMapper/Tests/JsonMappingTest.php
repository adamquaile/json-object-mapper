<?php

namespace AdamQuaile\JsonObjectMapper\Tests;

use AdamQuaile\JsonObjectMapper\EntityManager;

class JsonMappingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityManager
     */
    private $em;

    public function __construct()
    {
        $this->em = new EntityManager(__DIR__.'/../../../data/');
    }

    public function testReadingSingle()
    {
        $entity = $this->em->find('level1/level2/example');

        $this->assertTrue(is_object($entity));

        $this->assertEquals('level1/level2/example', $entity->getId());

    }
    public function testReadingMultiple()
    {
        $this->assertGreaterThan(1, $this->em->findAll('level1/level2'));
    }
}