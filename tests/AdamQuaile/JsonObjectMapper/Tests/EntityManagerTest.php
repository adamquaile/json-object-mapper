<?php

namespace AdamQuaile\JsonObjectMapper\Tests;

use AdamQuaile\JsonObjectMapper\EntityManager;
use AdamQuaile\JsonObjectMapper\Tests\Entity\Book;
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

    public function testFirstLevelAssociation()
    {
        $entity = $this->em->find('books/1984');
        $this->assertEquals('George Orwell', $this->accessor->getValue($entity, 'author.name', '', true));
    }

    public function testArrayOfAssociations()
    {
        $author = $this->em->find('authors/george-orwell');

        $this->assertCount(2, $this->accessor->getValue($author, 'books'));
        $this->assertEquals('1984',         $this->accessor->getValue($author, 'books[0].title', false, true));
        $this->assertEquals('Animal Farm',  $this->accessor->getValue($author, 'books[1].title', false, true));

    }

    public function testSimpleOneFieldEquality()
    {
        $books = $this->em->findAll('books', $this->em->query()->equals('title', '1984'));
        $this->assertCount(1, $books);

        $this->assertEquals('1984', $this->accessor->getValue($books[0], 'title'));
    }
    public function testSimpleCallbackSearch()
    {
        $books = $this->em->findAll('books', $this->em->query()->callback(function(Book $book) {
            return $book->getTitle() == '1984';
        }));
        $this->assertCount(1, $books);
        $this->assertEquals('1984', $this->accessor->getValue($books[0], 'title'));
    }

    public function testMetaFileNotReturned()
    {
        $books = $this->em->findAll('books', $this->em->query()->equals('id', 'books/_meta'));
        $this->assertCount(0, $books);
    }

    public function testNumericItemIdIsPreservedUsingFind()
    {
        $book = $this->em->find('books/1984');
        $this->assertEquals('books/1984', $this->accessor->getValue($book, 'id'));
    }
    public function testNumericItemIdIsPreservedUsingFindAll()
    {
        $books = $this->em->findAll('books', $this->em->query()->equals('title', '1984'));
        $this->assertEquals('books/1984', $this->accessor->getValue($books[0], 'id'));
    }

    public function testCanFindItemWithoutOrderPrefix()
    {
        $item = $this->em->find('ordered/first-item');
        $this->assertTrue(is_object($item));
    }

    /**
     * @depends testCanFindItemWithoutOrderPrefix
     */
    public function testItemWithNumericPrefixExcludesPrefix()
    {
        $item = $this->em->find('ordered/first-item');
        $this->assertEquals('ordered/first-item', $this->accessor->getValue($item, 'id'));
    }

    public function testItemsExcludePrefix()
    {
        $items = $this->em->findAll('ordered');
        $this->assertCount(4, $items);

        $ids = [
            'ordered/first-item',
            'ordered/second-item',
            'ordered/third-item',
            'ordered/fourth-item'
        ];

        $this->assertTrue(in_array($this->accessor->getValue($items[0], 'id'), $ids));
        $this->assertTrue(in_array($this->accessor->getValue($items[1], 'id'), $ids));
        $this->assertTrue(in_array($this->accessor->getValue($items[2], 'id'), $ids));
        $this->assertTrue(in_array($this->accessor->getValue($items[3], 'id'), $ids));
    }

    /**
     * @depends testItemsExcludePrefix
     */
    public function testUsesNaturalOrdering()
    {
        $items = $this->em->findAll('ordered');
        $this->assertCount(4, $items);
        $this->assertEquals('ordered/first-item',   $this->accessor->getValue($items[0], 'id'));
        $this->assertEquals('ordered/second-item',  $this->accessor->getValue($items[1], 'id'));
        $this->assertEquals('ordered/third-item',   $this->accessor->getValue($items[2], 'id'));
        $this->assertEquals('ordered/fourth-item',  $this->accessor->getValue($items[3], 'id'));

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