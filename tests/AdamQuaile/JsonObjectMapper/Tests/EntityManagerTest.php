<?php

namespace AdamQuaile\JsonObjectMapper\Tests;

use AdamQuaile\JsonObjectMapper\EntityManager;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class EntityManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var PropertyAccessor
     */
    private $accessor;

    public function __construct()
    {
        $this->em = new EntityManager(__DIR__.'/../../../data/');

        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    public function testReadingSingleReturnsEntity()
    {
        $this->assertTrue(is_object($this->getExampleEntity()));
    }

    /**
     * @depends testReadingSingleReturnsEntity
     */
    public function testReadingSingleReturnsCorrectId()
    {
        $this->assertEquals('level1/level2/example', $this->getExampleEntity()->getId());
    }

    /**
     * @depends testReadingSingleReturnsEntity
     */
    public function testReadingSingleReturnsCorrectData()
    {
        $entity = $this->get1984();

        $this->assertObjectHasAttribute('title', $entity);
        $this->assertEquals('1984', $this->accessor->getValue($entity, 'title'));
    }

    public function testReadingSingleReturnsCorrectClassFromMetaFile()
    {
        $entity = $this->get1984();
        $this->assertInstanceOf('AdamQuaile\JsonObjectMapper\Tests\Entity\Book', $entity);
    }
    public function testReadingSingleReturnsCorrectClassFromMetaKey()
    {
        $entity = $this->em->find('books/crime-and-punishment');
        $this->assertInstanceOf('AdamQuaile\JsonObjectMapper\Tests\Entity\OverriddenBook', $entity);
    }


    private function getExampleEntity()
    {
        return $this->em->find('level1/level2/example');
    }
    private function get1984()
    {
        return $this->em->find('books/1984');
    }


    public function testReadingMultiple()
    {
        $this->assertGreaterThan(1, $this->em->findAll('level1/level2'));
    }
}