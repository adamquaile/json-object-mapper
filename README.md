# JSON Object Mapper

Small library to read JSON files from a directory and turn them into PHP objects. Designed for simple content authoring and model separation by developers without the need for a whole DBMS.

**Not intended as any kind of DBMS, or application writable persistence layer**

## Installation

    composer require adamquaile/json-object-mapper

## Usage

**Full documentation [here](docs)**

    <?php

    require __DIR__.'/vendor/autoload.php';

    $manager = new \AdamQuaile\JsonObjectMapper\EntityManager('/path/to/storage');

    // Either
    $book = $manager->find('books/1984');
    $book->isbn;
    $book->getTitle(); // etc..

    // or

    $books = $manager->findAll('sub-folder');
    $books[0]->isbn
    $books[0]->getTitle() // etc

## Main Features

 - Tiny / simple [query API](docs/02-Querying.md)
 - Can either [map to objects you define](docs/03-Custom_Mappings.md], or use standard `stdObject`


